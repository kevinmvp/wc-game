<?php
declare(strict_types=1);

use App\Helpers\FlagHelper;

/** @var string $today */
/** @var string $tomorrow */
/** @var array<int, array<string, mixed>> $leaderboardRows */
/** @var array<int, array<string, mixed>> $upcomingMatches */
/** @var array<string, mixed>|null $participant */
/** @var array<int, string> $todayVotes */
/** @var array<int, string> $allowedPredictions */

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
<!--section class="panel">
    <h1>RPC League</h1>
    <p><a class="btn btn-success" href="<?= htmlspecialchars($url('league/join'), ENT_QUOTES, 'UTF-8'); ?>">Register to Join the League</a></p>
</section-->

<section class="panel">
    <h2>Leaderboard</h2>
    <p><a class="btn btn-outline-success btn-sm" href="<?= htmlspecialchars($url('league/join'), ENT_QUOTES, 'UTF-8'); ?>">New user? Register here to join the ranking</a></p>
    <?php if ($leaderboardRows === []): ?>
        <p class="muted">No participants yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Team</th>
                <th>Points</th>
                <th>Votes</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($leaderboardRows as $index => $row): ?>
                <tr>
                    <td><?= $index + 1; ?></td>
                    <td><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) ($row['team_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><strong><?= (int) ($row['points'] ?? 0); ?></strong></td>
                    <td><?= (int) ($row['total_votes'] ?? 0); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>

<section class="panel">
    <h2>Upcoming Fixtures</h2>
    <!--p class="muted">Date: <?= htmlspecialchars($today ?? '', ENT_QUOTES, 'UTF-8'); ?> - <?= htmlspecialchars($tomorrow ?? '', ENT_QUOTES, 'UTF-8'); ?></p-->
    <?php if ($participant === null): ?>
        <p class="muted">Only registered users can vote. <a href="<?= htmlspecialchars($url('league/join'), ENT_QUOTES, 'UTF-8'); ?>">Register now</a>.</p>
    <?php endif; ?>

    <?php if ($upcomingMatches === []): ?>
        <p class="muted">No upcoming fixtures for today or tomorrow.</p>
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
            <?php foreach ($upcomingMatches as $match): ?>
                <?php if ($currentDate !== (string) ($match['match_date'] ?? '')): ?>
                    <?php $currentDate = (string) ($match['match_date'] ?? ''); ?>
                    <tr>
                        <td class="table-date-divider" colspan="5"><?= htmlspecialchars($currentDate, ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endif; ?>
                <?php $matchId = (int) ($match['id'] ?? 0); ?>
                <?php $currentVote = $todayVotes[$matchId] ?? ''; ?>
                <tr>
                    <td>
                        <?= htmlspecialchars((string) (($match['local_time'] ?? '') ? substr((string) ($match['local_time'] ?? ''), 0, 5) : 'TBC'), ENT_QUOTES, 'UTF-8'); ?>
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
                        <?= htmlspecialchars((string) ($match['venue'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        <?php if ((string) ($match['venue_city'] ?? '') !== ''): ?>
                            <br><span class="muted"><?= htmlspecialchars((string) ($match['venue_city'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($participant === null): ?>
                            <span class="muted">Register to vote or <a href="league/login">login here</a</span>
                        <?php else: ?>
                            <form method="post" action="<?= htmlspecialchars($url('league/vote/' . (string) $matchId), ENT_QUOTES, 'UTF-8'); ?>" class="inline-form">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                                <select class="form-select form-select-sm" name="prediction" required>
                                    <option value="">Choose</option>
                                    <?php foreach ($allowedPredictions as $prediction): ?>
                                        <?php
                                        $predictionLabel = match ($prediction) {
                                            'home' => FlagHelper::getFlag((string) ($match['home_team'] ?? '')) . ' ' . $truncateTeamName((string) ($match['home_team'] ?? '')) . ' to win',
                                            'away' => FlagHelper::getFlag((string) ($match['away_team'] ?? '')) . ' ' . $truncateTeamName((string) ($match['away_team'] ?? '')) . ' to win',
                                            default => 'Draw',
                                        };
                                        ?>
                                        <option value="<?= htmlspecialchars($prediction, ENT_QUOTES, 'UTF-8'); ?>" <?= $prediction === $currentVote ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($predictionLabel, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-success btn-sm" type="submit">Save</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>