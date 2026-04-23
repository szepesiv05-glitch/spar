<?php
require_once(__DIR__ . "/../includes/db.php");
$hiba = "";
$siker = "";

$username = trim($_POST["username"] ?? "");
$password = trim($_POST["password"] ?? "");
$email = trim($_POST["email"] ?? "");
$fullname = trim($_POST["fullname"] ?? "");

if ($username == "" || $password == "" || $email == "" || $fullname == "") {
    $hiba = "Minden adatot meg kell adni";

} else {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $lekerdezes = $conn -> prepare("INSERT INTO users (username, password, email, full_name) VALUES (?,?,?,?)");
    $lekerdezes -> bind_param("ssss", $username, $hash, $email, $fullname);

    if($lekerdezes -> execute()) {
        $siker = "Sikeres regisztráció";
    } else {
        $hiba = "A felhasználó már foglalt";
    }
}
?>

<h1>Regisztráció</h1>
<form method="post">
    <label>Email: <input type="email" name="email" required></label>
    <label>Felhaszálónev: <input type="text" name="username" required></label>
    <label>Teljes név: <input type="text" name="fullname" required></label>
    <label>Jelszó: <input type="password" name="password" required></label>
    <button type="submit">Regisztráció</button>
</form>
<p style="color: red"><?= $hiba ?></p>
<p style="color: green"><?= $siker ?></p>

<p>Már van fiókod? <a href="login.php">Bejelentkezés</a></p>