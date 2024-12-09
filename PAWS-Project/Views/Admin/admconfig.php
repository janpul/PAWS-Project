<?php
session_start();

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "sysencode";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize user name variable
$userName = "";
$email = "";

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Retrieve user's name from the database using the user_id stored in the session
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userName = $row['username'];
    }

    $stmt->close();
}

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Retrieve user's name from the database using the user_id stored in the session
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = $row['username'];
    }

    $stmt->close();
}
?>
