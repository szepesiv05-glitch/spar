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

$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month_num, $year); //napok száma a hónapban
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
    <h1>Elég a bűnözésből</h1>
</body>
</html>