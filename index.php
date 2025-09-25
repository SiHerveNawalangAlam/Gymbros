<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$isLoggedIn = Auth::isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GymBros - Fitness & Bodybuilding</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap"
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
        <li><a href="index.php" class="active"><i class="fas fa-home"></i> Home</a></li>
        <?php if ($isLoggedIn): ?>
          <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
          <li><a href="change-password.php"><i class="fas fa-key"></i> Change Password</a></li>
          <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        <?php else: ?>
          <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
          <li><a href="register.php"><i class="fas fa-user-plus"></i> Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </header>

  <main>
    <section class="hero">
      <h2>Transform Your <span>Body</span>, Transform Your <span>Life</span></h2>
      <p>Join thousands of GymBros members achieving their fitness goals with world-class equipment, expert trainers,
        and a supportive community.</p>
      <?php if (!$isLoggedIn): ?>
        <a href="register.php" class="cta-button">Start Your Journey Today</a>
      <?php else: ?>
        <a href="dashboard.php" class="cta-button">Go to Dashboard</a>
      <?php endif; ?>
    </section>

    <section class="features">
      <div class="feature-card">
        <i class="fas fa-dumbbell"></i>
        <h3>Modern Equipment</h3>
        <p>Top-of-the-line fitness equipment from leading brands to help you reach your goals.</p>
      </div>

      <div class="feature-card">
        <i class="fas fa-user-friends"></i>
        <h3>Expert Trainers</h3>
        <p>Our certified trainers are dedicated to helping you perfect your form and maximize results.</p>
      </div>

      <div class="feature-card">
        <i class="fas fa-heartbeat"></i>
        <h3>Health First</h3>
        <p>We prioritize your health and safety with clean facilities and proper spacing.</p>
      </div>
    </section>
  </main>

  <footer>
    <div class="footer-content">
      <div class="social-icons">
        <a href="#"><i class="fab fa-facebook"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-youtube"></i></a>
      </div>
      <p class="copyright">© 2023 GymBros. All rights reserved.</p>
    </div>
  </footer>
</body>

</html>