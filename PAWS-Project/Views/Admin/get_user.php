<?php
include('admconfig.php');

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

if (isset($_SESSION["alertMessage"])) {
  $alertMessage = $_SESSION["alertMessage"];
  $alertType = $_SESSION["alertType"];

  // Unset the message so it's not displayed again
  unset($_SESSION["alertMessage"]);
  unset($_SESSION["alertType"]);
}

// List of roles allowed to access this page
$allowedRoles = ['Admin'];

// Check if the user's role is in the allowed roles
if (!in_array($userRole, $allowedRoles)) {
    // Redirect or show an error message
    header("Location: 404.php");
    exit();
}

// Fetch the user from the database
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();

// Return the user's information as JSON
header('Content-Type: application/json');
echo json_encode($user);

?>