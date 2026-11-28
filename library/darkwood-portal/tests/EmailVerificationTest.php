<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Page;
use App\Entity\PageTranslation;
use App\Entity\Site;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Authentication\AuthenticationMethod;

final class EmailVerificationTest extends WebTestCase
{
    public function testRegistrationRequiresTheMailedProofBeforeLogin(): void
    {
        $suffix = bin2hex(random_bytes(5));
        $client = self::createClient([], [
            'HTTPS' => 'on',
            'HTTP_HOST' => 'darkwood.localhost',
            'REMOTE_ADDR' => '2001:db8::' . implode(':', str_split($suffix, 4)),
        ]);
        $entityManager = $client->getContainer()->get(EntityManagerInterface::class);
        $this->seedPages($entityManager);

        $address = 'verification-' . $suffix . '@example.com';
        $password = 'QA-' . $suffix . '-password';
        $crawler = $client->request('GET', '/register');
        self::assertResponseIsSuccessful();

        foreach (['verification_' . $suffix, ' verification-' . $suffix] as $invalidUsername) {
            $client->submit($crawler->filter('#register_form')->form([
                'registration[username]' => $invalidUsername,
                'registration[email]' => $address,
                'registration[plainPassword][first]' => $password,
                'registration[plainPassword][second]' => $password,
            ]));
            self::assertSelectorTextContains('#register_form', 'The username can only contain letters A–Z, numbers and hyphens.');
            self::assertEmailCount(0);
            $crawler = $client->request('GET', '/register');
        }

        $client->submit($crawler->filter('#register_form')->form([
            'registration[username]' => 'Verification-' . $suffix,
            'registration[email]' => $address,
            'registration[plainPassword][first]' => $password,
            'registration[plainPassword][second]' => $password,
        ]));
        self::assertResponseRedirects('/register/resend-email', 303);
        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);
        self::assertEmailAddressContains($email, 'To', $address);
        self::assertEmailTextBodyContains($email, 'https://darkwood.localhost/register/verify-email#');
        self::assertEmailSubjectContains($email, 'Confirm your email address');
        $text = $email->getTextBody() ?? '';
        self::assertSame(1, preg_match('~https://[^\s]+#([^\s]+)~', $text, $parts));
        $user = $client->getContainer()->get(EntityManagerInterface::class)->getRepository(User::class)->findOneBy(['email' => $address]);
        self::assertInstanceOf(User::class, $user);
        self::assertTrue($user->isEmailVerificationPending());

        foreach ([$address, 'absent-' . $suffix . '@example.com'] as $requestedAddress) {
            $crawler = $client->request('GET', '/register/resend-email');
            $client->submit($crawler->selectButton('Resend the verification link')->form(['email' => $requestedAddress]));
            self::assertResponseRedirects('/register/resend-email', 303);
            self::assertEmailCount(1);
            $client->followRedirect();
            self::assertSelectorTextContains('[role="status"]', 'If this address can be verified');
        }

        $crawler = $client->request('GET', '/login');
        $client->submit($crawler->filter('#login_form')->form(['username' => $address, 'password' => $password]));
        $client->followRedirect();
        self::assertSelectorTextContains('[role="alert"]', 'Verify your email address before signing in.');

        // Model the end of the per-address cooldown without waiting five minutes.
        $client->getContainer()->get('limiter.email_verification_address')
            ->create(hash('sha256', $address))->reset()
        ;
        $crawler = $client->request('GET', '/register/resend-email');
        $client->submit($crawler->selectButton('Resend the verification link')->form(['email' => $address]));
        self::assertResponseRedirects('/register/resend-email', 303);
        self::assertEmailCount(2);
        $resend = self::getMailerMessage(1);
        self::assertNotNull($resend);
        self::assertEmailAddressContains($resend, 'To', $address);

        parse_str($parts[1], $pastQuery);
        unset($pastQuery['_hash'], $pastQuery['_expiration']);
        $signer = new UriSigner($client->getContainer()->getParameter('kernel.secret'));
        $version = implode("\0", ['verify-email', $user->getUuid(), $address, (string) $user->getEmailVerificationVersion(), 'pending']);
        $expired = $signer->sign('https://darkwood.localhost/register/verify-email?' . http_build_query($pastQuery), time() - 60, $version);
        $expiredProof = parse_url($expired, PHP_URL_QUERY);

        foreach ([$parts[1] . 'tampered', $expiredProof, $parts[1], $parts[1]] as $index => $proof) {
            $crawler = $client->request('GET', '/register/verify-email');
            self::assertResponseHeaderSame('Referrer-Policy', 'no-referrer');
            $client->submit($crawler->filter('#email-verification-confirm')->form(['proof' => $proof]));
            self::assertResponseRedirects('/register/resend-email', 303);
            $client->followRedirect();
            self::assertSelectorTextContains('[role="status"]', 2 === $index ? 'Your email address has been verified.' : 'This link is invalid');
        }

        $crawler = $client->request('GET', '/login');
        $client->submit($crawler->filter('#login_form')->form(['username' => $address, 'password' => $password]));
        self::assertResponseRedirects();
        self::assertStringNotContainsString('/login', (string) $client->getResponse()->headers->get('Location'));

        $client->request('GET', '/login');
        $token = $client->getContainer()->get('security.token_storage')->getToken();
        self::assertNotNull($token);
        self::assertArrayHasKey(AuthenticationMethod::PASSWORD, $token->getAuthenticationProofs());
        $entityManager = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $address]);
        $user->setEnabled(false);
        $entityManager->flush();

        $client->request('GET', '/login');
        self::assertNull($client->getContainer()->get('security.token_storage')->getToken());
    }

    public function testRenamedUniflowAccountRetainsMailboxActivation(): void
    {
        $client = self::createClient([], ['HTTPS' => 'on', 'HTTP_HOST' => 'darkwood.localhost']);
        $entityManager = $client->getContainer()->get(EntityManagerInterface::class);
        $this->seedPages($entityManager);
        $username = 'uniflow-activation-' . bin2hex(random_bytes(4));
        $address = $username . '@example.com';

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($address);
        $user->setUniflowActivationRequired(true);
        $entityManager->persist($user);
        $entityManager->flush();

        $client->request('POST', '/resetting/send-email', ['email' => $address]);
        self::assertResponseRedirects();
        self::assertEmailCount(1);
        $message = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $message);
        self::assertSame(1, preg_match('~/resetting/reset/[a-f0-9]{64}\.[a-f0-9]{64}~', $message->getHtmlBody() ?? '', $match));

        $crawler = $client->request('GET', $match[0]);
        $client->submit($crawler->filter('#resetting_form')->form([
            'resetting[plainPassword][first]' => 'QA-Uniflow-Strong-1!',
            'resetting[plainPassword][second]' => 'QA-Uniflow-Strong-1!',
        ]));
        self::assertResponseRedirects('/login');

        $user = $client->getContainer()->get(EntityManagerInterface::class)->getRepository(User::class)->findOneBy(['email' => $address]);
        self::assertInstanceOf(User::class, $user);
        self::assertSame($username, $user->getUsername());
        self::assertTrue($user->isVerified());
        self::assertFalse($user->isUniflowActivationRequired());
        self::assertNull($user->getResetToken());

        $crawler = $client->request('GET', '/login');
        $client->submit($crawler->filter('#login_form')->form([
            'username' => $username,
            'password' => 'QA-Uniflow-Strong-1!',
        ]));
        self::assertResponseRedirects();
        $client->request('GET', '/login');
        self::assertNotNull($client->getContainer()->get('security.token_storage')->getToken());
    }

    private function seedPages(EntityManagerInterface $entityManager): void
    {
        $sites = $entityManager->getRepository(Site::class);
        $site = $sites->findOneBy(['host' => 'darkwood.localhost']);
        if (!$site instanceof Site) {
            $site = new Site();
            $site->setName('Darkwood');
            $site->setRef('darkwood');
            $site->setHost('darkwood.localhost');
            $site->setPosition(1);
            $site->setActive(true);
            $entityManager->persist($site);
        }

        foreach (['register', 'login', 'resetting'] as $reference) {
            if (null !== $entityManager->getRepository(Page::class)->findOneBy(['ref' => $reference, 'site' => $site])) {
                continue;
            }
            $page = new Page();
            $page->setRef($reference);
            $page->setSite($site);
            $translation = new PageTranslation();
            $translation->setLocale('en');
            $translation->setTitle(ucfirst($reference));
            $translation->setActive(true);
            $page->addTranslation($translation);
            $entityManager->persist($page);
        }
        $entityManager->flush();
    }
}
