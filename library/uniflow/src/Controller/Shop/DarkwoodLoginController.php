<?php

declare(strict_types=1);

namespace App\Controller\Shop;

use App\Security\DarkwoodOidcSettings;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authenticator\OidcLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Throwable;

final class DarkwoodLoginController extends AbstractController
{
    use TargetPathTrait;

    public function __construct(
        #[Autowire(service: 'security.authenticator.oidc_login.shop')]
        private readonly OidcLoginAuthenticator $oidc,
        private readonly DarkwoodOidcSettings $settings,
    ) {}

    #[Route('/auth/darkwood', name: 'app_shop_darkwood_login', methods: ['GET'])]
    public function begin(Request $request, LoggerInterface $logger): Response
    {
        if (!$this->settings->isEnabled()) {
            throw $this->createNotFoundException();
        }

        try {
            if ($request->query->getBoolean('checkout')) {
                $this->saveTargetPath($request->getSession(), 'shop', $this->generateUrl('sylius_shop_checkout_address', ['_locale' => $request->getLocale()]));
            }

            return $this->oidc->start($request);
        } catch (Throwable $exception) {
            $logger->warning('Uniflow Darkwood sign-in could not start.', ['type' => $exception::class]);
            $this->addFlash('darkwood_login_error', 'Darkwood sign-in is temporarily unavailable.');

            return $this->redirectToRoute('app_shop_login');
        }
    }

    #[Route('/auth/darkwood/callback', name: 'app_shop_darkwood_callback', methods: ['GET'])]
    public function callback(): Response
    {
        return $this->redirectToRoute('app_shop_login');
    }
}
