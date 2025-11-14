<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Redirect to login if not logged in
if (!Auth::isLoggedIn()) {
  header("Location: login.php");
  exit();
}

$user = $_SESSION['user'];

// Calculate member since duration
$joinDate = new DateTime($user['created_at'] ?? date('Y-m-d H:i:s'));
$currentDate = new DateTime();
$membershipDuration = $currentDate->diff($joinDate);
$months = ($membershipDuration->y * 12) + $membershipDuration->m;
$memberSinceText = $months > 0 ? "$months month" . ($months > 1 ? 's' : '') : 'Less than a month';

// Mock data for dashboard (in a real app, this would come from database)
$workoutStats = [
  'completed' => 24,
  'calories' => 8450,
  'streak' => 7
];

$upcomingClasses = [
  ['name' => 'HIIT Training', 'time' => 'Tomorrow, 6:00 PM', 'trainer' => 'Mike Johnson'],
  ['name' => 'Yoga Flow', 'time' => 'Wednesday, 7:30 AM', 'trainer' => 'Sarah Williams'],
  ['name' => 'Strength Training', 'time' => 'Friday, 5:00 PM', 'trainer' => 'Carlos Rodriguez']
];

$achievements = [
  ['name' => 'First Workout', 'icon' => 'fas fa-dumbbell', 'earned' => true],
  ['name' => '5 Workouts', 'icon' => 'fas fa-fire', 'earned' => true],
  ['name' => 'Early Bird', 'icon' => 'fas fa-sun', 'earned' => false],
  ['name' => 'Week Warrior', 'icon' => 'fas fa-trophy', 'earned' => false]
];
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
  <link rel="stylesheet" href="../css/style.css">
</head>

<body>
  <!-- Loading Animation -->
  <div class="page-loader">
    <div class="loader">
      <div class="dumbbell">
        <div class="bar"></div>
        <div class="weight left"></div>
        <div class="weight right"></div>
      </div>
      <p>Loading Dashboard...</p>
    </div>
  </div>
  <!--<div class="heading">
    <h1>Gym System</h1>-->
  </div>
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

    <!-- Mobile Menu Button -->
    <div class="mobile-menu-btn">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </header>

  <main>
    <section class="dashboard">
      <!-- Welcome Section -->
      <div class="welcome-card">
        <div class="welcome-header">
          <div class="user-greeting">
            <h2>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
            <p>Great to see you again. Ready for your next workout?</p>
          </div>
          <div class="user-avatar">
            <i class="fas fa-user-circle"></i>
          </div>
        </div>
        <div class="welcome-stats">
          <div class="stat-item">
            <span class="stat-value"><?php echo htmlspecialchars($user['id_number']); ?></span>
            <span class="stat-label">Member ID</span>
          </div>
          <div class="stat-item">
            <span class="stat-value"><?php echo $memberSinceText; ?></span>
            <span class="stat-label">Member Since</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">Active</span>
            <span class="stat-label">Status</span>
          </div>
        </div>
      </div>

      <!-- Quick Stats Section -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-dumbbell"></i>
          </div>
          <div class="stat-content">
            <h3><?php echo $workoutStats['completed']; ?></h3>
            <p>Workouts Completed</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-fire"></i>
          </div>
          <div class="stat-content">
            <h3><?php echo number_format($workoutStats['calories']); ?></h3>
            <p>Calories Burned</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-calendar-check"></i>
          </div>
          <div class="stat-content">
            <h3><?php echo $workoutStats['streak']; ?> days</h3>
            <p>Current Streak</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-heartbeat"></i>
          </div>
          <div class="stat-content">
            <h3>24/7</h3>
            <p>Gym Access</p>
          </div>
        </div>
      </div>

      <!-- Main Dashboard Grid -->
      <div class="dashboard-grid">
        <!-- Profile Information Card -->
        <div class="dashboard-card profile-card">
          <div class="card-header">
            <i class="fas fa-user-circle"></i>
            <h3>Profile Information</h3>
          </div>
          <div class="card-content">
            <div class="info-item">
              <span class="info-label">Username:</span>
              <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Email:</span>
              <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Full Name:</span>
              <span
                class="info-value"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Membership:</span>
              <span class="info-value status-badge active">Active</span>
            </div>
          </div>
          <div class="card-footer">
            <a href="#" class="card-action">
              <i class="fas fa-edit"></i> Edit Profile
            </a>
          </div>
        </div>

        <!-- Upcoming Classes Card -->
        <div class="dashboard-card classes-card">
          <div class="card-header">
            <i class="fas fa-calendar-alt"></i>
            <h3>Upcoming Classes</h3>
          </div>
          <div class="card-content">
            <?php foreach ($upcomingClasses as $class): ?>
              <div class="class-item">
                <div class="class-info">
                  <h4><?php echo $class['name']; ?></h4>
                  <p><?php echo $class['time']; ?></p>
                  <span class="trainer">with <?php echo $class['trainer']; ?></span>
                </div>
                <button class="class-action">
                  <i class="fas fa-plus"></i> Join
                </button>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="card-footer">
            <a href="#" class="card-action">
              <i class="fas fa-search"></i> View All Classes
            </a>
          </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="dashboard-card actions-card">
          <div class="card-header">
            <i class="fas fa-bolt"></i>
            <h3>Quick Actions</h3>
          </div>
          <div class="card-content">
            <a href="change-password.php" class="action-btn">
              <i class="fas fa-key"></i>
              <span>Change Password</span>
            </a>
            <a href="#" class="action-btn">
              <i class="fas fa-user-edit"></i>
              <span>Update Profile</span>
            </a>
            <a href="#" class="action-btn">
              <i class="fas fa-calendar"></i>
              <span>Book a Class</span>
            </a>
            <a href="#" class="action-btn">
              <i class="fas fa-chart-line"></i>
              <span>Progress Tracking</span>
            </a>
            <a href="#" class="action-btn">
              <i class="fas fa-dumbbell"></i>
              <span>Workout Plans</span>
            </a>
            <a href="#" class="action-btn">
              <i class="fas fa-question-circle"></i>
              <span>Get Help</span>
            </a>
          </div>
        </div>

        <!-- Achievements Card -->
        <div class="dashboard-card achievements-card">
          <div class="card-header">
            <i class="fas fa-trophy"></i>
            <h3>Achievements</h3>
          </div>
          <div class="card-content">
            <div class="achievements-grid">
              <?php foreach ($achievements as $achievement): ?>
                <div class="achievement-item <?php echo $achievement['earned'] ? 'earned' : 'locked'; ?>">
                  <div class="achievement-icon">
                    <i class="<?php echo $achievement['icon']; ?>"></i>
                  </div>
                  <span class="achievement-name"><?php echo $achievement['name']; ?></span>
                  <?php if (!$achievement['earned']): ?>
                    <div class="achievement-lock">
                      <i class="fas fa-lock"></i>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="card-footer">
            <a href="#" class="card-action">
              <i class="fas fa-award"></i> View All Achievements
            </a>
          </div>
        </div>

        <!-- Recent Activity Card -->
        <div class="dashboard-card activity-card">
          <div class="card-header">
            <i class="fas fa-history"></i>
            <h3>Recent Activity</h3>
          </div>
          <div class="card-content">
            <div class="activity-item">
              <div class="activity-icon success">
                <i class="fas fa-check"></i>
              </div>
              <div class="activity-content">
                <p>Completed <strong>Upper Body Workout</strong></p>
                <span class="activity-time">2 hours ago</span>
              </div>
            </div>
            <div class="activity-item">
              <div class="activity-icon info">
                <i class="fas fa-calendar-plus"></i>
              </div>
              <div class="activity-content">
                <p>Booked <strong>Yoga Class</strong> for Wednesday</p>
                <span class="activity-time">Yesterday</span>
              </div>
            </div>
            <div class="activity-item">
              <div class="activity-icon warning">
                <i class="fas fa-dumbbell"></i>
              </div>
              <div class="activity-content">
                <p>Started new <strong>Strength Program</strong></p>
                <span class="activity-time">3 days ago</span>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <a href="#" class="card-action">
              <i class="fas fa-list"></i> View Full Activity Log
            </a>
          </div>
        </div>

        <!-- Gym Schedule Card -->
        <div class="dashboard-card schedule-card">
          <div class="card-header">
            <i class="fas fa-clock"></i>
            <h3>Today's Schedule</h3>
          </div>
          <div class="card-content">
            <div class="schedule-item">
              <div class="schedule-time">
                <span class="time">6:00 AM</span>
                <span class="duration">60 min</span>
              </div>
              <div class="schedule-details">
                <h4>HIIT Training</h4>
                <p>Main Studio</p>
              </div>
              <div class="schedule-status full">Full</div>
            </div>
            <div class="schedule-item">
              <div class="schedule-time">
                <span class="time">8:00 AM</span>
                <span class="duration">45 min</span>
              </div>
              <div class="schedule-details">
                <h4>Yoga Flow</h4>
                <p>Yoga Studio</p>
              </div>
              <div class="schedule-status available">Available</div>
            </div>
            <div class="schedule-item">
              <div class="schedule-time">
                <span class="time">5:00 PM</span>
                <span class="duration">90 min</span>
              </div>
              <div class="schedule-details">
                <h4>Strength Training</h4>
                <p>Weight Room</p>
              </div>
              <div class="schedule-status available">Available</div>
            </div>
          </div>
          <div class="card-footer">
            <a href="#" class="card-action">
              <i class="fas fa-calendar-week"></i> View Weekly Schedule
            </a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <div class="logo">
          <h1>Gym<span>Bros</span></h1>
        </div>
        <p>Your fitness journey starts here. Train hard, stay consistent.</p>
      </div>
      <div class="footer-section">
        <p class="copyright">© 2023 GymBros. All rights reserved.</p>
        <div class="footer-links">
          <a href="#">Privacy Policy</a>
          <a href="#">Terms of Service</a>
          <a href="#">Contact Support</a>
        </div>
      </div>
    </div>
  </footer>

  <script src="../js/loader.js"></script>
</body>

</html>