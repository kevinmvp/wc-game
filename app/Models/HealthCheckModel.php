<?php
declare(strict_types=1);

namespace App\Models;

use PDOException;

/**
 * Provides lightweight infrastructure checks.
 */
class HealthCheckModel extends BaseModel
{
    /**
     * Verifies that the database responds to a simple prepared query.
     */
    public function pingDatabase(): bool
    {
        try {
            $statement = $this->connection->prepare('SELECT 1 AS status');
            $statement->execute();
            $result = $statement->fetch();

            return (int) ($result['status'] ?? 0) === 1;
        } catch (PDOException $exception) {
            return false;
        }
    }
}
