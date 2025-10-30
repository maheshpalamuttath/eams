<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require('fpdf/fpdf.php');
include 'config.php';

// Enhanced FPDF class
class PDF extends FPDF {
    function Row($data, $widths, $aligns, $max_font = 10, $min_font = 6) {
        // Find smallest font size that fits all cells in the row
        $row_font = $max_font;
        for ($i = 0; $i < count($data); $i++) {
            $needed = $this->fitText($data[$i], $widths[$i] - 2, $max_font, $min_font);
            if ($needed < $row_font) {
                $row_font = $needed;
            }
        }

        // Determine row height based on wrapped text
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $this->SetFont('Arial', '', $row_font);
            $nb = max($nb, $this->NbLines($widths[$i], $data[$i]));
        }
        $h = 6 * $nb;

        $this->CheckPageBreak($h);

        // Draw cells with same font size
        for ($i = 0; $i < count($data); $i++) {
            $w = $widths[$i];
            $a = isset($aligns[$i]) ? $aligns[$i] : 'L';
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect($x, $y, $w, $h);
            $this->SetFont('Arial', '', $row_font);
            $this->MultiCell($w, 6, $data[$i], 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h) {
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    function fitText($text, $width, $max_font, $min_font) {
        for ($fs = $max_font; $fs >= $min_font; $fs--) {
            $this->SetFont('Arial', '', $fs);
            if ($this->GetStringWidth($text) <= $width)
                return $fs;
        }
        return $min_font;
    }
}

if (isset($_GET['event_id']) && is_numeric($_GET['event_id'])) {
    $event_id = (int) $_GET['event_id'];

    $event_result = $conn->query("SELECT * FROM events WHERE id = $event_id");
    if (!$event_result) die("Query failed: " . $conn->error);

    if ($event_result->num_rows > 0) {
        $event = $event_result->fetch_assoc();
        $attendance_result = $conn->query("SELECT * FROM attendance WHERE event_id = $event_id");
        if (!$attendance_result) die("Query failed: " . $conn->error);

        $pdf = new PDF('P', 'mm', 'A4');
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        $total_width = $pdf->GetPageWidth() - 20;
        $col_ratio = [0.1, 0.2, 0.35, 0.35];
        foreach ($col_ratio as $r) {
            $col_widths[] = $total_width * $r;
        }

        // Header
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Event: ' . $event['event_name'], 0, 1, 'L');
        $pdf->Cell(0, 8, 'Date: ' . $event['event_date'], 0, 1, 'L');
        $pdf->Ln(5);

        // Table Header
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Row(['Sl. No.', 'ID Number', 'Name', 'Department'], $col_widths, ['C', 'C', 'C', 'C']);

        // Data Rows
        $sl_no = 1;
        while ($row = $attendance_result->fetch_assoc()) {
            $cardnumber = $row['cardnumber'];
            $borrower_result = $koha_conn->query("
                SELECT CONCAT(COALESCE(b.title, ''), ' ', COALESCE(b.firstname, ''), ' ', COALESCE(b.surname, '')) AS name,
                       b.cardnumber,
                       c.description AS department
                FROM borrowers b
                LEFT JOIN categories c ON b.categorycode = c.categorycode
                WHERE b.cardnumber = '$cardnumber'
            ");
            $borrower = $borrower_result ? $borrower_result->fetch_assoc() : null;
            $name = trim($borrower['name'] ?? 'Unknown');
            $dept = trim($borrower['department'] ?? 'N/A');

            $pdf->Row([$sl_no, $cardnumber, $name, $dept], $col_widths, ['C', 'C', 'L', 'L']);
            $sl_no++;
        }

        // Footer / Signature
        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, 'Signature of Librarian: _________________________', 0, 1, 'L');
        $pdf->Cell(0, 8, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'L');

        // Save + Download
        $safe_event_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $event['event_name']);
        $safe_event_date = preg_replace('/[^0-9\-]/', '_', $event['event_date']);
        $pdf_file = "report/attendance_{$safe_event_name}_{$safe_event_date}.pdf";

        $pdf->Output('F', $pdf_file);

        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=\"" . basename($pdf_file) . "\"");
        readfile($pdf_file);
        exit;
    } else {
        echo "<div class='alert alert-danger text-center'>No event found with the given ID.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Generate Attendance PDF</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between mb-3">
        <h2>Generate Attendance PDF</h2>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>

    <form method="get" action="" class="mx-auto" style="max-width:400px;">
        <div class="form-group mb-3">
            <label for="event_id" class="form-label">Select Today's Event</label>
            <select name="event_id" id="event_id" class="form-select" required>
                <?php
                $today = date('Y-m-d');
                $events_result = $conn->query("SELECT id, event_name FROM events WHERE DATE(event_date) = '$today' ORDER BY id DESC");
                if ($events_result && $events_result->num_rows > 0) {
                    while ($row = $events_result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>" . htmlspecialchars($row['event_name']) . "</option>";
                    }
                } else {
                    echo "<option disabled>No events scheduled for today</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100 mb-3">Generate PDF</button>
        <a href="report_list.php" class="btn btn-info w-100 mb-3">View All Reports</a>
        <a href="index.php" class="btn btn-secondary w-100">Home</a>
    </form>
</div>
</body>
</html>

