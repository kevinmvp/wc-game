<?php
declare(strict_types=1);

use App\Helpers\FlagHelper;

/** @var array<int, array<string, mixed>> $matches */
/** @var string $selectedStage */
/** @var string $selectedGroup */
/** @var string $selectedDate */
/** @var string $selectedVenue */
/** @var array<int, string> $stageOptions */
/** @var array<int, string> $groupOptions */
/** @var array<int, string> $venueOptions */
/** @var int $page */
/** @var int $perPage */
/** @var int $totalFixtures */
/** @var int $totalPages */
/** @var int $imported */
/** @var int $skipped */
/** @var string $viewMode */

$predictionLabels = [
    'home' => 'Home Win',
    'away' => 'Away Win',
    'draw' => 'Draw',
];

$buildFixturesUrl = static function (int $targetPage, string $targetView) use ($selectedStage, $selectedGroup, $selectedDate, $selectedVenue): string {
    $query = [
        'stage' => $selectedStage,
        'group' => $selectedGroup,
        'date' => $selectedDate,
        'venue' => $selectedVenue,
        'view' => $targetView,
        'page' => (string) $targetPage,
    ];

    return 'league/fixtures?' . http_build_query(array_filter($query, static fn (string $value): bool => $value !== ''));
};
?>
<section class="panel">
    <h1>Fixtures</h1>
    <p class="muted">Browse all loaded 2026 World Cup fixtures by stage and group.</p>
    <p class="muted">Showing <?= count($matches); ?> of <?= $totalFixtures; ?> fixtures.</p>
    <?php if ($imported > 0 || $skipped > 0): ?>
        <div class="alert alert-success" role="alert">
            Imported <?= $imported; ?> fixture<?= $imported === 1 ? '' : 's'; ?>.
            <?php if ($skipped > 0): ?>Skipped <?= $skipped; ?> duplicate fixture<?= $skipped === 1 ? '' : 's'; ?>.<?php endif; ?>
        </div>
    <?php endif; ?>
    <p class="d-flex align-items-center gap-2 flex-wrap">
        View:
        <a class="btn btn-outline-success btn-sm" href="<?= htmlspecialchars($url($buildFixturesUrl(1, 'table')), ENT_QUOTES, 'UTF-8'); ?>">Table</a>
        <a class="btn btn-outline-success btn-sm" href="<?= htmlspecialchars($url($buildFixturesUrl(1, 'grid')), ENT_QUOTES, 'UTF-8'); ?>">Grid</a>
    </p>

    <form method="get" action="<?= htmlspecialchars($url('league/fixtures'), ENT_QUOTES, 'UTF-8'); ?>" class="form-grid">
        <label class="form-label" for="stage">Stage</label>
        <select class="form-select" id="stage" name="stage">
            <?php foreach ($stageOptions as $stageOption): ?>
                <option value="<?= htmlspecialchars($stageOption, ENT_QUOTES, 'UTF-8'); ?>" <?= $stageOption === $selectedStage ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($stageOption, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label class="form-label" for="group">Group</label>
        <select class="form-select" id="group" name="group">
            <option value="">All Groups</option>
            <?php foreach ($groupOptions as $groupOption): ?>
                <option value="<?= htmlspecialchars($groupOption, ENT_QUOTES, 'UTF-8'); ?>" <?= $groupOption === $selectedGroup ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($groupOption, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label class="form-label" for="date">Match Date</label>
        <input class="form-control" id="date" name="date" type="date" value="<?= htmlspecialchars($selectedDate, ENT_QUOTES, 'UTF-8'); ?>">

        <label class="form-label" for="venue">Venue</label>
        <select class="form-select" id="venue" name="venue">
            <option value="">All Venues</option>
            <?php foreach ($venueOptions as $venueOption): ?>
                <option value="<?= htmlspecialchars($venueOption, ENT_QUOTES, 'UTF-8'); ?>" <?= $venueOption === $selectedVenue ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($venueOption, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div>
            <button class="btn btn-success" type="submit">Apply Filters</button>
        </div>
    </form>
</section>

<?php if ($totalPages > 1): ?>
    <section class="panel">
        <p class="d-flex align-items-center gap-2 flex-wrap">
            Page <?= $page; ?> of <?= $totalPages; ?>
            <?php if ($page > 1): ?>
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($url($buildFixturesUrl($page - 1, $viewMode)), ENT_QUOTES, 'UTF-8'); ?>">Previous</a>
            <?php endif; ?>
            <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                <?php if ($pageNumber === $page): ?>
                    <span class="badge text-bg-success"><?= $pageNumber; ?></span>
                <?php else: ?>
                    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($url($buildFixturesUrl($pageNumber, $viewMode)), ENT_QUOTES, 'UTF-8'); ?>"><?= $pageNumber; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($url($buildFixturesUrl($page + 1, $viewMode)), ENT_QUOTES, 'UTF-8'); ?>">Next</a>
            <?php endif; ?>
        </p>
    </section>
<?php endif; ?>

<section class="panel">
    <?php if ($matches === []): ?>
        <p>No fixtures found for the selected filters.</p>
    <?php elseif ($viewMode === 'grid'): ?>
        <div class="card-grid">
            <?php foreach ($matches as $match): ?>
                <article class="fixture-card">
                    <p class="muted"><?= htmlspecialchars((string) $match['match_date'], ENT_QUOTES, 'UTF-8'); ?><?php if ((string) ($match['local_time'] ?? '') !== ''): ?> at <?= htmlspecialchars(substr((string) $match['local_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?><?php endif; ?></p>
                    <h3>
                        <?= FlagHelper::getFlag((string) $match['home_team']); ?>
                        <?= htmlspecialchars((string) $match['home_team'], ENT_QUOTES, 'UTF-8'); ?>
                        vs
                        <?= FlagHelper::getFlag((string) $match['away_team']); ?>
                        <?= htmlspecialchars((string) $match['away_team'], ENT_QUOTES, 'UTF-8'); ?>
                    </h3>
                    <p><strong><?= htmlspecialchars((string) $match['stage'], ENT_QUOTES, 'UTF-8'); ?></strong><?php if ((string) ($match['group_name'] ?? '') !== ''): ?> · <?= htmlspecialchars((string) $match['group_name'], ENT_QUOTES, 'UTF-8'); ?><?php endif; ?></p>
                    <?php if ((string) ($match['venue'] ?? '') !== ''): ?>
                        <p><?= htmlspecialchars((string) $match['venue'], ENT_QUOTES, 'UTF-8'); ?><?php if ((string) ($match['venue_city'] ?? '') !== ''): ?>, <?= htmlspecialchars((string) $match['venue_city'], ENT_QUOTES, 'UTF-8'); ?><?php endif; ?></p>
                    <?php endif; ?>
                    <p class="muted"><?= htmlspecialchars((string) ($predictionLabels[(string) ($match['result'] ?? '')] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <?php $currentDate = null; ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
            <thead>
            <tr>
                <th>Date</th>
                <th>Stage</th>
                <th>Fixture</th>
                <th>Venue</th>
                <th>Result</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($matches as $match): ?>
                <?php if ($currentDate !== (string) $match['match_date']): ?>
                    <?php $currentDate = (string) $match['match_date']; ?>
                    <tr>
                        <td class="table-date-divider" colspan="5"><?= htmlspecialchars($currentDate, ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td>
                        <?php if ((string) ($match['local_time'] ?? '') !== ''): ?>
                            <strong><?= htmlspecialchars(substr((string) $match['local_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?></strong>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= htmlspecialchars((string) $match['stage'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php if ((string) ($match['group_name'] ?? '') !== ''): ?>
                            <br><span class="muted"><?= htmlspecialchars((string) $match['group_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= FlagHelper::getFlag((string) $match['home_team']); ?>
                        <strong><?= htmlspecialchars((string) $match['home_team'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        vs
                        <?= FlagHelper::getFlag((string) $match['away_team']); ?>
                        <strong><?= htmlspecialchars((string) $match['away_team'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <?php if ((string) ($match['notes'] ?? '') !== ''): ?>
                            <br><span class="muted"><?= htmlspecialchars((string) $match['notes'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= htmlspecialchars((string) ($match['venue'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        <?php if ((string) ($match['venue_city'] ?? '') !== ''): ?>
                            <br><span class="muted"><?= htmlspecialchars((string) $match['venue_city'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars((string) ($predictionLabels[(string) ($match['result'] ?? '')] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>
