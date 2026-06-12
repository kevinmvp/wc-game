<?php
declare(strict_types=1);

/** @var array<string, mixed> $player */
?>
<section class="panel">
    <h1><?= htmlspecialchars((string) $player['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars((string) $player['email'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p class="mb-1"><strong>Level:</strong> <?= (int) $player['level']; ?></p>
    <p class="mb-1"><strong>Created:</strong> <?= htmlspecialchars((string) $player['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p class="mb-3"><strong>Updated:</strong> <?= htmlspecialchars((string) $player['updated_at'], ENT_QUOTES, 'UTF-8'); ?></p>

    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary" href="<?= htmlspecialchars($url('players/' . (string) ((int) $player['id']) . '/edit'), ENT_QUOTES, 'UTF-8'); ?>">Edit</a>
        <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($url('players'), ENT_QUOTES, 'UTF-8'); ?>">Back to list</a>
    </div>
</section>
