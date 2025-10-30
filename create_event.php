<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';
date_default_timezone_set('Asia/Kolkata');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = strtoupper($_POST['event_name']);
    $event_date = $_POST['event_date'];
    $current_time = date("H:i:s");
    $event_date_time = $event_date . ' ' . $current_time;

    $stmt = $conn->prepare("INSERT INTO events (event_name, event_date) VALUES (?, ?)");
    $stmt->bind_param("ss", $event_name, $event_date_time);

    if ($stmt->execute()) {
        $success_message = "New event created successfully.";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Event</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script>
function convertToUppercase(event){ event.target.value = event.target.value.toUpperCase(); }
</script>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between mb-3">
        <h2>Create Event</h2>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
    <?php if (isset($success_message)) echo "<div class='alert alert-success text-center'>$success_message</div>"; ?>
    <?php if (isset($error_message)) echo "<div class='alert alert-danger text-center'>$error_message</div>"; ?>

    <form method="post" style="max-width:400px;margin:auto;">
        <div class="mb-3">
            <label class="form-label">Event Name</label>
            <input type="text" name="event_name" class="form-control" oninput="convertToUppercase(event)" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Event Date</label>
            <input type="date" name="event_date" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 mb-3">Create Event</button>
        <a href="index.php" class="btn btn-secondary w-100">Home</a>
    </form>
</div>
</body>
</html>

