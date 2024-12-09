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
// Hash the password before storing it in the database
$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

// Update the user in the database
$stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, access_level = ? WHERE id = ?");
$stmt->bind_param("sssi", $_POST["username"], $password_hash, $_POST["access_level"], $_POST["userId"]);
$stmt->execute();

$stmt->close();
$conn->close();

// Redirect to the user list page
$_SESSION["alertType"] = "success";
$_SESSION["alertMessage"] = "User has been edited successfully.";
header("Location: users.php");
exit();

?>