<?php
require_once(__DIR__ . "/../includes/db.php");
require_once(__DIR__ . "/../includes/auth.php");

// Ellenőrizzük hogy adminként van-e bejelentkezve
csakBejelentekzve();
if ($_SESSION["role"] !== 'admin') {
    header("Location: index.php");
    exit("Nincs jogosultságod ehhez a művelethez!");
}

// Adatok átvétele url-ből
$id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;

// MÓDOSÍTÁS VÉGREHAJTÁSA
// Csak akkor fut le, ha kaptunk érvényes ID-t és a státusz vagy 'approved' vagy 'rejected'
if ($id && ($status === 'approved' || $status === 'rejected')) { //id és státusz ellenőrzése
    
    // Első lépésben lekérjük a műszak dátumát, hogy a végén a megfelelő hónapra tudjunk visszaugrani
    $date_stmt = $conn->prepare("SELECT date FROM shifts WHERE id = ?");
    $date_stmt->bind_param("i", $id);
    $date_stmt->execute();
    $result = $date_stmt->get_result()->fetch_assoc();
    
    if ($result) {
        $shift_date = $result['date'];
        $month = substr($shift_date, 0, 7); // Pl.: '2026-06'

        // Frissítjük a műszak státuszát
        $stmt = $conn->prepare("UPDATE shifts SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        
        // Sikeres frissítés után visszairányítunk a naptár megfelelő hónapjához
        header("Location: index.php?month=$month");
        exit;
    }
}