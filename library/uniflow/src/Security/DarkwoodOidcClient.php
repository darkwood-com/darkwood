<?php

declare(strict_types=1);

namespace App\Security;

use Jose\Component\Checker\AudienceChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\IssuedAtChecker;
use Jose\Component\Checker\IssuerChecker;
use Jose\Component\Checker\NotBeforeChecker;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use RuntimeException;
use Symfony\Component\Clock\Clock;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function count;
use function is_array;
use function is_int;
use function is_string;
use function parse_url;
use function sprintf;
use function str_ends_with;

/** Darkwood OIDC Authorization Code client with PKCE S256, based on Darkwaar. */
final readonly class DarkwoodOidcClient
{
    private const string SESSION_STATE = 'darkwood_oidc_state';
    private const string SESSION_NONCE = 'darkwood_oidc_nonce';
    private const string SESSION_VERIFIER = 'darkwood_oidc_code_verifier';

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        #[Autowire('%env(default::DARKWOOD_OIDC_ISSUER)%')]
        private ?string $issuer,
        #[Autowire('%env(default::DARKWOOD_OIDC_CLIENT_ID)%')]
        private ?string $clientId,
        #[Autowire('%env(default::DARKWOOD_OIDC_CLIENT_SECRET)%')]
        private ?string $clientSecret,
        #[Autowire('%env(default::DARKWOOD_OIDC_REDIRECT_URI)%')]
        private ?string $redirectUri,
    ) {}

    public function supportsDelegatedLogin(): bool
    {
        return '' !== trim((string) $this->issuer)
            && '' !== trim((string) $this->clientId)
            && '' !== trim((string) $this->clientSecret)
            && '' !== trim((string) $this->redirectUri);
    }

    public function getIssuer(): string
    {
        return $this->normalizedIssuer();
    }

    public function buildAuthorizeUrl(Request $request): string
    {
        $this->assertSecureUrl((string) $this->redirectUri, 'OIDC callback');
        $discovery = $this->getDiscovery();
        $state = bin2hex(random_bytes(16));
        $nonce = bin2hex(random_bytes(16));
        $verifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        $session = $request->getSession();
        $session->set(self::SESSION_STATE, $state);
        $session->set(self::SESSION_NONCE, $nonce);
        $session->set(self::SESSION_VERIFIER, $verifier);
        $session->set('darkwood_oidc_deadline', time() + 600);

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'openid email',
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
        ]);

        return $discovery['authorization_endpoint'] . '?' . $query;
    }

    /**
     * @return array{id: string, email: string, email_verified: bool}
     */
    public function handleCallback(Request $request): array
    {
        $session = $request->getSession();
        $expectedState = $session->remove(self::SESSION_STATE);
        $nonce = $session->remove(self::SESSION_NONCE);
        $verifier = $session->remove(self::SESSION_VERIFIER);
        $deadline = $session->remove('darkwood_oidc_deadline');
        $error = $request->query->getString('error');
        if ('' !== $error) {
            throw new RuntimeException(sprintf('OIDC authorization failed: %s.', $error));
        }

        $responseIss = $request->query->getString('iss');
        if ('' !== $responseIss && !$this->issuersMatch($responseIss, $this->normalizedIssuer())) {
            throw new RuntimeException('Invalid authorization response issuer.');
        }

        $state = $request->query->getString('state');
        $code = $request->query->getString('code');

        if (!is_int($deadline) || $deadline < time() || '' === $code || '' === $state || !is_string($expectedState) || !hash_equals($expectedState, $state)) {
            throw new RuntimeException('Invalid OIDC state.');
        }
        if (!is_string($nonce) || '' === $nonce || !is_string($verifier) || '' === $verifier) {
            throw new RuntimeException('OIDC session parameters missing.');
        }

        $discovery = $this->getDiscovery();
        $response = $this->httpClient->request('POST', $discovery['token_endpoint'], [
            'max_redirects' => 0,
            'timeout' => 8,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
                'code_verifier' => $verifier,
            ],
        ]);

        /** @var array<string, mixed> $tokens */
        $tokens = $response->toArray(false);
        if (!isset($tokens['id_token']) || !is_string($tokens['id_token'])) {
            $errorCode = is_string($tokens['error'] ?? null) ? $tokens['error'] : 'invalid_token_response';

            throw new RuntimeException(sprintf('OIDC token exchange failed: %s.', $errorCode));
        }

        $claims = $this->validateIdToken($tokens['id_token'], $discovery['jwks_uri'], $nonce);

        return [
            'id' => $claims['sub'],
            'email' => is_string($claims['email'] ?? null) ? $claims['email'] : '',
            'email_verified' => true === ($claims['email_verified'] ?? false),
        ];
    }

    /**
     * @return array{authorization_endpoint: string, token_endpoint: string, jwks_uri: string, issuer: string}
     */
    private function getDiscovery(): array
    {
        $issuer = $this->normalizedIssuer();
        $this->assertSecureUrl($issuer, 'OIDC issuer');

        $cacheKey = $this->discoveryCacheKey($issuer);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($issuer): array {
            $item->expiresAfter(3600);
            $response = $this->httpClient->request('GET', $issuer . '/.well-known/openid-configuration', ['max_redirects' => 0, 'timeout' => 8]);
            /** @var array<string, mixed> $config */
            $config = $response->toArray(false);

            foreach (['authorization_endpoint', 'token_endpoint', 'jwks_uri', 'issuer'] as $key) {
                if (!isset($config[$key]) || !is_string($config[$key]) || '' === $config[$key]) {
                    throw new RuntimeException(sprintf('OIDC discovery missing "%s".', $key));
                }
            }

            if (!$this->issuersMatch($config['issuer'], $issuer)) {
                throw new RuntimeException('OIDC discovery issuer does not match configured issuer.');
            }

            foreach (['authorization_endpoint', 'token_endpoint', 'jwks_uri'] as $endpoint) {
                $this->assertSecureUrl($config[$endpoint], $endpoint);
                $parts = parse_url($config[$endpoint]);
                $issuerParts = parse_url($issuer);
                foreach (['scheme', 'host', 'port'] as $component) {
                    if (($parts[$component] ?? null) !== ($issuerParts[$component] ?? null)) {
                        throw new RuntimeException('OIDC endpoints must use the issuer origin.');
                    }
                }
                if (isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) {
                    throw new RuntimeException('OIDC endpoint URL is invalid.');
                }
            }

            return [
                'authorization_endpoint' => $config['authorization_endpoint'],
                'token_endpoint' => $config['token_endpoint'],
                'jwks_uri' => $config['jwks_uri'],
                'issuer' => rtrim($config['issuer'], '/'),
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function validateIdToken(string $idToken, string $jwksUri, string $expectedNonce): array
    {
        $jws = $this->verifyIdTokenSignature($idToken, $jwksUri);

        /** @var array<string, mixed> $claims */
        $claims = json_decode((string) $jws->getPayload(), true, 512, JSON_THROW_ON_ERROR);

        $clock = new Clock();
        $checkers = new ClaimCheckerManager([
            new IssuedAtChecker(clock: $clock, allowedTimeDrift: 60),
            new NotBeforeChecker(clock: $clock, allowedTimeDrift: 60),
            new ExpirationTimeChecker(clock: $clock, allowedTimeDrift: 60),
            new AudienceChecker((string) $this->clientId),
            new IssuerChecker([$this->normalizedIssuer()]),
        ]);
        $checkers->check($claims, ['iat', 'exp', 'aud', 'iss']);

        if (!is_int($claims['exp']) || $claims['exp'] <= time() || !isset($claims['sub']) || !is_string($claims['sub']) || '' === $claims['sub']) {
            throw new RuntimeException('ID token missing sub claim.');
        }

        $this->assertAuthorizedParty($claims);

        $tokenNonce = $claims['nonce'] ?? null;
        if (!is_string($tokenNonce) || !hash_equals($expectedNonce, $tokenNonce)) {
            throw new RuntimeException('Invalid ID token nonce.');
        }

        return $claims;
    }

    private function verifyIdTokenSignature(string $idToken, string $jwksUri): JWS
    {
        $serializer = new JWSSerializerManager([new CompactSerializer()]);
        $jws = $serializer->unserialize($idToken);
        $verifier = new JWSVerifier(new AlgorithmManager([new RS256()]));

        $jwkSet = $this->loadJwkSet($jwksUri);
        if ($verifier->verifyWithKeySet($jws, $jwkSet, 0)) {
            return $jws;
        }

        $this->cache->delete($this->jwksCacheKey($jwksUri));
        $jwkSet = $this->loadJwkSet($jwksUri);
        if ($verifier->verifyWithKeySet($jws, $jwkSet, 0)) {
            return $jws;
        }

        throw new RuntimeException('Invalid ID token signature.');
    }

    private function loadJwkSet(string $jwksUri): JWKSet
    {
        /** @var array{keys: list<array<string, mixed>>} $jwks */
        $jwks = $this->cache->get($this->jwksCacheKey($jwksUri), function (ItemInterface $item) use ($jwksUri): array {
            $item->expiresAfter(3600);
            $response = $this->httpClient->request('GET', $jwksUri, ['max_redirects' => 0, 'timeout' => 8]);

            return $response->toArray(false);
        });

        if (!isset($jwks['keys']) || !is_array($jwks['keys']) || [] === $jwks['keys']) {
            throw new RuntimeException('OIDC JWKS is empty.');
        }

        return JWKSet::createFromKeyData($jwks);
    }

    /**
     * @param array<string, mixed> $claims
     */
    private function assertAuthorizedParty(array $claims): void
    {
        $aud = $claims['aud'] ?? null;
        $azp = $claims['azp'] ?? null;
        $audIsList = is_array($aud) && count($aud) > 1;

        if ($audIsList && (!is_string($azp) || '' === $azp)) {
            throw new RuntimeException('ID token with multiple audiences requires azp.');
        }

        if (is_string($azp) && '' !== $azp && !hash_equals((string) $this->clientId, $azp)) {
            throw new RuntimeException('Invalid ID token azp claim.');
        }
    }

    private function assertSecureUrl(string $url, string $label): void
    {
        $parts = parse_url($url);
        if (!is_array($parts) || !isset($parts['scheme'], $parts['host'])) {
            throw new RuntimeException(sprintf('Invalid %s URL.', $label));
        }

        $scheme = strtolower((string) $parts['scheme']);
        $host = strtolower((string) $parts['host']);

        if ('https' === $scheme) {
            return;
        }

        if ('http' === $scheme && $this->isLoopbackHost($host)) {
            return;
        }

        throw new RuntimeException(sprintf('%s must use HTTPS (except loopback hosts).', $label));
    }

    private function isLoopbackHost(string $host): bool
    {
        return '127.0.0.1' === $host
            || '::1' === $host
            || 'localhost' === $host
            || str_ends_with($host, '.localhost');
    }

    private function normalizedIssuer(): string
    {
        return rtrim(trim((string) $this->issuer), '/');
    }

    private function issuersMatch(string $left, string $right): bool
    {
        return hash_equals(rtrim($left, '/'), rtrim($right, '/'));
    }

    private function discoveryCacheKey(string $issuer): string
    {
        return 'darkwood_oidc_discovery_' . sha1($issuer);
    }

    private function jwksCacheKey(string $jwksUri): string
    {
        return 'darkwood_oidc_jwks_' . sha1($jwksUri);
    }
}
