<?php
/**
 * Router for PHP Built-in Server
 * Routes requests to appropriate directories
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Route API requests to /api directory
if (strpos($uri, '/api/') === 0) {
    $file = __DIR__ . '/api' . substr($uri, 4); // Remove '/api' prefix and add '/api' directory
    if (file_exists($file) && is_file($file)) {
        $_SERVER['SCRIPT_NAME'] = $uri;
        require $file;
        return true;
    }
}

// Route health check directly
if ($uri === '/healthz.php' || $uri === '/healthz') {
    $file = __DIR__ . '/public/healthz.php';
    if (file_exists($file)) {
        $_SERVER['SCRIPT_NAME'] = '/healthz.php';
        require $file;
        return true;
    }
}

// Route public files to /public directory (CSS, JS, images, etc.)
if (strpos($uri, '/assets/') === 0 || 
    strpos($uri, '/import-schema.php') === 0 ||
    preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/i', $uri)) {
    $file = __DIR__ . '/public' . $uri;
    if (file_exists($file) && is_file($file)) {
        return false; // Serve the file directly
    }
    // If file doesn't exist, try without public prefix
    $file = __DIR__ . $uri;
    if (file_exists($file) && is_file($file)) {
        return false;
    }
}

// Route root and other requests to /public/index.php
if ($uri === '/' || $uri === '') {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    require __DIR__ . '/public/index.php';
    return true;
}

// For any other request, try public directory
$file = __DIR__ . '/public' . $uri;
if (file_exists($file) && is_file($file)) {
    return false; // Serve the file
}

// Default: serve index.php from public
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/public/index.php';
return true;
