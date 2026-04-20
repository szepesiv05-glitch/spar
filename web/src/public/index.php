<?php
require_once("db.php");
require_once(__DIR__ . "/../auth.php");

csakBejelentekzve();

$userId = $_SESSION["user_id"];
$username = $_SESSION["username"];

$lekerdezes = $conn->prepare(
    "SELECT id, task, category, priority, is_done, created_at 
    FROM tasks 
    WHERE user_id = ? ORDER BY is_done ASC, id DESC"
);

$lekerdezes->bind_param("i", $userId);
$lekerdezes->execute();

$result = $lekerdezes->get_result();
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