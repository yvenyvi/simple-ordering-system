<?php
// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'delicious_eats');
define('DB_USER', 'root');
define('DB_PASS', 'password');
define('DB_CHARSET', 'utf8mb4');

/**
 * Database connection class using PDO
 */
class Database {
    private $host = DB_HOST;
    private $dbname = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    private $pdo;
    
    public function getConnection() {
        $this->pdo = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
            die();
        }
        
        return $this->pdo;
    }
    
    public function closeConnection() {
        $this->pdo = null;
    }
}

function getDBConnection() {
    $database = new Database();
    return $database->getConnection();
}

function testConnection() {
    try {
        $pdo = getDBConnection();
        echo "Database connection successful!";
        return true;
    } catch(Exception $e) {
        echo "Database connection failed: " . $e->getMessage();
        return false;
    }
}

// Uncomment the line below to test the connection
// testConnection();
?>
