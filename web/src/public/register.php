<?php
require_once(__DIR__ . "/../includes/db.php");
$hiba = "";
$siker = "";

$username = trim($_POST["username"] ?? "");
$password = trim($_POST["password"] ?? "");

if ($username == "" || $password == "") {
    $hiba = "Minden adatot meg kell adni";

} else {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $lekerdezes = $conn -> prepare("INSERT INTO users (username, password) VALUES (?,?)");
    $lekerdezes -> bind_param("ss", $username, $hash);

    if($lekerdezes -> execute()) {
        $siker = "Sikeres regisztráció";
    } else {
        $hiba = "A felhasználó már foglalt";
    }
}
?>

<h1>Regisztráció</h1>
<form method="post">
    <label>Felhaszálónev: <input type="text" name="username"></label>
    <label>Jelszó: <input type="password" name="password"></label>
    <button type="submit">Regisztráció</button>
</form>
<p style="color: red"><?= $hiba ?></p>
<p style="color: green"><?= $siker ?></p>

<a href="login.php">Bejelentkezés</a>