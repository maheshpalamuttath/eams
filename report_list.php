<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$reportDir = __DIR__ . '/report';
$reports = array_diff(scandir($reportDir), ['.', '..']);

// Function to extract date from filename
function extractDateTime($filename) {
    // Match date formats like YYYY-MM-DD HH:MM:SS or YYYY-MM-DD_HH_MM_SS
    if (preg_match('/(\d{4}-\d{2}-\d{2}[ _]\d{2}[:_]\d{2}[:_]\d{2})/', $filename, $matches)) {
        $dateStr = str_replace(['_', ':'], [' ', ':'], $matches[1]);
        return strtotime($dateStr);
    }
    return 0; // if no date found
}

// Sort reports based on extracted date (latest first)
usort($reports, function($a, $b) use ($reportDir) {
    $dateA = extractDateTime($a);
    $dateB = extractDateTime($b);
    return $dateB - $dateA;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>All Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <div class="d-flex justify-content-between mb-3">
    <h2>Attendance Reports</h2>
    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
  </div>
  <a href="index.php" class="btn btn-secondary mb-3">Home</a>

  <table class="table table-bordered align-middle">
    <thead class="table-light">
      <tr>
        <th style="width:5%;">#</th>
        <th>Report File</th>
        <th style="width:25%;">Date in Filename</th>
        <th style="width:15%;">Download</th>
      </tr>
    </thead>
    <tbody>
    <?php
    if (empty($reports)) {
        echo "<tr><td colspan='4' class='text-center text-muted'>No reports found.</td></tr>";
    } else {
        $i = 1;
        foreach ($reports as $file) {
            $timestamp = extractDateTime($file);
            $dateDisplay = $timestamp ? date("Y-m-d H:i:s", $timestamp) : "<i>Not Found</i>";
            echo "<tr>
                    <td>{$i}</td>
                    <td>{$file}</td>
                    <td>{$dateDisplay}</td>
                    <td><a href='report/{$file}' class='btn btn-success btn-sm' download>Download</a></td>
                  </tr>";
            $i++;
        }
    }
    ?>
    </tbody>
  </table>
</div>
</body>
</html>

