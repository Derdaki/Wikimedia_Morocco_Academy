<?php

declare(strict_types=1);

namespace App\Core;

final class Console
{
    private static ?bool $interactive = null;

    private static function interactive(): bool
    {
        // On Windows, checking a buffered stdin again can discard unread input.
        return self::$interactive ??= stream_isatty(STDIN);
    }

    public static function windowsPrompt(string $action): void
    {
        if (PHP_OS_FAMILY !== 'Windows' || !self::interactive()) return;
        $process = proc_open(['powershell.exe', '-NoProfile', '-File', BASE_PATH . '/scripts/admin-account.ps1',
            '-Action', $action, '-PhpBinary', PHP_BINARY], [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes, BASE_PATH);
        if (!is_resource($process)) throw new \RuntimeException('Unable to start the secure password prompt.');
        exit(proc_close($process));
    }

    public static function read(string $prompt): string
    {
        if (self::interactive()) fwrite(STDERR, $prompt);
        $line = fgets(STDIN);
        if ($line === false) throw new \RuntimeException('Input ended before account setup completed.');
        return rtrim($line, "\r\n");
    }

    public static function password(): string
    {
        if (!self::interactive()) return self::read('Password: ');
        $state = shell_exec('stty -g');
        if (!$state || !preg_match('/^[a-f0-9:]+$/i', trim($state))) {
            throw new \RuntimeException('Cannot hide password input. Use the Windows secure prompt or a secure stdin pipe.');
        }
        exec('stty -echo', $output, $status);
        if ($status !== 0) throw new \RuntimeException('Cannot hide password input.');
        try {
            return self::read('Password: ');
        } finally {
            exec('stty ' . escapeshellarg(trim($state)));
            fwrite(STDERR, PHP_EOL);
        }
    }
}
