<?php
declare(strict_types=1);

/**
 * Writes one line per event: "2026-11-19T10:04:12.403Z LEVEL greenpay message key=value ...".
 * The same format ResolveAI's log search reads.
 */
final class Logger
{
    private static string $file = '';

    public static function init(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        self::$file = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . 'app.log';
    }

    public static function info(string $message, array $context = []): void  { self::write('INFO', $message, $context); }
    public static function warn(string $message, array $context = []): void  { self::write('WARN', $message, $context); }
    public static function error(string $message, array $context = []): void { self::write('ERROR', $message, $context); }

    public static function shortPath(string $path): string
    {
        $root = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        return str_starts_with($path, $root) ? str_replace('\\', '/', substr($path, strlen($root))) : $path;
    }

    private static function write(string $level, string $message, array $context): void
    {
        $now = DateTimeImmutable::createFromFormat('U.u', sprintf('%.6F', microtime(true)));
        $line = $now->format('Y-m-d\TH:i:s.v\Z') . ' ' . str_pad($level, 5) . ' greenpay ' . $message;
        foreach ($context as $key => $value) {
            $value = (string) $value;
            $line .= ' ' . $key . '=' . (preg_match('/[\s"]/', $value) ? json_encode($value, JSON_UNESCAPED_SLASHES) : $value);
        }
        file_put_contents(self::$file, $line . "\n", FILE_APPEND | LOCK_EX);
    }
}
