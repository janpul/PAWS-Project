<?php
// Include database connection
include("admconfig.php");

// Get accId from URL
$accId = isset($_GET['id']) ? $_GET['id'] : null;

// Delete user from database
if ($accId) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $accId);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to users.php
header("Location: users.php");
exit();
?>