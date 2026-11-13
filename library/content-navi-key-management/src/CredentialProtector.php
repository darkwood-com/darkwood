<?php

declare(strict_types=1);

namespace App;

use App\Entity\AiConnection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\KeyManagement\Ciphertext;
use Symfony\Component\KeyManagement\DecrypterInterface;
use Symfony\Component\KeyManagement\EncrypterInterface;

/**
 * Direct KeyManagement encryption for API keys (small payloads, well under the 4 KB KMS limit).
 *
 * @experimental uses Symfony 8.2 KeyManagement
 */
final class CredentialProtector
{
    public function __construct(
        private EncrypterInterface $encrypter,
        private DecrypterInterface $decrypter,
        #[Autowire('%kms.key_id%')]
        private string $keyId,
    ) {
    }

    public function encrypt(#[\SensitiveParameter] string $apiKey): Ciphertext
    {
        return $this->encrypter->encrypt($this->keyId, $apiKey);
    }

    public function persist(AiConnection $connection, Ciphertext $ciphertext): void
    {
        $connection->setCiphertextBlob(base64_encode($ciphertext->blob));
        $connection->setKmsKeyId($ciphertext->keyId);
    }

    public function decrypt(AiConnection $connection): string
    {
        $blob = base64_decode($connection->getCiphertextBlob(), true);
        if ($blob === false) {
            throw new \RuntimeException('Stored ciphertext is not valid base64.');
        }

        return $this->decrypter->decrypt(new Ciphertext($blob, $connection->getKmsKeyId()));
    }

    public function keyId(): string
    {
        return $this->keyId;
    }
}
