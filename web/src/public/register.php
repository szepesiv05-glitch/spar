<?php
require_once(__DIR__ . "/../includes/db.php");
$hiba = "";
$siker = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // adatok átvétele a formból
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $fullname = trim($_POST["fullname"] ?? "");

    if ($username == "" || $password == "" || $email == "" || $fullname == "") {
        $hiba = "Minden adatot meg kell adni";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT); // jelszó tárolása hash-ként

        // jogosultság beállítása
        $role = "user";
        $admin_code = trim($_POST['admin_code'] ?? '');
        if ($admin_code === 'isAdmin') {
        $role = 'admin';
        }

        $lekerdezes = $conn -> prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?,?,?,?,?)");
        $lekerdezes -> bind_param("sssss", $username, $hash, $email, $fullname, $role);

        if($lekerdezes -> execute()) {
            $siker = "Sikeres regisztráció";
            header("Location: login.php");
        } else {
            $hiba = "A felhasználó már foglalt";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció</title>
    <style>
        body { font-family: sans-serif; padding: 0; margin: 0; background-color: #f4f7f6; color: #333; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); width: 100%; max-width: 400px; box-sizing: border-box; margin: 20px; }
        
        h2 { margin-top: 0; text-align: center; border-bottom: 2px solid #eee; padding-bottom: 15px; color: #333; }
        
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; font-size: 0.9em; color: #555; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; font-size: 1em; transition: border-color 0.2s; }
        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus { border-color: #007bff; outline: none; }
        
        button { background: #28a745; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; font-size: 1em; margin-top: 10px; }
        button:hover { background: #218838; }
        
        .error-msg { color: #dc3545; font-weight: bold; text-align: center; margin-bottom: 15px; font-size: 0.9em; }
        .success-msg { color: #28a745; font-weight: bold; text-align: center; margin-bottom: 15px; font-size: 0.9em; }
        .switch-link { text-align: center; margin-top: 20px; font-size: 0.9em; color: #666; }
        .switch-link a { color: #007bff; text-decoration: none; font-weight: bold; }
        .switch-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="card">
    <h2>Fiók létrehozása</h2>
    
    <?php if (!empty($hiba)): ?>
        <div class="error-msg"><?= htmlspecialchars($hiba) ?></div>
    <?php endif; ?>
    <?php if (!empty($siker)): ?>
        <div class="success-msg"><?= htmlspecialchars($siker) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="form-group">
            <label for="fullname">Teljes név:</label>
            <input type="text" name="fullname" id="fullname" required>
        </div>

        <div class="form-group">
            <label for="email">Email cím:</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="form-group">
            <label for="username">Felhasználónév:</label>
            <input type="text" name="username" id="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Jelszó:</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div class="form-group">
            <label for="admin_code">Adminisztrátori kód:</label>
            <input type="password" name="admin_code" id="admin_code" placeholder="Csak vezetőknek...">
            <small style="color: #666; font-size: 0.8em; display: block; margin-top: 4px;">
                (Hagyd üresen, ha normál dolgozóként regisztrálsz.)
            </small>
        </div>
        
        <button type="submit">Regisztráció</button>
    </form>
    
    <div class="switch-link">
        Már van fiókod? <a href="login.php">Jelentkezz be itt</a>
    </div>
</div>

</body>
</html>