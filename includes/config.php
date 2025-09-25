<?php
session_start();
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change if different
define('DB_PASS', ''); // Change if you have a password
define('DB_NAME', 'gymbros');
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_TIMES', [15, 30, 60]);

class Database
{
  private $connection;

  public function __construct()
  {
    $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($this->connection->connect_error) {
      error_log("Database connection failed: " . $this->connection->connect_error);
      die("Database connection failed. Please check your configuration.");
    }

    // Set charset to UTF-8
    $this->connection->set_charset("utf8mb4");
  }

  public function getConnection()
  {
    return $this->connection;
  }

  public function sanitize($data)
  {
    if (empty($data))
      return $data;
    return $this->connection->real_escape_string(htmlspecialchars(trim($data)));
  }

  public function close()
  {
    if ($this->connection) {
      $this->connection->close();
    }
  }
}
?>