<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Page;
use App\Entity\PageTranslation;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;

final class LoginPageTest extends CommonWebTestCase
{
    /**
     * @param array{0: string, 1: string, 2: string} $expectedLinks
     */
    #[DataProvider('provideCanonicalLoginFormAndLocalizedLinksCases')]
    public function testCanonicalLoginFormAndLocalizedLinks(string $path, array $expectedLinks): void
    {
        $client = $this->getHostClient();
        $this->seedLoginPage($client->getContainer()->get(EntityManagerInterface::class));
        $crawler = $client->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.login-shell.login-theme-darkwood'));
        self::assertCount(1, $crawler->filter('.login-brand h1:contains("Darkwood Identity")'));
        self::assertCount(1, $crawler->filter('form#login_form'));
        self::assertCount(1, $crawler->filter('form#login_form input[name="_csrf_token"]'));
        self::assertCount(1, $crawler->filter('form#login_form input[name="username"]'));
        self::assertCount(1, $crawler->filter('form#login_form input[name="password"]'));
        self::assertCount(1, $crawler->filter('form#login_form input[type="submit"]'));

        $links = $crawler->filter('.login-links p');
        self::assertCount(3, $links);
        self::assertSame($expectedLinks[0], trim($links->eq(0)->text(null, true)));
        self::assertSame($expectedLinks[1], trim($links->eq(1)->text(null, true)));
        self::assertStringContainsString($expectedLinks[2], (string) $links->eq(2)->filter('a')->attr('href'));
    }

    /**
     * @return iterable<string, array{string, array{string, string, string}}>
     */
    public static function provideCanonicalLoginFormAndLocalizedLinksCases(): iterable
    {
        yield 'French' => [
            '/fr/login',
            [
                "Si vous n'avez pas encore de compte, vous devez vous inscrire.",
                'Si vous avez oublié votre mot de passe, réinitialisez-le.',
                '/fr/inscription/renvoyer-email',
            ],
        ];

        yield 'English' => [
            '/login',
            [
                'If you do not have an account yet, you need to register.',
                'If you forgot your password, reset it.',
                '/register/resend-email',
            ],
        ];

        yield 'German' => [
            '/de/login',
            [
                'Wenn Sie noch kein Konto haben, müssen Sie sich registrieren.',
                'Wenn Sie Ihr Passwort vergessen haben, setzen Sie es zurück.',
                '/de/registrieren/e-mail-erneut-senden',
            ],
        ];
    }

    private function seedLoginPage(EntityManagerInterface $entityManager): void
    {
        if (null !== $entityManager->getRepository(Page::class)->findOneBy(['ref' => 'login'])) {
            return;
        }

        $site = new Site();
        $site->setName('Darkwood');
        $site->setRef('darkwood');
        $site->setHost('darkwood.localhost');
        $site->setPosition(1);
        $site->setActive(true);

        $page = new Page();
        $page->setRef('login');
        $page->setSite($site);

        foreach (['fr', 'en', 'de'] as $locale) {
            $translation = new PageTranslation();
            $translation->setLocale($locale);
            $translation->setTitle('Login');
            $translation->setActive(true);
            $page->addTranslation($translation);
        }

        $entityManager->persist($site);
        $entityManager->persist($page);
        $entityManager->flush();
    }
}
