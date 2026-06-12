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
                <th>Venue</th>
                <th>Result</th>
                <th>Update</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($matches as $match): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $match['stage'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) ($match['group_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) $match['match_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars(substr((string) ($match['local_time'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) $match['home_team'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) $match['away_team'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?= htmlspecialchars((string) ($match['venue'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        <?php if ((string) ($match['venue_city'] ?? '') !== ''): ?>
                            <br><span class="muted"><?= htmlspecialchars((string) $match['venue_city'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars((string) ($resultLabels[(string) ($match['result'] ?? '')] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <form method="post" action="<?= htmlspecialchars($url('league/matches/' . (string) ((int) $match['id']) . '/result'), ENT_QUOTES, 'UTF-8'); ?>" class="inline-form">
                            <select class="form-select form-select-sm" name="result">
                                <option value="">Pending</option>
                                <?php foreach ($allowedResults as $result): ?>
                                    <option value="<?= htmlspecialchars($result, ENT_QUOTES, 'UTF-8'); ?>" <?= $result === (string) ($match['result'] ?? '') ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars((string) $resultLabels[$result], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-success btn-sm" type="submit">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>
