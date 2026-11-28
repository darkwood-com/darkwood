<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final readonly class DarkwoodShopLoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    use TargetPathTrait;

    public function __construct(private UrlGeneratorInterface $urls) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $session = $request->getSession();
        $target = $this->getTargetPath($session, 'shop');
        $this->removeTargetPath($session, 'shop');
        $url = $this->urls->generate('app_shop_feed');

        if ($target !== null) {
            $origin = $request->getSchemeAndHttpHost();
            $path = str_starts_with($target, $origin . '/') ? substr($target, strlen($origin)) : $target;
            $decoded = rawurldecode($path);
            if (str_starts_with($path, '/') && !str_starts_with($decoded, '//')
                && !str_contains($decoded, '\\') && 0 === preg_match('/[\x00-\x20\x7f]/', $decoded)
                && !str_starts_with($decoded, '/auth/')) {
                $url = $path;
            }
        }

        return new RedirectResponse($url);
    }
}
