<?php
declare(strict_types=1);

use App\Helpers\FlagHelper;

/** @var string $today */
/** @var string $tomorrow */
/** @var array<int, array<string, mixed>> $leaderboardRows */
/** @var array<int, array<string, mixed>> $topPerformersToday */
/** @var array<int, array<string, mixed>> $matchVoteSummary */
/** @var array<int, array<string, mixed>> $upcomingMatches */
/** @var array<string, mixed>|null $participant */
/** @var array<int, string> $todayVotes */
/** @var array<int, array<string, mixed>> $pastVotedMatches */
/** @var array<int, string> $allowedPredictions */
/** @var array<int, array<string, mixed>> $todayMatches */

$buildPredictionLabel = static function (string $prediction, array $match): string {
    if ($prediction === 'home') {
        return FlagHelper::getFlag((string) ($match['home_team'] ?? '')) . ' ' . (string) ($match['home_team'] ?? '') . ' Win';
    }
    if ($prediction === 'away') {
        return FlagHelper::getFlag((string) ($match['away_team'] ?? '')) . ' ' . (string) ($match['away_team'] ?? '') . ' Win';
    }
    if ($prediction === 'draw') {
        return 'Draw';
    }
    return '';
};

$truncateTeamName = static function (string $team): string {
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($team) > 20 ? mb_substr($team, 0, 20) . '...' : $team;
    }

    return strlen($team) > 20 ? substr($team, 0, 20) . '...' : $team;
};
?>
<section class="panel">
    <h2>🏆 Top Performer(s) Today</h2>
    <p class="muted"><?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?></p>
    <div class="row g-3 g-md-4">
        <!-- Left Column: Top Performers -->
        <div class="col-12 col-md-5">
            <?php if ($topPerformersToday === []): ?>
                <p class="muted">No results available yet for today.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Team</th>
                        <th class="text-end">Pts</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($topPerformersToday as $index => $row): ?>
                        <tr>
                            <td><?= $index + 1; ?></td>
                            <td><strong><?= htmlspecialchars((string) ($row['team_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td class="text-end">
                                <strong><?= (int) ($row['total_score'] ?? $row['score'] ?? 0); ?></strong>
                                <?php if ($bonusEnabled && ((int) ($row['bonus_points'] ?? 0) > 0)): ?>
                                    <br><small class="muted"><?= (int) ($row['prediction_points'] ?? 0); ?> vote + <?= (int) ($row['bonus_points'] ?? 0); ?> bonus</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Today's Results -->
        <div class="col-12 col-md-7">
            <h5>Today's Results</h5>
            <?php if ($todayMatches === []): ?>
                <p class="muted">No matches scheduled for today.</p>
            <?php else: ?>
                <div class="today-results-list">
                    <?php foreach ($todayMatches as $match): ?>
                        <?php
                        $homeTeam = (string) ($match['home_team'] ?? '');
                        $awayTeam = (string) ($match['away_team'] ?? '');
                        $homeScore = $match['home_score'] ?? null;
                        $awayScore = $match['away_score'] ?? null;
                        $localTime = (string) ($match['local_time'] ?? '');
                        $matchResult = (string) ($match['result'] ?? '');
                        $hasScore = $homeScore !== null && $awayScore !== null;
                        ?>
                        <div class="today-result-item">
                            <div class="today-result-teams">
                                <span class="today-result-team today-result-team--home">
                                    <?= FlagHelper::getFlag($homeTeam); ?>
                                    <span class="today-result-team-name"><?= htmlspecialchars($homeTeam, ENT_QUOTES, 'UTF-8'); ?></span>
                                </span>
                                <span class="today-result-score">
                                    <?php if ($hasScore): ?>
                                        <strong><?= (int) $homeScore; ?> - <?= (int) $awayScore; ?></strong>
                                    <?php else: ?>
                                        <span class="muted">
                                            <?php if ($localTime !== ''): ?>
                                                <?= htmlspecialchars(substr($localTime, 0, 5), ENT_QUOTES, 'UTF-8'); ?>
                                            <?php else: ?>
                                                TBC
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                                <span class="today-result-team today-result-team--away">
                                    <span class="today-result-team-name"><?= htmlspecialchars($awayTeam, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?= FlagHelper::getFlag($awayTeam); ?>
                                </span>
                            </div>
                            <?php if ((string) ($match['stage'] ?? '') !== ''): ?>
                                <div class="today-result-meta">
                                    <?= htmlspecialchars((string) ($match['stage'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if ((string) ($match['group_name'] ?? '') !== ''): ?>
                                        · <?= htmlspecialchars((string) ($match['group_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

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
    <h2>Upcoming Fixtures
    <?php if ($participant !== null && $upcomingMatches !== []): ?>
        <?php
        $totalUpcoming = count($upcomingMatches);
        $votedCount = 0;
        foreach ($upcomingMatches as $um) {
            $umId = (int) ($um['id'] ?? 0);
            if (($todayVotes[$umId] ?? '') !== '') {
                $votedCount++;
            }
        }
        $hasPending = $votedCount < $totalUpcoming;
        ?>
        <?php if ($hasPending): ?>
            <span class="badge text-bg-danger" style="font-size: 0.6em; vertical-align: middle;">Pending votes</span>
        <?php endif; ?>
    <?php endif; ?>
    </h2>
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
                        <?php elseif ($currentVote === 'home'): ?>
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
        <h2>Your Vote Results</h2>
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
                            <td><?= htmlspecialchars($prediction !== '' ? $buildPredictionLabel($prediction, $match) : 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($result !== '' ? $buildPredictionLabel($result, $match) : 'Pending', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<section class="panel">
    <h2>Votes By Matches</h2>
    <?php if ($matchVoteSummary === []): ?>
        <p class="muted">No passed or next-day matches found yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Fixture</th>
                    <th>Votes</th>
                    <th style="min-width: 18rem;">Summary</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($matchVoteSummary as $summaryRow): ?>
                    <?php
                    $homeVotes = (int) ($summaryRow['home_votes'] ?? 0);
                    $awayVotes = (int) ($summaryRow['away_votes'] ?? 0);
                    $drawVotes = (int) ($summaryRow['draw_votes'] ?? 0);
                    $totalVotes = max(0, (int) ($summaryRow['total_votes'] ?? 0));

                    $homePercent = $totalVotes > 0 ? (int) round(($homeVotes / $totalVotes) * 100) : 0;
                    $awayPercent = $totalVotes > 0 ? (int) round(($awayVotes / $totalVotes) * 100) : 0;
                    $drawPercent = $totalVotes > 0 ? max(0, 100 - $homePercent - $awayPercent) : 0;
                    ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars((string) ($summaryRow['match_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            <?php if ((string) ($summaryRow['local_time'] ?? '') !== ''): ?>
                                <br><span class="muted"><?= htmlspecialchars(substr((string) ($summaryRow['local_time'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= FlagHelper::getFlag((string) ($summaryRow['home_team'] ?? '')); ?>
                            <strong><?= htmlspecialchars((string) ($summaryRow['home_team'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                            vs
                            <?= FlagHelper::getFlag((string) ($summaryRow['away_team'] ?? '')); ?>
                            <strong><?= htmlspecialchars((string) ($summaryRow['away_team'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                            <?php if ((string) ($summaryRow['group_name'] ?? '') !== ''): ?>
                                <br><span class="muted"><?= htmlspecialchars((string) ($summaryRow['group_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= $totalVotes; ?></strong></td>
                        <td>
                            <div class="progress mb-1" role="progressbar" aria-label="Vote summary" aria-valuemin="0" aria-valuemax="100" aria-valuenow="100">
                                <div class="progress-bar bg-success" style="width: <?= $homePercent; ?>%"></div>
                                <div class="progress-bar bg-secondary" style="width: <?= $drawPercent; ?>%"></div>
                                <div class="progress-bar bg-danger" style="width: <?= $awayPercent; ?>%"></div>
                            </div>
                            <small class="muted">
                                <?= htmlspecialchars((string) ($summaryRow['home_team'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>: <?= $homeVotes; ?>
                                | Draw: <?= $drawVotes; ?>
                                | <?= htmlspecialchars((string) ($summaryRow['away_team'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>: <?= $awayVotes; ?>
                            </small>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>