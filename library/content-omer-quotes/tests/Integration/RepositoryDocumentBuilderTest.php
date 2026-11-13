<?php
declare(strict_types=1);
namespace App\Tests\Integration;
use App\Application\Service\RepositoryDocumentBuilder;
use App\Domain\Repository\RepositoryId;
use App\Domain\Repository\RepositoryMetadata;
use PHPUnit\Framework\TestCase;

final class RepositoryDocumentBuilderTest extends TestCase
{
    public function testBuildFromStubInput(): void
    {
        $builder = new RepositoryDocumentBuilder();
        $meta = new RepositoryMetadata(RepositoryId::fromFullName('acme/demo'), 'demo', 'acme/demo', 'acme', 'Demo repo', 'https://example/repo.git', 'main', ['php']);
        $doc = $builder->build($meta, ['readme line', 'notes line']);
        self::assertStringContainsString('demo', $doc->toCanonicalText());
        self::assertStringContainsString('readme line', $doc->toCanonicalText());
    }
}
