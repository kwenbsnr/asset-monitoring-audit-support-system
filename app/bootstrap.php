<?php
if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

session_start();

// Dynamically computes the base URL so asset paths (css/js/images) work
// whether the app lives in a subfolder (local XAMPP) or at the domain
// root (InfinityFree, where files live directly under htdocs/).
if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $scriptDir = rtrim($scriptDir, '/');
    define('BASE_URL', $scriptDir);
}

// Load Composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// PSR‑4 autoloader for App\ namespace – fallback if Composer not available
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/';  // app/ is at the same level as bootstrap.php
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});