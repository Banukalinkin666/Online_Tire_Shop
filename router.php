<?php
/**
 * Router for PHP Built-in Server
 * Routes requests to appropriate directories
 */

// Parse URI and handle query strings properly
$parsedUri = parse_url($_SERVER['REQUEST_URI']);
$uri = $parsedUri['path'] ?? '/';

// Route API requests to /api directory
if (strpos($uri, '/api/') === 0) {
    // Extract the path after /api/
    $apiPath = substr($uri, 5); // Remove '/api/' prefix (5 chars including trailing slash)
    
    // Security: prevent directory traversal (no .. or /)
    if (strpos($apiPath, '..') !== false || strpos($apiPath, '/') === 0) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid API path']);
        return true;
    }
    
    $file = __DIR__ . '/api/' . $apiPath;
    $apiDir = __DIR__ . '/api';
    
    // Security check: ensure resolved path is within api directory
    $resolvedFile = realpath($file);
    $resolvedApiDir = realpath($apiDir);
    
    // If realpath fails, use direct path check (for Docker compatibility)
    if ($resolvedFile === false || $resolvedApiDir === false) {
        // Fallback: check if file exists and path starts with api directory
        if (file_exists($file) && is_file($file) && strpos($file, $apiDir) === 0) {
            $_SERVER['SCRIPT_NAME'] = $uri;
            require $file;
            return true;
        }
    } else {
        // Use realpath for security check
        if (strpos($resolvedFile, $resolvedApiDir) === 0 && file_exists($resolvedFile) && is_file($resolvedFile)) {
            $_SERVER['SCRIPT_NAME'] = $uri;
            require $resolvedFile;
            return true;
        }
    }
    
    // API file not found - return 404
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'API endpoint not found: ' . $uri,
        'debug' => [
            'requested_path' => $uri,
            'api_path' => $apiPath,
            'resolved_file' => $resolvedFile !== false ? $resolvedFile : $file,
            'file_exists' => file_exists($file),
            'is_file' => is_file($file)
        ]
    ]);
    return true;
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
