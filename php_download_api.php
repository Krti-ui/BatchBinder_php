<?php
/**
 * BatchBinder Download API
 * Handles file downloads and increments download counter
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

// Set CORS headers
setCorsHeaders();

// Only accept GET requests
if (Helpers::getRequestMethod() !== 'GET') {
    Helpers::sendError('Method not allowed', 405);
}

try {
    // Get content ID from URL or query parameter
    $contentId = Helpers::getContentIdFromPath();
    
    if (!$contentId) {
        Helpers::sendError('Content ID is required');
    }
    
    $db = Database::getInstance();
    
    // Find the content
    $content = $db->findContentById($contentId);
    
    if (!$content) {
        Helpers::sendError('Content not found', 404);
    }
    
    // Check if file exists
    if (!isset($content->filePath) || !file_exists($content->filePath)) {
        Helpers::sendError('File not found', 404);
    }
    
    $filePath = $content->filePath;
    $fileName = basename($filePath);
    
    // Get file info
    $fileSize = filesize($filePath);
    $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeType = Helpers::getMimeTypeFromExtension($fileExtension);
    
    // Increment download counter
    $db->incrementDownloads($contentId);
    
    // Set headers for file download
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    
    // Clear any output buffering
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Output the file
    readfile($filePath);
    exit;
    
} catch (Exception $e) {
    Helpers::logError('Download API Error', [
        'contentId' => $contentId ?? null,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    Helpers::sendError('Server error', 500);
}
?>