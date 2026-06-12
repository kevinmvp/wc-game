<?php
declare(strict_types=1);

namespace App\Controllers;

use RuntimeException;

/**
 * Common controller behavior for rendering views and JSON responses.
 */
abstract class BaseController
{
    /**
     * @param array<string, mixed> $appConfig Application-level configuration values.
     */
    public function __construct(protected array $appConfig)
    {
    }

    /**
     * Renders a PHP view wrapped by the shared layout.
     *
     * @param string $view Dot-notation view path, e.g. home.index.
     * @param array<string, mixed> $data Data exposed to the view.
     * @param int $statusCode HTTP status code.
     */
    protected function render(string $view, array $data = [], int $statusCode = 200): void
    {
        http_response_code($statusCode);

        $viewPath = ROOT_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($viewPath)) {
            throw new RuntimeException('View not found: ' . $viewPath);
        }

        $appConfig = $this->appConfig;
        $url = static function (string $path = '') use ($appConfig): string {
            $baseUrl = rtrim((string) ($appConfig['base_url'] ?? ''), '/');
            if ($path === '') {
                return $baseUrl === '' ? '/' : $baseUrl . '/';
            }

            return ($baseUrl === '' ? '' : $baseUrl) . '/' . ltrim($path, '/');
        };
        $csrfToken = fn (): string => $this->csrfToken();
        extract($data, EXTR_SKIP);

        require ROOT_PATH . '/app/Views/layouts/main.php';
    }

    /**
     * Sends a JSON response.
     *
     * @param array<string, mixed> $payload Response payload.
     * @param int $statusCode HTTP status code.
     */
    protected function renderJson(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($payload, JSON_THROW_ON_ERROR);
    }

    /**
     * Redirects to another path and terminates execution.
     *
     * @param string $path Target location.
     * @param int $statusCode Redirect status code.
     */
    protected function redirect(string $path, int $statusCode = 302): void
    {
        $baseUrl = rtrim((string) ($this->appConfig['base_url'] ?? ''), '/');
        $location = preg_match('#^https?://#i', $path) === 1
            ? $path
            : ($baseUrl === '' ? '' : $baseUrl) . '/' . ltrim($path, '/');

        http_response_code($statusCode);
        header('Location: ' . $location);
        exit;
    }

    /**
     * Returns a stable CSRF token for the current session.
     */
    protected function csrfToken(): string
    {
        $token = $_SESSION['_csrf_token'] ?? null;
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $_SESSION['_csrf_token'] = $token;
        }

        return $token;
    }

    /**
     * Validates a CSRF token submitted by a POST form.
     *
     * @param string $submittedToken Token provided by the form.
     */
    protected function verifyCsrfToken(string $submittedToken): bool
    {
        $sessionToken = $_SESSION['_csrf_token'] ?? null;

        return is_string($sessionToken)
            && $sessionToken !== ''
            && hash_equals($sessionToken, $submittedToken);
    }
}
