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
$allowedRoles = ['admin', 'user'];

// Check if the user's role is in the allowed roles
if (!in_array($userRole, $allowedRoles)) {
    // Redirect or show an error message
    header("Location: login.php");
    exit();
}

$fines = array(
  "Seat Belts Use Act of 1999" => array("1000", "2000", "5000"),
  "Child Safety in Motor Vehicles Act" => array("1000", "2000", "5000"),
  "Mandatory Use of Motorcycle Helmet Act" => array("1500", "3000", "5000", "10000"),
  "Children's Safety on Motorcycle Act" => array("3000", "5000", "10000"),
  "Anti-Distracted Driving Act" => array("5000", "10000", "15000", "20000"),
  "Illegal Parking, Unattended" => array("2000"),
  "Reckless Driving" => array("1000", "1000", "2000"),
  "Dress Code for Motorcycle Rider and Passenger" => array("500", "750", "1000"),
  "Arrogance/Discourteous Conduct (Driver)" => array("500"),
  "Illegal Counter flow" => array("2000", "5000"),
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

function determineOffenseLevel($conn, $license_no, $violation) {
  // Get the count of previous violations for the given license number and violation type
  $stmt = $conn->prepare("SELECT COUNT(*) AS num_violations FROM violations 
                          INNER JOIN violators ON violations.violator_id = violators.id 
                          WHERE violators.license_no = ? AND violations.violation = ?");
  $stmt->bind_param("ss", $license_no, $violation);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $num_violations = $row['num_violations'];

  // Determine the offense level based on the number of previous violations
  if ($num_violations >= 3) {
      return '3';
  } elseif ($num_violations == 2) {
      return '2';
  } else {
      return '1';
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $license_no = $_POST["licno"];
  $first_name = $_POST["fn"];
  $last_name = $_POST["ln"];
  $violation = $_POST["violation"];

$offense_level = determineOffenseLevel($conn, $license_no, $violation);
$violation_fine = $fines[$violation][$offense_level - 1];

  // Find the ID of the violator with the given license number
  $stmt = $conn->prepare("SELECT id FROM violators WHERE license_no = ?");
  $stmt->bind_param("s", $license_no);
  $stmt->execute();
  $result = $stmt->get_result();
  $violator = $result->fetch_assoc();

  $date = date("Y-m-d");

  if ($violator) {
    // The violator exists, so insert the violation
    $violator_id = $violator["id"];

    $stmt = $conn->prepare("INSERT INTO violations (violator_id, violation, offense_level, fine_amount, violation_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $violator_id, $violation, $offense_level, $violation_fine, $date);
    $stmt->execute();
  } else {
    // The violator does not exist, so insert the violator and the violation
    $stmt = $conn->prepare("INSERT INTO violators (license_no, first_name, last_name) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $license_no, $first_name, $last_name);
    $stmt->execute();

    $violator_id = $conn->insert_id;

    $stmt = $conn->prepare("INSERT INTO violations (violator_id, violation, offense_level, fine_amount, violation_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $violator_id, $violation, $offense_level, $violation_fine, $date);
    $stmt->execute();
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
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
        <a href="users.php">
        <i class='bx bx-shield'></i>
        <span class="links_name">User Management</span>
        </a>
      </li>
      <li>
        <a href="#">
          <i class="bx bx-cog"></i>
          <span class="links_name">Setting</span>
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
                <div class="form-group">
                    <label for="license_no">License Number</label>
                    <input type="text" class="form-control" id="license_no" name="licno" placeholder="Enter License Number">
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fn">First Name</label>
                        <input type="text" class="form-control" id="fn" name="fn" placeholder="Enter First Name">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="ln">Last Name</label>
                        <input type="text" class="form-control" id="ln" name="ln" placeholder="Enter Last Name">
                    </div>
                </div>
                <div class="form-group">
                    <label for="violation">Violation</label>
                    <select class="form-control" id="violation" name="violation">
                        <option value="Seat Belts Use Act of 1999">Seat Belts Use Act of 1999</option>
                        <option value="Child Safety in Motor Vehicles Act">Child Safety in Motor Vehicles Act</option>
                        <option value="Mandatory Use of Motorcycle Helmet Act">Mandatory Use of Motorcycle Helmet Act</option>
                        <option value="Children's Safety on Motorcycle Act">Children's Safety on Motorcycle Act</option>
                        <option value="Anti-Distracted Driving Act">Anti-Distracted Driving Act</option>
                        <option value="Illegal Parking, Unattended">Illegal Parking, Unattended</option>
                        <option value="Reckless Driving">Reckless Driving</option>
                        <option value="Dress Code for Motorcycle Rider and Passenger">Dress Code for Motorcycle Rider and Passenger</option>
                        <option value="Arrogance/Discourteous Conduct (Driver)">Arrogance/Discourteous Conduct (Driver)</option>
                        <option value="Illegal Counter flow">Illegal Counter flow</option>
                        <option value="Disregarding Traffic Sign">Disregarding Traffic Sign</option>
                        <option value="Unified Vehicle Volume Reduction Program (Number Coding Scheme)">Unified Vehicle Volume Reduction Program (Number Coding Scheme)</option>
                        <option value="Truck Ban">Truck Ban</option>
                        <option value="Light Truck Ban">Light Truck Ban</option>
                        <option value="Tricycle Ban">Tricycle Ban</option>
                        <option value="Obstruction">Obstruction</option>
                        <option value="Overloading">Overloading</option>
                        <option value="Defective Motor Vehicle Accessories">Defective Motor Vehicle Accessories</option>
                        <option value="Unauthorized Modification">Unauthorized Modification</option>
                        <option value="Loading and Unloading in Prohibited Zones">Loading and Unloading in Prohibited Zones</option>
                        <option value="Over speeding">Over speeding</option>
                    </select>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="correct">
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
