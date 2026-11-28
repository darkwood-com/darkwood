<?php

declare(strict_types=1);

namespace App\Controller\Shop;

use App\Model\Page;
use App\Security\DarkwoodOidcClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LoginController extends AbstractController
{
    public function __construct(private readonly DarkwoodOidcClient $oidc) {}

    #[Route('/login', name: 'app_shop_login', methods: ['GET'])]
    public function login(): Response
    {
        return $this->render('shop/login.html.twig', [
            'page' => new Page(page: 'login', title: 'Login', description: 'Sign in with Darkwood Identity'),
            'darkwood_issuer' => $this->oidc->supportsDelegatedLogin() ? $this->oidc->getIssuer() : null,
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
        if (!$this->oidc->supportsDelegatedLogin()) {
            return $this->redirectToRoute('app_shop_login');
        }

        return $this->redirect($this->oidc->getIssuer() . '/resetting/request', Response::HTTP_SEE_OTHER);
    }
}
