<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Loads key/value environment variables from a local .env file.
 */
final class Environment
{
    /**
     * Loads an environment file into runtime process variables.
     *
     * @param string $filePath Absolute path to .env file.
     * @param bool $override Whether existing values should be overwritten.
     */
    public static function load(string $filePath, bool $override = false): void
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if ($trimmedLine === '' || str_starts_with($trimmedLine, '#')) {
                continue;
            }

            $separatorPosition = strpos($trimmedLine, '=');
            if ($separatorPosition === false) {
                continue;
            }

            $rawKey = trim(substr($trimmedLine, 0, $separatorPosition));
            $rawValue = trim(substr($trimmedLine, $separatorPosition + 1));

            if ($rawKey === '') {
                continue;
            }

            $value = self::normalizeValue($rawValue);
            self::setVariable($rawKey, $value, $override);
        }
    }

    /**
     * Resolves an environment variable, returning a default when absent.
     *
     * @param mixed $default Fallback value.
     */
    public static function value(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        $processValue = getenv($key);
        if ($processValue !== false) {
            return $processValue;
        }

        return $default;
    }

    /**
     * Removes wrapping single or double quotes from values.
     */
    private static function normalizeValue(string $value): string
    {
        $length = strlen($value);
        if ($length >= 2) {
            $first = $value[0];
            $last = $value[$length - 1];

            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }

    /**
     * Writes a key/value pair into process and superglobal environments.
     */
    private static function setVariable(string $key, string $value, bool $override): void
    {
        $isAlreadyDefined = array_key_exists($key, $_ENV) || getenv($key) !== false;
        if ($isAlreadyDefined && !$override) {
            return;
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
