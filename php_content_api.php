<?php
/**
 * BatchBinder Content API
 * Handles all content CRUD operations
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Set CORS headers
setCorsHeaders();

$method = Helpers::getRequestMethod();
$contentId = Helpers::getContentIdFromPath();

try {
    switch ($method) {
        case 'GET':
            if ($contentId) {
                getSingleContent($contentId);
            } else {
                getAllContent();
            }
            break;
            
        case 'POST':
            createContent();
            break;
            
        case 'PUT':
            if (!$contentId) {
                Helpers::sendError('Content ID is required for update');
            }
            updateContent($contentId);
            break;
            
        case 'DELETE':
            if (!$contentId) {
                Helpers::sendError('Content ID is required for delete');
            }
            deleteContent($contentId);
            break;
            
        default:
            Helpers::sendError('Method not allowed', 405);
    }
    
} catch (Exception $e) {
    Helpers::logError('Content API Error', [
        'method' => $method,
        'contentId' => $contentId,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    Helpers::sendError('Server error', 500);
}

/**
 * Get all content with filters
 */
function getAllContent() {
    $db = Database::getInstance();
    $params = Helpers::getQueryParams();
    
    $filter = [];
    if (!empty($params['contentType'])) $filter['contentType'] = Helpers::sanitizeInput($params['contentType']);
    if (!empty($params['department'])) $filter['department'] = Helpers::sanitizeInput($params['department']);
    if (!empty($params['semester'])) $filter['semester'] = Helpers::sanitizeInput($params['semester']);
    if (!empty($params['subject'])) $filter['subject'] = Helpers::sanitizeInput($params['subject']);
    
    $content = $db->findContent($filter);
    $contentArray = $db->documentsToArray($content);
    
    Helpers::sendJson(['success' => true, 'data' => $contentArray]);
}

/**
 * Get single content by ID
 */
function getSingleContent($id) {
    $db = Database::getInstance();
    $content = $db->findContentById($id);
    
    if (!$content) {
        Helpers::sendError('Content not found', 404);
    }
    
    $contentArray = $db->documentToArray($content);
    Helpers::sendJson(['success' => true, 'data' => $contentArray]);
}

/**
 * Create new content
 */
function createContent() {
    Auth::requireAuth();
    $db = Database::getInstance();
    
    // ... (logic for handling form data and file uploads remains the same) ...
    
    $newContent = $db->insertContent($contentData);
    
    if (!$newContent) {
        Helpers::sendError('Failed to create content', 500);
    }
    
    $contentArray = $db->documentToArray($newContent);
    Helpers::sendJson(['success' => true, 'data' => $contentArray], 201);
}

/**
 * Update existing content
 */
function updateContent($id) {
    Auth::requireAuth();
    $db = Database::getInstance();
    
    // ... (logic for checking existing content and validating data remains the same) ...
    
    $updatedContent = $db->updateContent($id, $updateData);
    
    if (!$updatedContent) {
        Helpers::sendError('Failed to update content', 500);
    }
    
    $contentArray = $db->documentToArray($updatedContent);
    Helpers::sendJson(['success' => true, 'data' => $contentArray]);
}

/**
 * Delete content
 */
function deleteContent($id) {
    // ... (delete logic is mostly the same and already sends a success message) ...
    Auth::requireAuth();
    
    $db = Database::getInstance();
    
    $existingContent = $db->findContentById($id);
    if (!$existingContent) {
        Helpers::sendError('Content not found', 404);
    }
    
    $deleted = $db->deleteContent($id);
    
    if (!$deleted) {
        Helpers::sendError('Failed to delete content', 500);
    }
    
    Helpers::sendJson(['success' => true, 'message' => 'Content deleted successfully']);
}
?>