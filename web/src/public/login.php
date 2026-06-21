<?php
require_once(__DIR__ . "/../includes/db.php");
require_once(__DIR__ . "/../includes/auth.php");

$hiba = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // adatok átvétele a formból
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    // felhasználó megkeresése az adatbázisban username alapján
    $lekerdezes = $conn -> prepare("SELECT * FROM users WHERE username = ?");
    $lekerdezes -> bind_param("s", $username);
    $lekerdezes -> execute();

    $result = $lekerdezes -> get_result();
    $user = $result -> fetch_assoc();

    // jelszó és felhasználó ellenőrzése
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

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Bejelentkezés</title>
    <style>
        body { font-family: sans-serif; padding: 0; margin: 0; background-color: #f4f7f6; color: #333; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); width: 100%; max-width: 380px; box-sizing: border-box; }
        
        h2 { margin-top: 0; text-align: center; border-bottom: 2px solid #eee; padding-bottom: 15px; color: #333; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; font-size: 0.9em; color: #555; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; font-size: 1em; transition: border-color 0.2s; }
        input[type="text"]:focus, input[type="password"]:focus { border-color: #007bff; outline: none; }
        
        button { background: #007bff; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; font-size: 1em; margin-top: 10px; }
        button:hover { background: #0056b3; }
        
        .error-msg { color: #dc3545; font-weight: bold; text-align: center; margin-bottom: 15px; font-size: 0.9em; }
        .switch-link { text-align: center; margin-top: 20px; font-size: 0.9em; color: #666; }
        .switch-link a { color: #007bff; text-decoration: none; font-weight: bold; }
        .switch-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="card">
    <h2>Bejelentkezés</h2>
    
    <?php if (!empty($hiba)): ?>
        <div class="error-msg"><?= htmlspecialchars($hiba) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="form-group">
            <label for="username">Felhasználónév:</label>
            <input type="text" name="username" id="username" required autocomplete="username">
        </div>
        
        <div class="form-group">
            <label for="password">Jelszó:</label>
            <input type="password" name="password" id="password" required autocomplete="current-password">
        </div>
        
        <button type="submit">Belépés</button>
    </form>
    
    <div class="switch-link">
        Még nincs fiókod? <a href="register.php">Regisztrálj itt</a>
    </div>
</div>

</body>
</html>