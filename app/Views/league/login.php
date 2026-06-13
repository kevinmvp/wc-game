<?php
declare(strict_types=1);

/** @var array<int, string> $errors */
/** @var array<string, string> $formData */

// ... existing code ...

?>
<section class="panel">
    <h1>Login to League</h1>
    <p class="muted">Enter your mobile number and password to login.</p>

    <?php if ($errors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars($url('league/login'), ENT_QUOTES, 'UTF-8'); ?>" class="form-grid">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">

        <label class="form-label" for="mobile">Mobile Number</label>
        <input class="form-control" id="mobile" name="mobile" type="text" value="<?= htmlspecialchars($formData['mobile'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label class="form-label" for="password">Password</label>
        <input class="form-control" id="password" name="password" type="password" required>

        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-success" type="submit">Login</button>
            <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($url('league/join'), ENT_QUOTES, 'UTF-8'); ?>">Join League</a>
        </div>
    </form>
</section>

