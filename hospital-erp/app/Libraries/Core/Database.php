<?php

namespace App\Libraries\Core;

use PDO;
use PDOException;
use App\Config\Database as DbConfig;

/**
 * Class Database
 *
 * Handles the database connection using the Singleton pattern to ensure
 * that there is only one instance of the PDO connection.
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * The constructor is private to prevent direct creation of the object.
     */
    private function __construct() {}

    /**
     * Prevent cloning of the instance.
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the instance.
     */
    public function __wakeup() {}

    /**
     * Gets the single instance of the PDO connection.
     *
     * @return PDO The PDO database connection instance.
     * @throws PDOException If the connection fails.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = DbConfig::getConfig();

            $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $options);
            } catch (PDOException $e) {
                // In a real application, you would log this error and show a generic user-friendly error page.
                // For now, re-throwing the exception is sufficient for development.
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }

        return self::$instance;
    }
}
