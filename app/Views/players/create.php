<?php
declare(strict_types=1);

/** @var array<int, string> $errors */
/** @var array<string, string> $formData */
?>
<section class="panel">
    <h1>Create Player</h1>

    <?php if ($errors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars($url('players'), ENT_QUOTES, 'UTF-8'); ?>" class="form-grid">
        <label class="form-label" for="name">Name</label>
        <input class="form-control" id="name" name="name" type="text" value="<?= htmlspecialchars($formData['name'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label class="form-label" for="email">Email</label>
        <input class="form-control" id="email" name="email" type="email" value="<?= htmlspecialchars($formData['email'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label class="form-label" for="level">Level</label>
        <input class="form-control" id="level" name="level" type="number" min="1" max="100" value="<?= htmlspecialchars($formData['level'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-success" type="submit">Create</button>
            <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($url('players'), ENT_QUOTES, 'UTF-8'); ?>">Cancel</a>
        </div>
    </form>
</section>
