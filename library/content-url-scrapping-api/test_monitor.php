<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Request;

$_ENV['APP_SECRET'] = 'monitoring-test';
$_ENV['DATABASE_URL'] = 'sqlite:///:memory:';
$_ENV['CORS_ALLOW_ORIGIN'] = '^http://localhost$';

$kernel = new Kernel('test', true);
$request = Request::create('http://localhost/ping');
$response = $kernel->handle($request);
if (200 !== $response->getStatusCode() || ['status' => 'OK'] !== json_decode(
    (string) $response->getContent(),
    true,
    flags: JSON_THROW_ON_ERROR,
)) {
    throw new RuntimeException('Unexpected /ping response.');
}

$application = new Application($kernel);
$listing = new CommandTester($application->find('monitor:list'));
if (0 !== $listing->execute([]) || !str_contains($listing->getDisplay(), 'php_extensions Extension Loaded')
    || !str_contains($listing->getDisplay(), 'disk_usage Disk Usage')) {
    throw new RuntimeException('Missing monitoring checks.');
}

$health = new CommandTester($application->find('monitor:health'));
if (0 !== $health->execute(['checkName' => 'php_extensions', '--nagios' => true])
    || !str_contains($health->getDisplay(), 'OK Extension Loaded')) {
    throw new RuntimeException('The PHP extension check failed.');
}
if (3 !== $health->execute(['checkName' => 'does_not_exist', '--nagios' => true])) {
    throw new RuntimeException('An unknown check must report unknown to Nagios.');
}

echo "Monitoring route, checks and Nagios exit status passed.\n";
