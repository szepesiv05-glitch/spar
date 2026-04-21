<?php
require_once(__DIR__ . "/../includes/db.php");
require_once(__DIR__ . "/../includes/auth.php");

csakBejelentekzve();

$userId = $_SESSION["user_id"];
$username = $_SESSION["username"];

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