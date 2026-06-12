<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Lightweight HTTP router with dynamic path segment support.
 */
final class Router
{
    /**
     * @var array<string, array<int, array<string, mixed>>> Registered routes keyed by method.
     */
    private array $routes = [];

    /**
     * Registers a route for a given method and path.
     *
     * @param string $method HTTP method.
     * @param string $path Route path.
     * @param array{0: class-string, 1: string} $handler Controller class and action method.
     */
    public function add(string $method, string $path, array $handler): self
    {
        $normalizedMethod = strtoupper($method);
        $normalizedPath = $this->normalizePath($path);

        $this->routes[$normalizedMethod][] = [
            'path' => $normalizedPath,
            'pattern' => $this->compilePathPattern($normalizedPath),
            'handler' => $handler,
        ];

        return $this;
    }

    /**
     * Registers a GET route.
     *
     * @param string $path Route path.
     * @param array{0: class-string, 1: string} $handler Controller class and action method.
     */
    public function get(string $path, array $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    /**
     * Registers a POST route.
     *
     * @param string $path Route path.
     * @param array{0: class-string, 1: string} $handler Controller class and action method.
     */
    public function post(string $path, array $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    /**
     * Matches an incoming request and returns resolved handler data.
     *
     * @param string $method HTTP method.
     * @param string $path URI path.
     *
     * @return array{handler: array{0: class-string, 1: string}, params: array<int, string>}|null
     */
    public function dispatch(string $method, string $path): ?array
    {
        $normalizedMethod = strtoupper($method);
        $normalizedPath = $this->normalizePath($path);

        foreach ($this->routes[$normalizedMethod] ?? [] as $route) {
            $matches = [];
            if (preg_match($route['pattern'], $normalizedPath, $matches) !== 1) {
                continue;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[] = urldecode($value);
                }
            }

            return [
                'handler' => $route['handler'],
                'params' => $params,
            ];
        }

        return null;
    }

    /**
     * Normalizes route paths to a canonical /segment format.
     */
    private function normalizePath(string $path): string
    {
        $trimmedPath = trim($path);
        if ($trimmedPath === '' || $trimmedPath === '/') {
            return '/';
        }

        return '/' . trim($trimmedPath, '/');
    }

    /**
     * Compiles a route definition into a regex pattern.
     */
    private function compilePathPattern(string $path): string
    {
        $escaped = preg_quote($path, '#');
        $pattern = preg_replace('#\\\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\\\}#', '(?P<$1>[^/]+)', $escaped);

        return '#^' . $pattern . '$#';
    }
}

