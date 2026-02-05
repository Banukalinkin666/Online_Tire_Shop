<?php
/**
 * Router for PHP Built-in Server
 * Routes requests to appropriate directories
 */

// Parse URI and handle query strings properly
$parsedUri = parse_url($_SERVER['REQUEST_URI']);
$uri = $parsedUri['path'] ?? '/';

// Route API requests to /api directory
if (strpos($uri, '/api/') === 0 || $uri === '/api') {
    // Extract the path after /api/
    if ($uri === '/api') {
        $apiPath = '';
    } else {
        $apiPath = substr($uri, 5); // Remove '/api/' prefix (5 chars including trailing slash)
    }
    
    // Security: prevent directory traversal
    if (strpos($apiPath, '..') !== false) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid API path']);
        return true;
    }
    
    // Normalize: remove leading/trailing slashes and normalize separators
    $apiPath = trim($apiPath, '/\\');
    
    // Build file paths - try multiple variations for cross-platform compatibility
    $apiDir = __DIR__ . DIRECTORY_SEPARATOR . 'api';
    $apiDirAlt = __DIR__ . '/api';
    
    // Try with DIRECTORY_SEPARATOR first
    $file = $apiDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $apiPath);
    
    // Try with forward slashes (for Linux/Docker)
    $fileAlt = $apiDirAlt . '/' . str_replace('\\', '/', $apiPath);
    
    // Try each path
    $foundFile = null;
    foreach ([$file, $fileAlt] as $testFile) {
        if (file_exists($testFile) && is_file($testFile)) {
            // Security check: ensure file is within api directory
            $realFile = realpath($testFile);
            $realApiDir = realpath($apiDir) ?: realpath($apiDirAlt);
            
            if ($realFile && $realApiDir && strpos($realFile, $realApiDir) === 0) {
                $foundFile = $testFile;
                break;
            }
        }
    }
    
    if ($foundFile) {
        $_SERVER['SCRIPT_NAME'] = $uri;
        require $foundFile;
        return true;
    }
    
    // API file not found - return 404 with debug info
    $apiFiles = [];
    if (is_dir($apiDir)) {
        $apiFiles = array_slice(scandir($apiDir), 2);
    } elseif (is_dir($apiDirAlt)) {
        $apiFiles = array_slice(scandir($apiDirAlt), 2);
    }
    
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'API endpoint not found: ' . $uri,
        'errors' => [],
        'debug' => [
            'requested_uri' => $uri,
            'api_path' => $apiPath,
            'tested_file' => $file,
            'tested_file_alt' => $fileAlt,
            'file_exists' => file_exists($file),
            'file_alt_exists' => file_exists($fileAlt),
            'api_dir_exists' => is_dir($apiDir) || is_dir($apiDirAlt),
            'api_dir_path' => is_dir($apiDir) ? $apiDir : (is_dir($apiDirAlt) ? $apiDirAlt : 'NOT FOUND'),
            'api_dir_contents' => implode(', ', $apiFiles),
            'current_dir' => __DIR__
        ]
    ], JSON_PRETTY_PRINT);
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

// Route test-ai script
if ($uri === '/test-ai.php') {
    $file = __DIR__ . '/public/test-ai.php';
    if (file_exists($file)) {
        $_SERVER['SCRIPT_NAME'] = '/test-ai.php';
        require $file;
        return true;
    }
}

// Route add-vehicle-cache-table script
if ($uri === '/add-vehicle-cache-table.php') {
    $file = __DIR__ . '/public/add-vehicle-cache-table.php';
    if (file_exists($file)) {
        $_SERVER['SCRIPT_NAME'] = '/add-vehicle-cache-table.php';
        require $file;
        return true;
    }
}

// Route import-production-data script
if ($uri === '/import-production-data.php') {
    $file = __DIR__ . '/public/import-production-data.php';
    if (file_exists($file)) {
        $_SERVER['SCRIPT_NAME'] = '/import-production-data.php';
        require $file;
        return true;
    }
}

// Route populate-ymm-from-nhtsa script
if ($uri === '/populate-ymm-from-nhtsa.php') {
    $file = __DIR__ . '/public/populate-ymm-from-nhtsa.php';
    if (file_exists($file)) {
        $_SERVER['SCRIPT_NAME'] = '/populate-ymm-from-nhtsa.php';
        require $file;
        return true;
    }
}

// Route populate-2018-2025 script
if ($uri === '/populate-2018-2025.php') {
    $file = __DIR__ . '/public/populate-2018-2025.php';
    if (file_exists($file)) {
        $_SERVER['SCRIPT_NAME'] = '/populate-2018-2025.php';
        require $file;
        return true;
    }
}

// Route populate-2004-2017 script
if ($uri === '/populate-2004-2017.php') {
    $file = __DIR__ . '/public/populate-2004-2017.php';
    if (file_exists($file)) {
        $_SERVER['SCRIPT_NAME'] = '/populate-2004-2017.php';
        require $file;
        return true;
    }
}

// Route populate-2007-2010 script
if ($uri === '/populate-2007-2010.php') {
    $file = __DIR__ . '/public/populate-2007-2010.php';
    if (file_exists($file)) {
        $_SERVER['SCRIPT_NAME'] = '/populate-2007-2010.php';
        require $file;
        return true;
    }
}

// Route populate-2000-2003 script
if ($uri === '/populate-2000-2003.php') {
    $file = __DIR__ . '/public/populate-2000-2003.php';
    if (file_exists($file)) {
        $_SERVER['SCRIPT_NAME'] = '/populate-2000-2003.php';
        require $file;
        return true;
    }
}

// Route populate-1990-1999 script
if ($uri === '/populate-1990-1999.php') {
    $file = __DIR__ . '/public/populate-1990-1999.php';
    if (file_exists($file)) {
        $_SERVER['SCRIPT_NAME'] = '/populate-1990-1999.php';
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
