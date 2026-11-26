<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final readonly class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $helper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
    ) {}

    public function sendEmailConfirmation(User $user, string $locale): void
    {
        $signature = $this->helper->generateSignature(
            'common_register_check',
            $this->signatureSubject($user),
            $user->getEmail(),
            ['_locale' => $locale, 'account' => $user->getUuid()],
        );
        // The browser passes the proof in a POST body, outside access logs and referrers.
        $url = str_replace('?', '#', $signature->getSignedUrl());
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@darkwood.fr', 'Darkwood'))
            ->to($user->getEmail())
            ->subject($this->translator->trans('email_verification.mail_subject', locale: $locale))
            ->locale($locale)
            ->htmlTemplate('common/mails/registration.html.twig')
            ->textTemplate('common/mails/registration.txt.twig')
            ->context(['verificationUrl' => $url, 'expiresAt' => $signature->getExpiresAt()]);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface) {
            $this->logger->warning('Darkwood verification email delivery failed.');
        }
    }

    public function confirm(Request $signedRequest, User $user): bool
    {
        return $this->entityManager->wrapInTransaction(function () use ($signedRequest, $user): bool {
            // Doctrine refresh can include nullable joins, which PostgreSQL cannot lock.
            $id = $this->entityManager->getConnection()->fetchOne(
                'SELECT id FROM public."user" WHERE id = ? FOR UPDATE',
                [$user->getId()],
            );
            if (false === $id) {
                return false;
            }
            $this->entityManager->refresh($user);
            if (!$user->isEnabled()) {
                return false;
            }

            try {
                $this->helper->validateEmailConfirmationFromRequest($signedRequest, $this->signatureSubject($user), $user->getEmail());
            } catch (VerifyEmailExceptionInterface) {
                return false;
            }

            $user->setIsVerified(true);

            return true;
        });
    }

    private function signatureSubject(User $user): string
    {
        return $user->getUuid() . ':' . $user->getEmailVerificationVersion();
    }
}
