<?php
declare(strict_types=1);

/**
 * Root-level fallback bootstrap for local environments where the project
 * directory itself is the web root path segment (for example /wc-game).
 */
if (PHP_VERSION_ID < 80000) {
	http_response_code(500);
	header('Content-Type: text/plain; charset=utf-8');
	echo "This application requires PHP 8.0+ in Apache. Current version: " . PHP_VERSION;
	exit;
}

require __DIR__ . '/public/index.php';
