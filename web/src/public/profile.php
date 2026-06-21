<?php
require_once(__DIR__ . "/../includes/db.php");
require_once(__DIR__ . "/../includes/auth.php");

csakBejelentekzve();

$user_id = $_SESSION["user_id"];
$hiba = "";
$siker = "";

//hónapok kezelése
$current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$year = date('Y', strtotime($current_month));
$month_num = date('m', strtotime($current_month));

// felhasználó adatainak lekérése
$stmt = $conn->prepare("SELECT username, email, full_name, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

//saját műszakok lekérése
$month_pattern = $current_month . "%";
$shift_stmt = $conn->prepare("SELECT id, date, start_time, end_time, status, admin_comment FROM shifts WHERE user_id = ? AND date LIKE ? ORDER BY date DESC, start_time ASC");
$shift_stmt->bind_param("is", $user_id, $month_pattern);
$shift_stmt->execute();
$my_shifts = $shift_stmt->get_result();

//órák számolása
$total_seconds = 0;
$shifts_array = []; // ide menti a sorokat

while ($row = $my_shifts->fetch_assoc()) {
    $shifts_array[] = $row;
    if ($row['status'] === 'finished') {
        $start = strtotime($row['date'] . ' ' . $row['start_time']);
        $end = strtotime($row['date'] . ' ' . $row['end_time']);
        $total_seconds += ($end - $start);
    }
}

$total_hours = floor($total_seconds / 3600);
$total_remaining_minutes = floor(($total_seconds % 3600) / 60);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Profilom és Beosztásom</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f4f7f6; color: #333; }
        .container { max-width: 1000px; margin: auto; display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 0; }
        
        .profile-data-group { margin-bottom: 15px; border-bottom: 1px dashed #eee; padding-bottom: 10px; }
        .profile-data-group:last-child { border-bottom: none; }
        .data-label { display: block; font-size: 0.85em; color: #666; font-weight: bold; margin-bottom: 3px; }
        .data-value { font-size: 1.05em; color: #111; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border: 1px solid #eee; text-align: left; }
        th { background-color: #f8f9fa; }
        
        .status { padding: 3px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; display: inline-block; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .status-finished { background-color: #e2e3e5; color: #383d41; }
        
        .comment { font-size: 0.85em; color: #666; font-style: italic; margin-top: 4px; }
        .nav-links { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .nav-links a { text-decoration: none; color: #007bff; font-weight: bold; }
        .month-pager { background: #eef1f5; padding: 8px 15px; border-radius: 20px; font-weight: bold; }
        .hours-box { background: #e8f4ff; border-left: 4px solid #007bff; padding: 10px; margin-bottom: 15px; border-radius: 0 4px 4px 0; }
    </style>
</head>
<body>

<div class="nav-links">
    <div>
        <a href="index.php">← Vissza a fő naptárhoz</a>
    </div>
    
    <div class="month-pager">
        <a href="profile.php?month=<?= date('Y-m', strtotime($current_month . ' -1 month')) ?>">←</a>
        <span style="margin: 0 10px;"><?= $year ?>. <?= $month_num ?>.</span>
        <a href="profile.php?month=<?= date('Y-m', strtotime($current_month . ' +1 month')) ?>">→</a>
    </div>
</div>

<div class="container">
    
    <div class="card">
        <h2>Személyes adatok</h2>
        
        <div class="profile-data-group">
            <span class="data-label">Teljes név:</span>
            <span class="data-value"><strong><?= htmlspecialchars($user['full_name']) ?></strong></span>
        </div>

        <div class="profile-data-group">
            <span class="data-label">Felhasználónév:</span>
            <span class="data-value"><?= htmlspecialchars($user['username']) ?></span>
        </div>
        
        <div class="profile-data-group">
            <span class="data-label">Email cím:</span>
            <span class="data-value"><?= htmlspecialchars($user['email']) ?></span>
        </div>
    </div>
    
    <div class="card">
        <h2>Beosztásaim ebben a hónapban</h2>
        
        <div class="hours-box">
            Ledolgozott órák ebben a hónapban:<br>
            <strong><?= $total_hours ?> óra <?= $total_remaining_minutes ?> perc</strong>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Dátum</th>
                    <th>Időpont</th>
                    <th>Státusz</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($shifts_array)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: #999;">Ebben a hónapban még nem jelentkeztél műszakra.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($shifts_array as $row): ?>
                        <tr>
                            <td><strong><?= $row['date'] ?></strong></td>
                            <td><?= substr($row['start_time'], 0, 5) ?> - <?= substr($row['end_time'], 0, 5) ?></td>
                            <td>
                                <span class="status status-<?= $row['status'] ?>">
                                    <?php 
                                        if($row['status'] == 'pending') echo 'Elfogadásra vár';
                                        if($row['status'] == 'approved') echo 'Elfogadott';
                                        if($row['status'] == 'rejected') echo 'Elutasított';
                                        if($row['status'] == 'finished') echo 'Elvégzett';
                                    ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>