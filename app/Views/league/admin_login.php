<?php
declare(strict_types=1);

/** @var array<int, string> $errors */
?>
<section class="panel">
    <h1>League Admin Login</h1>
    <p class="muted">Sign in to import exact knockout fixtures.</p>

    <?php if ($errors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars($url('league/admin-login'), ENT_QUOTES, 'UTF-8'); ?>" class="form-grid">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">

        <label class="form-label" for="admin_password">Admin Password</label>
        <input class="form-control" id="admin_password" name="admin_password" type="password" value="" required>

        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-success" type="submit">Sign In</button>
            <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($url('league/fixtures'), ENT_QUOTES, 'UTF-8'); ?>">Back to Fixtures</a>
        </div>
    </form>
</section>
