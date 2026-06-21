<?php
declare(strict_types=1);

use App\Helpers\FlagHelper;

/** @var array<string, mixed> $participant */
/** @var array<int, array<string, mixed>> $matches */
/** @var array<int, array<string, mixed>> $existingGuesses */
/** @var bool $isEligible */
/** @var int $rank */
/** @var bool $bonusEnabled */
/** @var int $bonusPointsPerGuess */
/** @var int $bonusPositionThreshold */

$timezone = new DateTimeZone((string) ($appConfig['timezone'] ?? date_default_timezone_get()));
$currentTime = new DateTimeImmutable('now', $timezone);
?>
<section class="panel">
    <h1>Scoreline Guesses</h1>
    <p class="muted">Guess the exact final score of upcoming matches for bonus points!</p>

    <div class="notice <?= $isEligible ? 'bg-success bg-opacity-10 border border-success' : 'bg-warning bg-opacity-10 border border-warning'; ?>">
        <?php if ($isEligible): ?>
            <strong>You are eligible!</strong> As a participant ranked at or below the top <?= $bonusPositionThreshold; ?>
            (you are currently #<?= $rank === 0 ? 'N/A' : (string) $rank; ?>), you can make scoreline guesses.
            Each correct guess awards <strong><?= $bonusPointsPerGuess; ?> bonus points</strong>.
        <?php else: ?>
            <strong>Not eligible yet.</strong> This feature is available to participants ranked at or below the top <?= $bonusPositionThreshold; ?>.
            Your current rank is <strong>#<?= $rank; ?></strong>.
        <?php endif; ?>
    </div>
</section>

<?php if ($isEligible): ?>
<section class="panel">
    <h2>Upcoming Matches</h2>

    <?php if ($matches === []): ?>
        <p class="muted">No upcoming matches available for guessing.</p>
    <?php else: ?>
        <p class="muted">Enter your predicted scoreline for each match below.</p>

        <form method="post" action="<?= htmlspecialchars($url('league/scoreline-guesses'), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Fixture</th>
                        <th>Home Score</th>
                        <th>Away Score</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($matches as $match): ?>
                        <?php
                        $matchId = (int) ($match['id'] ?? 0);
                        $matchDate = (string) ($match['match_date'] ?? '');
                        $localTime = (string) ($match['local_time'] ?? '');
                        $existingGuess = $existingGuesses[$matchId] ?? null;
                        $hasGuess = $existingGuess !== null;
                        ?>
                        <tr>
                            <td class="text-nowrap">
                                <?= htmlspecialchars($matchDate, ENT_QUOTES, 'UTF-8'); ?>
                                <?php if ($localTime !== ''): ?>
                                    <br><span class="muted"><?= htmlspecialchars(substr($localTime, 0, 5), ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= FlagHelper::getFlag((string) ($match['home_team'] ?? '')); ?>
                                <strong><?= htmlspecialchars((string) ($match['home_team'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                                vs
                                <?= FlagHelper::getFlag((string) ($match['away_team'] ?? '')); ?>
                                <strong><?= htmlspecialchars((string) ($match['away_team'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                            </td>
                            <td style="width: 100px;">
                                <input type="number" class="form-control form-control-sm" name="scorelines[<?= $matchId; ?>][home]"
                                       min="0" max="99" step="1"
                                       value="<?= $hasGuess ? htmlspecialchars((string) ($existingGuess['home_score'] ?? ''), ENT_QUOTES, 'UTF-8') : ''; ?>"
                                       placeholder="Home" style="max-width: 80px;">
                            </td>
                            <td style="width: 100px;">
                                <input type="number" class="form-control form-control-sm" name="scorelines[<?= $matchId; ?>][away]"
                                       min="0" max="99" step="1"
                                       value="<?= $hasGuess ? htmlspecialchars((string) ($existingGuess['away_score'] ?? ''), ENT_QUOTES, 'UTF-8') : ''; ?>"
                                       placeholder="Away" style="max-width: 80px;">
                            </td>
                            <td>
                                <?php if ($hasGuess): ?>
                                    <span class="badge bg-info">Guessed <?= (int) ($existingGuess['home_score'] ?? 0); ?>-<?= (int) ($existingGuess['away_score'] ?? 0); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not guessed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-success" type="submit">Save Scoreline Guesses</button>
            </div>
        </form>
    <?php endif; ?>
</section>
<?php endif; ?>