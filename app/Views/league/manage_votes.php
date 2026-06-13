<?php
declare(strict_types=1);

use App\Helpers\FlagHelper;

/** @var array<int, array<string, mixed>> $participants */
/** @var int $selectedParticipantId */
/** @var array<int, array<string, mixed>> $matches */
/** @var array<int, string> $votes */
/** @var array<int, string> $allowedPredictions */

$predictionLabels = [
    'home' => 'Home Win',
    'away' => 'Away Win',
    'draw' => 'Draw',
];

$buildPredictionLabel = static function (string $prediction, array $match): string {
    if ($prediction === 'home') {
        return FlagHelper::getFlag((string) ($match['home_team'] ?? '')) . ' ' . (string) ($match['home_team'] ?? '') . ' to win';
    }

    if ($prediction === 'away') {
        return FlagHelper::getFlag((string) ($match['away_team'] ?? '')) . ' ' . (string) ($match['away_team'] ?? '') . ' to win';
    }

    return 'Draw';
};
?>

<section class="panel">
    <h1>Manage Participant Votes</h1>
    <p class="muted">Select a participant, edit votes per match, then save all changes.</p>

    <form method="get" action="<?= htmlspecialchars($url('league/manage-votes'), ENT_QUOTES, 'UTF-8'); ?>" class="form-grid">
        <label class="form-label" for="participant_id">Participant</label>
        <select class="form-select" id="participant_id" name="participant_id" required>
            <option value="">Choose participant</option>
            <?php foreach ($participants as $participant): ?>
                <?php $participantId = (int) ($participant['id'] ?? 0); ?>
                <option value="<?= $participantId; ?>" <?= $participantId === $selectedParticipantId ? 'selected' : ''; ?>>
                    <?= htmlspecialchars((string) ($participant['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    (<?= htmlspecialchars((string) ($participant['team_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <div>
            <button class="btn btn-success" type="submit">Load Votes</button>
        </div>
    </form>
</section>

<?php if ($selectedParticipantId > 0): ?>
    <section class="panel">
        <h2>Votes By Match</h2>
        <?php if ($matches === []): ?>
            <p class="muted">No matches available.</p>
        <?php else: ?>
            <form method="post" action="<?= htmlspecialchars($url('league/manage-votes/' . (string) $selectedParticipantId), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Stage</th>
                            <th>Fixture</th>
                            <th>Vote</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($matches as $match): ?>
                            <?php
                            $matchId = (int) ($match['id'] ?? 0);
                            $currentVote = (string) ($votes[$matchId] ?? '');
                            ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars((string) ($match['match_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if ((string) ($match['local_time'] ?? '') !== ''): ?>
                                        <br><span class="muted"><?= htmlspecialchars(substr((string) ($match['local_time'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars((string) ($match['stage'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if ((string) ($match['group_name'] ?? '') !== ''): ?>
                                        <br><span class="muted"><?= htmlspecialchars((string) ($match['group_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= FlagHelper::getFlag((string) ($match['home_team'] ?? '')); ?>
                                    <strong><?= htmlspecialchars((string) ($match['home_team'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                                    vs
                                    <?= FlagHelper::getFlag((string) ($match['away_team'] ?? '')); ?>
                                    <strong><?= htmlspecialchars((string) ($match['away_team'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm" name="votes[<?= $matchId; ?>]">
                                        <option value="">No vote</option>
                                        <?php foreach ($allowedPredictions as $prediction): ?>
                                            <?php $label = $buildPredictionLabel((string) $prediction, $match); ?>
                                            <option value="<?= htmlspecialchars((string) $prediction, ENT_QUOTES, 'UTF-8'); ?>" <?= $currentVote === $prediction ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button class="btn btn-success" type="submit">Save All Votes</button>
                </div>
            </form>
        <?php endif; ?>
    </section>
<?php endif; ?>
