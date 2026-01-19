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
    } else {
        // API file not found - return 404
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'API endpoint not found: ' . $uri
        ]);
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

// Route static assets (CSS, JS, images, etc.) - MUST be before other routes
if (strpos($uri, '/assets/') === 0) {
    $file = __DIR__ . '/public' . $uri;
    if (file_exists($file) && is_file($file)) {
        // Set proper content type
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        // Read and output the file
        readfile($file);
        return true; // File served, stop processing
    }
    // If file not found, return 404
    http_response_code(404);
    echo "File not found: " . $uri;
    return true;
}

// Route import script
if ($uri === '/import-schema.php') {
    $file = __DIR__ . '/public/import-schema.php';
    if (file_exists($file)) {
        $_SERVER['SCRIPT_NAME'] = '/import-schema.php';
        require $file;
        return true;
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
