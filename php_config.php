<?php
/**
 * BatchBinder PHP Configuration
 * Database and application settings
 */

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Database Configuration
define('DB_URI', $_ENV['DB_URI'] ?? 'mongodb://localhost:27017/batchbinder');
define('DB_NAME', 'batchbinder');

// JWT Configuration  
define('SECRET_KEY', $_ENV['SECRET_KEY'] ?? 'your-secret-key-here');
define('JWT_EXPIRE_TIME', 3600); // 1 hour

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt']);

// Application Configuration
define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost');
define('DEBUG_MODE', $_ENV['DEBUG_MODE'] ?? false);

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// CORS Headers for API
function setCorsHeaders() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json');
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

// Create upload directory if it doesn't exist
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
?>