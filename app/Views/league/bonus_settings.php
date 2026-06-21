<?php
declare(strict_types=1);

/** @var array<int, string> $errors */
/** @var array<string, string> $settings */
?>
<section class="panel">
    <h1>Bonus Settings</h1>
    <p class="muted">Configure the scoreline guess bonus feature for lower-ranked participants.</p>
</section>

<?php if ($errors !== []): ?>
    <div class="notice danger-notice">
        <?php foreach ($errors as $error): ?>
            <p class="mb-0"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<section class="panel">
    <form method="post" action="<?= htmlspecialchars($url('league/bonus-settings'), ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">

        <div class="form-grid">
            <div>
                <label for="bonus_enabled">Enable Bonus Feature</label>
                <select id="bonus_enabled" name="bonus_enabled" class="form-select">
                    <option value="1" <?= ((string) ($settings['bonus_enabled'] ?? '1') === '1') ? 'selected' : ''; ?>>Enabled</option>
                    <option value="0" <?= ((string) ($settings['bonus_enabled'] ?? '1') === '0') ? 'selected' : ''; ?>>Disabled</option>
                </select>
                <small class="muted">When disabled, no participants can access scoreline guesses.</small>
            </div>

            <div>
                <label for="bonus_position_threshold">Position Threshold</label>
                <input type="number" id="bonus_position_threshold" name="bonus_position_threshold"
                       min="1" max="999" step="1"
                       value="<?= htmlspecialchars((string) ($settings['bonus_position_threshold'] ?? '6'), ENT_QUOTES, 'UTF-8'); ?>">
                <small class="muted">Participants ranked at or below this position (e.g. 6 = 6th and below) can make scoreline guesses.</small>
            </div>

            <div>
                <label for="bonus_points_per_guess">Points per Correct Guess</label>
                <input type="number" id="bonus_points_per_guess" name="bonus_points_per_guess"
                       min="1" max="100" step="1"
                       value="<?= htmlspecialchars((string) ($settings['bonus_points_per_guess'] ?? '5'), ENT_QUOTES, 'UTF-8'); ?>">
                <small class="muted">Number of bonus points awarded for each correct scoreline guess.</small>
            </div>

            <div class="d-flex gap-2 pt-2">
                <button class="btn btn-success" type="submit">Save Settings</button>
                <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($url('league/manage-matches'), ENT_QUOTES, 'UTF-8'); ?>">Cancel</a>
            </div>
        </div>
    </form>
</section>