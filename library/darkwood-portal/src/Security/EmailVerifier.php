<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final readonly class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $legacyHelper,
        private UriSigner $signer,
        private UrlGeneratorInterface $urls,
        #[Autowire('%env(int:DARKWOOD_EMAIL_VERIFICATION_TTL)%')]
        private int $lifetime,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
    ) {}

    public function sendEmailConfirmation(User $user, string $locale): void
    {
        $expiresAt = new DateTimeImmutable('+' . $this->lifetime . ' seconds');
        $verificationUrl = $this->urls->generate(
            'common_register_check',
            ['_locale' => $locale, 'account' => $user->getUuid()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
        /**
         * The browser passes the proof in a POST body, outside access logs and referrers.
         *
         * @phpstan-ignore arguments.count (Symfony 8.2 reads the documented version argument with func_get_arg for BC.)
         */
        $url = str_replace('?', '#', $this->signer->sign($verificationUrl, $expiresAt, $this->verificationVersion($user)));
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@darkwood.fr', 'Darkwood'))
            ->to($user->getEmail())
            ->subject($this->translator->trans('email_verification.mail_subject', locale: $locale))
            ->locale($locale)
            ->htmlTemplate('common/mails/registration.html.twig')
            ->textTemplate('common/mails/registration.txt.twig')
            ->context(['verificationUrl' => $url, 'expiresAt' => $expiresAt])
        ;

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
            $connection = $this->entityManager->getConnection();
            $id = $connection->fetchOne(
                'SELECT id FROM ' . $connection->quoteIdentifier('user') . ' WHERE id = ? FOR UPDATE',
                [$user->getId()],
            );
            if (false === $id) {
                return false;
            }
            $this->entityManager->refresh($user);
            if (!$user->isEnabled() || $user->isVerified()) {
                return false;
            }

            if (!$this->isValid($signedRequest, $user)) {
                return false;
            }

            $user->setIsVerified(true);

            return true;
        });
    }

    private function isValid(Request $request, User $user): bool
    {
        if ($request->query->has('_hash')) {
            // @phpstan-ignore arguments.count (Symfony 8.2 reads the documented version argument with func_get_arg for BC.)
            return $request->query->has('_expiration') && $this->signer->checkRequest($request, $this->verificationVersion($user));
        }

        try {
            $this->legacyHelper->validateEmailConfirmationFromRequest($request, $user->getUuid() . ':' . $user->getEmailVerificationVersion(), $user->getEmail());

            return true;
        } catch (VerifyEmailExceptionInterface) {
            return false;
        }
    }

    private function verificationVersion(User $user): string
    {
        return implode("\0", ['verify-email', $user->getUuid(), $user->getEmail(), (string) $user->getEmailVerificationVersion(), $user->isVerified() ? 'verified' : 'pending']);
    }
}
