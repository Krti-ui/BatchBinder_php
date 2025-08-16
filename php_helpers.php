<?php
/**
 * BatchBinder Helper Functions
 * Utility functions for file handling and validation
 */

require_once __DIR__ . '/config.php';

class Helpers {
    
    /**
     * Handle file upload
     */
    public static function handleFileUpload($fileInput = 'file') {
        if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $file = $_FILES[$fileInput];
        
        // Validate file size
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('File size too large. Maximum size is ' . (MAX_FILE_SIZE / (1024 * 1024)) . 'MB');
        }
        
        // Validate file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            throw new Exception('File type not allowed. Allowed types: ' . implode(', ', ALLOWED_EXTENSIONS));
        }
        
        // Generate unique filename
        $filename = time() . '_' . uniqid() . '.' . $extension;
        $filepath = UPLOAD_DIR . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'filename' => $filename,
                'filepath' => $filepath,
                'originalname' => $file['name'],
                'size' => $file['size'],
                'extension' => $extension
            ];
        }
        
        throw new Exception('Failed to upload file');
    }
    
    /**
     * Validate content type
     */
    public static function validateContentType($contentType) {
        $validTypes = ['notes', 'exclusive', 'assignments', 'tests'];
        return in_array($contentType, $validTypes);
    }
    
    /**
     * Validate required fields for content
     */
    public static function validateContentData($data, $contentType) {
        $errors = [];
        
        if (!self::validateContentType($contentType)) {
            $errors[] = 'Invalid content type';
        }
        
        if ($contentType === 'exclusive') {
            // Validate exclusive content fields
            if (empty($data['title'])) $errors[] = 'Title is required';
            if (empty($data['description'])) $errors[] = 'Description is required';
            if (empty($data['price'])) $errors[] = 'Price is required';
            if (empty($data['quote'])) $errors[] = 'Quote is required';
        } else {
            // Validate regular content fields
            if (empty($data['department'])) $errors[] = 'Department is required';
            if (empty($data['semester'])) $errors[] = 'Semester is required';
            if (empty($data['subject'])) $errors[] = 'Subject is required';
            if (empty($data['topic'])) $errors[] = 'Topic is required';
            if (empty($data['professor'])) $errors[] = 'Professor is required';
        }
        
        return $errors;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Send JSON response
     */
    public static function sendJson($data, $status = 200) {
        http_response_code($status);
        setCorsHeaders();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Send error response
     */
    public static function sendError($message, $status = 400) {
        self::sendJson(['error' => $message], $status);
    }
    
    /**
     * Get request method
     */
    public static function getRequestMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Get request body for PUT/POST requests
     */
    public static function getRequestBody() {
        $input = file_get_contents('php://input');
        return json_decode($input, true);
    }
    
    /**
     * Get query parameters
     */
    public static function getQueryParams() {
        return $_GET;
    }
    
    /**
     * Get path parameter (for URLs like /api/content/123)
     */
    public static function getPathParam($index = 0) {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $parts = array_filter(explode('/', $path));
        $values = array_values($parts);
        
        return isset($values[$index]) ? $values[$index] : null;
    }
    
    /**
     * Get content ID from URL path
     */
    public static function getContentIdFromPath() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Match patterns like /api/content/123 or /api/download.php?id=123
        if (preg_match('/\/content\/([a-f0-9]{24})/', $path, $matches)) {
            return $matches[1];
        }
        
        // Fallback to query parameter
        return $_GET['id'] ?? null;
    }
    
    /**
     * Get file extension from MIME type
     */
    public static function getExtensionFromMimeType($mimeType) {
        $extensions = [
            'application/pdf' => '.pdf',
            'application/msword' => '.doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '.docx',
            'application/vnd.ms-powerpoint' => '.ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => '.pptx',
            'application/vnd.ms-excel' => '.xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '.xlsx',
            'text/plain' => '.txt'
        ];
        
        return $extensions[$mimeType] ?? '';
    }
    
    /**
     * Get MIME type from file extension
     */
    public static function getMimeTypeFromExtension($extension) {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain'
        ];
        
        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }
    
    /**
     * Log errors to file (if debug mode is on)
     */
    public static function logError($message, $context = []) {
        if (DEBUG_MODE) {
            $logMessage = date('Y-m-d H:i:s') . ' - ' . $message;
            if (!empty($context)) {
                $logMessage .= ' - Context: ' . json_encode($context);
            }
            error_log($logMessage . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
        }
    }
    
    /**
     * Create logs directory if it doesn't exist
     */
    public static function ensureLogsDirectory() {
        $logDir = __DIR__ . '/../logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
}

// Ensure logs directory exists
Helpers::ensureLogsDirectory();
?>