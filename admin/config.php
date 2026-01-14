<?php
// Database configuration
// IMPORTANT: Set these values in environment variables or a separate config file not tracked in git
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'sheet_homes');
define('DB_USER', getenv('DB_USER') ?: 'YOUR_DB_USERNAME');
define('DB_PASS', getenv('DB_PASS') ?: 'YOUR_DB_PASSWORD');

// Admin credentials
// IMPORTANT: Set these values in environment variables or a separate config file not tracked in git
define('ADMIN_USERNAME', getenv('ADMIN_USERNAME') ?: 'YOUR_ADMIN_USERNAME');
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'YOUR_ADMIN_PASSWORD');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// File upload configuration
define('UPLOAD_DIR', __DIR__ . '/../assets/img/properties/');
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Base URL
define('BASE_URL', '/');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please contact the administrator.");
        }
    }
    return $pdo;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /admin/login');
        exit;
    }
}

// Initialize database tables
function initDatabase() {
    $pdo = getDB();
    
    // Create properties table
    $pdo->exec("CREATE TABLE IF NOT EXISTS properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        subtitle VARCHAR(255) NOT NULL,
        description1 TEXT,
        description2 TEXT,
        description3 TEXT,
        description4 TEXT,
        image_path VARCHAR(500) NOT NULL,
        slides TEXT,
        property_id VARCHAR(100),
        location VARCHAR(255),
        property_type VARCHAR(100) DEFAULT 'House',
        status VARCHAR(50) NOT NULL,
        price VARCHAR(100),
        area VARCHAR(100),
        beds INT,
        baths INT,
        garages INT,
        featured BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_featured (featured)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

// Initialize database on first load
try {
    initDatabase();
} catch (PDOException $e) {
    error_log("Database initialization failed: " . $e->getMessage());
}
?>

