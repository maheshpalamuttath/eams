<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($admin_id, $hashed_password);
    $stmt->fetch();
    $stmt->close();

    if ($admin_id && hash('sha256', $password) === $hashed_password) {
        $_SESSION['admin_id'] = $admin_id;
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.footer_lnk {
    color: #0d6efd;
    text-decoration: none;
}
.footer_lnk:hover {
    text-decoration: underline;
}
footer {
    font-size: 0.85rem;
    color: #555;
}
</style>
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card p-4 shadow mx-auto" style="max-width:400px;">
    <h3 class="text-center mb-3">Login</h3>
    <?php if (isset($error)) echo "<div class='alert alert-danger text-center'>$error</div>"; ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100 mb-2">Login</button>
    </form>

    <!-- Footer inside card, below the button -->
    <footer class="text-center mt-3">
        &copy; <script>document.write(new Date().getFullYear())</script>
        <a href="#/" target="_blank" class="footer_lnk">Event Attendance Management System</a><br>
        Developed by <a href="https://maheshpalamuttath.info/" target="_blank" class="footer_lnk" rel="noopener">Mahesh Palamuttath</a>
    </footer>
  </div>
</div>

</body>
</html>

