<?php

class Database
{
    public static function getConfig(): array
    {
        $configPath = __DIR__ . '/config.local.php';
        if (!is_file($configPath)) {
            throw new RuntimeException(
                'Missing backend/config/config.local.php. Copy config.example.php and set local credentials.'
            );
        }

        $config = require $configPath;
        if (!is_array($config)) {
            throw new RuntimeException('The local configuration must return an array.');
        }

        return $config;
    }

    public static function getConnection(): PDO
    {
        $config = self::getConfig();
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['database'],
            $config['charset'] ?? 'utf8mb4'
        );

        return new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}