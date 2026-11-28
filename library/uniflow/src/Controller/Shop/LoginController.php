<?php

declare(strict_types=1);

namespace App\Controller\Shop;

use App\Model\Page;
use App\Security\DarkwoodOidcSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginController extends AbstractController
{
    public function __construct(private readonly DarkwoodOidcSettings $oidc) {}

    #[Route('/login', name: 'app_shop_login', methods: ['GET'])]
    public function login(AuthenticationUtils $authentication): Response
    {
        if ($authentication->getLastAuthenticationError() !== null) {
            $this->addFlash('darkwood_login_error', 'Darkwood sign-in could not complete. Please try again from Uniflow.');
        }

        return $this->render('shop/login.html.twig', [
            'page' => new Page(page: 'login', title: 'Login', description: 'Sign in with Darkwood Identity'),
            'darkwood_issuer' => $this->oidc->isEnabled() ? $this->oidc->getIssuer() : null,
        ]);
    }

    #[Route('/register', name: 'app_shop_register', methods: ['GET'])]
    public function redirectToLogin(): Response
    {
        return $this->redirectToRoute('app_shop_login');
    }

    public function retiredLogin(): Response
    {
        return new Response('Sign in with Darkwood Identity.', Response::HTTP_GONE);
    }

    public function resetPassword(): Response
    {
        if (!$this->oidc->isEnabled()) {
            return $this->redirectToRoute('app_shop_login');
        }

        return $this->redirect($this->oidc->getIssuer() . '/resetting/request', Response::HTTP_SEE_OTHER);
    }
}
