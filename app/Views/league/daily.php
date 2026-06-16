<?php
declare(strict_types=1);

use App\Helpers\FlagHelper;

/** @var string $today */
/** @var string $tomorrow */
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

$buildPredictionLabel = static function (string $prediction, array $match, callable $truncateTeamName): string {
    if ($prediction === 'home') {
        return FlagHelper::getFlag((string) ($match['home_team'] ?? '')) . ' '
            . $truncateTeamName((string) ($match['home_team'] ?? '')) . ' to win';
    }

    if ($prediction === 'away') {
        return FlagHelper::getFlag((string) ($match['away_team'] ?? '')) . ' '
            . $truncateTeamName((string) ($match['away_team'] ?? '')) . ' to win';
    }

    return 'Draw';
};

$timezone = new DateTimeZone((string) ($appConfig['timezone'] ?? date_default_timezone_get()));
$currentTime = new DateTimeImmutable('now', $timezone);
$viewMode = in_array($viewMode ?? 'grid', ['table', 'grid'], true) ? $viewMode : 'grid';
?>

<section class="panel">
    <h1>Daily Games</h1>
    <p class="muted">Showing upcoming fixtures for <?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?> and <?= htmlspecialchars($tomorrow, ENT_QUOTES, 'UTF-8'); ?>.</p>
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
            <a class="btn btn-outline-success btn-sm" href="<?= htmlspecialchars($url('league/daily?view=table'), ENT_QUOTES, 'UTF-8'); ?>">Table</a>
        <?php endif; ?>
        <?php if ($viewMode === 'grid'): ?>
            <span class="badge text-bg-success">Grid</span>
        <?php else: ?>
            <a class="btn btn-outline-success btn-sm" href="<?= htmlspecialchars($url('league/daily?view=grid'), ENT_QUOTES, 'UTF-8'); ?>">Grid</a>
        <?php endif; ?>
    </p>

    <form method="post" action="<?= htmlspecialchars($url('league/votes'), ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="view" value="<?= htmlspecialchars($viewMode, ENT_QUOTES, 'UTF-8'); ?>">

    <?php if ($matches === []): ?>
        <p class="muted">No upcoming fixtures for today or tomorrow.</p>
    <?php elseif ($viewMode === 'grid'): ?>
        <div class="card-grid">
            <?php foreach ($matches as $match): ?>
                <?php
                $matchId = (int) ($match['id'] ?? 0);
                $currentVote = $votes[$matchId] ?? '';
                $matchDate = (string) ($match['match_date'] ?? '');
                $localTime = (string) ($match['local_time'] ?? '');
                $kickoff = false;

                if ($localTime !== '') {
                    $timeValue = strlen($localTime) === 5 ? ($localTime . ':00') : $localTime;
                    $kickoff = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $matchDate . ' ' . $timeValue, $timezone);
                }

                $isMatchPast = ($kickoff !== false && $currentTime >= $kickoff);
                $isDisabled = $isMatchPast ? 'disabled' : '';
                ?>
                <article class="fixture-card">
                    <p><strong><?= htmlspecialchars((string) (($match['group_name'] ?? '') !== '' ? ($match['group_name'] ?? '') : ($match['stage'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></strong></p>
                    <h3>
                        <?= FlagHelper::getFlag((string) ($match['home_team'] ?? '')); ?>
                        <?= htmlspecialchars((string) ($match['home_team'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        vs
                        <?= FlagHelper::getFlag((string) ($match['away_team'] ?? '')); ?>
                        <?= htmlspecialchars((string) ($match['away_team'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    </h3>
                    <p><strong>Date:</strong> <?= htmlspecialchars($matchDate, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Kickoff:</strong> <?= htmlspecialchars($localTime !== '' ? substr($localTime, 0, 5) : 'TBC', ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php if ((string) ($match['venue'] ?? '') !== ''): ?>
                        <p><strong>Venue:</strong> <?= htmlspecialchars((string) ($match['venue'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <?php if ((string) ($match['venue_city'] ?? '') !== ''): ?>
                        <p class="muted"><?= htmlspecialchars((string) ($match['venue_city'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <p><strong>Current Vote:</strong> <?= htmlspecialchars((string) ($predictionLabels[$currentVote] ?? 'No vote yet'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <select class="form-select mb-2" name="predictions[<?= $matchId; ?>]" <?= $isDisabled; ?>>
                            <option value="">Choose</option>
                            <?php foreach ($allowedPredictions as $prediction): ?>
                                <?php
                                $predictionLabel = $buildPredictionLabel((string) $prediction, $match, $truncateTeamName);
                                ?>
                                <option value="<?= htmlspecialchars($prediction, ENT_QUOTES, 'UTF-8'); ?>" <?= $prediction === $currentVote ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($predictionLabel, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-success" type="submit">Save All Votes</button>
        </div>
    <?php else: ?>
        <?php $currentDate = null; ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                <tr>
                    <th>Time</th>
                    <th>Stage</th>
                    <th>Fixture</th>
                    <th>Venue</th>
                    <th>Your Vote</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($matches as $match): ?>
                    <?php
                    $matchDate = (string) ($match['match_date'] ?? '');
                    $localTime = (string) ($match['local_time'] ?? '');
                    if ($currentDate !== $matchDate) {
                        $currentDate = $matchDate;
                        ?>
                        <tr>
                            <td class="table-date-divider" colspan="5"><?= htmlspecialchars($currentDate, ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php } ?>

                    <?php
                    $matchId = (int) ($match['id'] ?? 0);
                    $currentVote = $votes[$matchId] ?? '';
                    $kickoff = false;

                    if ($localTime !== '') {
                        $timeValue = strlen($localTime) === 5 ? ($localTime . ':00') : $localTime;
                        $kickoff = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $matchDate . ' ' . $timeValue, $timezone);
                    }

                    $isMatchPast = ($kickoff !== false && $currentTime >= $kickoff);
                    $isDisabled = $isMatchPast ? 'disabled' : '';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($localTime !== '' ? substr($localTime, 0, 5) : 'TBC', ENT_QUOTES, 'UTF-8'); ?></td>
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
                            <?= htmlspecialchars((string) ($match['venue'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            <?php if ((string) ($match['venue_city'] ?? '') !== ''): ?>
                                <br><span class="muted"><?= htmlspecialchars((string) ($match['venue_city'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-nowrap">
                            <?php if ($currentVote === 'home'): ?>
                                <span class="vote-indicator vote-indicator--active">
                                    <?= FlagHelper::getFlag((string) ($match['home_team'] ?? '')); ?>
                                    <?= htmlspecialchars((string) ($match['home_team'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php elseif ($currentVote === 'away'): ?>
                                <span class="vote-indicator vote-indicator--active">
                                    <?= FlagHelper::getFlag((string) ($match['away_team'] ?? '')); ?>
                                    <?= htmlspecialchars((string) ($match['away_team'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php elseif ($currentVote === 'draw'): ?>
                                <span class="vote-indicator vote-indicator--active">Draw</span>
                            <?php else: ?>
                                <select class="form-select form-select-sm" name="predictions[<?= $matchId; ?>]" <?= $isDisabled; ?>>
                                    <option value="">Choose</option>
                                    <?php foreach ($allowedPredictions as $prediction): ?>
                                        <?php
                                        $predictionLabel = $buildPredictionLabel((string) $prediction, $match, $truncateTeamName);
                                        ?>
                                        <option value="<?= htmlspecialchars($prediction, ENT_QUOTES, 'UTF-8'); ?>" <?= $prediction === $currentVote ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($predictionLabel, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-success" type="submit">Save All Votes</button>
        </div>
    <?php endif; ?>
    </form>
</section>
