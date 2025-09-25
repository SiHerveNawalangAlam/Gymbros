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
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts 
                               WHERE username = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR) 
                               AND success = 0");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['attempts'] >= 3;
  }

  public static function getLockoutTime($username)
  {
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT COUNT(*) as attempt_group FROM (
                               SELECT FLOOR((ROW_NUMBER() OVER (ORDER BY attempt_time) - 1) / 3) as grp
                               FROM login_attempts 
                               WHERE username = ? AND success = 0 
                               AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                               ) t GROUP BY grp ORDER BY grp DESC LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $group = $row['attempt_group'];
      $lockout_times = [15, 30, 60];
      return $lockout_times[min($group, count($lockout_times) - 1)];
    }
    return 0;
  }
}
?>