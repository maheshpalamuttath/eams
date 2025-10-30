<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

include 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = $_POST['event_id'];
    $cardnumber = trim($_POST['cardnumber']);

    // Get event name
    $stmt_event = $conn->prepare("SELECT event_name FROM events WHERE id = ?");
    $stmt_event->bind_param("i", $event_id);
    $stmt_event->execute();
    $stmt_event->bind_result($event_name);
    $stmt_event->fetch();
    $stmt_event->close();

    // Get student name from Koha
    $stmt_koha = $koha_conn->prepare("
        SELECT CONCAT(COALESCE(title, ''), ' ', COALESCE(firstname, ''), ' ', COALESCE(surname, '')) AS name
        FROM borrowers
        WHERE cardnumber = ?
    ");
    $stmt_koha->bind_param("s", $cardnumber);
    $stmt_koha->execute();
    $stmt_koha->bind_result($student_name);
    $found = $stmt_koha->fetch();
    $stmt_koha->close();

    if (!$found) {
        // Card not found in Koha borrowers table
        $error_message = "You are not a registered user, please consult the librarian.";
    } else {
        $display_name = trim($student_name) !== '' ? $student_name : $cardnumber;

        // Check if attendance already recorded
        $stmt = $conn->prepare("SELECT id FROM attendance WHERE event_id = ? AND cardnumber = ?");
        $stmt->bind_param("is", $event_id, $cardnumber);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = "Attendance already recorded for <strong>" . htmlspecialchars($display_name) . "</strong>.";
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO attendance (event_id, cardnumber) VALUES (?, ?)");
            $stmt->bind_param("is", $event_id, $cardnumber);
            if ($stmt->execute()) {
                $success_message = "Attendance recorded successfully for <strong>" . htmlspecialchars($display_name) . "</strong>.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

// Show today's events (latest first, even if multiple)
$events_result = $conn->query("
    SELECT id, event_name 
    FROM events 
    WHERE DATE(event_date) = CURDATE() 
    ORDER BY event_date DESC, id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Record Attendance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.footer_lnk { color: #0d6efd; text-decoration: none; }
.footer_lnk:hover { text-decoration: underline; }
.alert { transition: opacity 0.5s ease-out; }
</style>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between mb-3">
        <h2>Record Attendance</h2>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>

    <?php if (isset($success_message)) { ?>
        <div class="alert alert-success text-center"><?php echo $success_message; ?></div>
    <?php } elseif (isset($error_message)) { ?>
        <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
    <?php } ?>

    <form method="post" action="" class="mx-auto" style="max-width:400px;">
        <?php if ($events_result && $events_result->num_rows > 0) { ?>
            <div class="mb-3">
                <label for="event_id" class="form-label">Event</label>
                <select name="event_id" id="event_id" class="form-select" required>
                    <?php while ($row = $events_result->fetch_assoc()) { ?>
                        <option value="<?php echo htmlspecialchars($row['id']); ?>">
                            <?php echo htmlspecialchars($row['event_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        <?php } else { ?>
            <div class="alert alert-warning text-center">No events scheduled for today.</div>
        <?php } ?>

        <div class="mb-3">
            <label for="cardnumber" class="form-label">Register Number</label>
            <input type="text" name="cardnumber" class="form-control" id="cardnumber" required autofocus style="text-transform: uppercase;">
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">Record Attendance</button>
        <a href="generate_pdf.php" class="btn btn-warning w-100 mb-3">Generate PDF</a>
        <a href="index.php" class="btn btn-secondary w-100">Home</a>

        <footer class="text-center mt-4">
            &copy; <script>document.write(new Date().getFullYear())</script>
            <a href="#/" class="footer_lnk">Event Attendance Management System</a><br>
            Developed by <a href="https://maheshpalamuttath.info/" target="_blank" class="footer_lnk" rel="noopener">Mahesh Palamuttath</a>
        </footer>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Convert to uppercase while typing
    document.getElementById('cardnumber').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Hide alerts after 3 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 3000);

    // Focus input again after submission
    const cardInput = document.getElementById('cardnumber');
    if (cardInput) {
        cardInput.focus();
        <?php if (isset($success_message)) { ?> cardInput.value = ""; <?php } ?>
    }
});
</script>
</body>
</html>

<?php
$conn->close();
$koha_conn->close();
?>

