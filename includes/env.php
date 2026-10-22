<?php
/**
 * Minimal .env loader (KEY=VALUE per line, # for comments).
 *
 * Real environment variables (e.g. set by Docker or the hosting panel)
 * always win over values in the .env file.
 */

declare(strict_types=1);

function load_env(string $path): void
{
    if (!is_readable($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
            // Quoted value: take everything up to the closing quote.
            $end = strpos($value, $value[0], 1);
            $value = $end === false ? substr($value, 1) : substr($value, 1, $end - 1);
        } else {
            // Unquoted value: drop an inline "# comment".
            $value = trim((string) preg_replace('/(^|\s+)#.*$/', '', $value));
        }

        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}
