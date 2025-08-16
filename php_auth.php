<?php
/**
 * BatchBinder Authentication
 * JWT token handling and admin verification
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

class Auth {
    
    // ... (generateToken, verifyToken, getBearerToken, authenticateAdmin functions remain the same) ...

    /**
     * Generate JWT token for admin
     */
    public static function generateToken($email) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $payload = json_encode([
            'email' => $email,
            'iat' => time(),
            'exp' => time() + JWT_EXPIRE_TIME
        ]);
        
        $headerEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $payloadEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, SECRET_KEY, true);
        $signatureEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }
    
    /**
     * Verify JWT token
     */
    public static function verifyToken($token) {
        if (!$token) return false;
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;
        
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
        
        // Verify signature
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, SECRET_KEY, true);
        $signatureCheck = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        if (!hash_equals($signatureEncoded, $signatureCheck)) {
            return false;
        }
        
        // Decode payload
        $payloadEncoded = str_replace(['-', '_'], ['+', '/'], $payloadEncoded);
        $payload = json_decode(base64_decode($payloadEncoded), true);
        
        if (!$payload) return false;
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    /**
     * Get token from Authorization header
     */
    public static function getBearerToken() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Authenticate admin request
     */
    public static function authenticateAdmin() {
        $token = self::getBearerToken();
        
        if (!$token) {
            self::sendUnauthorized('No token provided');
            return false;
        }
        
        $payload = self::verifyToken($token);
        
        if (!$payload) {
            self::sendUnauthorized('Invalid token');
            return false;
        }
        
        // Verify admin exists in database
        $db = Database::getInstance();
        $admin = $db->findAdmin($payload['email']);
        
        if (!$admin) {
            self::sendUnauthorized('Admin not found');
            return false;
        }
        
        return $admin;
    }
    
    /**
     * Validate admin credentials
     */
    public static function validateCredentials($email, $password) {
        if (!$email || !$password) {
            return false;
        }
        
        $db = Database::getInstance();
        $admin = $db->findAdmin($email);
        
        if (!$admin) {
            return false;
        }
        
        // CORRECTED: Replaced plaintext password comparison with secure password_verify.
        // Your admin creation script should use: password_hash($password, PASSWORD_DEFAULT);
        // This securely checks the provided password against the hash stored in the database.
        return password_verify($password, $admin->password) ? $admin : false;
    }
    
    /**
     * Send unauthorized response
     */
    public static function sendUnauthorized($message = 'Unauthorized') {
        http_response_code(401);
        echo json_encode(['error' => $message]);
        exit;
    }
    
    /**
     * Require admin authentication middleware
     */
    public static function requireAuth() {
        $admin = self::authenticateAdmin();
        if (!$admin) {
            exit; // authenticateAdmin already sent the response
        }
        return $admin;
    }
}

/**
 * Helper function to get all headers (for servers that don't support getallheaders)
 */
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
?>