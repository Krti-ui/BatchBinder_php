<?php
/**
 * BatchBinder Database Connection
 * MongoDB connection and operations
 */

require_once __DIR__ . '/config.php';

class Database {
    private $client;
    private $database;
    private static $instance = null;
    
    private function __construct() {
        try {
            // Create MongoDB client
            $this->client = new MongoDB\Client(DB_URI);
            $this->database = $this->client->selectDatabase(DB_NAME);
            
            // Test connection
            $this->client->listDatabases();
        } catch (Exception $e) {
            error_log("MongoDB Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getDatabase() {
        return $this->database;
    }
    
    public function getCollection($name) {
        return $this->database->selectCollection($name);
    }
    
    // Content operations
    public function findContent($filter = [], $options = []) {
        $collection = $this->getCollection('contents');
        
        // Default sorting by creation date (newest first)
        if (!isset($options['sort'])) {
            $options['sort'] = ['createdAt' => -1];
        }
        
        return $collection->find($filter, $options)->toArray();
    }
    
    public function findContentById($id) {
        $collection = $this->getCollection('contents');
        
        try {
            $objectId = new MongoDB\BSON\ObjectId($id);
            return $collection->findOne(['_id' => $objectId]);
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function insertContent($data) {
        $collection = $this->getCollection('contents');
        
        // Add timestamps
        $data['createdAt'] = new MongoDB\BSON\UTCDateTime();
        $data['updatedAt'] = new MongoDB\BSON\UTCDateTime();
        $data['downloads'] = 0;
        
        $result = $collection->insertOne($data);
        
        if ($result->getInsertedCount() > 0) {
            // Return the inserted document
            return $this->findContentById($result->getInsertedId());
        }
        
        return null;
    }
    
    public function updateContent($id, $data) {
        $collection = $this->getCollection('contents');
        
        try {
            $objectId = new MongoDB\BSON\ObjectId($id);
            $data['updatedAt'] = new MongoDB\BSON\UTCDateTime();
            
            $result = $collection->updateOne(
                ['_id' => $objectId],
                ['$set' => $data]
            );
            
            if ($result->getModifiedCount() > 0) {
                return $this->findContentById($id);
            }
            
            return null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function deleteContent($id) {
        $collection = $this->getCollection('contents');
        
        try {
            $objectId = new MongoDB\BSON\ObjectId($id);
            
            // Get the document first (for file cleanup)
            $document = $this->findContentById($id);
            
            $result = $collection->deleteOne(['_id' => $objectId]);
            
            if ($result->getDeletedCount() > 0) {
                // Clean up associated file
                if ($document && isset($document->filePath) && file_exists($document->filePath)) {
                    unlink($document->filePath);
                }
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function incrementDownloads($id) {
        $collection = $this->getCollection('contents');
        
        try {
            $objectId = new MongoDB\BSON\ObjectId($id);
            
            $result = $collection->updateOne(
                ['_id' => $objectId],
                ['$inc' => ['downloads' => 1]]
            );
            
            return $result->getModifiedCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Admin operations
    public function findAdmin($email) {
        $collection = $this->getCollection('admins');
        return $collection->findOne(['email' => $email]);
    }
    
    public function updateAdminLogin($email) {
        $collection = $this->getCollection('admins');
        
        return $collection->updateOne(
            ['email' => $email],
            ['$set' => ['lastLogin' => new MongoDB\BSON\UTCDateTime()]]
        );
    }
    
    // Utility methods
    public function objectIdToString($objectId) {
        if ($objectId instanceof MongoDB\BSON\ObjectId) {
            return (string) $objectId;
        }
        return $objectId;
    }
    
    public function dateToString($date) {
        if ($date instanceof MongoDB\BSON\UTCDateTime) {
            return $date->toDateTime()->format('Y-m-d H:i:s');
        }
        return $date;
    }
    
    // Convert MongoDB document to array for JSON response
    public function documentToArray($document) {
        if (!$document) return null;
        
        $array = [];
        foreach ($document as $key => $value) {
            if ($key === '_id') {
                $array['_id'] = $this->objectIdToString($value);
            } elseif ($value instanceof MongoDB\BSON\UTCDateTime) {
                $array[$key] = $this->dateToString($value);
            } else {
                $array[$key] = $value;
            }
        }
        
        return $array;
    }
    
    // Convert array of MongoDB documents
    public function documentsToArray($documents) {
        $result = [];
        foreach ($documents as $document) {
            $result[] = $this->documentToArray($document);
        }
        return $result;
    }
}
?>