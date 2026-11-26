<?php

declare(strict_types=1);

namespace App\Tests;

use App\Security\DarkwoodOidcClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class DarkwoodOidcClientTest extends TestCase
{
    public function testCallbackChecksSignedIdentityAndConsumesState(): void
    {
        $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
        $rsa = openssl_pkey_get_details($key)['rsa'];
        $metadata = [
            'issuer' => 'https://id.example',
            'authorization_endpoint' => 'https://id.example/authorize',
            'token_endpoint' => 'https://id.example/token',
            'jwks_uri' => 'https://id.example/jwks',
        ];
        $query = [];
        $encode = static fn (string $value): string => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
        $http = new MockHttpClient(static function (string $method, string $url, array $options) use (&$query, $encode, $key, $rsa, $metadata): MockResponse {
            $payload = match ($url) {
                'https://id.example/.well-known/openid-configuration' => $metadata,
                'https://id.example/jwks' => [
                    'keys' => [['kty' => 'RSA', 'kid' => 'test', 'n' => $encode($rsa['n']), 'e' => $encode($rsa['e'])]],
                ],
                'https://id.example/token' => (static function () use ($query, $encode, $key, $method, $options): array {
                    self::assertSame('POST', $method);
                    parse_str($options['body'], $body);
                    self::assertSame($query['code_challenge'], $encode(hash('sha256', $body['code_verifier'], true)));
                    $token = $encode('{"alg":"RS256","kid":"test"}') . '.' . $encode(json_encode([
                        'iss' => 'https://id.example',
                        'sub' => 'test-id',
                        'aud' => 'product',
                        'iat' => time(),
                        'exp' => time() + 60,
                        'nonce' => $query['nonce'],
                        'email' => 'pilot@example.test',
                        'email_verified' => true,
                    ], JSON_THROW_ON_ERROR));
                    self::assertTrue(openssl_sign($token, $signature, $key, OPENSSL_ALGO_SHA256));

                    return ['id_token' => $token . '.' . $encode($signature)];
                })(),
                default => throw new \LogicException('Unexpected HTTP request.'),
            };

            return new MockResponse(json_encode($payload, JSON_THROW_ON_ERROR));
        });
        $client = new DarkwoodOidcClient($http, new ArrayAdapter(), 'https://id.example', 'product', 'test-secret', 'https://product.example/auth/callback');
        $session = new Session(new MockArraySessionStorage());
        $begin = Request::create('/auth/continue');
        $begin->setSession($session);
        parse_str(parse_url($client->buildAuthorizeUrl($begin), PHP_URL_QUERY), $query);
        self::assertSame('openid email', $query['scope']);
        $callback = Request::create('/auth/callback?' . http_build_query(['code' => 'test-code', 'state' => $query['state']]));
        $callback->setSession($session);
        $identity = $client->handleCallback($callback);
        self::assertSame('test-id', $identity['id']);
        self::assertSame('pilot@example.test', $identity['email']);
        self::assertTrue($identity['email_verified']);

        $this->expectExceptionMessage('Invalid OIDC state.');
        $client->handleCallback($callback);
    }
}
