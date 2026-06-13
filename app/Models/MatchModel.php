<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Handles match schedule and result persistence.
 */
class MatchModel extends BaseModel
{
    public const RESULT_HOME = 'home';
    public const RESULT_AWAY = 'away';
    public const RESULT_DRAW = 'draw';
    public const STAGE_GROUP = 'Group Stage';
    public const STAGE_ROUND_OF_32 = 'Round of 32';
    public const STAGE_ROUND_OF_16 = 'Round of 16';
    public const STAGE_QUARTER_FINALS = 'Quarter-Finals';
    public const STAGE_SEMI_FINALS = 'Semi-Finals';
    public const STAGE_THIRD_PLACE = 'Third-Place Playoff';
    public const STAGE_FINAL = 'Final';

    /**
     * @return array<int, string>
     */
    public static function allowedResults(): array
    {
        return [self::RESULT_HOME, self::RESULT_AWAY, self::RESULT_DRAW];
    }

    /**
     * @return array<int, string>
     */
    public static function allowedStages(): array
    {
        return [
            self::STAGE_GROUP,
            self::STAGE_ROUND_OF_32,
            self::STAGE_ROUND_OF_16,
            self::STAGE_QUARTER_FINALS,
            self::STAGE_SEMI_FINALS,
            self::STAGE_THIRD_PLACE,
            self::STAGE_FINAL,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function allowedGroups(): array
    {
        return [
            'Group A',
            'Group B',
            'Group C',
            'Group D',
            'Group E',
            'Group F',
            'Group G',
            'Group H',
            'Group I',
            'Group J',
            'Group K',
            'Group L',
        ];
    }

    /**
     * Returns all matches scheduled on a specific date.
      * Optionally filters to upcoming matches when current time is provided.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allByDate(string $matchDate, ?string $currentTime = null): array
    {
        $sql = 'SELECT id, stage, group_name, match_date, local_time, home_team, away_team, venue, venue_city, notes, result, created_at, updated_at
             FROM league_matches
                WHERE match_date = :match_date';
        $bindings = [':match_date' => $matchDate];

        if ($currentTime !== null) {
            $sql .= ' AND (local_time IS NULL OR CONCAT(match_date, \' \', local_time) >= :current_datetime)';
            $bindings[':current_datetime'] = $matchDate . ' ' . $currentTime;
        }
        $sql .= ' ORDER BY local_time ASC, id ASC';
        $statement = $this->connection->prepare($sql);
        foreach ($bindings as $parameter => $value) {
            $statement->bindValue($parameter, $value, PDO::PARAM_STR);
        }
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        return $rows;
    }

    /**
     * Returns all matches ordered by date and id.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allOrdered(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, stage, group_name, match_date, local_time, home_team, away_team, venue, venue_city, notes, result, created_at, updated_at
             FROM league_matches
             ORDER BY match_date DESC, local_time DESC, id DESC'
        );
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        return $rows;
    }

    /**
     * Returns fixtures filtered by stage and optional group.
     *
     * @return array<int, array<string, mixed>>
     */
    public function filterFixtures(?string $stage, ?string $groupName): array
    {
        return $this->filterFixturesAdvanced($stage, $groupName, null, null);
    }
        /**
         * Returns fixtures filtered by stage, optional group, optional date, and optional venue.
         *
         * @return array<int, array<string, mixed>>
         */
    public function filterFixturesAdvanced(?string $stage, ?string $groupName, ?string $matchDate, ?string $venue): array
    {
        $sql = 'SELECT id, stage, group_name, match_date, local_time, home_team, away_team, venue, venue_city, notes, result, created_at, updated_at
                FROM league_matches
                WHERE 1 = 1';
        $bindings = [];

        if ($stage !== null && $stage !== '') {
            $sql .= ' AND stage = :stage';
            $bindings[':stage'] = $stage;
        }

        if ($groupName !== null && $groupName !== '') {
            $sql .= ' AND group_name = :group_name';
            $bindings[':group_name'] = $groupName;
        }

        if ($matchDate !== null && $matchDate !== '') {
            $sql .= ' AND match_date = :match_date';
            $bindings[':match_date'] = $matchDate;
        }

        if ($venue !== null && $venue !== '') {
            $sql .= ' AND venue = :venue';
            $bindings[':venue'] = $venue;
        }

        $sql .= ' ORDER BY match_date ASC, local_time ASC, id ASC';
        $statement = $this->connection->prepare($sql);
        foreach ($bindings as $parameter => $value) {
            $statement->bindValue($parameter, $value, PDO::PARAM_STR);
        }
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        return $rows;
    }

    /**
     * Returns paginated fixtures filtered by stage, group, date, and venue.
     *
     * @return array<int, array<string, mixed>>
     */
    public function filterFixturesPaginated(?string $stage, ?string $groupName, ?string $matchDate, ?string $venue, int $limit, int $offset): array
    {
        $sql = 'SELECT id, stage, group_name, match_date, local_time, home_team, away_team, venue, venue_city, notes, result, created_at, updated_at
                FROM league_matches
                WHERE 1 = 1';
        $bindings = [];

        if ($stage !== null && $stage !== '') {
            $sql .= ' AND stage = :stage';
            $bindings[':stage'] = $stage;
        }

        if ($groupName !== null && $groupName !== '') {
            $sql .= ' AND group_name = :group_name';
            $bindings[':group_name'] = $groupName;
        }

        if ($matchDate !== null && $matchDate !== '') {
            $sql .= ' AND match_date = :match_date';
            $bindings[':match_date'] = $matchDate;
        }

        if ($venue !== null && $venue !== '') {
            $sql .= ' AND venue = :venue';
            $bindings[':venue'] = $venue;
        }

        $sql .= ' ORDER BY match_date ASC, local_time ASC, id ASC LIMIT :limit OFFSET :offset';

        $statement = $this->connection->prepare($sql);
        foreach ($bindings as $parameter => $value) {
            $statement->bindValue($parameter, $value, PDO::PARAM_STR);
        }
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        return $rows;
    }

    /**
     * Counts fixtures matching the provided filters.
     */
    public function countFixtures(?string $stage, ?string $groupName, ?string $matchDate, ?string $venue): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM league_matches WHERE 1 = 1';
        $bindings = [];

        if ($stage !== null && $stage !== '') {
            $sql .= ' AND stage = :stage';
            $bindings[':stage'] = $stage;
        }

        if ($groupName !== null && $groupName !== '') {
            $sql .= ' AND group_name = :group_name';
            $bindings[':group_name'] = $groupName;
        }

        if ($matchDate !== null && $matchDate !== '') {
            $sql .= ' AND match_date = :match_date';
            $bindings[':match_date'] = $matchDate;
        }

        if ($venue !== null && $venue !== '') {
            $sql .= ' AND venue = :venue';
            $bindings[':venue'] = $venue;
        }

        $statement = $this->connection->prepare($sql);
        foreach ($bindings as $parameter => $value) {
            $statement->bindValue($parameter, $value, PDO::PARAM_STR);
        }
        $statement->execute();

        $row = $statement->fetch();

        return (int) ($row['total'] ?? 0);
    }

    /**
     * Returns the distinct venue options currently present in fixtures.
     *
     * @return array<int, string>
     */
    public function distinctVenues(): array
    {
        $statement = $this->connection->prepare(
            "SELECT DISTINCT venue
             FROM league_matches
             WHERE venue IS NOT NULL AND venue <> ''
             ORDER BY venue ASC"
        );
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        $venues = [];
        foreach ($rows as $row) {
            $venues[] = (string) $row['venue'];
        }

        return $venues;
    }

    /**
     * Removes placeholder knockout timeline rows until exact teams are known.
     */
    public function deleteNonGroupStageFixtures(): int
    {
        $statement = $this->connection->prepare(
            'DELETE FROM league_matches WHERE stage <> :stage'
        );
        $statement->bindValue(':stage', self::STAGE_GROUP, PDO::PARAM_STR);
        $statement->execute();

        return $statement->rowCount();
    }

    /**
     * Finds one match by id.
     *
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, stage, group_name, match_date, local_time, home_team, away_team, venue, venue_city, notes, result, created_at, updated_at
             FROM league_matches
             WHERE id = :id
             LIMIT 1'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Creates a new match entry.
     */
    public function create(string $matchDate, string $homeTeam, string $awayTeam): int
    {
        $statement = $this->connection->prepare(
            "INSERT INTO league_matches (stage, group_name, match_date, local_time, home_team, away_team, venue, venue_city, notes, result, created_at, updated_at)
             VALUES ('Group Stage', NULL, :match_date, NULL, :home_team, :away_team, NULL, NULL, NULL, NULL, NOW(), NOW())"
        );
        $statement->bindValue(':match_date', $matchDate, PDO::PARAM_STR);
        $statement->bindValue(':home_team', $homeTeam, PDO::PARAM_STR);
        $statement->bindValue(':away_team', $awayTeam, PDO::PARAM_STR);
        $statement->execute();

        return (int) $this->connection->lastInsertId();
    }

    /**
     * Creates an exact fixture row for later knockout-stage imports.
     */
    public function createDetailedFixture(
        string $stage,
        ?string $groupName,
        string $matchDate,
        ?string $localTime,
        string $homeTeam,
        string $awayTeam,
        ?string $venue,
        ?string $venueCity,
        ?string $notes
    ): int {
        $statement = $this->connection->prepare(
            'INSERT INTO league_matches (stage, group_name, match_date, local_time, home_team, away_team, venue, venue_city, notes, result, created_at, updated_at)
             VALUES (:stage, :group_name, :match_date, :local_time, :home_team, :away_team, :venue, :venue_city, :notes, NULL, NOW(), NOW())'
        );
        $statement->bindValue(':stage', $stage, PDO::PARAM_STR);
        if ($groupName === null || $groupName === '') {
            $statement->bindValue(':group_name', null, PDO::PARAM_NULL);
        } else {
            $statement->bindValue(':group_name', $groupName, PDO::PARAM_STR);
        }
        $statement->bindValue(':match_date', $matchDate, PDO::PARAM_STR);
        if ($localTime === null || $localTime === '') {
            $statement->bindValue(':local_time', null, PDO::PARAM_NULL);
        } else {
            $statement->bindValue(':local_time', $localTime, PDO::PARAM_STR);
        }
        $statement->bindValue(':home_team', $homeTeam, PDO::PARAM_STR);
        $statement->bindValue(':away_team', $awayTeam, PDO::PARAM_STR);
        if ($venue === null || $venue === '') {
            $statement->bindValue(':venue', null, PDO::PARAM_NULL);
        } else {
            $statement->bindValue(':venue', $venue, PDO::PARAM_STR);
        }
        if ($venueCity === null || $venueCity === '') {
            $statement->bindValue(':venue_city', null, PDO::PARAM_NULL);
        } else {
            $statement->bindValue(':venue_city', $venueCity, PDO::PARAM_STR);
        }
        if ($notes === null || $notes === '') {
            $statement->bindValue(':notes', null, PDO::PARAM_NULL);
        } else {
            $statement->bindValue(':notes', $notes, PDO::PARAM_STR);
        }
        $statement->execute();

        return (int) $this->connection->lastInsertId();
    }

    /**
     * Creates many detailed fixtures in one transaction and skips duplicates.
     *
     * Duplicate identity is based on stage, date, local time, home team, and away team.
     *
     * @param array<int, array<string, string>> $fixtures
     *
     * @return array{imported:int, skipped:int}
     */
    public function createDetailedFixturesBulk(array $fixtures): array
    {
        $imported = 0;
        $skipped = 0;

        $this->connection->beginTransaction();
        try {
            foreach ($fixtures as $fixture) {
                $stage = (string) ($fixture['stage'] ?? '');
                $matchDate = (string) ($fixture['match_date'] ?? '');
                $localTime = (string) ($fixture['local_time'] ?? '');
                $homeTeam = (string) ($fixture['home_team'] ?? '');
                $awayTeam = (string) ($fixture['away_team'] ?? '');
                $venue = (string) ($fixture['venue'] ?? '');
                $venueCity = (string) ($fixture['venue_city'] ?? '');
                $notes = (string) ($fixture['notes'] ?? '');

                $normalizedLocalTime = $localTime === '' ? null : $localTime;

                if ($this->detailedFixtureExists($stage, $matchDate, $normalizedLocalTime, $homeTeam, $awayTeam)) {
                    $skipped++;
                    continue;
                }

                $this->createDetailedFixture(
                    $stage,
                    null,
                    $matchDate,
                    $normalizedLocalTime,
                    $homeTeam,
                    $awayTeam,
                    $venue === '' ? null : $venue,
                    $venueCity === '' ? null : $venueCity,
                    $notes === '' ? null : $notes
                );
                $imported++;
            }

            $this->connection->commit();
        } catch (\Throwable $throwable) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $throwable;
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
        ];
    }

    /**
     * Checks whether a detailed fixture already exists.
     */
    private function detailedFixtureExists(
        string $stage,
        string $matchDate,
        ?string $localTime,
        string $homeTeam,
        string $awayTeam
    ): bool {
        $sql = 'SELECT id
                FROM league_matches
                WHERE stage = :stage
                  AND match_date = :match_date
                  AND home_team = :home_team
                  AND away_team = :away_team';

        if ($localTime === null || $localTime === '') {
            $sql .= ' AND local_time IS NULL';
        } else {
            $sql .= ' AND local_time = :local_time';
        }

        $sql .= ' LIMIT 1';

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':stage', $stage, PDO::PARAM_STR);
        $statement->bindValue(':match_date', $matchDate, PDO::PARAM_STR);
        $statement->bindValue(':home_team', $homeTeam, PDO::PARAM_STR);
        $statement->bindValue(':away_team', $awayTeam, PDO::PARAM_STR);
        if ($localTime !== null && $localTime !== '') {
            $statement->bindValue(':local_time', $localTime, PDO::PARAM_STR);
        }
        $statement->execute();

        return $statement->fetch() !== false;
    }

    /**
     * Sets or clears a match result.
     */
    public function updateResult(int $id, ?string $result): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE league_matches SET result = :result, updated_at = NOW() WHERE id = :id'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        if ($result === null) {
            $statement->bindValue(':result', null, PDO::PARAM_NULL);
        } else {
            $statement->bindValue(':result', $result, PDO::PARAM_STR);
        }

        return $statement->execute();
    }
}

