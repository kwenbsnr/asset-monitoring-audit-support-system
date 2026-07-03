<?php
if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

session_start();

// Simple namespace-to-path autoloader
spl_autoload_register(function ($class) {
    // Project root namespace prefix
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/';

    // Does the class use the App\ namespace?
    if (strpos($class, $prefix) === 0) {
        $relative_class = substr($class, strlen($prefix));
        // Replace backslashes with directory separators
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});