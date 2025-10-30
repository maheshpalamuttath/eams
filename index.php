<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>EAMS Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 text-center">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Event Attendance Management System</h2>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>

    <div class="card p-4 shadow mx-auto" style="max-width:400px;">
        <a href="create_event.php" class="btn btn-primary w-100 mb-3">Create Event</a>
        <a href="take_attendance.php" class="btn btn-success w-100 mb-3">Record Attendance</a>
        <a href="generate_pdf.php" class="btn btn-warning w-100 mb-3">Generate PDF</a>
        <a href="report_list.php" class="btn btn-info w-100 mb-3">View All Reports</a>
    </div>
</div>
</body>
</html>

