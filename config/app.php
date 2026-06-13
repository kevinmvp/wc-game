<?php
declare(strict_types=1);

/**
 * Global application configuration.
 *
 * @return array<string, mixed>
 */
return [
    'app_name' => 'Lemonade Stack',
    'environment' => getenv('APP_ENV') ?: 'development',
    'debug' => filter_var(getenv('APP_DEBUG') ?: '1', FILTER_VALIDATE_BOOL),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Singapore',
    'base_url' => getenv('APP_BASE_URL') ?: '',
    'league_admin_password' => getenv('123qwe') ?: '',
];


