<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';

require __DIR__ . '/Logger.php';
require __DIR__ . '/Database.php';
require __DIR__ . '/Auth.php';
require __DIR__ . '/View.php';

ini_set('display_errors', $config['debug'] ? '1' : '0');
error_reporting(E_ALL);

Logger::init($config['log_dir']);

set_exception_handler(function (Throwable $e) use ($config): void {
    Logger::error(sprintf('%s: %s in %s:%d', get_class($e), $e->getMessage(), Logger::shortPath($e->getFile()), $e->getLine()), [
        'path' => $_SERVER['REQUEST_URI'] ?? 'cli',
        'ip'   => $_SERVER['REMOTE_ADDR'] ?? '-',
    ]);
    http_response_code(500);
    if ($config['debug']) {
        echo '<pre>' . $e . '</pre>';
    } else {
        echo 'Something went wrong. Please try again.';
    }
});

session_name($config['session_name']);
session_start();

$db = Database::connect($config['db_path']);
