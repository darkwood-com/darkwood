<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\User;
use App\Security\IdentityUserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

final class IdentityUserCheckerTest extends TestCase
{
    public function testNewAccountCannotAuthenticateBeforeVerification(): void
    {
        $user = new User();

        $this->expectExceptionMessage('email_verification.login_required');
        $this->expectException(CustomUserMessageAccountStatusException::class);
        (new IdentityUserChecker())->checkPostAuth($user);
    }

    public function testEmailChangeRemovesVerificationAndInvalidatesEarlierLinks(): void
    {
        $user = new User();
        $user->setEmail('first@example.com');
        $user->setIsVerified(true);
        $user->setResetToken('old-reset');
        (new IdentityUserChecker())->checkPostAuth($user);
        $user->setEmail('second@example.com');
        $user->setEmail('first@example.com');

        self::assertFalse($user->isVerified());
        self::assertTrue($user->isEmailVerificationPending());
        self::assertSame(2, $user->getEmailVerificationVersion());
        self::assertNull($user->getResetToken());
    }

    public function testVerificationCannotLiftASuspension(): void
    {
        $user = new User();
        $user->setEnabled(false);
        $user->setIsVerified(true);

        $this->expectExceptionMessage('email_verification.account_disabled');
        $this->expectException(CustomUserMessageAccountStatusException::class);
        (new IdentityUserChecker())->checkPostAuth($user);
    }
}
