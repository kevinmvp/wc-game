<?php

// ... (existing PHP code)
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
                    <?php
                    // Logic to show top 3 performers with ties
                    $position = 1;
                    $displayed = 0;
                    $previousScore = null;

                    foreach ($topPerformersToday as $row):
                        $currentScore = (int) ($row['total_score'] ?? $row['score'] ?? 0);

                        // Only display if we haven't shown 3 unique positions yet
                        // or if we're showing tied teams at position 3
                        if ($displayed < 3 || $currentScore === $previousScore):
                            // Update position only if score is different from previous
                            if ($currentScore !== $previousScore):
                                $position = $displayed + 1;
                                $previousScore = $currentScore;
                            endif;
                            $displayed++;
                    ?>
                        <tr>
                            <td><?= $position; ?></td>
                            <td><strong><?= htmlspecialchars((string) ($row['team_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td class="text-end">
                                <strong><?= $currentScore; ?></strong>
                                <?php if ($bonusEnabled && ((int) ($row['bonus_points'] ?? 0) > 0)): ?>
                                    <br><small class="muted"><?= (int) ($row['prediction_points'] ?? 0); ?> vote + <?= (int) ($row['bonus_points'] ?? 0); ?> bonus</small>
            <?php endif; ?>
                    </td>
                        </tr>
                    <?php
                        endif;
                    endforeach;
                    ?>
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

<?php if ($participant !== null): ?>
    <section class="panel">
        <h2>My Results</h2>
        <!-- Existing code for displaying participant's results -->
    </section>
<?php endif; ?>

<!-- ... (existing PHP code) -->
