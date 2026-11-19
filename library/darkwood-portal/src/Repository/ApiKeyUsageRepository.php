<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ApiKey;
use App\Entity\ApiKeyUsage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\ApiKeyUsage>
 */
final class ApiKeyUsageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiKeyUsage::class);
    }

    public function incrementIfBelowLimit(ApiKey $apiKey, \DateTimeImmutable $date, int $limit): bool
    {
        $connection = $this->getEntityManager()->getConnection();
        $table = $this->getClassMetadata()->getTableName();
        $dateValue = $date->format('Y-m-d');

        // Ensure row exists, then atomically increment only if still under limit.
        $insert = $connection->getDatabasePlatform() instanceof AbstractMySQLPlatform
            ? 'INSERT IGNORE INTO %s (api_key_id, usage_date, usage_count) VALUES (:apiKeyId, :usageDate, 0)'
            : 'INSERT INTO %s (api_key_id, usage_date, usage_count) VALUES (:apiKeyId, :usageDate, 0) ON CONFLICT (api_key_id, usage_date) DO NOTHING';
        $connection->executeStatement(
            \sprintf(
                $insert,
                $table
            ),
            [
                'apiKeyId' => $apiKey->getId(),
                'usageDate' => $dateValue,
            ]
        );

        $updated = $connection->executeStatement(
            \sprintf(
                'UPDATE %s
                 SET usage_count = usage_count + 1
                 WHERE api_key_id = :apiKeyId
                   AND usage_date = :usageDate
                   AND usage_count < :dailyLimit',
                $table
            ),
            [
                'apiKeyId' => $apiKey->getId(),
                'usageDate' => $dateValue,
                'dailyLimit' => $limit,
            ]
        );

        return 1 === $updated;
    }
}
