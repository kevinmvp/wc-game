<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Provides a shared PDO connection for application models.
 */
abstract class BaseModel
{
    /**
     * Active PDO connection used by inheriting models.
     */
    protected PDO $connection;

    /**
     * @param array<string, mixed> $databaseConfig Database configuration values.
     */
    public function __construct(protected array $databaseConfig)
    {
        $this->connection = $this->createConnection();
    }

    /**
     * Builds and returns a PDO connection based on configuration.
     */
    protected function createConnection(): PDO
    {
        $driver = (string) ($this->databaseConfig['driver'] ?? 'mysql');
        $host = (string) ($this->databaseConfig['host'] ?? '127.0.0.1');
        $port = (int) ($this->databaseConfig['port'] ?? 3306);
        $database = (string) ($this->databaseConfig['database'] ?? '');
        $charset = (string) ($this->databaseConfig['charset'] ?? 'utf8mb4');

        $dsn = sprintf('%s:host=%s;port=%d;dbname=%s;charset=%s', $driver, $host, $port, $database, $charset);

        return new PDO(
            $dsn,
            (string) ($this->databaseConfig['username'] ?? ''),
            (string) ($this->databaseConfig['password'] ?? ''),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
}
