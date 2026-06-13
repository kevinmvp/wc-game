<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Handles participant voting operations.
 */
class VoteModel extends BaseModel
{
    /**
     * Creates or updates one vote per participant per match.
     */
    public function saveVote(int $participantId, int $matchId, string $prediction): bool
    {
        $statement = $this->connection->prepare(
            'INSERT INTO league_votes (participant_id, match_id, prediction, created_at, updated_at)
             VALUES (:participant_id, :match_id, :prediction, NOW(), NOW())
             ON DUPLICATE KEY UPDATE prediction = VALUES(prediction), updated_at = NOW()'
        );
        $statement->bindValue(':participant_id', $participantId, PDO::PARAM_INT);
        $statement->bindValue(':match_id', $matchId, PDO::PARAM_INT);
        $statement->bindValue(':prediction', $prediction, PDO::PARAM_STR);

        return $statement->execute();
    }

    /**
     * Deletes a vote for one participant and match.
     */
    public function deleteVote(int $participantId, int $matchId): bool
    {
        $statement = $this->connection->prepare(
            'DELETE FROM league_votes WHERE participant_id = :participant_id AND match_id = :match_id'
        );
        $statement->bindValue(':participant_id', $participantId, PDO::PARAM_INT);
        $statement->bindValue(':match_id', $matchId, PDO::PARAM_INT);

        return $statement->execute();
    }

    /**
     * Returns votes for a participant keyed by match id on a specific date.
     *
     * @return array<int, string>
     */
    public function votesByParticipantOnDate(int $participantId, string $matchDate): array
    {
        $statement = $this->connection->prepare(
            'SELECT v.match_id, v.prediction
             FROM league_votes v
             INNER JOIN league_matches m ON m.id = v.match_id
             WHERE v.participant_id = :participant_id AND m.match_date = :match_date'
        );
        $statement->bindValue(':participant_id', $participantId, PDO::PARAM_INT);
        $statement->bindValue(':match_date', $matchDate, PDO::PARAM_STR);
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        $votes = [];
        foreach ($rows as $row) {
            $votes[(int) $row['match_id']] = (string) $row['prediction'];
        }

        return $votes;
    }

    /**
     * Returns votes for a participant keyed by match id for provided matches.
     *
     * @param array<int, int> $matchIds
     *
     * @return array<int, string>
     */
    public function votesByParticipantForMatches(int $participantId, array $matchIds): array
    {
        if ($matchIds === []) {
            return [];
        }

        $matchIds = array_values(array_unique(array_map(static fn ($id): int => (int) $id, $matchIds)));
        $placeholders = implode(', ', array_fill(0, count($matchIds), '?'));

        $statement = $this->connection->prepare(
            'SELECT match_id, prediction
             FROM league_votes
             WHERE participant_id = ? AND match_id IN (' . $placeholders . ')'
        );

        $statement->bindValue(1, $participantId, PDO::PARAM_INT);
        foreach ($matchIds as $index => $matchId) {
            $statement->bindValue($index + 2, $matchId, PDO::PARAM_INT);
        }

        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        $votes = [];
        foreach ($rows as $row) {
            $votes[(int) $row['match_id']] = (string) $row['prediction'];
        }

        return $votes;
    }

    /**
     * Returns past matches a participant has voted on.
     *
     * @return array<int, array<string, mixed>>
     */
    public function pastVotedMatchesByParticipant(int $participantId, string $currentDateTime): array
    {
        $statement = $this->connection->prepare(
            'SELECT m.id,
                    m.stage,
                    m.group_name,
                    m.match_date,
                    m.local_time,
                    m.home_team,
                    m.away_team,
                    m.venue,
                    m.venue_city,
                    m.home_score,
                    m.away_score,
                    m.result,
                    v.prediction
             FROM league_votes v
             INNER JOIN league_matches m ON m.id = v.match_id
             WHERE v.participant_id = :participant_id
               AND CONCAT(m.match_date, " ", COALESCE(m.local_time, "00:00:00")) < :current_datetime
             ORDER BY m.match_date DESC, m.local_time DESC, m.id DESC'
        );
        $statement->bindValue(':participant_id', $participantId, PDO::PARAM_INT);
        $statement->bindValue(':current_datetime', $currentDateTime, PDO::PARAM_STR);
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        return $rows;
    }

    /**
     * Returns upcoming matches a participant has voted on.
     *
     * @return array<int, array<string, mixed>>
     */
    public function futureVotedMatchesByParticipant(int $participantId, string $currentDateTime): array
    {
        $statement = $this->connection->prepare(
            'SELECT m.id,
                    m.stage,
                    m.group_name,
                    m.match_date,
                    m.local_time,
                    m.home_team,
                    m.away_team,
                    m.venue,
                    m.venue_city,
                    m.notes,
                    m.home_score,
                    m.away_score,
                    m.result,
                    m.created_at,
                    m.updated_at
             FROM league_votes v
             INNER JOIN league_matches m ON m.id = v.match_id
             WHERE v.participant_id = :participant_id
                             AND CONCAT(m.match_date, " ", COALESCE(m.local_time, "00:00:00")) > :current_datetime
             ORDER BY m.match_date ASC, m.local_time ASC, m.id ASC'
        );
        $statement->bindValue(':participant_id', $participantId, PDO::PARAM_INT);
        $statement->bindValue(':current_datetime', $currentDateTime, PDO::PARAM_STR);
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        return $rows;
    }

    /**
     * Returns distinct voter counts per voted team.
     *
     * @return array<int, array<string, mixed>>
     */
    public function voteSummaryByTeam(): array
    {
        $statement = $this->connection->prepare(
            'SELECT team_name, COUNT(DISTINCT participant_id) AS voter_count
             FROM (
                SELECT v.participant_id,
                       CASE
                           WHEN v.prediction = "home" THEN m.home_team
                           WHEN v.prediction = "away" THEN m.away_team
                           ELSE NULL
                       END AS team_name
                FROM league_votes v
                INNER JOIN league_matches m ON m.id = v.match_id
             ) AS team_votes
             WHERE team_name IS NOT NULL AND team_name <> ""
             GROUP BY team_name
             ORDER BY voter_count DESC, team_name ASC'
        );
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        return $rows;
    }

    /**
     * Returns vote counts by match for past matches and tomorrow's fixtures.
     *
     * @return array<int, array<string, mixed>>
     */
    public function voteSummaryByPastAndTomorrowMatches(string $currentDateTime, string $tomorrowDate): array
    {
        $statement = $this->connection->prepare(
            'SELECT m.id,
                    m.match_date,
                    m.local_time,
                    m.stage,
                    m.group_name,
                    m.home_team,
                    m.away_team,
                    SUM(CASE WHEN v.prediction = "home" THEN 1 ELSE 0 END) AS home_votes,
                    SUM(CASE WHEN v.prediction = "away" THEN 1 ELSE 0 END) AS away_votes,
                    SUM(CASE WHEN v.prediction = "draw" THEN 1 ELSE 0 END) AS draw_votes,
                    COUNT(v.id) AS total_votes
             FROM league_matches m
             LEFT JOIN league_votes v ON v.match_id = m.id
                     WHERE CONCAT(m.match_date, " ", COALESCE(m.local_time, "00:00:00")) < :current_datetime
                    OR m.match_date = :tomorrow_date
             GROUP BY m.id, m.match_date, m.local_time, m.stage, m.group_name, m.home_team, m.away_team
             ORDER BY m.match_date DESC, m.local_time DESC, m.id DESC'
        );
        $statement->bindValue(':current_datetime', $currentDateTime, PDO::PARAM_STR);
                $statement->bindValue(':tomorrow_date', $tomorrowDate, PDO::PARAM_STR);
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        return $rows;
    }
}
