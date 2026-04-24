<?php
require_once(__DIR__ . "/../includes/db.php");
require_once(__DIR__ . "/../includes/auth.php");

csakBejelentekzve();

$userId = $_SESSION["user_id"];
$username = $_SESSION["username"];

$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$days_in_month = date('t', strtotime($month . "-01"));
$first_day_of_month = date('N', strtotime($month . "-01")); // 1 (hétfő) - 7 (vasárnap)

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beosztáskezelő</title>
</head>
<body>
    <h1>Elég a bűnözésből</h1>
</body>
</html>