<?php
declare(strict_types=1);

/** @var array<int, array<string, mixed>> $rows */
/** @var bool $canViewMobile */
/** @var bool $bonusEnabled */
/** @var int $bonusPointsPerGuess */
/** @var int $bonusPositionThreshold */
?>
<section class="panel">
    <h1>League Leaderboard</h1>
    <p class="muted">Each correct prediction gives 1 point.</p>
    <?php if ($bonusEnabled): ?>
        <p class="muted">Bonus: Participants ranked at or below the top <?= $bonusPositionThreshold; ?> can earn <?= $bonusPointsPerGuess; ?> extra points per correct scoreline guess.</p>
    <?php endif; ?>
    <p class="d-flex flex-wrap gap-2">
        <a class="btn btn-success btn-sm" href="<?= htmlspecialchars($url('league/join'), ENT_QUOTES, 'UTF-8'); ?>">Join the league</a>
        <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($url('league/fixtures'), ENT_QUOTES, 'UTF-8'); ?>">Browse all fixtures</a>
    </p>
</section>

<section class="panel">
    <?php if ($rows === []): ?>
        <p>No participants registered yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Team Name</th>
                <?php if ($canViewMobile): ?>
                    <th>Mobile</th>
                <?php endif; ?>
                <!--th>Prediction Points</th>
                <th>Bonus Points</th-->
                <th>Total Points</th>
                <th>Total Votes</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $index => $row): ?>
                <tr>
                    <td><?= $index + 1; ?></td>
                    <td><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) $row['team_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <?php if ($canViewMobile): ?>
                        <td><?= htmlspecialchars((string) ($row['mobile'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <?php endif; ?>
                    <!--td><?= (int) ($row['prediction_points'] ?? 0); ?></td>
                    <td><?= (int) ($row['bonus_correct'] ?? 0) * $bonusPointsPerGuess; ?> <span class="muted">(<?= (int) ($row['bonus_correct'] ?? 0); ?> correct)</span></td-->
                    <td><strong><?= (int) $row['points']; ?></strong></td>
                    <td><?= (int) $row['total_votes']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>
