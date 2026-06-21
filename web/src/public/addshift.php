<?php
require_once(__DIR__ . "/../includes/db.php");
require_once(__DIR__ . "/../includes/auth.php");

csakBejelentekzve();

$hiba = "";

// dátum meghatározása
$date = $_GET['date'] ?? $_POST['date'] ?? date('Y-m-d');

// Mentés feldolgozása
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // adatok átvétele a formból
    $user_id = $_SESSION["user_id"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];
    $date = $_POST["date"];

    if (strtotime($start_time) >= strtotime($end_time)) {  //ne tudja későbbre állítani a kezdési idöt mint a műszak végét
        $hiba = "A befejezés időpontja nem lehet korábban, mint a kezdés!";
    } else {
        // Mentés az adatbázisba 'pending' státusszal
        $stmt = $conn->prepare("INSERT INTO shifts (user_id, date, start_time, end_time, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isss", $user_id, $date, $start_time, $end_time);

        if ($stmt->execute()) {
            // Sikeres mentés után visszaugrunk a főoldalra az adott hónaphoz
            $month = substr($date, 0, 7);
            header("Location: index.php?month=$month");
            exit;
        } else {
            $hiba = "Hiba történt a mentés során.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Jelentkezés műszakra</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .card { max-width: 400px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .back-link { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #666; }
    </style>
</head>
<body>

<div class="card">
    <h2>Műszak hozzáadása</h2>
    <p>Dátum: <strong><?= htmlspecialchars($date) ?></strong></p>

    <?php if ($hiba): ?>
        <p style="color: red;"><?= $hiba ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
        
        <div class="form-group">
            <label>Munka kezdete:</label>
            <input type="time" name="start_time" required value="08:00">
        </div>

        <div class="form-group">
            <label>Munka vége:</label>
            <input type="time" name="end_time" required value="16:00">
        </div>

        <button type="submit">Jelentkezés beküldése</button>
    </form>

    <a href="index.php" class="back-link">Vissza a naptárhoz</a>
</div>

</body>
</html>