<?php
$conn = new mysqli(
    hostname:"spar-db", 
    username: "root",
    password: "root",
    database: "spar_belso_db"
);

if($conn->connect_error) {
    die("Kapcsolódási hiba");
}

$conn -> set_charset("utf8mb4");
?>