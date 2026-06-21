<?php
declare(strict_types=1);

/** @var array<string, mixed> $appConfig */
/** @var string $viewPath */
/** @var string|null $title */
/** @var bool|null $hideAppBrand */
$appName = (string) ($appConfig['app_name'] ?? 'Lemonade Stack');
$hideAppBrand = (bool) ($hideAppBrand ?? false);
$pageTitle = $hideAppBrand
    ? (isset($title) && $title !== '' ? $title : 'Home')
    : (isset($title) && $title !== '' ? $title . ' | ' . $appName : $appName);
$leagueAdminAuthenticated = (($_SESSION['league_admin_authenticated'] ?? false) === true);

$requestUriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = is_string($requestUriPath) ? $requestUriPath : '/';
$baseUrl = rtrim((string) ($appConfig['base_url'] ?? ''), '/');
if ($baseUrl !== '' && str_starts_with($requestPath, $baseUrl)) {
    $requestPath = substr($requestPath, strlen($baseUrl)) ?: '/';
}
$requestPath = '/' . trim($requestPath, '/');
if ($requestPath === '//') {
    $requestPath = '/';
}

$isActive = static function (string $path) use ($requestPath): bool {
    if ($path === '/') {
        return $requestPath === '/';
    }

    return $requestPath === $path || str_starts_with($requestPath, $path . '/');
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        :root {
            --accent: #1f6f4a;
            --soft-bg: #f4f8f5;
        }

        body {
            background: radial-gradient(circle at 5% 0%, #e8f3ec 0%, #f7faf8 35%, #ffffff 100%);
        }

        [data-bs-theme="dark"] body {
            background: radial-gradient(circle at 5% 0%, #16231b 0%, #101714 35%, #0b0f0d 100%);
        }

        .navbar-brand-text {
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .navbar .nav-link.active {
            font-weight: 600;
            color: #ffffff;
        }

        .session-pill {
            display: inline-flex;
            align-items: center;
            background: #eaf5ee;
            border: 1px solid #c8dfcf;
            color: #174f34;
            border-radius: 999px;
            padding: 0.2rem 0.65rem;
            font-size: 0.8rem;
            margin-left: 0.5rem;
        }

        .panel {
            background: #ffffff;
            border: 1px solid #dde7e0;
            border-radius: 0.9rem;
            box-shadow: 0 10px 28px rgba(23, 61, 42, 0.08);
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        [data-bs-theme="dark"] .panel {
            background: #161d19;
            border-color: #2a3a32;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.35);
        }

        .panel h1,
        .panel h2 {
            margin-bottom: 0.75rem;
        }

        .muted {
            color: #5f6d64;
        }

        [data-bs-theme="dark"] .muted {
            color: #9db1a5;
        }

        .status-ok {
            color: #198754;
            font-weight: 700;
        }

        .status-down {
            color: #dc3545;
            font-weight: 700;
        }

        .notice {
            border-radius: 0.65rem;
            padding: 0.75rem 0.9rem;
            margin-bottom: 0.9rem;
        }

        .inline-form {
            display: inline-flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .inline-nav-form {
            display: inline;
            margin: 0;
            padding: 0;
        }

        .inline-nav-form button {
            border: none;
            background: transparent;
            color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 0.75rem;
            font-weight: 500;
            cursor: pointer;
        }

        .inline-nav-form button:hover {
            color: #ffffff;
        }

        .form-grid {
            display: grid;
            gap: 0.75rem;
            max-width: 680px;
        }

        input,
        select,
        textarea,
        .form-grid input,
        .form-grid select,
        .form-grid textarea {
            display: block;
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }

        textarea {
            resize: vertical;
            min-height: 7.5rem;
        }

        .btn-success {
            --bs-btn-bg: var(--accent);
            --bs-btn-border-color: var(--accent);
            --bs-btn-hover-bg: #18583b;
            --bs-btn-hover-border-color: #18583b;
            --bs-btn-active-bg: #134a32;
            --bs-btn-active-border-color: #134a32;
        }

        .danger {
            border-color: #f1b0b7;
            background: #f8d7da;
        }

        .danger-notice {
            background: #f8d7da;
            border: 1px solid #f1b0b7;
            color: #842029;
        }

        .card-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }

        .fixture-card {
            border: 1px solid #dce6df;
            border-radius: 0.75rem;
            padding: 1rem;
            background: #fcfdfc;
        }

        [data-bs-theme="dark"] .fixture-card {
            border-color: #2d3d34;
            background: #141b17;
        }

        table {
            width: 100%;
        }

        .table {
            margin-bottom: 0;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table-date-divider {
            background: #f3f7ef;
            font-weight: 600;
        }

        .table-responsive {
            overflow-x: auto;
        }

        footer {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .today-results-list {
            display: flex;
            flex-direction: column;
            gap: 0;
            border: 1px solid #e8eeea;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        [data-bs-theme="dark"] .today-results-list {
            border-color: #2a3a32;
        }

        .today-result-item {
            padding: 0.6rem 0.75rem;
            border-bottom: 1px solid #e8eeea;
        }

        [data-bs-theme="dark"] .today-result-item {
            border-bottom-color: #2a3a32;
        }

        .today-result-item:last-child {
            border-bottom: none;
        }

        .today-result-teams {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.4rem;
        }

        .today-result-team {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-weight: 500;
            font-size: 0.9rem;
            min-width: 0;
            overflow: hidden;
        }

        .today-result-team--home {
            justify-content: flex-start;
            text-align: left;
        }

        .today-result-team--away {
            justify-content: flex-end;
            text-align: right;
        }

        .today-result-team-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 140px;
        }

        .today-result-score {
            font-size: 0.95rem;
            padding: 0.15rem 0.5rem;
            background: #f3f7ef;
            border-radius: 0.35rem;
            white-space: nowrap;
            flex-shrink: 0;
            min-width: 3.2rem;
            text-align: center;
        }

        [data-bs-theme="dark"] .today-result-score {
            background: #1e2b23;
        }

        .today-result-meta {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.2rem;
            padding-left: 0.1rem;
        }

        [data-bs-theme="dark"] .today-result-meta {
            color: #9db1a5;
        }

        @media (max-width: 768px) {
            .panel {
                padding: 1rem;
            }

            .inline-form {
                display: grid;
                width: 100%;
            }

            .today-result-team-name {
                max-width: 80px;
            }

            .today-result-teams {
                gap: 0.3rem;
            }

            .today-result-team {
                font-size: 0.82rem;
            }

            .today-result-score {
                font-size: 0.85rem;
                padding: 0.1rem 0.4rem;
                min-width: 2.8rem;
            }
        }

        @media (max-width: 480px) {
            .today-result-team-name {
                max-width: 56px;
            }

            .today-result-team {
                font-size: 0.78rem;
                gap: 0.2rem;
            }

            .today-result-score {
                font-size: 0.8rem;
                min-width: 2.5rem;
            }
        }
    </style>
</head>
<body>
<header class="sticky-top">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid container-xl">
            <a class="navbar-brand navbar-brand-text" href="<?= htmlspecialchars($url(''), ENT_QUOTES, 'UTF-8'); ?>">
                <?php if (!$hideAppBrand): ?>
                    RPC World Cup League
                <?php else: ?>
                    RPC World Cup League
                <?php endif; ?>
                <?php if (isset($_SESSION['participant']['name'])): ?>
                    <span class="session-pill">
                        <?= htmlspecialchars((string) $_SESSION['participant']['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                <?php endif; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link <?= $isActive('/') ? 'active' : ''; ?>" <?= $isActive('/') ? 'aria-current="page"' : ''; ?> href="<?= htmlspecialchars($url(''), ENT_QUOTES, 'UTF-8'); ?>">Home</a></li>
                    <?php if (!isset($_SESSION['participant']['name'])): ?>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/league/join') ? 'active' : ''; ?>" <?= $isActive('/league/join') ? 'aria-current="page"' : ''; ?> href="<?= htmlspecialchars($url('league/join'), ENT_QUOTES, 'UTF-8'); ?>">Join League</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link <?= $isActive('/league/daily') ? 'active' : ''; ?>" <?= $isActive('/league/daily') ? 'aria-current="page"' : ''; ?> href="<?= htmlspecialchars($url('league/daily'), ENT_QUOTES, 'UTF-8'); ?>">Daily Games</a></li>
                    <!--
                    <?php if (isset($_SESSION['participant']['name'])): ?>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/league/scoreline-guesses') ? 'active' : ''; ?>" <?= $isActive('/league/scoreline-guesses') ? 'aria-current="page"' : ''; ?> href="<?= htmlspecialchars($url('league/scoreline-guesses'), ENT_QUOTES, 'UTF-8'); ?>">Scoreline Guesses</a></li>
                    <?php endif; ?>

                    -->
                    <li class="nav-item"><a class="nav-link <?= $isActive('/league/fixtures') ? 'active' : ''; ?>" <?= $isActive('/league/fixtures') ? 'aria-current="page"' : ''; ?> href="<?= htmlspecialchars($url('league/fixtures'), ENT_QUOTES, 'UTF-8'); ?>">Fixtures</a></li>
                    <li class="nav-item"><a class="nav-link <?= $isActive('/league/leaderboard') ? 'active' : ''; ?>" <?= $isActive('/league/leaderboard') ? 'aria-current="page"' : ''; ?> href="<?= htmlspecialchars($url('league/leaderboard'), ENT_QUOTES, 'UTF-8'); ?>">Leaderboard</a></li>
                    <?php if ($leagueAdminAuthenticated): ?>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/league/manage-matches') ? 'active' : ''; ?>" <?= $isActive('/league/manage-matches') ? 'aria-current="page"' : ''; ?> href="<?= htmlspecialchars($url('league/manage-matches'), ENT_QUOTES, 'UTF-8'); ?>">Manage Matches</a></li>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/league/bonus-settings') ? 'active' : ''; ?>" <?= $isActive('/league/bonus-settings') ? 'aria-current="page"' : ''; ?> href="<?= htmlspecialchars($url('league/bonus-settings'), ENT_QUOTES, 'UTF-8'); ?>">Bonus Settings</a></li>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/league/manage-votes') ? 'active' : ''; ?>" <?= $isActive('/league/manage-votes') ? 'aria-current="page"' : ''; ?> href="<?= htmlspecialchars($url('league/manage-votes'), ENT_QUOTES, 'UTF-8'); ?>">Manage Votes</a></li>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/league/knockout-import') ? 'active' : ''; ?>" <?= $isActive('/league/knockout-import') ? 'aria-current="page"' : ''; ?> href="<?= htmlspecialchars($url('league/knockout-import'), ENT_QUOTES, 'UTF-8'); ?>">Knockout Import</a></li>
                        <li class="nav-item d-flex align-items-center">
                            <form method="post" action="<?= htmlspecialchars($url('league/admin-logout'), ENT_QUOTES, 'UTF-8'); ?>" class="inline-nav-form">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit">Admin Logout</button>
                            </form>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item d-flex align-items-center">
                        <button class="btn btn-outline-light btn-sm ms-lg-2" type="button" id="themeToggle" aria-label="Toggle color theme">Dark mode</button>
                    </li>
                    <?php if (isset($_SESSION['participant']['name'])): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($url('league/logout'), ENT_QUOTES, 'UTF-8'); ?>">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/league/login') ? 'active' : ''; ?>" <?= $isActive('/league/login') ? 'aria-current="page"' : ''; ?> href="<?= htmlspecialchars($url('league/login'), ENT_QUOTES, 'UTF-8'); ?>">Login</a></li>
                    <?php endif; ?>
                </ul>
        </div>
        </div>
    </nav>
</header>
<main class="container-xl py-4">
    <?php require $viewPath; ?>
</main>
<footer class="border-top py-3">
    <div class="container-xl">Powered by Picklereward Games</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
    (() => {
        const key = 'rpc_theme';
        const root = document.documentElement;
        const button = document.getElementById('themeToggle');

        const applyTheme = (theme) => {
            root.setAttribute('data-bs-theme', theme);
            if (button) {
                button.textContent = theme === 'dark' ? 'Light mode' : 'Dark mode';
            }
        };

        const storedTheme = sessionStorage.getItem(key);
        const initialTheme = storedTheme === 'dark' ? 'dark' : 'light';
        applyTheme(initialTheme);

        if (button) {
            button.addEventListener('click', () => {
                const currentTheme = root.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
                const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
                sessionStorage.setItem(key, nextTheme);
                applyTheme(nextTheme);
            });
        }
    })();
</script>
</body>
</html>


