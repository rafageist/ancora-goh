<?php

declare(strict_types=1);

namespace Ancora\Portal;

use PDO;

/** PDO connection with native prepared statements and exceptions. */
final class Database
{
    public static function connect(Config $config): PDO
    {
        $password = '';
        if (($config->get('PORTAL_DB_PASSWORD_FILE', '') !== '') || ($config->get('PORTAL_DB_PASSWORD', '') !== '')) {
            $password = $config->secret('PORTAL_DB_PASSWORD');
        }
        return self::fromDsn($config->get('PORTAL_DB_DSN'), $config->get('PORTAL_DB_USER', ''), $password);
    }

    public static function fromDsn(string $dsn, string $user = '', string $password = ''): PDO
    {
        $pdo = new PDO($dsn, $user === '' ? null : $user, $password === '' ? null : $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]);
        $pdo->exec("SET statement_timeout = '5s'");
        $pdo->exec("SET TIME ZONE 'UTC'");
        return $pdo;
    }
}
