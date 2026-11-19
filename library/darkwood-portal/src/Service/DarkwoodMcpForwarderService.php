<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/** Reuse the existing HTTP providers, authorization and quotas; never a second MCP server. */
final readonly class DarkwoodMcpForwarderService
{
    public const string APPROVAL_HEADER = 'X-Darkwood-Action-Approval';
    public const string FORWARDED_ATTRIBUTE = '_darkwood_mcp_forwarded';

    public function __construct(
        private HttpKernelInterface $kernel,
        private RequestStack $requestStack,
        private RouterInterface $router,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function forward(string $routeName, string $method = 'GET', ?array $body = null, array $routeParams = []): mixed
    {
        $current = $this->requestStack->getCurrentRequest();
        if (!$current instanceof Request) {
            throw new AccessDeniedHttpException('Darkwood MCP requires an authenticated HTTP transport context.');
        }
        $allowed = ['api_darkwood_get_state', 'api_darkwood_archives', 'api_darkwood_archive_get', 'api_darkwood_post_action'];
        if (!in_array($routeName, $allowed, true) || $method !== ('api_darkwood_post_action' === $routeName ? 'POST' : 'GET')) {
            throw new AccessDeniedHttpException('Unsupported Darkwood MCP operation.');
        }
        $user = $this->tokenStorage->getToken()?->getUser();
        // Even the legacy GET can initialize a player's persistent data. MCP observation must not do so.
        if ('api_darkwood_get_state' === $routeName && $user instanceof User && null === $user->getPlayer()) {
            throw new HttpException(409, 'Initialize your player through the normal game interface before MCP observation.');
        }
        if ('POST' === $method) {
            if (!$user instanceof User || null === $user->getId()) {
                throw new HttpException(401, 'Authenticate the player before approving a game action.');
            }
            $query = $body['query'] ?? [];
            ksort($query);
            $expected = hash('sha256', json_encode(['user_id' => $user->getId(), 'query' => $query], JSON_THROW_ON_ERROR));
            // This is transport-level approval supplied by a trusted interactive client, never a model argument.
            $approval = $current->headers->get(self::APPROVAL_HEADER, '');
            if (!hash_equals($expected, $approval) || $current->attributes->get('_darkwood_action_approval_consumed', false)) {
                throw new AccessDeniedHttpException('Explicit client approval for this exact action is required.');
            }
            $current->attributes->set('_darkwood_action_approval_consumed', true);
        }

        $url = $this->router->generate($routeName, $routeParams, RouterInterface::ABSOLUTE_PATH);
        $subRequest = Request::create($current->getSchemeAndHttpHost().$url, $method, [], $current->cookies->all(), [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], null !== $body ? json_encode($body, JSON_THROW_ON_ERROR) : null);
        $subRequest->attributes->set(self::FORWARDED_ATTRIBUTE, true);
        foreach ([ApiKeyResolverService::HEADER_NAME, 'Authorization'] as $header) {
            if ($current->headers->has($header)) {
                $subRequest->headers->set($header, $current->headers->get($header));
            }
        }
        if ($current->hasSession()) {
            $subRequest->setSession($current->getSession());
        }
        $response = $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $status = $response->getStatusCode();
        if ($status >= 300) {
            // Do not relay HTML login redirects, debug traces, credentials or arbitrary provider messages to the model.
            $messages = [401 => 'A valid Darkwood API key and player authentication are required.', 403 => 'Darkwood access denied; check active beta and premium permissions.', 404 => 'Darkwood archive not found.', 429 => 'Darkwood daily action limit reached.'];
            throw new HttpException($status >= 400 ? $status : 502, $messages[$status] ?? 'Darkwood API request failed.');
        }
        try {
            $decoded = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new HttpException(502, 'Darkwood API returned an invalid JSON response.');
        }

        return $this->withoutSecrets($decoded);
    }

    private function withoutSecrets(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        foreach ($value as $key => $item) {
            if (is_string($key) && preg_match('/(?:password|secret|token|api[_-]?key|authorization|cookie)/i', $key)) {
                unset($value[$key]);
            } else {
                $value[$key] = $this->withoutSecrets($item);
            }
        }

        return $value;
    }
}
