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
}
