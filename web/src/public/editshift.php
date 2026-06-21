<?php
require_once(__DIR__ . "/../includes/db.php");
require_once(__DIR__ . "/../includes/auth.php");

csakBejelentekzve();
if ($_SESSION["role"] !== 'admin') {
    header("Location: index.php");
    exit("Nincs jogosultságod ehhez a művelethez!");
}

$hiba = "";
$siker = "";

// azonosító átvétele url-ből
$shift_id = $_GET['id'] ?? $_POST['shift_id'] ?? null;

if (!$shift_id) {
    header("Location: index.php");
    exit;
}

// műszak lekérése
$sql = "SELECT s.date, s.start_time, s.end_time, u.full_name 
        FROM shifts s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $shift_id);
$stmt->execute();
$shift = $stmt->get_result()->fetch_assoc();

if (!$shift) {
    header("Location: index.php");
    exit;
}

// módosítások végrehajtása
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // törlés
    if (isset($_POST['delete_shift'])) {
        $delete_stmt = $conn->prepare("DELETE FROM shifts WHERE id = ?");
        $delete_stmt->bind_param("i", $shift_id);
        if ($delete_stmt->execute()) {
            $month = substr($shift['date'], 0, 7);
            header("Location: index.php?month=$month");
            exit;
        } else {
            $hiba = "Hiba történt a törlés során.";
        }
    }
    
    // módosítás
    if (isset($_POST['update_shift'])) {
        $start_time = $_POST["start_time"];
        $end_time = $_POST["end_time"];

        if (strtotime($start_time) >= strtotime($end_time)) {
            $hiba = "A befejezés időpontja nem lehet korábban, mint a kezdés!";
        } else {
            $update = $conn->prepare("UPDATE shifts SET start_time = ?, end_time = ? WHERE id = ?");
            $update->bind_param("ssi", $start_time, $end_time, $shift_id);
            
            if ($update->execute()) {
                $month = substr($shift['date'], 0, 7);
                header("Location: index.php?month=$month");
                exit;
            } else {
                $hiba = "Hiba történt a frissítés során.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Műszak szerkesztése</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f4f7f6; color: #333; }
        .card { max-width: 450px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        h2 { margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .info-block { background: #f8f9fa; padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 0.95em; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; font-size: 0.9em; }
        input[type="time"] { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; font-size: 1em; }
        
        .btn-group { display: flex; gap: 10px; margin-top: 25px; }
        button { flex: 1; padding: 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 0.95em; }
        .btn-save { background: #007bff; color: white; }
        .btn-save:hover { background: #0056b3; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #bd2130; }
        
        .back-link { display: block; text-align: center; margin-top: 20px; text-decoration: none; color: #666; font-weight: bold; }
        .back-link:hover { color: #333; }
    </style>
</head>
<body>

<div class="card">
    <h2>Műszak módosítása</h2>
    
    <?php if ($hiba): ?>
        <p style="color: #dc3545; font-weight: bold;"><?= htmlspecialchars($hiba) ?></p>
    <?php endif; ?>

    <div class="info-block">
        Dolgozó: <strong><?= htmlspecialchars($shift['full_name']) ?></strong><br>
        Dátum: <strong><?= htmlspecialchars($shift['date']) ?></strong>
    </div>

    <form method="post">
        <input type="hidden" name="shift_id" value="<?= $shift_id ?>">
        
        <div class="form-group">
            <label>Munka kezdete:</label>
            <input type="time" name="start_time" value="<?= substr($shift['start_time'], 0, 5) ?>" required>
        </div>

        <div class="form-group">
            <label>Munka vége:</label>
            <input type="time" name="end_time" value="<?= substr($shift['end_time'], 0, 5) ?>" required>
        </div>

        <div class="btn-group">
            <button type="submit" name="update_shift" class="btn-save">Mentés</button>
            <button type="submit" name="delete_shift" class="btn-delete" onclick="return confirm('Biztosan törölni szeretnéd ezt a műszakot?');">Műszak törlése</button>
        </div>
    </form>

    <?php 
        // Visszairányításnál megőrizzük az adott hónapot a linkben
        $month = substr($shift['date'], 0, 7); 
    ?>
    <a href="index.php?month=<?= $month ?>" class="back-link">Vissza a naptárhoz</a>
</div>

</body>
</html>