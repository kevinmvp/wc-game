<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
/**
 * Handles participant registration and lookup operations.
 */
class ParticipantModel extends BaseModel
{
    /**
     * Returns all participants ordered by name for admin management screens.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allOrdered(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, team_name, mobile, created_at, updated_at
             FROM league_participants
             ORDER BY name ASC, team_name ASC, id ASC'
        );
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        return $rows;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, team_name, mobile, password, created_at, updated_at FROM league_participants WHERE id = :id LIMIT 1'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Finds a participant by mobile number.
     *
     * @return array<string, mixed>|null
     */
    public function findByMobile(string $mobile): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, team_name, mobile, password, created_at, updated_at FROM league_participants WHERE mobile = :mobile LIMIT 1'
        );
        $statement->bindValue(':mobile', $mobile, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch();

        return $row === false ? null : $row;
    }
    /**
     * Creates a new participant.
     */
    public function create(string $name, string $teamName, string $mobile, string $password): int
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $statement = $this->connection->prepare(
            'INSERT INTO league_participants (name, team_name, mobile, password, created_at, updated_at) VALUES (:name, :team_name, :mobile, :password, NOW(), NOW())'
        );
        $statement->bindValue(':name', $name, PDO::PARAM_STR);
        $statement->bindValue(':team_name', $teamName, PDO::PARAM_STR);
        $statement->bindValue(':mobile', $mobile, PDO::PARAM_STR);
        $statement->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
        $statement->execute();

        return (int) $this->connection->lastInsertId();
    }

    /**
     * Updates participant profile values by id.
     */
    public function updateById(int $id, string $name, string $teamName): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE league_participants SET name = :name, team_name = :team_name, updated_at = NOW() WHERE id = :id'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->bindValue(':name', $name, PDO::PARAM_STR);
        $statement->bindValue(':team_name', $teamName, PDO::PARAM_STR);

        return $statement->execute();
    }

    /**
     * Registers a participant into the league table using mobile as the unique key.
     * Existing participants are refreshed with the latest name and team name.
     *
     * @return array<string, mixed>
     */
    public function registerForLeague(string $name, string $teamName, string $mobile, string $password): array
    {
        $existing = $this->findByMobile($mobile);
        if ($existing !== null) {
            // For simplicity, we are not updating the password if the participant already exists
            // You might want to add a separate function for password updates if needed
            $this->updateById((int) $existing['id'], $name, $teamName);

            $updated = $this->findById((int) $existing['id']);
            if ($updated !== null) {
                return $updated;
            }
        }

        $newId = $this->create($name, $teamName, $mobile, $password);
        $created = $this->findById($newId);

        return $created ?? [
            'id' => $newId,
            'name' => $name,
            'team_name' => $teamName,
            'mobile' => $mobile,
            'password' => password_hash($password, PASSWORD_DEFAULT), // This should ideally come from the DB after creation
        ];
    }

    /**
     * Creates or refreshes a participant identified by mobile number.
     *
     * @return array<string, mixed>
     */
    public function upsertByMobile(string $name, string $teamName, string $mobile, string $password): array
    {
        return $this->registerForLeague($name, $teamName, $mobile, $password);
    }
}

spl_autoload_register(static function (string $className): void {
    $prefix = 'App\\';
    if (!str_starts_with($className, $prefix)) {
        return;
    }

    $relativeClass = substr($className, strlen($prefix));
    $filePath = ROOT_PATH . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($filePath)) {
        require_once $filePath;
    }
});

