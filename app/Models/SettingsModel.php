<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Manages league settings persisted in the league_settings table.
 */
class SettingsModel extends BaseModel
{
    /**
     * Retrieves a setting value by key.
     */
    public function get(string $key, string $default = ''): string
    {
        $statement = $this->connection->prepare(
            'SELECT setting_value FROM league_settings WHERE setting_key = :setting_key LIMIT 1'
        );
        $statement->bindValue(':setting_key', $key, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch();

        return $row === false ? $default : (string) ($row['setting_value'] ?? $default);
    }

    /**
     * Retrieves an integer setting value by key.
     */
    public function getInt(string $key, int $default = 0): int
    {
        return max(0, (int) $this->get($key, (string) $default));
    }

    /**
     * Sets a setting value by key (inserts or updates).
     */
    public function set(string $key, string $value): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO league_settings (setting_key, setting_value, created_at, updated_at)
             VALUES (:setting_key, :setting_value, NOW(), NOW())
             ON DUPLICATE KEY UPDATE setting_value = :setting_value2, updated_at = NOW()'
        );
        $statement->bindValue(':setting_key', $key, PDO::PARAM_STR);
        $statement->bindValue(':setting_value', $value, PDO::PARAM_STR);
        $statement->bindValue(':setting_value2', $value, PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * Returns all settings as a key-value array.
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        $statement = $this->connection->prepare(
            'SELECT setting_key, setting_value FROM league_settings ORDER BY setting_key ASC'
        );
        $statement->execute();

        /** @var array<int, array<string, string>> $rows */
        $rows = $statement->fetchAll();

        $settings = [];
        foreach ($rows as $row) {
            $settings[(string) $row['setting_key']] = (string) $row['setting_value'];
        }

        return $settings;
    }
}
