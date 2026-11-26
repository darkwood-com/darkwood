<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Security\WebAuthenticationHostResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use function is_int;
use function is_string;

/**
 * Auth target paths saved on the API host must be replayed on the Darkwood site host.
 *
 * @author Mathieu Ledru
 */
final readonly class AuthenticationTargetPathSubscriber implements EventSubscriberInterface
{
    public const string OIDC_TARGET_EXPIRY_KEY = '_security.main.oidc_target_expires_at';

    private const string TARGET_PATH_KEY = '_security.main.target_path';

    public function __construct(
        private WebAuthenticationHostResolver $authenticationHostResolver,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 90],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();
        $resumingLogin = 1 === preg_match('~^/(?:(?:en|fr|de)/)?login$|^/2fa_check$~', $request->getPathInfo());
        if (!$session->isStarted() && !($resumingLogin && $request->hasPreviousSession())) {
            return;
        }

        $targetPath = $session->get(self::TARGET_PATH_KEY);
        if (!is_string($targetPath) || '' === $targetPath) {
            return;
        }

        if ('/authorize' === parse_url($targetPath, PHP_URL_PATH)) {
            $expiresAt = $session->get(self::OIDC_TARGET_EXPIRY_KEY);
            if (is_int($expiresAt) && $expiresAt <= time()) {
                $session->remove(self::TARGET_PATH_KEY);
                $session->remove(self::OIDC_TARGET_EXPIRY_KEY);

                return;
            }
        }

        $mirroredTargetPath = $this->authenticationHostResolver->mirrorAuthenticationUri($targetPath);
        if ($mirroredTargetPath !== $targetPath) {
            $session->set(self::TARGET_PATH_KEY, $mirroredTargetPath);
        }
    }
}
