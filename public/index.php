<?php
declare(strict_types=1);

use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\LeagueController;
use App\Controllers\PlayerController;
use App\Core\Environment;
use App\Core\Router;

/**
 * Absolute project root path.
 */
define('ROOT_PATH', dirname(__DIR__));

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Simple PSR-4-like autoloader for the App namespace.
 */
spl_autoload_register(static function (string $className): void {
    $prefix = 'App\\';
    if (!str_starts_with($className, $prefix)) {
        return;
    }

    $relativeClass = substr($className, strlen($prefix));
    $filePath = ROOT_PATH . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($filePath)) {
        require_once $filePath;
    }
});

Environment::load(ROOT_PATH . '/.env');

$appConfig = require ROOT_PATH . '/config/app.php';
$databaseConfig = require ROOT_PATH . '/config/database.php';

$configuredBaseUrl = trim((string) ($appConfig['base_url'] ?? ''));
if ($configuredBaseUrl === '') {
    $appConfig['base_url'] = '';
} else {
    $appConfig['base_url'] = rtrim($configuredBaseUrl, '/');
}

date_default_timezone_set((string) ($appConfig['timezone'] ?? 'UTC'));

$environment = (string) ($appConfig['environment'] ?? 'production');
$debugMode = (bool) ($appConfig['debug'] ?? false);

match ($environment) {
    'development' => error_reporting(E_ALL),
    default => error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT),
};

ini_set('display_errors', $debugMode ? '1' : '0');

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$routePath = is_string($requestUriPath) ? $requestUriPath : '/';

$scriptBasePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
if ($scriptBasePath !== '/' && $scriptBasePath !== '.' && str_starts_with($routePath, $scriptBasePath)) {
    $routePath = substr($routePath, strlen($scriptBasePath)) ?: '/';
}

$routePath = '/' . trim($routePath, '/');
if ($routePath === '//') {
    $routePath = '/';
}

$router = new Router();
$router->get('/', [HomeController::class, 'index']);
        $router->get('/players', [PlayerController::class, 'index']);
$router->get('/health', [HomeController::class, 'health']);
$router->get('/players/create', [PlayerController::class, 'createForm']);
$router->post('/players', [PlayerController::class, 'store']);
$router->get('/players/{id}', [PlayerController::class, 'show']);
$router->get('/players/{id}/edit', [PlayerController::class, 'editForm']);
$router->post('/players/{id}/update', [PlayerController::class, 'update']);
$router->post('/players/{id}/delete', [PlayerController::class, 'destroy']);
$router->get('/league/login', [LeagueController::class, 'loginForm']);
$router->post('/league/login', [LeagueController::class, 'loginSubmit']);
$router->get('/league/join', [LeagueController::class, 'joinForm']);
$router->post('/league/join', [LeagueController::class, 'joinSubmit']);
$router->get('/league/daily', [LeagueController::class, 'dailyGames']);
$router->get('/league/fixtures', [LeagueController::class, 'fixtures']);
$router->post('/league/vote/{id}', [LeagueController::class, 'submitVote']);
$router->get('/league/leaderboard', [LeagueController::class, 'leaderboard']);
$router->get('/league/manage-matches', [LeagueController::class, 'manageMatches']);
$router->get('/league/admin-login', [LeagueController::class, 'adminLoginForm']);
$router->post('/league/admin-login', [LeagueController::class, 'adminLoginSubmit']);
$router->get('/league/knockout-import', [LeagueController::class, 'knockoutImportForm']);
$router->post('/league/admin-logout', [LeagueController::class, 'adminLogout']);
$router->post('/league/matches', [LeagueController::class, 'createMatch']);
$router->post('/league/knockout-import', [LeagueController::class, 'knockoutImportSubmit']);
$router->post('/league/matches/{id}/result', [LeagueController::class, 'setResult']);
$router->get('/league/logout', [LeagueController::class, 'logoutParticipant']);

$route = $router->dispatch($requestMethod, $routePath);

try {
    if ($route === null) {
        $errorController = new ErrorController($appConfig);
        $errorController->notFound();
        return;
    }

    [$controllerClass, $actionMethod] = $route['handler'];
    $actionParams = $route['params'];

    $controller = match ($controllerClass) {
        HomeController::class => new HomeController($appConfig, $databaseConfig),
        PlayerController::class => new PlayerController($appConfig, $databaseConfig),
        LeagueController::class => new LeagueController($appConfig, $databaseConfig),
        default => new ErrorController($appConfig),
    };

    $controller->{$actionMethod}(...$actionParams);
} catch (Throwable $throwable) {
    $errorController = new ErrorController($appConfig);
    $errorController->serverError($throwable);
}

