<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\CommonController;
use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly EmailVerifier $emailVerifier,
        private readonly CommonController $commonController,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $users,
        #[Target('email_verification_ip')] private readonly RateLimiterFactoryInterface $ipLimiter,
        #[Target('email_verification_address')] private readonly RateLimiterFactoryInterface $addressLimiter,
    ) {}

    #[Route(path: ['fr' => '/fr/inscription', 'en' => '/register', 'de' => '/de/registrieren'], name: 'common_register', defaults: ['ref' => 'register'], methods: ['GET', 'POST'])]
    public function register(Request $request, string $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();
        $user = new User();
        $user->setNewsletterEnabled(true);
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addressLimiter->create(hash('sha256', mb_strtolower($user->getEmail())))->consume();
            $this->emailVerifier->sendEmailConfirmation($user, $request->getLocale());

            $this->addFlash('registration_verification', 'email_verification.registration_pending');

            return $this->redirectToRoute('common_register_resend', status: Response::HTTP_SEE_OTHER);
        }

        return $this->render('common/pages/register.html.twig', ['page' => $page, 'form' => $form, 'site_ref' => $siteRef]);
    }

    #[Route(path: ['fr' => '/fr/inscription/verifier-email', 'en' => '/register/verify-email', 'de' => '/de/registrieren/e-mail-bestaetigen'], name: 'common_register_check', host: '%darkwood_host%', schemes: ['https'], methods: ['GET', 'POST'])]
    public function checkUserEmail(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $verified = false;
            $proof = $request->request->getString('proof');
            if (strlen($proof) <= 1024 && $this->isCsrfTokenValid('confirm_email', $request->request->getString('_csrf_token'))) {
                $signedRequest = Request::create($request->getUriForPath($request->getPathInfo()) . '?' . $proof);
                $account = $signedRequest->query->all()['account'] ?? '';
                if (is_string($account) && Uuid::isValid($account)) {
                    $user = $this->users->findOneByUuid($account);
                    $verified = $user instanceof User && $this->emailVerifier->confirm($signedRequest, $user);
                }
            }

            $this->addFlash('registration_verification', $verified ? 'email_verification.confirmed' : 'email_verification.invalid');

            return $this->redirectToRoute('common_register_resend', status: Response::HTTP_SEE_OTHER);
        }

        $response = $this->render('common/pages/email_verification.html.twig', ['confirm' => true, 'page' => $this->commonController->getPage($request, 'register')]);
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }

    #[Route(path: ['fr' => '/fr/inscription/confimer-email', 'en' => '/register/check-email', 'de' => '/de/registrieren/check-email'], name: 'common_register_legacy_check', methods: ['GET'])]
    public function legacyCheck(): Response
    {
        return $this->redirectToRoute('common_register_resend');
    }

    #[Route(path: ['fr' => '/fr/inscription/renvoyer-email', 'en' => '/register/resend-email', 'de' => '/de/registrieren/e-mail-erneut-senden'], name: 'common_register_resend', host: '%darkwood_host%', schemes: ['https'], methods: ['GET', 'POST'])]
    public function resendVerification(Request $request): Response
    {
        if (!$request->isMethod('POST')) {
            return $this->render('common/pages/email_verification.html.twig', ['confirm' => false, 'page' => $this->commonController->getPage($request, 'register')]);
        }

        $email = trim($request->request->getString('email'));
        if ($this->isCsrfTokenValid('resend_email', $request->request->getString('_csrf_token'))
            && $this->ipLimiter->create($request->getClientIp() ?? 'unknown')->consume()->isAccepted()
            && filter_var($email, FILTER_VALIDATE_EMAIL)
            && $this->addressLimiter->create(hash('sha256', mb_strtolower($email)))->consume()->isAccepted()
        ) {
            $user = $this->users->findOneBy(['email' => $email]);
            if ($user instanceof User && $user->isEnabled() && !$user->isVerified() && '' !== $user->getPassword()) {
                $this->emailVerifier->sendEmailConfirmation($user, $request->getLocale());
            }
        }

        $this->addFlash('registration_verification', 'email_verification.resend_result');

        return $this->redirectToRoute('common_register_resend', status: Response::HTTP_SEE_OTHER);
    }
}
