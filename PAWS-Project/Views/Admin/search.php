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

$alertType = '';
$alertMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $license_no = $_POST["licno"];

  // Find the violator with the given license number
  $stmt = $conn->prepare("SELECT * FROM violators WHERE license_no = ?");
  $stmt->bind_param("s", $license_no);
  $stmt->execute();
  $result = $stmt->get_result();
  $violator = $result->fetch_assoc();
  
  if ($violator) {
    // The violator exists, so fetch their offenses
    $violator_id = $violator["id"];
  
    $stmt = $conn->prepare("SELECT v.violation, v.fine_amount, v.violation_date, v.encoded_by FROM violations v LEFT JOIN paid_violations pv ON v.id = pv.violation_id WHERE v.violator_id = ? AND pv.violation_id IS NULL");
    $stmt->bind_param("i", $violator_id);
    $stmt->execute();
    $offenses = $stmt->get_result();
  
    if ($offenses->num_rows == 0) {
      // The violator has no offenses, so set the alert type and message
      $alertType = 'success';
      $alertMessage = 'Driver has no active violations.';
    } else {
      // The violator has offenses, so set the alert type and message
      $alertType = 'warning';
      $alertMessage = 'Driver has active violations.';
    }
  } else {
    $alertType = 'danger';
    $alertMessage = 'No driver found with the given license number.';
  }
  
  $stmt->close();
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
        <a href="search.php" class="active">
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
        <a href="changepass.php">
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
        <span class="dashboard">Driver Search</span>
      </div>
      <span class="dashboard text-right">Welcome, <?php echo $_SESSION["username"]; ?></span>
    </nav> 
    <br> <br>

<!---------------------- Violation ---------------------->   
<div class="container pt-5">
  <div class="card rounded-card">
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
          <label for="licno">License Number</label>
          <input type="text" class="form-control" id="licno" name="licno" placeholder="Enter the driver's license number" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Submit</button>
      </form>
    </div>
  </div>
</div>

<?php if (isset($offenses)): ?>
  <div class="container pt-5">
  <div class="card">
    <div class="card-body">
    <h2 class="card-title">Violations for <u><?php echo $violator['first_name'] . ' ' . $violator['last_name']; ?></u>:</h2>
      <table class="table">
        <thead>
          <tr>
            <th scope="col">Violation</th>
            <th scope="col">Fine Amount</th>
            <th scope="col">Violation Date</th>
            <th scope="col">Encoded By</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($offense = $offenses->fetch_assoc()): ?>
            <tr>
              <td><?php echo $offense['violation']; ?></td>
              <td><?php echo $offense['fine_amount']; ?></td>
              <td><?php echo $offense['violation_date']; ?></td>
              <td><?php echo $offense['encoded_by']; ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
  <br> <br>
<?php endif; ?>

<?php
// Fetch the past violations
$stmt = $conn->prepare("SELECT v.violation, v.fine_amount, v.violation_date, v.encoded_by FROM violations v INNER JOIN paid_violations pv ON v.id = pv.violation_id WHERE v.violator_id = ?");
$stmt->bind_param("i", $violator_id);
$stmt->execute();
$past_violations = $stmt->get_result();

if ($past_violations->num_rows > 0): ?>
    <div class="card">
        <div class="card-body">
            <h2 class="card-title">Past Violations of <u><?php echo $violator['first_name'] . ' ' . $violator['last_name']; ?></u>:</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Violation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($past_violation = $past_violations->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $past_violation['violation']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <br>
<?php endif; ?>


  </section>
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
