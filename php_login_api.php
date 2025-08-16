<?php
/**
 * BatchBinder Admin Login API
 * Handles admin authentication and JWT token generation
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Set CORS headers
setCorsHeaders();

// Only accept POST requests
if (Helpers::getRequestMethod() !== 'POST') {
    Helpers::sendError('Method not allowed', 405);
}

try {
    // Get request data
    $requestData = Helpers::getRequestBody();
    
    if (!$requestData) {
        Helpers::sendError('Invalid request data');
    }
    
    $email = Helpers::sanitizeInput($requestData['email'] ?? '');
    // CORRECTED: Do not sanitize the password. Hashing functions are designed to handle raw input.
    $password = $requestData['password'] ?? ''; 
    
    // Validate input
    if (empty($email) || empty($password)) {
        Helpers::sendError('Email and password are required');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Helpers::sendError('Invalid email format');
    }
    
    // Validate credentials
    $admin = Auth::validateCredentials($email, $password);
    
    if (!$admin) {
        // Use a generic message to prevent email enumeration
        Helpers::sendError('Invalid email or password', 401);
    }
    
    // Generate JWT token
    $token = Auth::generateToken($email);
    
    // Update last login time
    $db = Database::getInstance();
    $db->updateAdminLogin($email);
    
    // Send success response
    // CORRECTED: Added 'success: true' to the response for consistency with the frontend expectations.
    Helpers::sendJson([
        'success' => true,
        'token' => $token,
        'message' => 'Login successful'
    ]);
    
} catch (Exception $e) {
    Helpers::logError('Login API Error', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    Helpers::sendError('Server error', 500);
}
?>