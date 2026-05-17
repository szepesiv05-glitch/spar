<?php
require_once(__DIR__ . "/../includes/db.php");
require_once(__DIR__ . "/../includes/auth.php");

csakBejelentekzve();

$hiba = "";
$siker = "";
$date = $_GET['date'] ?? $_POST['date'] ?? date('Y-m-d');

// Mentés feldolgozása
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];
    $date = $_POST["date"];
?>