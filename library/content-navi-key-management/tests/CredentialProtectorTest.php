<?php

declare(strict_types=1);

namespace App\Tests;

use App\CredentialProtector;
use App\Entity\AiConnection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CredentialProtectorTest extends KernelTestCase
{
    public function testEncryptAndDecryptRoundTripLeavesNoPlaintextOnTheEntity(): void
    {
        self::bootKernel();

        $protector = self::getContainer()->get(CredentialProtector::class);
        $plaintext = 'sk-ant-test-plaintext-key-do-not-store';

        $connection = new AiConnection();
        $protector->persist($connection, $protector->encrypt($plaintext));

        self::assertSame('app-key', $connection->getKmsKeyId());
        self::assertStringNotContainsString($plaintext, $connection->getCiphertextBlob());
        self::assertNotSame($plaintext, $connection->getCiphertextBlob());
        self::assertSame($plaintext, $protector->decrypt($connection));
    }
}
