<?php
require_once '../config/database.php';
include 'staff_portal.php';


$summaryQuery = "SELECT availability, COUNT(*) as count FROM rooms GROUP BY availability";
$summaryStmt = $pdo->prepare($summaryQuery);
$summaryStmt->execute();
$summary = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);
$availabilityCounts = [];
foreach ($summary as $item) {
    $availabilityCounts[$item['availability']] = $item['count'];
}


$query = "SELECT room_number, full_name, email, phone_number, guests, check_in_out, preferred_date, preferred_time, price, total_payment FROM reserved";
$stmt = $pdo->prepare($query);
$stmt->execute();

$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$checkInCount = 0;
$checkOutCount = 0;foreach ($reservations as $reservation) {
    if ($reservation['check_in_out'] === 'check-in') {
        $checkInCount++;
    } elseif ($reservation['check_in_out'] === 'check-out') {
        $checkOutCount++;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<div class="content">
    <!-- Summary Dashboard -->
    <div class="summary-dashboard">
        <div class="summary-box">
            <h3>Available</h3>
            <p><?php echo $availabilityCounts['Available'] ?? 0; ?></p>
        </div>
        <div class="summary-box">
            <h3>Unavailable</h3>
            <p><?php echo $availabilityCounts['Unavailable'] ?? 0; ?></p>
        </div>
        <div class="summary-box">
            <h3>Booked</h3>
            <p><?php echo $availabilityCounts['Booked'] ?? 0; ?></p>
        </div>
        <div class="summary-box">
            <h3>Reserved</h3>
            <p><?php echo $availabilityCounts['Reserved'] ?? 0; ?></p>
        </div>
    </div>
    <!-- Button to view rooms -->
    <div class="view-rooms-btn">
        <a href="view_rooms.php" class="btn">View Rooms</a>
    </div>


    <div class="widget-container">
        <div class="widget">
            <h3>Check In Count</h3>
            <p><?php echo $checkInCount; ?></p>
        </div>
        <div class="widget">
            <h3>Check Out Count</h3>
            <p><?php echo $checkOutCount; ?></p>
        </div>
    </div>

    <div class="view-rooms-btn">
        <a href="manage_reservation.php" class="btn">Manage Reservation</a>
    </div>
</div>

</body>
</html>

<style>
.summary-dashboard {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
    text-align: center;
    margin-top: 20px;
}

.summary-box {
    flex: 1;
    margin: 0 10px;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.summary-box:hover {
    transform: translateY(-5px);
}

.summary-box h3 {
    margin-bottom: 10px;
    color: #333;
    font-size: 18px;
    font-weight: bold;
}

.summary-box p {
    margin: 0;
    font-size: 24px;
    color: #4CAF50;
    font-weight: bold;
}

.summary-box:nth-child(2) p {
    color: #f44336;
}

.summary-box:nth-child(3) p {
    color: #ffa500;
}

.summary-box:nth-child(4) p {
    color: #2196f3;
}

.view-rooms-btn {
    text-align: center;
    margin-top: 20px;
}

.view-rooms-btn .btn {
    display: inline-block;
    padding: 10px 20px;
    color: #fff;
    background-color: #007bff;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-size: 16px;
    transition: background-color 0.3s ease;
}

.view-rooms-btn .btn:hover {
    background-color: #0056b3;
}

.widget-container {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
    text-align: center;
    margin-top: 50px;
}
.widget {
    flex: 1;
    margin: 0 10px;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    max-width: 400px;
}

.widget p {
    margin: 0;
    font-size: 18px;
    color: #4CAF50;
    font-weight: bold;
}

.widget h3 {
    margin-bottom: 10px;
    color: #333;
    font-size: 18px;
    font-weight: bold;
}
</style>