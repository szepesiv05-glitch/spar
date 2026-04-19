<?php
session_start();

function Bejelentkezve(): bool {
    return isset($_SESSION["user_id"]);
}

function csakBejelentekzve() : void {
    if(!Bejelentkezve()) {
        header("Location: login.php");
        exit;
    }
}
?>