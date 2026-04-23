<?php
require_once(__DIR__ . "/../includes/db.php");
require_once(__DIR__ . "/../includes/auth.php");

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
        $_SESSION["full_name"] = $user["full_name"];
        $_SESSION["role"] = $user["role"];

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
    <div>
        <label>Felhasználónév:</label>
        <input type="text" name="username" required>
    </div>
    <div>
        <label>Jelszó:</label>
        <input type="password" name="password" required>
    </div>
    <button type="submit">Bejelentkezés</button>
</form>

<p>Még nincs fiókod? <a href="register.php">Regisztráció</a></p>
<p style="color: red"><?= $hiba ?></p>