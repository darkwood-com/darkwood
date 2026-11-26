<?php

declare(strict_types=1);

namespace App\Controller\Shop;

use App\Entity\Customer\Customer;
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
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Uid\Uuid;
use Throwable;

final class DarkwoodLoginController extends AbstractController
{
    use TargetPathTrait;

    public function __construct(
        private readonly DarkwoodOidcClient $oidc,
        #[Autowire('%env(default::UNIFLOW_OIDC_PILOT_UID)%')]
        private readonly ?string $pilotUid,
        #[Autowire('%env(bool:default::UNIFLOW_OIDC_ALLOW_LINKED)%')]
        private readonly bool $allowLinked,
    ) {}

    #[Route('/auth/darkwood', name: 'app_shop_darkwood_login', methods: ['GET'])]
    public function begin(Request $request, LoggerInterface $logger): Response
    {
        $this->requireEnabled();
        try {
            if ($request->query->getBoolean('checkout')) {
                $this->saveTargetPath($request->getSession(), 'shop', $this->generateUrl('sylius_shop_checkout_address', ['_locale' => $request->getLocale()]));
            }

            return $this->redirect($this->oidc->buildAuthorizeUrl($request));
        } catch (Throwable $exception) {
            $logger->warning('Uniflow Darkwood sign-in could not start.', ['type' => $exception::class]);
            $this->addFlash('darkwood_login_error', 'Darkwood sign-in is temporarily unavailable.');

            return $this->redirectToRoute('app_shop_login');
        }
    }

    #[Route('/auth/darkwood/callback', name: 'app_shop_darkwood_callback', methods: ['GET'])]
    public function callback(
        Request $request,
        ShopUserRepository $users,
        EntityManagerInterface $entityManager,
        Security $security,
        LoggerInterface $logger,
    ): Response {
        $this->requireEnabled();
        try {
            $identity = $this->oidc->handleCallback($request);
            if (!$this->allowLinked && $identity['id'] !== $this->pilotUid) {
                throw new RuntimeException('Identity outside the pilot.');
            }
            if (!Uuid::isValid($identity['id'])) {
                throw new RuntimeException('Darkwood did not provide a valid subject.');
            }

            $email = trim($identity['email']);
            $user = $users->findOneBy(['darkwoodUid' => $identity['id']]);
            if (null === $user) {
                // Darkwood admits eligible legacy identities even when the mailbox
                // has no verification record. Their local account starts unverified.
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return $this->loginError('Darkwood Identity did not provide a usable email address for your new Uniflow account.');
                }
                $canonicalEmail = mb_strtolower($email);
                if (null !== $entityManager->getRepository(Customer::class)->findOneBy(['emailCanonical' => $canonicalEmail])) {
                    return $this->loginError('A Uniflow customer already uses this email. Sign in with the Darkwood account linked to that customer, or contact support.');
                }

                $customer = new Customer();
                $customer->setEmail($email);
                $customer->setEmailCanonical($canonicalEmail);
                $user = new ShopUser();
                $user->setCustomer($customer);
                $user->setDarkwoodUid($identity['id']);
                $user->setUsername((string) $user->getUid());
                $user->setUsernameCanonical((string) $user->getUid());
                $user->setEnabled(true);
                $entityManager->persist($customer);
                $entityManager->persist($user);
            }
            if (!$user->isEnabled()) {
                return $this->loginError('This Uniflow account is disabled.');
            }
            if (!$user->isVerified() && $identity['email_verified']
                && 0 === strcasecmp(trim((string) $user->getEmail()), $email)
            ) {
                $user->setVerifiedAt(new DateTime());
            }
            $entityManager->flush();
            $response = $security->login($user, DarkwoodShopAuthenticator::class, 'shop');
            $target = $this->getTargetPath($request->getSession(), 'shop');
            $this->removeTargetPath($request->getSession(), 'shop');

            return $response ?? ($target ? $this->redirect($target) : $this->redirectToRoute('app_shop_feed'));
        } catch (AuthenticationException|RuntimeException $exception) {
            $logger->warning('Uniflow Darkwood login was rejected.', ['type' => $exception::class]);
        } catch (Throwable $exception) {
            $logger->error('Uniflow Darkwood login could not complete.', ['type' => $exception::class]);
        }

        return $this->loginError('Darkwood sign-in could not complete. Please try again from Uniflow.');
    }

    private function loginError(string $message): Response
    {
        $this->addFlash('darkwood_login_error', $message);

        return $this->redirectToRoute('app_shop_login');
    }

    private function requireEnabled(): void
    {
        if (!$this->oidc->supportsDelegatedLogin() || (!$this->allowLinked && '' === trim((string) $this->pilotUid))) {
            throw $this->createNotFoundException();
        }
    }
}
