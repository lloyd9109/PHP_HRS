<?php
require_once '../config/database.php';

if (isset($_GET['room_type'])) {
    $room_type = $_GET['room_type'];

    $prefixMap = [
        "Standard" => "SD",
        "Deluxe" => "DX",
        "Suite" => "ST",
        "Superior" => "SPR",
        "Family" => "FM"
    ];

    $prefix = isset($prefixMap[$room_type]) ? $prefixMap[$room_type] : '';
    $stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING(room_number, LENGTH(?) + 1) AS UNSIGNED)) AS max_room_number FROM rooms WHERE room_name = ?");
    $stmt->execute([$prefix, $room_type]);
    $maxRoomNumber = $stmt->fetchColumn();

    $nextRoomNumber = str_pad(($maxRoomNumber + 1) ?: 1, 2, '0', STR_PAD_LEFT);
    echo $prefix . $nextRoomNumber;
}
?>
