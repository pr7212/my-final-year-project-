<?php
/**
 * Database Configuration
 * 
 * Credentials are loaded from environment variables when available,
 * falling back to defaults for local development.
 * 
 * For production, set these environment variables:
 *   DB_HOST, DB_USER, DB_PASS, DB_NAME
 * 
 * Or create a .env file in the project root (already gitignored).
 */

// Load .env file if it exists (simple key=value parser)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
  $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue; // skip comments
    if (strpos($line, '=') === false) continue;
    list($key, $value) = array_map('trim', explode('=', $line, 2));
    if (!array_key_exists($key, $_ENV)) {
      putenv("$key=$value");
      $_ENV[$key] = $value;
    }
  }
}

$servername = getenv('DB_HOST') ?: 'localhost';
$username   = getenv('DB_USER') ?: 'root';
$password   = getenv('DB_PASS') ?: '';
$dbname     = getenv('DB_NAME') ?: 'garbage_tracker';

try {
  $conn = new mysqli($servername, $username, $password, $dbname);
} catch (mysqli_sql_exception $e) {
  error_log('DB Connection failed: ' . $e->getMessage());
  die('A database connection error occurred. Please try again later.');
}

if ($conn->connect_error) {
  error_log('DB Connection failed: ' . $conn->connect_error);
  die('A database connection error occurred. Please try again later.');
}

$conn->set_charset('utf8mb4');
