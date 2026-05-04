<?php
require_once(__DIR__ . "/../includes/db.php");
require_once(__DIR__ . "/../includes/auth.php");

csakBejelentekzve();

$userId = $_SESSION["user_id"];
$username = $_SESSION["username"];
$fullname = $_SESSION["full_name"];

//Aktuális hónap meghatározása
$current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$year = date('Y', strtotime($current_month));
$month_num = date('m', strtotime($current_month));

$days_in_month = date('t', strtotime("$year-$month_num-01")); //napok száma a hónapban
$first_day_timestamp = strtotime("$current_month-01"); //hónap első napja
$first_day_of_month = date('N', $first_day_timestamp); // 1 (H) - 7 (V)

//Műszakok lekérése a hónapra
$shifts = [];
$timetable = $conn->prepare("SELECT s.date, s.start_time, s.end_time, s.status, u.full_name 
        FROM shifts s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.date LIKE ? 
        ORDER BY s.start_time ASC");

$month_pattern = $current_month . "%";
$timetable->bind_param("s", $month_pattern);
$timetable->execute();
$result = $timetable->get_result();

while ($row = $result->fetch_assoc()) {
    $shifts[$row['date']][] = $row;
}
?>


   

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beosztáskezelő</title>
</head>
<body>
    <header>
        <nav>
            
        </nav>
    </header>
    <h1>Munkarend - <?= $current_month ?></h1>
    <?php $napok = ["", "Hétfő", "Kedd", "Szerda", "Csütörtök", "Péntek", "Szombat", "Vasárnap"]; ?>
    <table>
    <thead>
        <tr>
            <th>Dátum</th>
            <th>Nap</th>
            <th colspan="3">Beosztások (Név | Kezdés - Vége)</th>
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
            
            $day_shifts = isset($shifts[$date_string]) ? $shifts[$date_string] : [];
            $max_slots = 5; // Hány oszlopnyi hely legyen
        ?>
            <tr class="<?= $is_weekend ? 'weekend' : '' ?> <?= $is_today ? 'today' : '' ?>">
                <td><?= $d ?>.</td>
                <td><?= $day_name ?></td>
                
                <?php 
                // Kilistázzuk a már meglévő műszakokat
                for ($i = 0; $i < $max_slots; $i++): ?>
                    <td>
                        <?php if (isset($day_shifts[$i])): $s = $day_shifts[$i]; ?>
                            <div class="shift-slot <?= $s['status'] ?>">
                                <strong><?= htmlspecialchars($s['full_name']) ?></strong><br>
                                <?= substr($s['start_time'], 0, 5) ?> - <?= substr($s['end_time'], 0, 5) ?>
                            </div>
                        <?php else: ?>
                            <a href="add_shift.php?date=<?= $date_string ?>" class="add-link">+ Jelentkezés</a>
                        <?php endif; ?>
                    </td>
                <?php endfor; ?>
            </tr>
        <?php endfor; ?>
    </tbody>
</table>
</body>
</html>