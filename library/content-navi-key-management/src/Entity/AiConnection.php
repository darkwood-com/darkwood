<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class AiConnection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $label = '';

    #[ORM\Column(length: 32)]
    private string $provider = 'anthropic';

    #[ORM\Column(length: 80)]
    private string $model = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $endpoint = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $ciphertextBlob = '';

    #[ORM\Column(length: 255)]
    private string $kmsKeyId = '';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setEndpoint(?string $endpoint): void
    {
        $this->endpoint = $endpoint !== '' ? $endpoint : null;
    }

    public function getCiphertextBlob(): string
    {
        return $this->ciphertextBlob;
    }

    public function setCiphertextBlob(string $ciphertextBlob): void
    {
        $this->ciphertextBlob = $ciphertextBlob;
    }

    public function getKmsKeyId(): string
    {
        return $this->kmsKeyId;
    }

    public function setKmsKeyId(string $kmsKeyId): void
    {
        $this->kmsKeyId = $kmsKeyId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    public function onPersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
