<?php
require_once 'includes/config.php';
require_once 'includes/security.php';
require_once 'includes/validation.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Verify CSRF token first
  if (!Security::verifyCSRFToken($_POST['csrf_token'])) {
    $errors[] = "Security token invalid. Please try again.";
  } else {
    $db = new Database();
    $conn = $db->getConnection();

    // Debug: Check if form data is received
    error_log("Form submitted: " . print_r($_POST, true));

    // Collect and sanitize all form data with proper null checks
    $id_number = $db->sanitize($_POST['id_number'] ?? '');
    $username = $db->sanitize($_POST['username'] ?? '');
    $first_name = $db->sanitize($_POST['first_name'] ?? '');
    $middle_name = $db->sanitize($_POST['middle_name'] ?? '');
    $last_name = $db->sanitize($_POST['last_name'] ?? '');
    $extension_name = $db->sanitize($_POST['extension_name'] ?? '');
    $birthdate = $db->sanitize($_POST['birthdate'] ?? '');
    $email = $db->sanitize($_POST['email'] ?? '');
    $sex = $db->sanitize($_POST['sex'] ?? '');
    $purok_street = $db->sanitize($_POST['purok_street'] ?? '');
    $barangay = $db->sanitize($_POST['barangay'] ?? '');
    $city_municipality = $db->sanitize($_POST['city_municipality'] ?? '');
    $province = $db->sanitize($_POST['province'] ?? '');
    $country = $db->sanitize($_POST['country'] ?? '');
    $zip_code = $db->sanitize($_POST['zip_code'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Security Questions
    $security_question1 = $db->sanitize($_POST['security_question1'] ?? '');
    $security_answer1 = $db->sanitize($_POST['security_answer1'] ?? '');
    $security_answer1_repeat = $db->sanitize($_POST['security_answer1_repeat'] ?? '');
    $security_question2 = $db->sanitize($_POST['security_question2'] ?? '');
    $security_answer2 = $db->sanitize($_POST['security_answer2'] ?? '');
    $security_answer2_repeat = $db->sanitize($_POST['security_answer2_repeat'] ?? '');
    $security_question3 = $db->sanitize($_POST['security_question3'] ?? '');
    $security_answer3 = $db->sanitize($_POST['security_answer3'] ?? '');
    $security_answer3_repeat = $db->sanitize($_POST['security_answer3_repeat'] ?? '');

    // Basic validation
    if (empty($id_number))
      $errors[] = "ID Number is required";
    if (empty($username))
      $errors[] = "Username is required";
    if (empty($first_name))
      $errors[] = "First Name is required";
    if (empty($last_name))
      $errors[] = "Last Name is required";
    if (empty($birthdate))
      $errors[] = "Birthdate is required";
    if (empty($email))
      $errors[] = "Email is required";
    if (empty($password))
      $errors[] = "Password is required";

    if (empty($errors)) {
      // Validate ID Number format
      $idErrors = Validation::validateIDFormat($id_number);
      $errors = array_merge($errors, $idErrors);

      // Check if ID already exists
      $stmt = $conn->prepare("SELECT id_number FROM users WHERE id_number = ?");
      if ($stmt) {
        $stmt->bind_param("s", $id_number);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
          $errors[] = "ID Number already exists in the database";
        }
        $stmt->close();
      } else {
        $errors[] = "Database error: " . $conn->error;
      }

      // Check if username exists
      $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
      if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
          $errors[] = "Username already exists in the database";
        }
        $stmt->close();
      }

      // Validate names
      $nameErrors = array_merge(
        Validation::validateName($first_name, 'First Name'),
        Validation::validateName($last_name, 'Last Name')
      );
      $errors = array_merge($errors, $nameErrors);

      // Validate middle name if provided
      if (!empty($middle_name)) {
        $middleErrors = Validation::validateName($middle_name, 'Middle Name');
        $errors = array_merge($errors, $middleErrors);
      }

      // Validate age
      $ageErrors = Validation::validateAge($birthdate);
      $errors = array_merge($errors, $ageErrors);

      // Validate password
      $passwordStrength = Validation::validatePasswordStrength($password);
      if ($passwordStrength['strength'] === 'weak') {
        $errors[] = "Password is too weak. " . implode(', ', $passwordStrength['feedback']);
      }

      if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
      }

      // Validate security answers match
      if ($security_answer1 !== $security_answer1_repeat) {
        $errors[] = "Security Answer 1 does not match";
      }
      if ($security_answer2 !== $security_answer2_repeat) {
        $errors[] = "Security Answer 2 does not match";
      }
      if ($security_answer3 !== $security_answer3_repeat) {
        $errors[] = "Security Answer 3 does not match";
      }
    }

    if (empty($errors)) {
      // Calculate age
      $birthdate_obj = new DateTime($birthdate);
      $today = new DateTime();
      $age = $today->diff($birthdate_obj)->y;

      // Hash password
      $password_hash = Security::hashPassword($password);

      // Insert user with error handling
      $stmt = $conn->prepare("INSERT INTO users (id_number, username, password_hash, first_name, middle_name, last_name, extension_name, birthdate, age, email, sex, purok_street, barangay, city_municipality, province, country, zip_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

      if ($stmt) {
        $stmt->bind_param(
          "ssssssssissssssss",
          $id_number,
          $username,
          $password_hash,
          $first_name,
          $middle_name,
          $last_name,
          $extension_name,
          $birthdate,
          $age,
          $email,
          $sex,
          $purok_street,
          $barangay,
          $city_municipality,
          $province,
          $country,
          $zip_code
        );

        if ($stmt->execute()) {
          // Hash security answers and insert
          $answer1_hash = Security::hashPassword($security_answer1);
          $answer2_hash = Security::hashPassword($security_answer2);
          $answer3_hash = Security::hashPassword($security_answer3);

          $stmt2 = $conn->prepare("INSERT INTO security_questions (user_id, question1, answer1_hash, question2, answer2_hash, question3, answer3_hash) VALUES (?, ?, ?, ?, ?, ?, ?)");

          if ($stmt2) {
            $stmt2->bind_param("sssssss", $id_number, $security_question1, $answer1_hash, $security_question2, $answer2_hash, $security_question3, $answer3_hash);

            if ($stmt2->execute()) {
              // AUTO-LOGIN: Set session and redirect to dashboard
              $_SESSION['user'] = [
                'id_number' => $id_number,
                'username' => $username,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email
              ];
              $_SESSION['login_time'] = time();

              $success = true;
              header("Location: dashboard.php");
              exit();
            } else {
              $errors[] = "Failed to save security questions: " . $stmt2->error;
            }
            $stmt2->close();
          } else {
            $errors[] = "Database error: " . $conn->error;
          }
        } else {
          $errors[] = "Registration failed: " . $stmt->error;
        }
        $stmt->close();
      } else {
        $errors[] = "Database error: " . $conn->error;
      }
    }

    $db->close();
  }
}

$csrf_token = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | GymBros</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Oswald:wght@500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="css/auth.css">
</head>

<body>
  <header>
    <div class="logo">
      <h1>Gym<span>Bros</span></h1>
    </div>
    <div class="navBar">
      <ul>
        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Log-in</a></li>
        <li><a href="register.php" class="active"><i class="fas fa-user-plus"></i> Registered</a></li>
      </ul>
    </div>
  </header>

  <div class="container">
    <div class="form-logo">
      <i class="fas fa-dumbbell"></i>
      <h1>Gym<span>Bros</span></h1>
    </div>

    <h2 class="form-title">Register New Account</h2>

    <?php if (!empty($errors)): ?>
      <div class="error-message">
        <?php foreach ($errors as $error): ?>
          <p><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form id="register-form" method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

      <!-- Personal Information Section -->
      <div class="form-section">
        <h3><i class="fas fa-user"></i> Personal Information</h3>

        <div class="row">
          <div class="col">
            <div class="form-group">
              <label class="form-label">ID Number <span class="required">*</span></label>
              <div class="input-with-icon">
                <i class="fas fa-id-card input-icon"></i>
                <input type="text" name="id_number" class="form-input" placeholder="xxxx-xxxx" pattern="\d{4}-\d{4}"
                  title="Format: xxxx-xxxx" required oninput="formatID(this)"
                  value="<?php echo isset($_POST['id_number']) ? htmlspecialchars($_POST['id_number']) : ''; ?>">
              </div>
            </div>
          </div>
          <div class="col">
            <div class="form-group">
              <label class="form-label">Username <span class="required">*</span></label>
              <div class="input-with-icon">
                <i class="fas fa-user input-icon"></i>
                <input type="text" name="username" class="form-input" placeholder="Choose a username"
                  pattern="[a-zA-Z0-9_]{3,20}" title="3-20 characters, letters, numbers, underscore only" required
                  onblur="checkUsername(this.value)"
                  value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                <div id="username-feedback"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col">
            <div class="form-group">
              <label class="form-label">First Name <span class="required">*</span></label>
              <div class="input-with-icon">
                <i class="fas fa-signature input-icon"></i>
                <input type="text" name="first_name" class="form-input" placeholder="Enter first name" required
                  onblur="validateName(this, 'First Name')"
                  value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
              </div>
            </div>
          </div>
          <div class="col">
            <div class="form-group">
              <label class="form-label">Middle Name <span class="optional">optional</span></label>
              <div class="input-with-icon">
                <i class="fas fa-signature input-icon"></i>
                <input type="text" name="middle_name" class="form-input" placeholder="Enter middle name"
                  onblur="validateName(this, 'Middle Name')"
                  value="<?php echo isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : ''; ?>">
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col">
            <div class="form-group">
              <label class="form-label">Last Name <span class="required">*</span></label>
              <div class="input-with-icon">
                <i class="fas fa-signature input-icon"></i>
                <input type="text" name="last_name" class="form-input" placeholder="Enter last name" required
                  onblur="validateName(this, 'Last Name')"
                  value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
              </div>
            </div>
          </div>
          <div class="col">
            <div class="form-group">
              <label class="form-label">Extension Name <span class="optional">optional</span></label>
              <div class="input-with-icon">
                <i class="fas fa-signature input-icon"></i>
                <input type="text" name="extension_name" class="form-input" placeholder="e.g. Jr., Sr., III"
                  value="<?php echo isset($_POST['extension_name']) ? htmlspecialchars($_POST['extension_name']) : ''; ?>">
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col">
            <div class="form-group">
              <label class="form-label">Birthdate <span class="required">*</span></label>
              <div class="input-with-icon">
                <i class="fas fa-calendar input-icon"></i>
                <input type="date" name="birthdate" class="form-input" id="birthdate" required onchange="calculateAge()"
                  value="<?php echo isset($_POST['birthdate']) ? htmlspecialchars($_POST['birthdate']) : ''; ?>">
              </div>
            </div>
          </div>
          <div class="col">
            <div class="form-group">
              <label class="form-label">Age <span class="required">*</span></label>
              <div class="input-with-icon">
                <i class="fas fa-birthday-cake input-icon"></i>
                <div class="age-display" id="age-display">Enter birthdate</div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col">
            <div class="form-group">
              <label class="form-label">Email Address <span class="required">*</span></label>
              <div class="input-with-icon">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" name="email" class="form-input" placeholder="Enter your email" required
                  value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
              </div>
            </div>
          </div>
          <div class="col">
            <div class="form-group">
              <label class="form-label">Sex <span class="required">*</span></label>
              <div class="input-with-icon">
                <i class="fas fa-venus-mars input-icon"></i>
                <select name="sex" class="form-input" required>
                  <option value="">Select sex</option>
                  <option value="male" <?php echo (isset($_POST['sex']) && $_POST['sex'] == 'male') ? 'selected' : ''; ?>>
                    Male</option>
                  <option value="female" <?php echo (isset($_POST['sex']) && $_POST['sex'] == 'female') ? 'selected' : ''; ?>>Female</option>
                  <option value="other" <?php echo (isset($_POST['sex']) && $_POST['sex'] == 'other') ? 'selected' : ''; ?>>Other</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Address Information Section -->
      <div class="form-section">
        <h3><i class="fas fa-map-marker-alt"></i> Address Information</h3>

        <div class="form-group">
          <label class="form-label">Purok/Street <span class="required">*</span></label>
          <div class="input-with-icon">
            <i class="fas fa-road input-icon"></i>
            <input type="text" name="purok_street" class="form-input" placeholder="Enter your street address" required
              value="<?php echo isset($_POST['purok_street']) ? htmlspecialchars($_POST['purok_street']) : ''; ?>">
          </div>
        </div>

        <div class="row">
          <div class="col">
            <div class="form-group">
              <label class="form-label">Barangay <span class="required">*</span></label>
              <div class="input-with-icon">
                <i class="fas fa-map-marked input-icon"></i>
                <input type="text" name="barangay" class="form-input" placeholder="Enter barangay" required
                  value="<?php echo isset($_POST['barangay']) ? htmlspecialchars($_POST['barangay']) : ''; ?>">
              </div>
            </div>
          </div>
          <div class="col">
            <div class="form-group">
              <label class="form-label">City/Municipality <span class="required">*</span></label>
              <div class="input-with-icon">
                <i class="fas fa-city input-icon"></i>
                <input type="text" name="city_municipality" class="form-input" placeholder="Enter city or municipality"
                  required
                  value="<?php echo isset($_POST['city_municipality']) ? htmlspecialchars($_POST['city_municipality']) : ''; ?>">
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col">
            <div class="form-group">
              <label class="form-label">Province <span class="required">*</span></label>
              <div class="input-with-icon">
                <i class="fas fa-globe input-icon"></i>
                <input type="text" name="province" class="form-input" placeholder="Enter province" required
                  value="<?php echo isset($_POST['province']) ? htmlspecialchars($_POST['province']) : ''; ?>">
              </div>
            </div>
          </div>
          <div class="col">
            <div class="form-group">
              <label class="form-label">Country <span class="required">*</span></label>
              <div class="input-with-icon">
                <i class="fas fa-globe-americas input-icon"></i>
                <input type="text" name="country" class="form-input" placeholder="Enter country" required
                  value="<?php echo isset($_POST['country']) ? htmlspecialchars($_POST['country']) : ''; ?>">
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Zip Code <span class="required">*</span></label>
          <div class="input-with-icon">
            <i class="fas fa-map-pin input-icon"></i>
            <input type="text" name="zip_code" class="form-input" placeholder="Enter zip code (numbers only)"
              pattern="\d{4,10}" title="4-10 digits only" required
              value="<?php echo isset($_POST['zip_code']) ? htmlspecialchars($_POST['zip_code']) : ''; ?>">
          </div>
        </div>
      </div>

      <!-- Password Section -->
      <div class="form-section">
        <h3><i class="fas fa-lock"></i> Password</h3>

        <div class="row">
          <div class="col">
            <div class="form-group">
              <label class="form-label">Password <span class="required">*</span></label>
              <div class="input-with-icon">
                <i class="fas fa-key input-icon"></i>
                <input type="password" name="password" id="password" class="form-input" placeholder="Create a password"
                  required>
                <span class="password-toggle" onclick="togglePassword('password')">
                  <i class="fas fa-eye" id="password-icon"></i>
                </span>
              </div>
              <div class="password-strength" id="password-strength"></div>
            </div>
          </div>
          <div class="col">
            <div class="form-group">
              <label class="form-label">Confirm Password <span class="required">*</span></label>
              <div class="input-with-icon">
                <i class="fas fa-key input-icon"></i>
                <input type="password" name="confirm_password" class="form-input" placeholder="Re-enter password"
                  required>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Security Questions Section -->
      <div class="form-section">
        <h3><i class="fas fa-shield-alt"></i> Security Questions</h3>

        <!-- Question 1 -->
        <div class="form-group">
          <label class="form-label">Security Question 1 <span class="required">*</span></label>
          <select name="security_question1" class="form-input" required>
            <option value="">Select a question</option>
            <option value="Who is your best friend in Elementary?" <?php echo (isset($_POST['security_question1']) && $_POST['security_question1'] == 'Who is your best friend in Elementary?') ? 'selected' : ''; ?>>Who is your
              best friend in Elementary?</option>
            <option value="What is the name of your favorite pet?" <?php echo (isset($_POST['security_question1']) && $_POST['security_question1'] == 'What is the name of your favorite pet?') ? 'selected' : ''; ?>>What is the
              name of your favorite pet?</option>
            <option value="Who is your favorite teacher in high school?" <?php echo (isset($_POST['security_question1']) && $_POST['security_question1'] == 'Who is your favorite teacher in high school?') ? 'selected' : ''; ?>>Who
              is your favorite teacher in high school?</option>
          </select>
        </div>
        <div class="row">
          <div class="col">
            <div class="form-group">
              <label class="form-label">Your Answer <span class="required">*</span></label>
              <input type="text" name="security_answer1" class="form-input" placeholder="Enter your answer" required
                value="<?php echo isset($_POST['security_answer1']) ? htmlspecialchars($_POST['security_answer1']) : ''; ?>">
            </div>
          </div>
          <div class="col">
            <div class="form-group">
              <label class="form-label">Re-enter Answer <span class="required">*</span></label>
              <input type="text" name="security_answer1_repeat" class="form-input" placeholder="Re-enter your answer"
                required
                value="<?php echo isset($_POST['security_answer1_repeat']) ? htmlspecialchars($_POST['security_answer1_repeat']) : ''; ?>">
            </div>
          </div>
        </div>

        <!-- Question 2 -->
        <div class="form-group">
          <label class="form-label">Security Question 2 <span class="required">*</span></label>
          <select name="security_question2" class="form-input" required>
            <option value="">Select a question</option>
            <option value="Who is your best friend in Elementary?" <?php echo (isset($_POST['security_question2']) && $_POST['security_question2'] == 'Who is your best friend in Elementary?') ? 'selected' : ''; ?>>Who is your
              best friend in Elementary?</option>
            <option value="What is the name of your favorite pet?" <?php echo (isset($_POST['security_question2']) && $_POST['security_question2'] == 'What is the name of your favorite pet?') ? 'selected' : ''; ?>>What is the
              name of your favorite pet?</option>
            <option value="Who is your favorite teacher in high school?" <?php echo (isset($_POST['security_question2']) && $_POST['security_question2'] == 'Who is your favorite teacher in high school?') ? 'selected' : ''; ?>>Who
              is your favorite teacher in high school?</option>
          </select>
        </div>
        <div class="row">
          <div class="col">
            <div class="form-group">
              <label class="form-label">Your Answer <span class="required">*</span></label>
              <input type="text" name="security_answer2" class="form-input" placeholder="Enter your answer" required
                value="<?php echo isset($_POST['security_answer2']) ? htmlspecialchars($_POST['security_answer2']) : ''; ?>">
            </div>
          </div>
          <div class="col">
            <div class="form-group">
              <label class="form-label">Re-enter Answer <span class="required">*</span></label>
              <input type="text" name="security_answer2_repeat" class="form-input" placeholder="Re-enter your answer"
                required
                value="<?php echo isset($_POST['security_answer2_repeat']) ? htmlspecialchars($_POST['security_answer2_repeat']) : ''; ?>">
            </div>
          </div>
        </div>

        <!-- Question 3 -->
        <div class="form-group">
          <label class="form-label">Security Question 3 <span class="required">*</span></label>
          <select name="security_question3" class="form-input" required>
            <option value="">Select a question</option>
            <option value="Who is your best friend in Elementary?" <?php echo (isset($_POST['security_question3']) && $_POST['security_question3'] == 'Who is your best friend in Elementary?') ? 'selected' : ''; ?>>Who is your
              best friend in Elementary?</option>
            <option value="What is the name of your favorite pet?" <?php echo (isset($_POST['security_question3']) && $_POST['security_question3'] == 'What is the name of your favorite pet?') ? 'selected' : ''; ?>>What is the
              name of your favorite pet?</option>
            <option value="Who is your favorite teacher in high school?" <?php echo (isset($_POST['security_question3']) && $_POST['security_question3'] == 'Who is your favorite teacher in high school?') ? 'selected' : ''; ?>>Who
              is your favorite teacher in high school?</option>
          </select>
        </div>
        <div class="row">
          <div class="col">
            <div class="form-group">
              <label class="form-label">Your Answer <span class="required">*</span></label>
              <input type="text" name="security_answer3" class="form-input" placeholder="Enter your answer" required
                value="<?php echo isset($_POST['security_answer3']) ? htmlspecialchars($_POST['security_answer3']) : ''; ?>">
            </div>
          </div>
          <div class="col">
            <div class="form-group">
              <label class="form-label">Re-enter Answer <span class="required">*</span></label>
              <input type="text" name="security_answer3_repeat" class="form-input" placeholder="Re-enter your answer"
                required
                value="<?php echo isset($_POST['security_answer3_repeat']) ? htmlspecialchars($_POST['security_answer3_repeat']) : ''; ?>">
            </div>
          </div>
        </div>
      </div>

      <button type="submit" class="btn">
        <i class="fas fa-user-plus"></i> Create Account
      </button>

      <div class="login-link">
        <p>Already have an account? <a href="login.php">Log-in here</a></p>
      </div>
    </form>
  </div>

  <script src="js/validation.js"></script>
  <script src="js/auth.js"></script>
</body>

</html>