<?php
include('admconfig.php');

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST["username"]);
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $access_level = sanitize_input($_POST["access_level"]);

    // SQL query to insert user
    $stmt = $conn->prepare("INSERT INTO users (username, password, access_level) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashedPassword, $access_level);

    // Execute the query
    if ($stmt->execute()) {
        // User added successfully
        sleep(1);
        $_SESSION["alertType"] = "success";
        $_SESSION["alertMessage"] = "User <b>$username</b> added successfully.";
        header("Location: users.php");
    } else {
        // Error inserting user
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    // Redirect to an error page or handle the case where the form wasn't submitted properly
    $_SESSION["alertType"] = "danger";
    $_SESSION["alertMessage"] = "Invalid request. Please try again.";
    header("Location: 404.php");
    exit();
}
?>