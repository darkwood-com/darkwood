<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\CommonController;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\AtomicLoginLinkHandler;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class EmailLoginController extends AbstractController
{
    public function __construct(
        private readonly CommonController $commonController,
        private readonly UserRepository $users,
        private readonly AtomicLoginLinkHandler $loginLinks,
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
        #[Autowire(service: 'limiter.email_login_ip')]
        private readonly RateLimiterFactoryInterface $ipLimiter,
        #[Autowire(service: 'limiter.email_login_address')]
        private readonly RateLimiterFactoryInterface $addressLimiter,
        #[Autowire('%env(bool:DARKWOOD_PASSWORDLESS_ENABLED)%')]
        private readonly bool $enabled,
    ) {}

    #[Route(path: ['en' => '/login/email', 'fr' => '/fr/login/email', 'de' => '/de/login/email'], name: 'security_email_login_request', host: '%darkwood_host%', schemes: ['https'], methods: ['POST'])]
    public function requestLink(Request $request): Response
    {
        $email = trim($request->request->getString('email'));
        if ($this->enabled
            && $this->isCsrfTokenValid('darkwood_email_login_request', $request->request->getString('_csrf_token'))
            && $this->ipLimiter->create($request->getClientIp() ?? 'unknown')->consume()->isAccepted()
            && filter_var($email, FILTER_VALIDATE_EMAIL)
            && $this->addressLimiter->create(hash('sha256', mb_strtolower($email)))->consume()->isAccepted()) {
            $user = $this->users->findOneBy(['email' => $email]);
            if ($user instanceof User && $user->isVerified() && $user->canAuthenticate()) {
                $details = $this->loginLinks->createLoginLink($user, $request);
                $message = (new TemplatedEmail())
                    ->from(new Address('no-reply@darkwood.fr', 'Darkwood'))
                    ->to($email)
                    ->locale($request->getLocale())
                    ->subject($this->translator->trans('email_login.mail_subject', locale: $request->getLocale()))
                    ->htmlTemplate('common/mails/login_link.html.twig')
                    ->textTemplate('common/mails/login_link.txt.twig')
                    ->context(['loginUrl' => str_replace('?', '#', $details->getUrl())])
                ;

                try {
                    $this->mailer->send($message);
                } catch (TransportExceptionInterface) {
                    $this->logger->warning('Darkwood sign-in email delivery failed.');
                }
            }
        }

        $this->addFlash('email_login_status', 'email_login.request_result');

        return $this->redirectToRoute('security_login', status: Response::HTTP_SEE_OTHER);
    }

    #[Route(path: ['en' => '/login/email/confirm', 'fr' => '/fr/login/email/confirm', 'de' => '/de/login/email/confirm'], name: 'security_email_login_confirm', host: '%darkwood_host%', schemes: ['https'], methods: ['GET', 'POST'])]
    public function confirm(Request $request): Response
    {
        if (!$this->enabled) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('POST')) {
            return $this->redirectToRoute('security_login', status: Response::HTTP_SEE_OTHER);
        }

        $response = $this->render('common/pages/email_login_confirm.html.twig', ['page' => $this->commonController->getPage($request, 'login')]);
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
