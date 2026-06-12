<?php
declare(strict_types=1);

use App\Helpers\FlagHelper;

/** @var string $today */
/** @var array<string, mixed> $participant */
/** @var array<int, array<string, mixed>> $matches */
/** @var array<int, string> $votes */
/** @var int $points */
/** @var array<int, string> $allowedPredictions */
/** @var string $viewMode */

$predictionLabels = [
    'home' => 'Home Win',
    'away' => 'Away Win',
    'draw' => 'Draw',
];

$truncateTeamName = static function (string $team): string {
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($team) > 20 ? mb_substr($team, 0, 20) . '...' : $team;
    }

    return strlen($team) > 20 ? substr($team, 0, 20) . '...' : $team;
};
?>
<section class="panel">
    <h1>Daily Games</h1>
    <p class="muted">Date: <?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?></p>
    <p class="muted">
        Participant: <?= htmlspecialchars((string) $participant['name'], ENT_QUOTES, 'UTF-8'); ?>
        (<?= htmlspecialchars((string) $participant['team_name'], ENT_QUOTES, 'UTF-8'); ?>)
    </p>
    <p><strong>Total Points:</strong> <?= $points; ?></p>
</section>

<section class="panel">
    <h2>Upcoming Matches</h2>
    <p class="d-flex align-items-center gap-2 flex-wrap">
        View:
        <?php if ($viewMode === 'table'): ?>
            <span class="badge text-bg-success">Table</span>
        <?php else: ?>
            <a class="btn btn-outline-success btn-sm" href="<?= htmlspecialchars($url('league/daily?date=' . urlencode($today) . '&view=table'), ENT_QUOTES, 'UTF-8'); ?>">Table</a>
        <?php endif; ?>
        <?php if ($viewMode === 'grid'): ?>
            <span class="badge text-bg-success">Grid</span>
        <?php else: ?>
            <a class="btn btn-outline-success btn-sm" href="<?= htmlspecialchars($url('league/daily?date=' . urlencode($today) . '&view=grid'), ENT_QUOTES, 'UTF-8'); ?>">Grid</a>
        <?php endif; ?>
    </p>
    <?php if ($matches === []): ?>
        <p>No matches scheduled for today yet.</p>
    <?php elseif ($viewMode === 'grid'): ?>
        <div class="card-grid">
            <?php foreach ($matches as $match): ?>
                <?php $currentVote = $votes[(int) $match['id']] ?? ''; ?>
                <article class="fixture-card">
                    <p><strong><?= htmlspecialchars((string) ($match['group_name'] ?: $match['stage']), ENT_QUOTES, 'UTF-8'); ?></strong></p>
                    <h3>
                        <?= FlagHelper::getFlag((string) $match['home_team']); ?>
                        <?= htmlspecialchars((string) $match['home_team'], ENT_QUOTES, 'UTF-8'); ?>
                        vs
                        <?= FlagHelper::getFlag((string) $match['away_team']); ?>
                        <?= htmlspecialchars((string) $match['away_team'], ENT_QUOTES, 'UTF-8'); ?>
                    </h3>
                    <?php if ((string) ($match['local_time'] ?? '') !== ''): ?>
                        <p><strong>Kickoff:</strong> <?= htmlspecialchars(substr((string) $match['local_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <?php if ((string) ($match['venue'] ?? '') !== ''): ?>
                        <p><strong>Venue:</strong> <?= htmlspecialchars((string) $match['venue'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <?php if ((string) ($match['venue_city'] ?? '') !== ''): ?>
                        <p class="muted"><?= htmlspecialchars((string) $match['venue_city'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <p><strong>Current Vote:</strong> <?= htmlspecialchars((string) ($predictionLabels[$currentVote] ?? 'No vote yet'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Final Result:</strong> <?= htmlspecialchars((string) ($predictionLabels[(string) ($match['result'] ?? '')] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <form method="post" action="<?= htmlspecialchars($url('league/vote/' . (string) ((int) $match['id'])), ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">

                        <select class="form-select mb-2" name="prediction" required>
                            <option value="">Choose</option>
                            <?php foreach ($allowedPredictions as $prediction): ?>
                                <?php
                                $predictionLabel = match ($prediction) {
                                    'home' => FlagHelper::getFlag((string) $match['home_team']) . ' ' . $truncateTeamName((string) $match['home_team']) . ' to win',
                                    'away' => FlagHelper::getFlag((string) $match['away_team']) . ' ' . $truncateTeamName((string) $match['away_team']) . ' to win',
                                    default => 'Draw',
                                };
                                ?>
                                <option value="<?= htmlspecialchars($prediction, ENT_QUOTES, 'UTF-8'); ?>" <?= $prediction === $currentVote ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($predictionLabel, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-success btn-sm" type="submit">Save Vote</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
            <thead>
            <tr>
                <th>Match</th>
                <th>Your Vote</th>
                <th>Final Result</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($matches as $match): ?>
                <?php $currentVote = $votes[(int) $match['id']] ?? ''; ?>
                <tr>
                    <td>
                        <div>
                            <strong><?= htmlspecialchars((string) ($match['group_name'] ?: $match['stage']), ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                        <div class="fs-5 mt-1">
                            <?= FlagHelper::getFlag((string) $match['home_team']); ?>
                            <strong><?= htmlspecialchars((string) $match['home_team'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span class="muted">vs</span>
                            <?= FlagHelper::getFlag((string) $match['away_team']); ?>
                            <strong><?= htmlspecialchars((string) $match['away_team'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                        <?php if ((string) ($match['local_time'] ?? '') !== ''): ?>
                            <div class="mt-1"><strong>Kickoff:</strong> <?= htmlspecialchars(substr((string) $match['local_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                        <?php if ((string) ($match['venue'] ?? '') !== ''): ?>
                            <div><strong>Venue:</strong> <?= htmlspecialchars((string) $match['venue'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                        <?php if ((string) ($match['venue_city'] ?? '') !== ''): ?>
                            <div class="muted"><?= htmlspecialchars((string) $match['venue_city'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars((string) ($predictionLabels[$currentVote] ?? 'No vote yet'), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?= htmlspecialchars((string) ($predictionLabels[(string) ($match['result'] ?? '')] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td>
                        <form method="post" action="<?= htmlspecialchars($url('league/vote/' . (string) ((int) $match['id'])), ENT_QUOTES, 'UTF-8'); ?>" class="inline-form">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">

                            <select class="form-select form-select-sm" name="prediction" required>
                                <option value="">Choose</option>
                                <?php foreach ($allowedPredictions as $prediction): ?>
                                    <?php
                                    $predictionLabel = match ($prediction) {
                                        'home' => FlagHelper::getFlag((string) $match['home_team']) . ' ' . $truncateTeamName((string) $match['home_team']) . ' to win',
                                        'away' => FlagHelper::getFlag((string) $match['away_team']) . ' ' . $truncateTeamName((string) $match['away_team']) . ' to win',
                                        default => 'Draw',
                                    };
                                    ?>
                                    <option value="<?= htmlspecialchars($prediction, ENT_QUOTES, 'UTF-8'); ?>" <?= $prediction === $currentVote ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($predictionLabel, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-success btn-sm" type="submit">Save Vote</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>
