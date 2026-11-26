<?php

declare(strict_types=1);

namespace App\Controller\Shop;

use App\Entity\User\ShopUser;
use App\Repository\User\ShopUserRepository;
use App\Security\DarkwoodOidcClient;
use App\Security\DarkwoodShopAuthenticator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Throwable;

final class DarkwoodLoginController extends AbstractController
{
    public function __construct(
        private readonly DarkwoodOidcClient $oidc,
        #[Autowire('%env(default::UNIFLOW_OIDC_PILOT_UID)%')]
        private readonly ?string $pilotUid,
    ) {}

    #[Route('/auth/darkwood', name: 'app_shop_darkwood_login', methods: ['GET'])]
    public function begin(Request $request): Response
    {
        $this->requirePilot();

        return $this->redirect($this->oidc->buildAuthorizeUrl($request));
    }

    #[Route('/auth/darkwood/callback', name: 'app_shop_darkwood_callback', methods: ['GET'])]
    public function callback(
        Request $request,
        ShopUserRepository $users,
        EntityManagerInterface $entityManager,
        Security $security,
        LoggerInterface $logger,
    ): Response {
        $this->requirePilot();
        try {
            $identity = $this->oidc->handleCallback($request);
            if ($identity['id'] !== $this->pilotUid) {
                throw new RuntimeException('Identity outside the pilot.');
            }
            $user = $users->findOneBy(['darkwoodUid' => $identity['id']]);
            if (!$user instanceof ShopUser || !$user->isEnabled() || !$identity['email_verified']
                || '' === $identity['email'] || 0 !== strcasecmp(trim((string) $user->getEmail()), trim($identity['email']))) {
                throw new RuntimeException('The linked account or the verified address is unavailable.');
            }
            if (!$user->isVerified()) {
                $user->setVerifiedAt(new DateTime());
                $entityManager->flush();
            }
            $response = $security->login($user, DarkwoodShopAuthenticator::class, 'shop');

            return $response ?? $this->redirectToRoute('app_shop_feed');
        } catch (AuthenticationException|RuntimeException $exception) {
            $logger->warning('Uniflow Darkwood login was rejected.', ['type' => $exception::class]);
        } catch (Throwable $exception) {
            $logger->error('Uniflow Darkwood login could not complete.', ['type' => $exception::class]);
        }

        $this->addFlash('darkwood_login_error', 'Darkwood sign-in could not complete. Check that your Darkwood address is verified and your account is part of the pilot.');

        return $this->redirectToRoute('app_shop_login');
    }

    private function requirePilot(): void
    {
        if (!$this->oidc->supportsDelegatedLogin() || '' === trim((string) $this->pilotUid)) {
            throw $this->createNotFoundException();
        }
    }
}
