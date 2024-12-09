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
        <a href="users.php" class="active">
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
        <span class="dashboard">User Management</span>
      </div>
      <span class="dashboard text-right">Welcome, <?php echo $_SESSION["username"]; ?></span>
    </nav> 
    <br> <br>

<!---------------------- Violation ---------------------->   
<div class="container pt-5">
  <div class="card rounded-card">
    <div class="card-body">
    <form method="get" action="users.php">
        <div class="input-group mb-3">
          <input type="text" class="form-control" placeholder="Search" name="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
          <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
          </div>
        </div>
      </form>
      <?php
      $limit = 5; // Number of users to show per page
      $page = isset($_GET['page']) ? $_GET['page'] : 1;
      $start = ($page - 1) * $limit;
      $search = isset($_GET['search']) ? $_GET['search'] : '';

      $stmt = $conn->prepare("SELECT id, username, access_level FROM users WHERE username LIKE ? OR access_level LIKE ? LIMIT ?, ?");
      $searchParam = '%' . $search . '%';
      $stmt->bind_param("ssii", $searchParam, $searchParam, $start, $limit);
      $stmt->execute();
      $result = $stmt->get_result();
      ?>
      <table class="table table-striped">
        <thead>
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Username</th>
            <th scope="col">Access Level</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . $row["id"] . "</td>";
                  echo "<td>" . $row["username"] . "</td>";
                  echo "<td>" . $row["access_level"] . "</td>"; 
                  echo "<td>";
                  echo '<li class="list-inline-item"><a href="#" onclick=\'editUser("' . $row["id"] . '")\' title="Edit"><i class="bx bx-pencil font-size-18"></i></a></li>';
                  echo '<li class="list-inline-item"><a href="#" data-toggle="modal" data-target="#deleteUserModal" data-id="' . $row["id"] . '" title="Delete" class="px-2 text-danger deleteUser"><i class="bx bx-trash font-size-18"></i></a></li>';
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='4'>No users found</td></tr>";
          }

          $stmt->close();
          ?>
        </tbody>
      </table>
      <?php
    // Get the total number of users
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    $totalUsers = $result->fetch_row()[0];
    $stmt->close();

    $totalPages = ceil($totalUsers / $limit);

    if ($totalPages > 1): ?>
      <div aria-label="Page navigation">
        <ul class="pagination">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
          <?php endfor; ?>
        </ul>
          </div>
    <?php endif; ?>
    </div>
  </div>

  <!-- Delete User Modal -->
  <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this user?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
      </div>
    </div>
  </div>

  <div class="card rounded-card mt-4">
    <div class="card-body">
    <div class="row align-items-center">
      <div class="col">
      <?php
if (isset($alertMessage)) {
  echo '<div class="alert alert-' . $alertType . ' alert-dismissible fade show" role="alert">
          ' . $alertMessage . '
          </button>
        </div>';
}
    ?>
        <h2 class="card-title" id="formTitle">Create New User</h2>
      </div>
      <div class="col-auto">
        <button type="button" class="btn btn-danger btn-sm" id="cancelButton" style="display: none;">Cancel</button>
      </div>
    </div>
      <form method="post" action="create_user.php" id="userForm">
        <input type="hidden" id="userId" name="userId">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
        </div>
        <div class="form-group">
          <label for="access_level">Access Level</label>
          <select class="form-control" id="access_level" name="access_level" required>
            <option value="" disabled selected>Select access level</option>
            <option value="Admin">Admin</option>
            <option value="User">User</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block" id="formButton">Create User</button>
      </form>
    </div>
  </div>
  <br> <br>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

<script>
function editUser(userId) {
  // Fetch the user's information
  fetch('get_user.php?id=' + userId)
    .then(response => response.json())
    .then(user => {
      // Fill the form with the user's information
      document.getElementById('userId').value = user.id;
      document.getElementById('username').value = user.username;
      document.getElementById('access_level').value = user.access_level;

      // Change the form title and action
      document.getElementById('formTitle').textContent = 'Editing User (' + user.username + ')';
      document.getElementById('userForm').action = 'edit_user.php';
      document.getElementById('formButton').textContent = 'Update User';
    });
}

var cancelButton = document.getElementById('cancelButton');

function resetForm() {
  // Clear the form
  document.getElementById('userId').value = '';
  document.getElementById('username').value = '';
  document.getElementById('password').value = '';
  document.getElementById('access_level').value = '';

  // Change the form title and action
  document.getElementById('formTitle').textContent = 'Create New User';
  document.getElementById('userForm').action = 'create_user.php';
  document.getElementById('formButton').textContent = 'Create User';

  // Hide the cancel button
  cancelButton.style.display = 'none';
}

cancelButton.addEventListener('click', resetForm);

function editUser(userId) {
  // Fetch the user's information
  fetch('get_user.php?id=' + userId)
    .then(response => response.json())
    .then(user => {
      // Fill the form with the user's information
      document.getElementById('userId').value = user.id;
      document.getElementById('username').value = user.username;
      document.getElementById('access_level').value = user.access_level;

      // Change the form title and action
      document.getElementById('formTitle').textContent = 'Editing User (' + user.username + ')';
      document.getElementById('userForm').action = 'edit_user.php';
      document.getElementById('formButton').textContent = 'Update User';

      // Show the cancel button
      cancelButton.style.display = 'block';
    });
}
</script>

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

  <script>
  $('.deleteUser').on('click', function() {
  var userId = $(this).data('id');
  $('#confirmDeleteBtn').data('id', userId);
});

$('#confirmDeleteBtn').on('click', function() {
  var userId = $(this).data('id');
  window.location.href = 'delete_user.php?id=' + userId;
});
</script>

</body>
</html>
