<?php
/**
 * One-time script to create initial admin users with hashed passwords.
 *
 * HOW TO USE:
 * 1. Fill in the $admins_to_create array with your real admin emails and passwords.
 * 2. Upload this file to your server.
 * 3. Run it ONCE.
 * 4. DELETE THIS FILE IMMEDIATELY AFTER USE.
 */

require_once __DIR__ . '/includes/database.php';

echo "<pre>"; // For clean browser output

// --- STEP 1: EDIT THIS LIST WITH YOUR ADMIN DETAILS ---
$admins_to_create = [
    'admin1@example.com' => 'PasswordForAdmin1!',
    'admin2@example.com' => 'PasswordForAdmin2!',
    'admin3@example.com' => 'PasswordForAdmin3!',
    'admin4@example.com' => 'PasswordForAdmin4!',
    'admin5@example.com' => 'PasswordForAdmin5!',
];
// ----------------------------------------------------

echo "Starting admin creation process...\n\n";

try {
    $db = Database::getInstance();
    $collection = $db->getCollection('admins');

    foreach ($admins_to_create as $email => $password) {
        // Check if the admin already exists
        $existingAdmin = $collection->findOne(['email' => $email]);

        if ($existingAdmin) {
            echo "SKIPPED: Admin with email '" . htmlspecialchars($email) . "' already exists.\n";
            continue;
        }

        // Hash the raw password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        if (!$hashed_password) {
            echo "ERROR: Failed to hash password for " . htmlspecialchars($email) . "\n";
            continue;
        }

        // Insert the new admin with the HASHED password
        $collection->insertOne([
            'email'     => $email,
            'password'  => $hashed_password,
            'createdAt' => new MongoDB\BSON\UTCDateTime(),
            'lastLogin' => null
        ]);

        echo "SUCCESS: Created admin for '" . htmlspecialchars($email) . "'.\n";
    }

    echo "\nAdmin creation process finished.\n";
    echo "===================================================================\n";
    echo "!!! IMPORTANT: DELETE THIS SCRIPT (create_admins.php) FROM YOUR SERVER NOW !!!\n";
    echo "===================================================================\n";

} catch (Exception $e) {
    echo "AN ERROR OCCURRED: " . $e->getMessage() . "\n";
    echo "Please check your database connection and try again.\n";
}

echo "</pre>";
?>