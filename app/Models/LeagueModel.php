<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Provides league scoring and ranking queries.
 */
class LeagueModel extends BaseModel
{
    /**
     * Returns leaderboard rows ordered by points descending.
     *
     * @return array<int, array<string, mixed>>
     */
    public function leaderboard(): array
    {
        $statement = $this->connection->prepare(
            'SELECT p.id,
                    p.name,
                    p.team_name,
                    p.mobile,
                    COALESCE(SUM(CASE WHEN m.result IS NOT NULL AND v.prediction = m.result THEN 1 ELSE 0 END), 0) AS points,
                    COUNT(v.id) AS total_votes
             FROM league_participants p
             LEFT JOIN league_votes v ON v.participant_id = p.id
             LEFT JOIN league_matches m ON m.id = v.match_id
             GROUP BY p.id, p.name, p.team_name, p.mobile
             ORDER BY points DESC, total_votes DESC, p.id ASC'
        );
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        return $rows;
    }

    /**
     * Returns current score for one participant.
     */
    public function pointsForParticipant(int $participantId): int
    {
        $statement = $this->connection->prepare(
            'SELECT COALESCE(SUM(CASE WHEN m.result IS NOT NULL AND v.prediction = m.result THEN 1 ELSE 0 END), 0) AS points
             FROM league_votes v
             INNER JOIN league_matches m ON m.id = v.match_id
             WHERE v.participant_id = :participant_id'
        );
        $statement->bindValue(':participant_id', $participantId, PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetch();

        return (int) ($row['points'] ?? 0);
    }
}
