<?php

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

include('admconfig.php');

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
if (isset($_SESSION["alertMessage"])) {
    $alertType = "danger";
    $alertMessage = $_SESSION["alertMessage"];
    unset($_SESSION["alertMessage"]);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST["username"]);
    $password = sanitize_input($_POST["password"]);

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE BINARY username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row["password"])) {
            $_SESSION["user_id"] = $row["id"];
            // Redirect to the last visited page
            $redirectUrl = isset($_SESSION['last_page']) ? $_SESSION['last_page'] : 'index.php';
            
            header("Location: index.php");
            exit();
        } else {
            $alertType = "danger";
            $alertMessage = "Invalid username or password.";
        }
    } else {
        $alertType = "danger";
        $alertMessage = "Invalid username or password.";
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <title>Login</title>
  <link rel="stylesheet" href="style.css" />
  <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.5.3/css/bootstrap.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body class="d-flex align-items-center justify-content-center" style="height: 100vh;">
<style>
        .logo-details {
            text-align: center;
            font-size: 3em;
        }
    </style>
  <div class="container">
    <div class="logo-details text-center">
      <i class='bx bxs-traffic'></i>
      <span class="logo_name">SysEncoder</span>
    </div>

    <div class="row justify-content-center pt-3">
      <div class="col-md-6">
        <div class="card">
                    <div class="card-body">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <?php
                    if (!empty($alertType) && !empty($alertMessage)) {
                    echo '<div class="alert alert-' . $alertType . ' alert-dismissible fade show" role="alert" style="font-size: 12px;">
                            ' . $alertMessage . '
                          </div>';
                    }
                    ?>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username">
                            </div>
                            <div class="form-group pt-2">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password">
                            </div>
                            <button type="submit" class="btn btn-primary mt-2">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>