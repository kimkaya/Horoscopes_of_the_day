<?php
// Simple router for PHP built-in server

error_log("Router: " . $_SERVER['REQUEST_URI']);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

error_log("Looking for file: " . $file);
error_log("File exists: " . (file_exists($file) ? 'yes' : 'no'));
error_log("Is file: " . (is_file($file) ? 'yes' : 'no'));

// If requested file exists and is a file, serve it
if (file_exists($file) && is_file($file)) {
    error_log("Serving file: " . $file);
    return false; // Let PHP serve the file
}

// If it's a directory, check for index.html
if (is_dir($file)) {
    if (file_exists($file . '/index.html')) {
        error_log("Serving directory index: " . $file . '/index.html');
        return false;
    }
}

// Default to Fortune/index.html for root
if ($path === '/' || $path === '') {
    error_log("Serving root index");
    readfile(__DIR__ . '/Fortune/index.html');
    return true;
}

// 404
error_log("404: " . $path);
http_response_code(404);
echo "404 Not Found: {$path}";
return true;
