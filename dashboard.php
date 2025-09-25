<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect to login if not logged in
if (!Auth::isLoggedIn()) {
  header("Location: login.php");
  exit();
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | GymBros</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Oswald:wght@500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <header>
    <div class="logo">
      <h1>Gym<span>Bros</span></h1>
    </div>
    <div class="navBar">
      <ul>
        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="change-password.php"><i class="fas fa-key"></i> Change Password</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </div>
  </header>

  <main>
    <section class="dashboard">
      <div class="welcome-card">
        <h2>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h2>
        <p>You have successfully registered and logged in.</p>
        <p>Member ID: <?php echo htmlspecialchars($user['id_number']); ?></p>
      </div>

      <div class="dashboard-grid">
        <div class="dashboard-card">
          <i class="fas fa-user-circle"></i>
          <h3>Profile Information</h3>
          <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
          <p><strong>Status:</strong> Active Member</p>
        </div>

        <div class="dashboard-card">
          <i class="fas fa-dumbbell"></i>
          <h3>Gym Access</h3>
          <p><strong>Membership:</strong> Active</p>
          <p><strong>Join Date:</strong> <?php echo date('F j, Y'); ?></p>
          <p><strong>Access:</strong> 24/7 Gym Access</p>
        </div>

        <div class="dashboard-card">
          <i class="fas fa-cog"></i>
          <h3>Quick Actions</h3>
          <p><a href="change-password.php">Change Password</a></p>
          <p><a href="#">View Profile</a></p>
          <p><a href="#">Gym Schedule</a></p>
        </div>
      </div>
    </section>
  </main>

  <footer>
    <div class="footer-content">
      <p class="copyright">© 2023 GymBros. All rights reserved.</p>
    </div>
  </footer>

  <script>
    // Prevent back button after login
    history.pushState(null, null, location.href);
    window.onpopstate = function () {
      history.go(1);
    };
  </script>
</body>

</html>