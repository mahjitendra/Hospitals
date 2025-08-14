<?php

namespace App\Config;

/**
 * Class Database
 *
 * Contains the database configuration settings.
 *
 * For security, these settings should be loaded from an environment file (.env)
 * and not hard-coded directly in the file, especially for production environments.
 * We use getenv() to read from environment variables, with sensible defaults for local development.
 */
class Database
{
    /**
     * Returns the database configuration array.
     *
     * @return array
     */
    public static function getConfig(): array
    {
        return [
            'driver'    => getenv('DB_DRIVER') ?: 'mysql',
            'host'      => getenv('DB_HOST') ?: '127.0.0.1',
            'port'      => getenv('DB_PORT') ?: '3306',
            'database'  => getenv('DB_NAME') ?: 'hospital_erp',
            'username'  => getenv('DB_USER') ?: 'root',
            'password'  => getenv('DB_PASS') ?: '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ];
    }
}
