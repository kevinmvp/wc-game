<?php
declare(strict_types=1);

/** @var array<int, string> $errors */
/** @var array<string, string> $formData */
/** @var array<int, string> $stageOptions */
?>
<section class="panel">
    <h1>Import Knockout Fixture</h1>
    <p class="muted">Use this screen later when exact knockout teams are known.</p>
    <p class="muted">Bulk format per line: <strong>date|time|home|away|venue|city|notes</strong> or <strong>stage|date|time|home|away|venue|city|notes</strong>.</p>

    <?php if ($errors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars($url('league/knockout-import'), ENT_QUOTES, 'UTF-8'); ?>" class="form-grid">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">

        <label class="form-label" for="stage">Stage</label>
        <select class="form-select" id="stage" name="stage" required>
            <?php foreach ($stageOptions as $stageOption): ?>
                <option value="<?= htmlspecialchars($stageOption, ENT_QUOTES, 'UTF-8'); ?>" <?= $stageOption === $formData['stage'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($stageOption, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label class="form-label" for="match_date">Match Date</label>
        <input class="form-control" id="match_date" name="match_date" type="date" value="<?= htmlspecialchars($formData['match_date'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label class="form-label" for="local_time">Local Time</label>
        <input class="form-control" id="local_time" name="local_time" type="time" value="<?= htmlspecialchars($formData['local_time'], ENT_QUOTES, 'UTF-8'); ?>">

        <label class="form-label" for="home_team">Home Team</label>
        <input class="form-control" id="home_team" name="home_team" type="text" value="<?= htmlspecialchars($formData['home_team'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label class="form-label" for="away_team">Away Team</label>
        <input class="form-control" id="away_team" name="away_team" type="text" value="<?= htmlspecialchars($formData['away_team'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label class="form-label" for="venue">Venue</label>
        <input class="form-control" id="venue" name="venue" type="text" value="<?= htmlspecialchars($formData['venue'], ENT_QUOTES, 'UTF-8'); ?>">

        <label class="form-label" for="venue_city">Venue City</label>
        <input class="form-control" id="venue_city" name="venue_city" type="text" value="<?= htmlspecialchars($formData['venue_city'], ENT_QUOTES, 'UTF-8'); ?>">

        <label class="form-label" for="notes">Notes</label>
        <input class="form-control" id="notes" name="notes" type="text" value="<?= htmlspecialchars($formData['notes'], ENT_QUOTES, 'UTF-8'); ?>">

        <label class="form-label" for="bulk_fixtures">Bulk Fixtures (optional)</label>
        <textarea class="form-control" id="bulk_fixtures" name="bulk_fixtures" rows="8" placeholder="2026-07-03|16:00|Winner R16-1|Winner R16-2|SoFi Stadium|Inglewood|Quarter-final&#10;Semi-Finals|2026-07-14|15:00|Winner QF-1|Winner QF-2|AT&amp;T Stadium|Arlington|Semi-final"><?= htmlspecialchars($formData['bulk_fixtures'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        <p class="muted">If bulk lines are provided, they are imported in one submit and single-entry fields are ignored.</p>

        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-success" type="submit">Import Fixture</button>
            <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($url('league/fixtures'), ENT_QUOTES, 'UTF-8'); ?>">Back to Fixtures</a>
        </div>
    </form>
</section>
