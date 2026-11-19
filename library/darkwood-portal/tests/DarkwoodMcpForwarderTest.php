<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\ApiKey;
use App\Entity\Game\Player;
use App\Entity\User;
use App\EventSubscriber\DarkwoodBetaAccessSubscriber;
use App\EventSubscriber\DarkwoodRateLimitSubscriber;
use App\Repository\ApiKeyUsageRepository;
use App\Service\ApiKeyResolverService;
use App\Service\DarkwoodMcpForwarderService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class DarkwoodMcpForwarderTest extends TestCase
{
    private function forwarder(Request $request, ?User $user = null, int $status = 200, array $payload = []): DarkwoodMcpForwarderService
    {
        $stack = new RequestStack();
        $stack->push($request);
        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')->willReturn('/api/darkwood/state');
        $storage = new TokenStorage();
        if (null !== $user) {
            $storage->setToken(new UsernamePasswordToken($user, 'main', ['ROLE_USER']));
        }
        $kernel = $this->createStub(HttpKernelInterface::class);
        $kernel->method('handle')->willReturnCallback(function (Request $sub, int $type) use ($status, $payload, $request) {
            self::assertSame(HttpKernelInterface::SUB_REQUEST, $type);
            self::assertTrue($sub->attributes->get(DarkwoodMcpForwarderService::FORWARDED_ATTRIBUTE));
            self::assertSame('api.darkwood.localhost', $sub->getHost());
            self::assertSame($request->headers->get('X-API-Key'), $sub->headers->get('X-API-Key'));
            self::assertFalse($sub->headers->has(DarkwoodMcpForwarderService::APPROVAL_HEADER));
            if ($request->hasSession()) {
                self::assertSame($request->getSession(), $sub->getSession());
            }

            return new JsonResponse($payload, $status);
        });

        return new DarkwoodMcpForwarderService($kernel, $stack, $router, $storage);
    }

    public function testObservationKeepsTransportContextAndRemovesSecretFields(): void
    {
        $request = Request::create('https://api.darkwood.localhost/mcp');
        $request->headers->set('X-API-Key', 'fixture-key');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $result = $this->forwarder($request, payload: ['state' => 'not-logged', 'nested' => ['token' => 'fixture-private', 'life' => 50]])->forward('api_darkwood_get_state');
        self::assertSame(['state' => 'not-logged', 'nested' => ['life' => 50]], $result);
    }

    public function testObservationDoesNotInitializePlayer(): void
    {
        $this->expectException(HttpExceptionInterface::class);
        $this->expectExceptionMessage('Initialize your player');
        $this->forwarder(Request::create('https://api.darkwood.localhost/mcp'), new User())->forward('api_darkwood_get_state');
    }

    public function testActionRequiresTransportApprovalForExactQueryAndConsumesIt(): void
    {
        $request = Request::create('https://api.darkwood.localhost/mcp');
        $user = new User();
        (new \ReflectionProperty(User::class, 'id'))->setValue($user, 7);
        $user->setPlayer(new Player());
        $forwarder = $this->forwarder($request, $user, payload: ['state' => 'combat']);
        $query = ['state' => 'combat', 'actionFight' => '1'];
        foreach ([null, str_repeat('a', 64)] as $approval) {
            if (null !== $approval) {
                $request->headers->set(DarkwoodMcpForwarderService::APPROVAL_HEADER, $approval);
            }
            try {
                $forwarder->forward('api_darkwood_post_action', 'POST', ['query' => $query]);
                self::fail('Unapproved action accepted.');
            } catch (HttpExceptionInterface $error) {
                self::assertSame(403, $error->getStatusCode());
            }
        }
        ksort($query);
        $request->headers->set(DarkwoodMcpForwarderService::APPROVAL_HEADER, hash('sha256', json_encode(['user_id' => 7, 'query' => $query], JSON_THROW_ON_ERROR)));
        self::assertSame(['state' => 'combat'], $forwarder->forward('api_darkwood_post_action', 'POST', ['query' => $query]));
        $this->expectException(HttpExceptionInterface::class);
        $forwarder->forward('api_darkwood_post_action', 'POST', ['query' => $query]);
    }

    public function testHttpErrorsAreNotReturnedAsSuccessfulToolDataOrDebugContent(): void
    {
        foreach ([401, 403, 404, 429, 500] as $status) {
            try {
                $this->forwarder(Request::create('https://api.darkwood.localhost/mcp'), status: $status, payload: ['exception' => 'fixture-private'])->forward('api_darkwood_get_state');
                self::fail('HTTP error hidden.');
            } catch (HttpExceptionInterface $error) {
                self::assertSame($status, $error->getStatusCode());
                self::assertStringNotContainsString('fixture-private', $error->getMessage());
            }
        }
    }

    public function testBetaGateAlsoRunsForMarkedMcpSubrequest(): void
    {
        $request = Request::create('/api/darkwood/state');
        $request->attributes->set(DarkwoodMcpForwarderService::FORWARDED_ATTRIBUTE, true);
        $kernel = $this->createStub(HttpKernelInterface::class);
        $resolver = $this->createMock(ApiKeyResolverService::class);
        $resolver->expects(self::once())->method('resolveForGate')->willReturn(null);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);
        (new DarkwoodBetaAccessSubscriber($resolver))->onKernelRequest($event);
        self::assertSame(401, $event->getResponse()?->getStatusCode());
    }

    public function testQuotaCannotBeBypassedViaMarkedMcpSubrequest(): void
    {
        $request = Request::create('/api/darkwood/action', 'POST');
        $request->attributes->set(DarkwoodMcpForwarderService::FORWARDED_ATTRIBUTE, true);
        $request->attributes->set('_route', 'api_darkwood_post_action');
        $key = (new ApiKey())->setIsPremium(false)->setDailyActionLimit(0);
        $request->attributes->set('api_key', $key);
        $registry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $registry->expects(self::never())->method('getManagerForClass');
        $repository = new ApiKeyUsageRepository($registry);
        $event = new RequestEvent($this->createStub(HttpKernelInterface::class), $request, HttpKernelInterface::SUB_REQUEST);
        (new DarkwoodRateLimitSubscriber($repository))->onKernelRequest($event);
        self::assertSame(429, $event->getResponse()?->getStatusCode());
    }

    public function testMalformedActionsAreRejectedBeforeTransport(): void
    {
        $processor = new \App\State\DarkwoodActionProcessor($this->forwarder(Request::create('https://api.darkwood.localhost/mcp')));
        foreach ([
            ['state' => 'login', '_password' => 'fixture'],
            ['state' => 'combat', 'actionFight' => 'false'],
            ['state' => 'combat', 'actionFight' => '1', 'actionEndFight' => '1'],
            ['state' => 'daily-battle', 'actionUsePotion' => true],
            ['state' => 'main', 'actionFight' => true],
            ['state' => 'info', 'actionAddPoint' => 'gold'],
            ['state' => ['combat']],
        ] as $query) {
            try {
                $processor->process(new \App\Dto\DarkwoodMcpActionInput($query), new \ApiPlatform\Metadata\McpTool());
                self::fail('Invalid action reached the transport.');
            } catch (HttpExceptionInterface $error) {
                self::assertSame(400, $error->getStatusCode());
            }
        }
    }

    public function testArchiveIdentifiersRejectInvalidDatesAndPathInjection(): void
    {
        $processor = new \App\State\DarkwoodArchiveGetProcessor($this->forwarder(Request::create('https://api.darkwood.localhost/mcp')));
        foreach (['2026-02-30', '../../state', '2026-1-1', ''] as $id) {
            try {
                $processor->process(new \App\Dto\DarkwoodArchiveIdInput($id), new \ApiPlatform\Metadata\McpTool());
                self::fail('Invalid archive date reached the transport.');
            } catch (HttpExceptionInterface $error) {
                self::assertSame(400, $error->getStatusCode());
            }
        }
    }

    public function testDailyQuotaStopsAtTheLimitWithARealSqliteConnection(): void
    {
        $connection = \Doctrine\DBAL\DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('CREATE TABLE api_key_usage (id INTEGER PRIMARY KEY AUTOINCREMENT, api_key_id INTEGER NOT NULL, usage_date DATE NOT NULL, usage_count INTEGER NOT NULL, UNIQUE(api_key_id, usage_date))');
        $metadata = new \Doctrine\ORM\Mapping\ClassMetadata(\App\Entity\ApiKeyUsage::class);
        $metadata->setPrimaryTable(['name' => 'api_key_usage']);
        $manager = $this->createStub(\Doctrine\ORM\EntityManagerInterface::class);
        $manager->method('getConnection')->willReturn($connection);
        $manager->method('getClassMetadata')->willReturn($metadata);
        $registry = $this->createStub(\Doctrine\Persistence\ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($manager);
        $repository = new ApiKeyUsageRepository($registry);
        $key = new ApiKey();
        (new \ReflectionProperty(ApiKey::class, 'id'))->setValue($key, 7);
        $date = new \DateTimeImmutable('2026-11-19T00:00:00Z');
        self::assertTrue($repository->incrementIfBelowLimit($key, $date, 2));
        self::assertTrue($repository->incrementIfBelowLimit($key, $date, 2));
        self::assertFalse($repository->incrementIfBelowLimit($key, $date, 2));
        self::assertSame(2, (int) $connection->fetchOne('SELECT usage_count FROM api_key_usage'));
        self::assertSame(1, (int) $connection->fetchOne('SELECT COUNT(*) FROM api_key_usage'));
        $connection->close();
    }
}
