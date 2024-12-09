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

$fines = array(
  "Seat Belts Use Act of 1999" => array("1000"),
  "Child Safety in Motor Vehicles Act" => array("1000"),
  "Mandatory Use of Motorcycle Helmet Act" => array("1500"),
  "Children Safety on Motorcycle Act" => array("3000"),
  "Anti-Distracted Driving Act" => array("5000"),
  "Illegal Parking, Unattended" => array("2000"),
  "Reckless Driving" => array("1000"),
  "Dress Code for Motorcycle Rider and Passenger" => array("500"),
  "Arrogance/Discourteous Conduct (Driver)" => array("500"),
  "Illegal Counter flow" => array("2000"),
  "Disregarding Traffic Sign" => array("1000"),
  "Unified Vehicle Volume Reduction Program (Number Coding Scheme)" => array("500"),
  "Truck Ban" => array("3000"),
  "Light Truck Ban" => array("2000"),
  "Tricycle Ban" => array("500"),
  "Obstruction" => array("1000"),
  "Overloading" => array("1000"),
  "Defective Motor Vehicle Accessories" => array("1000"),
  "Unauthorized Modification" => array("2000"),
  "Loading and Unloading in Prohibited Zones" => array("1000"),
  "Over speeding" => array("1000")
);

$alertType = '';
$alertMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $license_no = $_POST["licno"];
  $first_name = $_POST["fn"];
  $last_name = $_POST["ln"];
  $violation = $_POST["violation"];
  $violation_fine = $fines[$violation][0];
  $encoded_by = $_SESSION["username"];

  // Find the ID of the violator with the given license number, first name, and last name
  $stmt = $conn->prepare("SELECT id FROM violators WHERE license_no = ? AND first_name = ? AND last_name = ?");
  $stmt->bind_param("sss", $license_no, $first_name, $last_name);
  $stmt->execute();
  $result = $stmt->get_result();
  $violator = $result->fetch_assoc();

  $date = date("Y-m-d");

  if ($violator) {
    // The violator exists, so insert the violation
    $violator_id = $violator["id"];
  
    $stmt = $conn->prepare("SELECT COUNT(*) as offense_count FROM violations v LEFT JOIN paid_violations pv ON v.id = pv.violation_id WHERE v.violator_id = ? AND pv.violation_id IS NULL");
    $stmt->bind_param("i", $violator_id);
    $stmt->bind_param("i", $violator_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $offenses = $result->fetch_assoc();
    
    if ($offenses['offense_count'] > 0) {
      $alertType = 'danger';
      $alertMessage = 'Successfully encoded a violation. <b>WARN:</b> This driver has a current offense.</b>';
    } else {
      $alertType = 'success';
      $alertMessage = 'Successfully encoded a violation.';
    }
  
    if ($offenses['offense_count'] > 0) {
      $alertType = 'danger';
      $alertMessage = 'Successfully encoded a violation. <b>WARN:</b> This driver has a current offense.</b>';
    } else {
      $alertType = 'success';
      $alertMessage = 'Successfully encoded a violation.';
    }

    $stmt = $conn->prepare("INSERT INTO violations (violator_id, violation, fine_amount, violation_date, encoded_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $violator_id, $violation, $violation_fine, $date, $encoded_by);
    $stmt->execute();
  } else {
    // The violator does not exist or the first name and last name do not match
    // Check if the license number exists
    $stmt = $conn->prepare("SELECT id FROM violators WHERE license_no = ?");
    $stmt->bind_param("s", $license_no);
    $stmt->execute();
    $result = $stmt->get_result();
    $violator = $result->fetch_assoc();

    if ($violator) {
      // The license number exists but the first name and last name do not match, so show an error message
      $alertType = 'danger';
      $alertMessage = '<b>ERROR:</b> The license number exists in the database but doesn\'t match name given.';
    } else {
      // The violator does not exist, so insert the violator and the violation
      $stmt = $conn->prepare("INSERT INTO violators (license_no, first_name, last_name) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $license_no, $first_name, $last_name);
      $stmt->execute();
  
      $violator_id = $conn->insert_id;
  
      $stmt = $conn->prepare("INSERT INTO violations (violator_id, violation, fine_amount, violation_date, encoded_by) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("isiss", $violator_id, $violation, $violation_fine, $date, $encoded_by);
      $stmt->execute();
  
      $alertType = 'success';
      $alertMessage = 'Successfully encoded a violation.';
    }
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
        <a href="#" class="active">
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
        <span class="dashboard">Encode a Violation</span>
      </div>
      <span class="dashboard text-right">Welcome, <?php echo $_SESSION["username"]; ?></span>
    </nav> 
    <br> <br>

<!---------------------- Violation ---------------------->   
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
                    <label for="license_no">License Number</label>
                    <input type="number" class="form-control" id="license_no" name="licno" placeholder="Enter License Number" required>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fn">First Name</label>
                        <input type="text" class="form-control" id="fn" name="fn" placeholder="Enter First Name" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="ln">Last Name</label>
                        <input type="text" class="form-control" id="ln" name="ln" placeholder="Enter Last Name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="violation">Violation</label>
                    <select class="form-control" id="violation" name="violation" required>
                      <option value="">Select a violation</option>
                      <?php
                      foreach ($fines as $violation => $fine) {
                        echo "<option value='$violation'>$violation</option>";
                      }
                      ?>
                      </select>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="correct" required>
                  <label class="form-check-label" for="correct">The information above is correct and true.</label>
                </div>
                <br>
                <button type="submit" class="btn btn-primary btn-block">Submit</button>
            </form>
        </div>
    </div>
</div>

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
