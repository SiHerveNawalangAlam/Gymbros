<?php
class Validation
{
  public static function validateName($name, $fieldName)
  {
    $errors = [];

    if (empty($name)) {
      return ["$fieldName is required"];
    }

    // Check for double spaces
    if (preg_match('/\s{2,}/', $name)) {
      $errors[] = "Double spaces are not allowed in $fieldName";
    }

    // Check for three consecutive identical letters (case-insensitive)
    if (preg_match('/([a-zA-Z])\1\1/i', $name)) {
      $errors[] = "Three consecutive identical letters are not allowed in $fieldName";
    }

    // Check if all capital letters
    if (preg_match('/^[A-Z\s]+$/', $name)) {
      $errors[] = "All capital letters are not allowed in $fieldName";
    }

    // Check first letter is capital
    if (!preg_match('/^[A-Z]/', $name)) {
      $errors[] = "First letter of $fieldName must be capital";
    }

    // Check rest of letters should be lowercase
    $words = explode(' ', $name);
    foreach ($words as $word) {
      if (!preg_match('/^[A-Z][a-z]*$/', $word)) {
        $errors[] = "Each word in $fieldName must start with capital letter followed by lowercase";
        break;
      }
    }

    // No special characters or numbers
    if (preg_match('/[^a-zA-Z\s]/', $name)) {
      $errors[] = "Special characters and numbers are not allowed in $fieldName";
    }

    return $errors;
  }

  public static function validatePasswordStrength($password)
  {
    $strength = 0;
    $feedback = [];

    if (strlen($password) >= 8)
      $strength++;
    else
      $feedback[] = "Password should be at least 8 characters";

    if (preg_match('/[A-Z]/', $password))
      $strength++;
    else
      $feedback[] = "Include at least one uppercase letter";

    if (preg_match('/[a-z]/', $password))
      $strength++;
    else
      $feedback[] = "Include at least one lowercase letter";

    if (preg_match('/[0-9]/', $password))
      $strength++;
    else
      $feedback[] = "Include at least one number";

    if (preg_match('/[^a-zA-Z0-9]/', $password))
      $strength++;
    else
      $feedback[] = "Include at least one special character";

    if ($strength >= 4)
      return ['strength' => 'strong', 'feedback' => []];
    if ($strength >= 3)
      return ['strength' => 'medium', 'feedback' => $feedback];
    return ['strength' => 'weak', 'feedback' => $feedback];
  }

  public static function validateAge($birthdate)
  {
    $today = new DateTime();
    $birthdate = new DateTime($birthdate);
    $age = $today->diff($birthdate)->y;

    if ($age < 18) {
      return ["Must be at least 18 years old"];
    }
    return [];
  }

  public static function validateIDFormat($id)
  {
    if (!preg_match('/^\d{4}-\d{4}$/', $id)) {
      return ["ID must be in format xxxx-xxxx (numbers only)"];
    }
    return [];
  }
}
?>