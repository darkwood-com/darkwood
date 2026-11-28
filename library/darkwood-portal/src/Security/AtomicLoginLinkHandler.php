<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\EventSubscriber\AuthenticationTargetPathSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Stringable;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\LoginLink\Exception\InvalidLoginLinkException;
use Symfony\Component\Security\Http\LoginLink\Exception\InvalidLoginLinkExceptionInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

use function is_int;
use function is_string;

#[AsDecorator(decorates: 'security.authenticator.login_link_handler.main')]
final readonly class AtomicLoginLinkHandler implements LoginLinkHandlerInterface
{
    private const string TARGET = '_security.main.target_path';

    public function __construct(
        #[AutowireDecorated]
        private LoginLinkHandlerInterface $inner,
        private EntityManagerInterface $entityManager,
        private CsrfTokenManagerInterface $csrf,
        private WebAuthenticationHostResolver $hostResolver,
        #[Autowire('%env(bool:DARKWOOD_PASSWORDLESS_ENABLED)%')]
        private bool $enabled,
    ) {}

    /** @param array<string, scalar|Stringable> $parameters */
    public function createLoginLink(UserInterface $user, ?Request $request = null, ?int $lifetime = null, array $parameters = []): LoginLinkDetails
    {
        if (!$this->enabled) {
            throw new LogicException('Passwordless sign-in is disabled.');
        }

        $parameters['flow'] = $request !== null ? $this->binding($request) : 'none';

        // Symfony 8.2 documents this named argument on the concrete native linker.
        // @phpstan-ignore arguments.count (The interface keeps the original signature for BC.)
        return $this->inner->createLoginLink($user, $request, $lifetime, $parameters);
    }

    public function consumeLoginLink(Request $request): UserInterface
    {
        if (!$this->enabled || !$this->csrf->isTokenValid(new CsrfToken('darkwood_email_login_confirm', $request->request->getString('_csrf_token')))) {
            throw new InvalidLoginLinkException('Invalid sign-in request.');
        }

        $user = $this->inner->consumeLoginLink($request);
        if (!$user instanceof User || !$user->isVerified() || !$user->canAuthenticate()) {
            throw new InvalidLoginLinkException('This account cannot sign in through email.');
        }

        $consumed = $this->entityManager->wrapInTransaction(function () use ($request, $user): bool {
            $connection = $this->entityManager->getConnection();
            $id = $connection->fetchOne(
                'SELECT id FROM ' . $connection->quoteIdentifier('user') . ' WHERE id = ? FOR UPDATE',
                [$user->getId()],
            );
            if ($id === false) {
                return false;
            }
            $this->entityManager->refresh($user);
            if (!$user->isVerified() || !$user->canAuthenticate()) {
                return false;
            }

            try {
                $this->inner->consumeLoginLink($request);
            } catch (InvalidLoginLinkExceptionInterface) {
                // Another worker may have consumed the link before this one acquired the row lock.
                return false;
            }

            $user->consumeEmailLoginLink();

            return true;
        });
        if (!$consumed) {
            throw new InvalidLoginLinkException('This sign-in link is invalid or has already been used.');
        }

        $parameters = $request->attributes->get('_login_link_parameters', []);
        $binding = $this->binding($request);
        if ($binding === 'none' || ($parameters['flow'] ?? null) !== $binding) {
            $request->getSession()->remove(self::TARGET);
            $request->getSession()->remove(AuthenticationTargetPathSubscriber::OIDC_TARGET_EXPIRY_KEY);
        } else {
            $target = (string) $request->getSession()->get(self::TARGET);
            $request->getSession()->set(self::TARGET, $this->hostResolver->mirrorAuthenticationUri($target));
        }

        return $user;
    }

    private function binding(Request $request): string
    {
        $session = $request->getSession();
        $target = $session->get(self::TARGET);
        $expiresAt = $session->get(AuthenticationTargetPathSubscriber::OIDC_TARGET_EXPIRY_KEY);
        if (!is_string($target) || !is_int($expiresAt) || $expiresAt <= time()) {
            return 'none';
        }

        $target = $this->hostResolver->mirrorAuthenticationUri($target);
        $origin = $request->getSchemeAndHttpHost();
        if (!str_starts_with($target, $origin . '/authorize?') || str_contains($target, '#')) {
            return 'none';
        }

        return hash('sha256', $session->getId() . "\0" . $target);
    }
}
