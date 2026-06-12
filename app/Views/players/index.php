<?php
declare(strict_types=1);

/** @var array<int, array<string, mixed>> $players */
?>
<section class="panel">
    <h1>Players</h1>
    <p class="muted">Manage player records with secure prepared statements.</p>
    <p><a class="btn btn-success btn-sm" href="<?= htmlspecialchars($url('players/create'), ENT_QUOTES, 'UTF-8'); ?>">Create New Player</a></p>
</section>

<section class="panel">
    <?php if ($players === []): ?>
        <p>No players yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Level</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($players as $player): ?>
                <tr>
                    <td><?= (int) $player['id']; ?></td>
                    <td><?= htmlspecialchars((string) $player['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) $player['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= (int) $player['level']; ?></td>
                    <td>
                        <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($url('players/' . (string) ((int) $player['id'])), ENT_QUOTES, 'UTF-8'); ?>">View</a>
                        <a class="btn btn-outline-primary btn-sm" href="<?= htmlspecialchars($url('players/' . (string) ((int) $player['id']) . '/edit'), ENT_QUOTES, 'UTF-8'); ?>">Edit</a>
                        <form class="inline-form" method="post" action="<?= htmlspecialchars($url('players/' . (string) ((int) $player['id']) . '/delete'), ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>
