<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\DarkwoodEntitlementService;
use League\Bundle\OAuth2ServerBundle\Security\Authentication\Token\OAuth2Token;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class OidcUserInfoController extends AbstractController
{
    public function __construct(
        private readonly TokenStorageInterface $tokens,
        private readonly DarkwoodEntitlementService $entitlements,
        #[Autowire('%env(default:heroclip_oauth_client_default:HEROCLIP_OAUTH_CLIENT_ID)%')]
        private readonly string $heroClipClientId,
        #[Autowire('%env(default:darkwaar_oauth_client_default:DARKWAAR_OAUTH_CLIENT_ID)%')]
        private readonly string $darkwaarClientId,
    ) {}

    #[Route('/userinfo', name: 'openid_connect_userinfo', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $token = $this->tokens->getToken();
        $user = $this->getUser();
        if (!$token instanceof OAuth2Token || !$user instanceof User
            || (!$this->isClient($token->getOAuthClientId(), $this->heroClipClientId)
                && !$this->isClient($token->getOAuthClientId(), $this->darkwaarClientId))) {
            return $this->json(['error' => 'invalid_token'], Response::HTTP_UNAUTHORIZED, [
                'WWW-Authenticate' => 'Bearer error="invalid_token"',
            ]);
        }

        $scopes = $token->getScopes();
        $claims = ['sub' => $user->getUuid()];
        if (in_array('email', $scopes, true)) {
            $claims['email'] = (string) $user->getEmail();
            $claims['email_verified'] = (bool) $user->isVerified();
        }
        if (in_array('profile', $scopes, true)) {
            $claims['preferred_username'] = $user->getUsername();
            $claims['name'] = trim(sprintf('%s %s', (string) $user->getFirstname(), (string) $user->getLastname()));
        }
        if ($this->isClient($token->getOAuthClientId(), $this->darkwaarClientId)
            && in_array('entitlements', $scopes, true)) {
            $claims['entitlements'] = $this->entitlements->activeCodes($user);
        }

        return $this->json($claims, headers: ['Cache-Control' => 'no-store']);
    }

    private function isClient(string $actual, string $expected): bool
    {
        return '' !== $expected && hash_equals($expected, $actual);
    }
}
