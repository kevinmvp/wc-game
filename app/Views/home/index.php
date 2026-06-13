<?php
declare(strict_types=1);

use App\Helpers\FlagHelper;

/** @var string $today */
/** @var string $tomorrow */
/** @var array<int, array<string, mixed>> $leaderboardRows */
/** @var array<int, array<string, mixed>> $upcomingMatches */
/** @var array<string, mixed>|null $participant */
/** @var array<int, string> $todayVotes */
/** @var array<int, array<string, mixed>> $pastVotedMatches */
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
                            <span class="muted">Register to vote or <a href="<?= htmlspecialchars($url('league/login'), ENT_QUOTES, 'UTF-8'); ?>">login here</a></span>
                        <?php else: ?>
                            <select class="form-select form-select-sm" disabled>
                                <option value="" <?= $currentVote === '' ? 'selected' : ''; ?>>Choose</option>
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
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if ($participant !== null): ?>
            <div class="d-flex justify-content-end mt-3">
                <a class="btn btn-success" href="<?= htmlspecialchars($url('league/daily'), ENT_QUOTES, 'UTF-8'); ?>">Vote Now</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php if ($participant !== null): ?>
    <section class="panel">
        <h2>Your Past Votes</h2>
        <?php if ($pastVotedMatches === []): ?>
            <p class="muted">You have no past voted matches yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Stage</th>
                        <th>Fixture</th>
                        <th>Scoreline</th>
                        <th>Your Vote</th>
                        <th>Result</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pastVotedMatches as $match): ?>
                        <?php
                        $prediction = (string) ($match['prediction'] ?? '');
                        $result = (string) ($match['result'] ?? '');
                        $homeScore = $match['home_score'] ?? null;
                        $awayScore = $match['away_score'] ?? null;
                        $scoreline = ($homeScore !== null && $awayScore !== null)
                            ? ((string) $homeScore . ' - ' . (string) $awayScore)
                            : '-';
                        $statusLabel = 'Pending';
                        $statusClass = 'text-bg-secondary';

                        if ($prediction === '') {
                            $statusLabel = 'Did not vote';
                            $statusClass = 'text-bg-warning';
                        } elseif ($result !== '') {
                            if ($prediction === $result) {
                                $statusLabel = 'Correct';
                                $statusClass = 'text-bg-success';
                            } else {
                                $statusLabel = 'Incorrect';
                                $statusClass = 'text-bg-danger';
                            }
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($match['match_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) (($match['local_time'] ?? '') !== '' ? substr((string) ($match['local_time'] ?? ''), 0, 5) : 'TBC'), ENT_QUOTES, 'UTF-8'); ?></td>
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
                            <td><?= htmlspecialchars($scoreline, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) ($predictionLabels[$prediction] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) ($predictionLabels[$result] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>