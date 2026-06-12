<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Handles database operations for players.
 */
class PlayerModel extends BaseModel
{
    /**
     * Returns all players ordered by id descending.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, email, level, created_at, updated_at FROM players ORDER BY id DESC'
        );
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        return $rows;
    }

    /**
     * Finds a player by id.
     *
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, email, level, created_at, updated_at FROM players WHERE id = :id LIMIT 1'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Inserts a new player and returns its generated id.
     */
    public function create(string $name, string $email, int $level): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO players (name, email, level, created_at, updated_at) VALUES (:name, :email, :level, NOW(), NOW())'
        );
        $statement->bindValue(':name', $name, PDO::PARAM_STR);
        $statement->bindValue(':email', $email, PDO::PARAM_STR);
        $statement->bindValue(':level', $level, PDO::PARAM_INT);
        $statement->execute();

        return (int) $this->connection->lastInsertId();
    }

    /**
     * Updates an existing player.
     */
    public function updateById(int $id, string $name, string $email, int $level): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE players SET name = :name, email = :email, level = :level, updated_at = NOW() WHERE id = :id'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->bindValue(':name', $name, PDO::PARAM_STR);
        $statement->bindValue(':email', $email, PDO::PARAM_STR);
        $statement->bindValue(':level', $level, PDO::PARAM_INT);

        return $statement->execute();
    }

    /**
     * Deletes a player by id.
     */
    public function deleteById(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM players WHERE id = :id');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        return $statement->execute();
    }
}
