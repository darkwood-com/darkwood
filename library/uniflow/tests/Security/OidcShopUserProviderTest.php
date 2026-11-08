<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User\ShopUser;
use App\Entity\User\UserOAuth;
use App\Repository\User\ShopUserRepository;
use App\Security\OidcShopUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

final class OidcShopUserProviderTest extends TestCase
{
    public function testItLoadsAnExplicitlyLinkedShopUser(): void
    {
        $user = new ShopUser();
        $account = new UserOAuth();
        $account->setUser($user);
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(['provider' => 'example', 'identifier' => 'stable-subject'])
            ->willReturn($account)
        ;

        $provider = $this->createProvider($repository);

        self::assertSame($user, $provider->loadUserByIdentifier('stable-subject', ['email' => 'ignored@example.test']));
    }

    public function testItNeverProvisionsAnUnknownSubject(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->willReturn(null);
        $provider = $this->createProvider($repository);

        $this->expectException(UserNotFoundException::class);

        $provider->loadUserByIdentifier('unknown-subject', ['email' => 'existing@example.test']);
    }

    public function testItRefreshesTheShopUserByLocalUid(): void
    {
        $user = new ShopUser();
        $shopUsers = $this->createMock(ShopUserRepository::class);
        $shopUsers->expects(self::once())
            ->method('findOneBy')
            ->with(['uid' => $user->getUid()])
            ->willReturn($user)
        ;
        $provider = $this->createProvider($this->createMock(EntityRepository::class), $shopUsers);

        self::assertSame($user, $provider->refreshUser($user));
    }

    /**
     * @param EntityRepository<UserOAuth> $repository
     */
    private function createProvider(EntityRepository $repository, ?ShopUserRepository $shopUsers = null): OidcShopUserProvider
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->with(UserOAuth::class)->willReturn($repository);

        return new OidcShopUserProvider(
            $entityManager,
            $shopUsers ?? $this->createMock(ShopUserRepository::class),
            'example',
        );
    }
}
