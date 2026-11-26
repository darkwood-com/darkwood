<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    require dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    new Dotenv()->bootEnv(dirname(__DIR__) . '/.env');
}

$signingKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
if (false === $signingKey || !openssl_pkey_export($signingKey, $privateKey) || false === ($details = openssl_pkey_get_details($signingKey))) {
    throw new RuntimeException('Could not generate the test signing key.');
}

foreach ([
    'JWT_SECRET_KEY' => $privateKey,
    'JWT_PUBLIC_KEY' => $details['key'],
    'JWT_PASSPHRASE' => '',
    'OAUTH_PRIVATE_KEY' => $privateKey,
    'OAUTH_PUBLIC_KEY' => $details['key'],
    'OAUTH_ENCRYPTION_KEY' => bin2hex(random_bytes(32)),
] as $name => $value) {
    $_SERVER[$name] ??= $_ENV[$name] ?? $value;
}

if ($_SERVER['APP_DEBUG']) {
    umask(0o000);
}
