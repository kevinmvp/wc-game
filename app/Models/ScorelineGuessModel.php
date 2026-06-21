<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Manages scoreline guess submissions and scoring for eligible participants.
 */
class ScorelineGuessModel extends BaseModel
{
    /**
     * Saves or updates a scoreline guess for a participant on a match.
     */
    public function saveGuess(int $participantId, int $matchId, int $homeScore, int $awayScore): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO league_scoreline_guesses (participant_id, match_id, home_score, away_score, is_correct, created_at, updated_at)
             VALUES (:participant_id, :match_id, :home_score, :away_score, NULL, NOW(), NOW())
             ON DUPLICATE KEY UPDATE home_score = :home_score2, away_score = :away_score2, is_correct = NULL, updated_at = NOW()'
        );
        $statement->bindValue(':participant_id', $participantId, PDO::PARAM_INT);
        $statement->bindValue(':match_id', $matchId, PDO::PARAM_INT);
        $statement->bindValue(':home_score', $homeScore, PDO::PARAM_INT);
        $statement->bindValue(':away_score', $awayScore, PDO::PARAM_INT);
        $statement->bindValue(':home_score2', $homeScore, PDO::PARAM_INT);
        $statement->bindValue(':away_score2', $awayScore, PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Returns all scoreline guesses for a participant, keyed by match_id.
     *
     * @return array<int, array<string, mixed>>
     */
    public function guessesByParticipant(int $participantId): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, participant_id, match_id, home_score, away_score, is_correct, created_at, updated_at
             FROM league_scoreline_guesses
             WHERE participant_id = :participant_id
             ORDER BY match_id ASC'
        );
        $statement->bindValue(':participant_id', $participantId, PDO::PARAM_INT);
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        $guesses = [];
        foreach ($rows as $row) {
            $guesses[(int) $row['match_id']] = $row;
        }

        return $guesses;
    }

    /**
     * Evaluates all guesses for matches that now have scorelines and marks them correct/incorrect.
     * Returns the count of guesses that were newly evaluated.
     */
    public function evaluateGuesses(): int
    {
        $statement = $this->connection->prepare(
            'SELECT g.id, g.participant_id, g.match_id, g.home_score AS guess_home, g.away_score AS guess_away,
                    m.home_score AS actual_home, m.away_score AS actual_away
             FROM league_scoreline_guesses g
             INNER JOIN league_matches m ON m.id = g.match_id
             WHERE g.is_correct IS NULL
               AND m.home_score IS NOT NULL
               AND m.away_score IS NOT NULL'
        );
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        $count = 0;
        foreach ($rows as $row) {
            $guessId = (int) $row['id'];
            $guessHome = (int) $row['guess_home'];
            $guessAway = (int) $row['guess_away'];
            $actualHome = (int) $row['actual_home'];
            $actualAway = (int) $row['actual_away'];

            $isCorrect = ($guessHome === $actualHome && $guessAway === $actualAway) ? 1 : 0;

            $update = $this->connection->prepare(
                'UPDATE league_scoreline_guesses SET is_correct = :is_correct, updated_at = NOW() WHERE id = :id'
            );
            $update->bindValue(':is_correct', $isCorrect, PDO::PARAM_INT);
            $update->bindValue(':id', $guessId, PDO::PARAM_INT);
            $update->execute();

            $count++;
        }

        return $count;
    }

    /**
     * Evaluates guesses for a single match (called when match score is updated).
     */
    public function evaluateGuessesForMatch(int $matchId): int
    {
        $statement = $this->connection->prepare(
            'SELECT g.id, g.home_score AS guess_home, g.away_score AS guess_away,
                    m.home_score AS actual_home, m.away_score AS actual_away
             FROM league_scoreline_guesses g
             INNER JOIN league_matches m ON m.id = g.match_id
             WHERE g.match_id = :match_id
               AND g.is_correct IS NULL
               AND m.home_score IS NOT NULL
               AND m.away_score IS NOT NULL'
        );
        $statement->bindValue(':match_id', $matchId, PDO::PARAM_INT);
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        $count = 0;
        foreach ($rows as $row) {
            $guessId = (int) $row['id'];
            $guessHome = (int) $row['guess_home'];
            $guessAway = (int) $row['guess_away'];
            $actualHome = (int) $row['actual_home'];
            $actualAway = (int) $row['actual_away'];

            $isCorrect = ($guessHome === $actualHome && $guessAway === $actualAway) ? 1 : 0;

            $update = $this->connection->prepare(
                'UPDATE league_scoreline_guesses SET is_correct = :is_correct, updated_at = NOW() WHERE id = :id'
            );
            $update->bindValue(':is_correct', $isCorrect, PDO::PARAM_INT);
            $update->bindValue(':id', $guessId, PDO::PARAM_INT);
            $update->execute();

            $count++;
        }

        return $count;
    }

    /**
     * Returns the count of correct scoreline guesses for a participant.
     */
    public function correctGuessCount(int $participantId): int
    {
        $statement = $this->connection->prepare(
            'SELECT COUNT(*) AS total
             FROM league_scoreline_guesses
             WHERE participant_id = :participant_id AND is_correct = 1'
        );
        $statement->bindValue(':participant_id', $participantId, PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetch();

        return (int) ($row['total'] ?? 0);
    }
}
