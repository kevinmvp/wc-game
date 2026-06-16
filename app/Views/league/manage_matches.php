<?php
declare(strict_types=1);

/** @var array<int, string> $errors */
/** @var array<string, string> $formData */
/** @var array<int, array<string, mixed>> $matches */
/** @var array<int, string> $allowedResults */

$resultLabels = [
    'home' => 'Home Win',
    'away' => 'Away Win',
    'draw' => 'Draw',
];
?>
<section class="panel">
    <h1>Manage Matches</h1>
    <p class="muted">Create daily games and set results to trigger point updates.</p>

    <?php if ($errors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars($url('league/matches'), ENT_QUOTES, 'UTF-8'); ?>" class="form-grid">
        <label class="form-label" for="match_date">Match Date</label>
        <input class="form-control" id="match_date" name="match_date" type="date" value="<?= htmlspecialchars($formData['match_date'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label class="form-label" for="home_team">Home Team</label>
        <input class="form-control" id="home_team" name="home_team" type="text" value="<?= htmlspecialchars($formData['home_team'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label class="form-label" for="away_team">Away Team</label>
        <input class="form-control" id="away_team" name="away_team" type="text" value="<?= htmlspecialchars($formData['away_team'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <div>
            <button class="btn btn-success" type="submit">Create Match</button>
        </div>
    </form>
</section>

<section class="panel">
    <h2>Scheduled Matches</h2>
    <?php if ($matches === []): ?>
        <p>No matches available.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
            <thead>
            <tr>
                <th>Stage</th>
                <th>Group</th>
                <th>Date</th>
                <th>Time</th>
                <th>Home</th>
                <th>Away</th>
                <!--<th>Venue</th>-->
                <th>Score</th>
                <th>Result</th>
                <th>Update</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($matches as $match): ?>
                <?php
                $homeScore = $match['home_score'] ?? null;
                $awayScore = $match['away_score'] ?? null;
                $scoreline = ($homeScore !== null && $awayScore !== null)
                    ? ((string) $homeScore . ' - ' . (string) $awayScore)
                    : '-';
                $formId = 'match-update-' . (string) ((int) $match['id']);
                ?>
                <tr>
                    <td><?= htmlspecialchars((string) $match['stage'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) ($match['group_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <input class="form-control form-control-sm" form="<?= htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>" name="match_date" type="date" value="<?= htmlspecialchars((string) $match['match_date'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </td>
                    <td>
                        <input class="form-control form-control-sm" form="<?= htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>" name="local_time" type="time" value="<?= htmlspecialchars(substr((string) ($match['local_time'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8'); ?>">
                    </td>
                    <td>
                        <input class="form-control form-control-sm" form="<?= htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>" name="home_team" type="text" value="<?= htmlspecialchars((string) $match['home_team'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </td>
                    <td>
                        <input class="form-control form-control-sm" form="<?= htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>" name="away_team" type="text" value="<?= htmlspecialchars((string) $match['away_team'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </td>
                    <!--td>
                        <input class="form-control form-control-sm mb-1" form="<?= htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>" name="venue" type="text" placeholder="Venue" value="<?= htmlspecialchars((string) ($match['venue'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        <input class="form-control form-control-sm" form="<?= htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>" name="venue_city" type="text" placeholder="City" value="<?= htmlspecialchars((string) ($match['venue_city'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    </td-->
                    <td class="text-nowrap">
                            <span class="d-block mb-1"><?= htmlspecialchars($scoreline, ENT_QUOTES, 'UTF-8'); ?></span>
                            <div class="d-flex align-items-center gap-1">
                        <input class="form-control form-control-sm" style="max-width: 4rem;" form="<?= htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>" name="home_score" type="number" min="0" max="99" value="<?= $homeScore === null ? '' : htmlspecialchars((string) $homeScore, ENT_QUOTES, 'UTF-8'); ?>">
                                <span>-</span>
                        <input class="form-control form-control-sm" style="max-width: 4rem;" form="<?= htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>" name="away_score" type="number" min="0" max="99" value="<?= $awayScore === null ? '' : htmlspecialchars((string) $awayScore, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                    </td>
                    <td>
                            <?= htmlspecialchars((string) ($resultLabels[(string) ($match['result'] ?? '')] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td>
                        <form id="<?= htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>" method="post" action="<?= htmlspecialchars($url('league/matches/' . (string) ((int) $match['id']) . '/details'), ENT_QUOTES, 'UTF-8'); ?>"></form>
                        <button class="btn btn-success btn-sm" form="<?= htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>" type="submit">Save</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>
