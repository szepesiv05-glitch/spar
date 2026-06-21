<?php
session_start();

// ellenőrizzük hogy a fekhasználó be van-e jelentkezve
function Bejelentkezve(): bool {
    return isset($_SESSION["user_id"]);
}

// ha nincs akkor átirányítjuk a login oldalra
function csakBejelentekzve() : void {
    if(!Bejelentkezve()) {
        header("Location: login.php");
        exit;
    }
}
?>