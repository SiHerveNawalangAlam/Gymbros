<?php
class Security
{
  public static function generateCSRFToken()
  {
    if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
  }

  public static function verifyCSRFToken($token)
  {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
  }

  public static function hashPassword($password)
  {
    return password_hash($password, PASSWORD_DEFAULT);
  }

  public static function verifyPassword($password, $hash)
  {
    return password_verify($password, $hash);
  }

  public static function checkLockout($username)
  {
    // Delegate to getLockoutTime so checkLockout returns true only when there
    // is an active lockout (remaining seconds > 0). This makes server and
    // client behavior consistent (the client receives the remaining seconds
    // and the server will only block while that remaining time is > 0).
    return self::getLockoutTime($username) > 0;
  }

  public static function getLockoutTime($username)
  {
    $db = new Database();
    $conn = $db->getConnection();
    // Get recent attempts within the assessment window and the most recent attempt time
    $stmt = $conn->prepare("SELECT success, UNIX_TIMESTAMP(attempt_time) as ts 
                           FROM login_attempts 
                           WHERE username = ? 
                           AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                           ORDER BY attempt_time DESC 
                           LIMIT 9");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $consecutiveFailed = 0;
    $mostRecentFailedTs = null;

    // Count consecutive failures starting from the most recent attempt
    while ($row = $result->fetch_assoc()) {
      if ($row['success'] == 0) {
        $consecutiveFailed++;
        if ($mostRecentFailedTs === null) {
          $mostRecentFailedTs = (int) $row['ts'];
        }
      } else {
        // Stop when a successful attempt is found — consecutive streak ended
        break;
      }
    }

    // Determine the lockout duration based on consecutive failures
    $lockoutDuration = 0;
    if ($consecutiveFailed >= 9) {
      $lockoutDuration = 60; // 60 seconds for 9+ consecutive failures
    } elseif ($consecutiveFailed >= 6) {
      $lockoutDuration = 30; // 30 seconds for 6-8 consecutive failures
    } elseif ($consecutiveFailed >= 3) {
      $lockoutDuration = 15; // 15 seconds for 3-5 consecutive failures
    }

    if ($lockoutDuration > 0 && $mostRecentFailedTs !== null) {
      $now = time();
      $elapsed = $now - $mostRecentFailedTs;
      $remaining = $lockoutDuration - $elapsed;
      return $remaining > 0 ? $remaining : 0;
    }

    return 0;
  }
}
?>