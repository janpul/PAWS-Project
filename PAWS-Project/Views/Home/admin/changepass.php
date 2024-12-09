<?php
include('admconfig.php');

if (!isset($_SESSION["user_id"])) {
  $_SESSION["alertMessage"] = "You need to be logged in to access this page.";
  header("Location: login.php");
  exit();
}

$_SESSION['last_page'] = $_SERVER['REQUEST_URI'];
$_SESSION["username"] = $row["username"];
$userId = $_SESSION['user_id']; // Replace with the actual session variable for user ID

$stmt = $conn->prepare("SELECT access_level FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $userRole = $row['access_level'];
} else {
    echo "No user found";
}

// List of roles allowed to access this page
$allowedRoles = ['Admin', 'User'];

// Check if the user's role is in the allowed roles
if (!in_array($userRole, $allowedRoles)) {
    // Redirect or show an error message
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

// Fetch the current password of the user from the database
$stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION["username"]);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Verify the current password
if (password_verify($current_password, $user["password"])) {
    // The current password is correct, so check if the new password and the confirmation match
    if ($new_password == $confirm_password) {
        // The new password and the confirmation match, so update the password in the database
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $hashedPassword, $_SESSION["username"]);
        $stmt->execute();

        $alertType = 'success';
        $alertMessage = 'Password changed successfully.';
    } else {
        // The new password and the confirmation do not match
        $alertType = 'danger';
        $alertMessage = 'New password and confirmation do not match.';
    }
} else {
    // The current password is not correct
    $alertType = 'danger';
    $alertMessage = 'Current password is not correct.';
}

$stmt->close();
$conn->close();
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <title>Admin</title>
  <link rel="stylesheet" href="style.css" />
  <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.5.3/css/bootstrap.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body>

<!---------------------- Sidebar ---------------------->   

  <div class="sidebar">
    <div class="logo-details">
      <i class='bx bxs-traffic'></i>
      <span class="logo_name">SysEncoder</span>
    </div>
    <ul class="nav-links">
      <li>
        <a href="index.php">
        <i class='bx bx-receipt'></i>
        <span class="links_name">Encode</span>
        </a>
      </li>
      <li>
        <a href="search.php">
        <i class='bx bx-search'></i>
        <span class="links_name">Driver Search</span>
        </a>
      </li>
      <?php
      if($userRole == 'Admin') {
      echo '<li>
        <a href="users.php">
        <i class=\'bx bx-shield\'></i>
        <span class="links_name">User Management</span>
        </a>
      </li>';
      }
      ?>
      <li>
        <a href="#" class="active">
          <i class="bx bx-cog"></i>
          <span class="links_name">Change Password</span>
        </a>
      </li>
      <li class="log_out">
        <a href="logout.php">
          <i class="bx bx-log-out"></i>
          <span class="links_name">Log out</span>
        </a>
      </li>
    </ul>
  </div>
  <section class="home-section">
    <nav>
      <div class="sidebar-button">
        <i class="bx bx-menu sidebarBtn"></i>
        <span class="dashboard">Change Your Password</span>
      </div>
      <span class="dashboard text-right">Welcome, <?php echo $_SESSION["username"]; ?></span>
    </nav> 
    <br> <br>

    <div class="container pt-5">
    <div class="card">
        <div class="card-body">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <?php
            if (!empty($alertType) && !empty($alertMessage)) {
            echo '<div class="alert alert-' . $alertType . ' alert-dismissible fade show" role="alert" style="font-size: 18px;">
                    ' . $alertMessage . '
                  </div>';
            }
            ?>
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter Current Password">
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter New Password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm New Password">
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="correct">
                  <label class="form-check-label" for="correct">The information above is correct and true.</label>
                </div>
                <br>
                <button type="submit" class="btn btn-primary btn-block">Change Password</button>
            </form>
        </div>
    </div>
</div>

<script>
    let sidebar = document.querySelector(".sidebar");
    let sidebarBtn = document.querySelector(".sidebarBtn");
    sidebarBtn.onclick = function () {
      sidebar.classList.toggle("active");
      if (sidebar.classList.contains("active")) {
        sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
      } else sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
    };
  </script>
</body>
</html>