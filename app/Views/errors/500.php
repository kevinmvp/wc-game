<?php
declare(strict_types=1);

/** @var string $errorMessage */
?>
<section class="panel">
    <h1>500</h1>
    <p>Something went wrong while processing your request.</p>
    <p class="muted"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
    <p><a class="btn btn-outline-secondary" href="<?= htmlspecialchars($url(''), ENT_QUOTES, 'UTF-8'); ?>">Return to home</a></p>
</section>
