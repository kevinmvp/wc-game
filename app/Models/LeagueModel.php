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
     * Bonus points from correct scoreline guesses are included.
     *
     * @return array<int, array<string, mixed>>
     */
    public function leaderboard(bool $includeMobile = false, int $bonusPointsPerGuess = 5): array
    {
        $mobileSelect = $includeMobile ? 'p.mobile,' : 'NULL AS mobile,';
        $mobileGroupBy = $includeMobile ? ', p.mobile' : '';

        $statement = $this->connection->prepare(
            'SELECT p.id,
                    p.name,
                    p.team_name,
                    ' . $mobileSelect . '
                    COALESCE(SUM(CASE WHEN m.result IS NOT NULL AND v.prediction = m.result THEN 1 ELSE 0 END), 0) AS prediction_points,
                    COALESCE(SUM(CASE WHEN sg.is_correct = 1 THEN 1 ELSE 0 END), 0) AS bonus_correct,
                    COALESCE(SUM(CASE WHEN m.result IS NOT NULL AND v.prediction = m.result THEN 1 ELSE 0 END), 0)
                    + (COALESCE(SUM(CASE WHEN sg.is_correct = 1 THEN 1 ELSE 0 END), 0) * :bonus_pts) AS points,
                    COUNT(DISTINCT v.id) AS total_votes
             FROM league_participants p
             LEFT JOIN league_votes v ON v.participant_id = p.id
             LEFT JOIN league_matches m ON m.id = v.match_id
             LEFT JOIN league_scoreline_guesses sg ON sg.participant_id = p.id AND sg.match_id = m.id
             GROUP BY p.id, p.name, p.team_name' . $mobileGroupBy . '
             ORDER BY points DESC, prediction_points DESC, p.id ASC'
        );
        $statement->bindValue(':bonus_pts', $bonusPointsPerGuess, PDO::PARAM_INT);
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        return $rows;
    }

    /**
     * Returns current score for one participant, including bonus points.
     */
    public function pointsForParticipant(int $participantId, int $bonusPointsPerGuess = 5): int
    {
        $statement = $this->connection->prepare(
            'SELECT COALESCE(SUM(CASE WHEN m.result IS NOT NULL AND v.prediction = m.result THEN 1 ELSE 0 END), 0)
                    + (COALESCE(SUM(CASE WHEN sg.is_correct = 1 THEN 1 ELSE 0 END), 0) * :bonus_pts) AS points
             FROM league_votes v
             INNER JOIN league_matches m ON m.id = v.match_id
             LEFT JOIN league_scoreline_guesses sg ON sg.participant_id = v.participant_id AND sg.match_id = m.id
             WHERE v.participant_id = :participant_id'
        );
        $statement->bindValue(':participant_id', $participantId, PDO::PARAM_INT);
        $statement->bindValue(':bonus_pts', $bonusPointsPerGuess, PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetch();

        return (int) ($row['points'] ?? 0);
    }

    /**
     * Returns the rank (1-based) of a participant in the leaderboard.
     * Bonus points are included in the ranking.
     */
    public function participantRank(int $participantId, int $bonusPointsPerGuess = 5): int
    {
        $rows = $this->leaderboard(false, $bonusPointsPerGuess);

        foreach ($rows as $index => $row) {
            if ((int) $row['id'] === $participantId) {
                return $index + 1;
            }
        }

        return 0;
    }
}