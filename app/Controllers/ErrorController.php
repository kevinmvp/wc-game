<?php
declare(strict_types=1);

namespace App\Controllers;

use Throwable;

/**
 * Handles error responses.
 */
class ErrorController extends BaseController
{
    /**
     * Renders a not-found page.
     */
    public function notFound(): void
    {
        $this->render('errors.404', [
            'title' => 'Page Not Found',
        ], 404);
    }

    /**
     * Renders a server-error page.
     */
    public function serverError(Throwable $exception): void
    {
        $isDebug = (bool) ($this->appConfig['debug'] ?? false);

        $this->render('errors.500', [
            'title' => 'Server Error',
            'errorMessage' => $isDebug ? $exception->getMessage() : 'An unexpected error occurred.',
        ], 500);
    }
}
