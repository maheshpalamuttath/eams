<?php
// Database configuration
$servername = "localhost";
$username = "eams";
$password = "eams123"; // Replace this with your actual password
$dbname = "eams";
$koha_dbname = "koha_library";

// Create connection to event_management database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create connection to koha_library database
$koha_conn = new mysqli($servername, $username, $password, $koha_dbname);

// Check connection
if ($koha_conn->connect_error) {
    die("Connection failed: " . $koha_conn->connect_error);
}
?>

