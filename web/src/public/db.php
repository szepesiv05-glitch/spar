<?php
$conn = new mysqli(
    hostname:"127.0.0.1", 
    username: "root",
    password: "root",
    database: "spar_belso_db"
);

if($conn->connect_error) {
    die("Kapcsolódási hiba");
}

$conn -> set_charset("utf8mb4");
?>