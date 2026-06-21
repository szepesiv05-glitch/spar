<?php
require_once(__DIR__ . "/../includes/db.php");
require_once(__DIR__ . "/../includes/auth.php");

csakBejelentekzve();

$userId = $_SESSION["user_id"];
$username = $_SESSION["username"];
$fullname = $_SESSION["full_name"];
$user_role = $_SESSION["role"];

//Aktuális hónap meghatározása
$current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$year = date('Y', strtotime($current_month));
$month_num = date('m', strtotime($current_month));

$days_in_month = date('t', strtotime("$year-$month_num-01")); //napok száma a hónapban
$first_day_timestamp = strtotime("$current_month-01"); //hónap első napja
$first_day_of_month = date('N', $first_day_timestamp); // 1 (H) - 7 (V)

$napok = ["", "Hétfő", "Kedd", "Szerda", "Csütörtök", "Péntek", "Szombat", "Vasárnap"];

// ünnepnapok lekérése
$holidays = [];
$holiday_stmt = $conn->prepare("SELECT holiday_date, description FROM holidays WHERE holiday_date LIKE ?");
$month_pattern = $current_month . "%";
$holiday_stmt->bind_param("s", $month_pattern);
$holiday_stmt->execute();
$holiday_res = $holiday_stmt->get_result();
while ($h = $holiday_res->fetch_assoc()) {
    $holidays[$h['holiday_date']] = $h['description'];
}

//Műszakok lekérése a hónapra
$shifts = [];
$sql = "SELECT s.id, s.date, s.start_time, s.end_time, s.status, u.full_name 
        FROM shifts s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.date LIKE ? 
        ORDER BY s.start_time ASC";

$month_pattern = $current_month . "%";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $month_pattern);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $shifts[$row['date']][] = $row;
}

// műszakok lezárása
$now_string = date('Y-m-d H:i:s');
$close_shift_sql = "UPDATE shifts 
                   SET status = 'finished' 
                   WHERE status = 'approved' 
                   AND CONCAT(date, ' ', end_time) < ?";
$close_shift_stmt = $conn->prepare($close_shift_sql);
$close_shift_stmt->bind_param("s", $now_string);
$close_shift_stmt->execute();
?>


<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Munkarend Naptár</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f4f7f6; color: #333; margin: 0; }
        .container { max-width: 1200px; margin: auto; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px; }
        
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 0; }
        
        .nav-links { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .nav-links a { text-decoration: none; color: #007bff; font-weight: bold; margin-right: 15px; }
        .month-pager { background: #eef1f5; padding: 8px 15px; border-radius: 20px; font-weight: bold; }
        
        .hours-box { background: #e8f4ff; border-left: 4px solid #007bff; padding: 12px; border-radius: 0 4px 4px 0; margin-top: 10px; display: inline-block; }
        .admin-badge { background: #dc3545; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold; margin-left: 10px; }

        /* Táblázat */
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border: 1px solid #eee; text-align: left; vertical-align: top; }
        th { background-color: #f8f9fa; color: #333; font-weight: bold; }
        
        .weekend { background-color: #fff5f5; }
        .today { background-color: #e8f4ff; font-weight: bold; }
        
        /* Műszak kártyák */
        .shift-slot { 
            display: block; 
            padding: 8px 8px; 
            margin-bottom: 5px; 
            border-radius: 4px; 
            font-size: 0.85em;
            line-height: 1.2;
            position: relative;
        }
        .status-pending { background-color: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        .status-approved { background-color: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .status-rejected { background-color: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .status-finished { background-color: #e2e3e5; color: #383d41; border-left: 4px solid #6c757d; }
        
        /* Admin gombok stílusai a kártyán belül */
        .admin-actions { margin-top: 8px; display: flex; gap: 5px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 6px; }
        .btn-mini { 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            text-decoration: none; 
            font-weight: bold; 
            font-size: 0.85em; 
            padding: 3px 8px; 
            border-radius: 3px; 
            color: white;
        }
        .btn-approve { background-color: #28a745; }
        .btn-approve:hover { background-color: #218838; }
        .btn-reject { background-color: #dc3545; }
        .btn-reject:hover { background-color: #c82333; }
        .btn-edit { background-color: #007bff; color: white; width: 100%; text-align: center; justify-content: center; }
        .btn-edit:hover { background-color: #0069d9; }
        
        .add-link { 
            display: inline-block; color: #007bff; text-decoration: none; font-size: 0.85em; font-weight: bold;
            padding: 4px 8px; border: 1px dashed #007bff; border-radius: 4px; background: #fbfdff;
        }
        .add-link:hover { background: #e8f4ff; }
    </style>
</head>
<body>

<div class="container">

    <div class="card">
        <div class="nav-links">
            <div>
                <?php if ($user_role === 'admin'): ?>
                    <span class="admin-badge">Adminisztrátor</span>
                <?php endif; ?>
                <span style="margin: 0 15px; color: #ccc;">|</span>
                <?php if ($user_role !== 'admin'): ?>
                    <a href="profile.php">👤 Profilom és Beosztásom</a>
                <?php endif; ?>
                <a href="logout.php" style="color: #666;">Kijelentkezés</a>
            </div>
            
            <div class="month-pager">
                <a href="index.php?month=<?= date('Y-m', strtotime($current_month . ' -1 month')) ?>">←</a>
                <span style="margin: 0 10px;"><?= $year ?>. <?= $month_num ?>.</span>
                <a href="index.php?month=<?= date('Y-m', strtotime($current_month . ' +1 month')) ?>">→</a>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Havi beosztások</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">Dátum</th>
                    <th style="width: 90px;">Nap</th>
                    <th colspan="5">Beosztott dolgozók</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                for ($d = 1; $d <= $days_in_month; $d++): 
                    $date_string = sprintf("%s-%02d", $current_month, $d);
                    $timestamp = strtotime($date_string);
                    $day_name = $napok[date('N', $timestamp)];
                    $is_weekend = (date('N', $timestamp) >= 6);
                    $is_today = ($date_string == date('Y-m-d'));
                    
                    // Ünnepnap ellenőrzése
                    $is_holiday = isset($holidays[$date_string]);
                    $holiday_name = $is_holiday ? $holidays[$date_string] : '';
                    
                    $day_shifts = isset($shifts[$date_string]) ? $shifts[$date_string] : [];
                    $max_slots = 5;
                    
                    // egy napra csak egy jelentkezés gombot teszünk ki
                    $add_button_shown = false;
                ?>
                    <tr class="<?= $is_weekend ? 'weekend' : '' ?> <?= $is_today ? 'today' : '' ?> <?= $is_holiday ? 'holiday-row' : '' ?>">
                        <td><strong><?= $d ?>.</strong></td>
                        <td>
                            <?= $day_name ?>
                            <?php if ($is_holiday): ?>
                                <br><span style="font-size:0.75em; color:#dc3545; font-weight:bold;"><?= htmlspecialchars($holiday_name) ?></span>
                            <?php endif; ?>
                        </td>
                        
                        <?php for ($i = 0; $i < $max_slots; $i++): ?>
                            <td>
                                <?php if (isset($day_shifts[$i])): $s = $day_shifts[$i]; ?>
                                    <div class="shift-slot status-<?= $s['status'] ?>">
                                        <strong><?= htmlspecialchars($s['full_name']) ?></strong><br>
                                        <?= substr($s['start_time'], 0, 5) ?> - <?= substr($s['end_time'], 0, 5) ?>
                                        
                                        <?php if ($user_role === 'admin'): ?>
                                            <div class="admin-actions">
                                                <?php if ($s['status'] === 'pending'): ?>
                                                    <a href="updateshift.php?id=<?= $s['id'] ?>&status=approved" class="btn-mini btn-approve" title="Elfogad">✓ Elfogad</a>
                                                    <a href="updateshift.php?id=<?= $s['id'] ?>&status=rejected" class="btn-mini btn-reject" title="Elutasít">✗ Elutasít</a>
                                                <?php else: ?>
                                                    <a href="editshift.php?id=<?= $s['id'] ?>" class="btn-mini btn-edit" title="Módosítás / Törlés">✏️ Szerkesztés</a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                <?php else: ?>
                                    <?php if ($is_holiday): ?>
                                        <?php if ($i === 0 || ($i === 1 && count($day_shifts) === 0)): ?>
                                            <span class="closed-text">🔒 Bolt zárva</span>
                                        <?php endif; ?>
                                    <?php elseif ($user_role !== 'admin' && !$add_button_shown): ?>
                                        <a href="addshift.php?date=<?= $date_string ?>" class="add-link">+ Jelentkezés</a>
                                        <?php $add_button_shown = true; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>