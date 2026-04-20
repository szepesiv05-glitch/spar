<?php
require_once("db.php");
require_once("auth.php");

$hiba = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    $lekerdezes = $conn -> prepare("SELECT * FROM users WHERE username = ?");
    $lekerdezes -> bind_param("s", $username);
    $lekerdezes -> execute();

    $result = $lekerdezes -> get_result();

    $user = $result -> fetch_assoc();

    if($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        header("Location: index.php");
        exit;
    }
    else {
        $hiba = "Hibás felhasználónév vagy jelszó";
    }
} 

?>

<h1>Bejelentkezés</h1>
<form method="post">
    <label>Felhaszálónev: <input type="text" name="username"></label>
    <label>Jelszó: <input type="password" name="password"></label>
    <button type="submit">Bejelentkezés</button>
</form>
<p style="color: red"><?= $hiba ?></p>
<a href="register.php">Regisztrácio</a>