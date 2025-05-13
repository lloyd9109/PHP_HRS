<?php
session_start();
require_once '../config/database.php';
require_once 'admin.php';
include 'header.php';

if (!$admin->isLoggedIn()) {
    header('Location: admin_login.php');
    exit();
}

// Determine the current page number
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Set the number of items per page
$items_per_page = 10;

// Calculate the offset for the SQL query
$offset = ($page - 1) * $items_per_page;

// Fetch the total number of bookings
$total_query = "SELECT COUNT(*) FROM booking_history";
$total_stmt = $pdo->prepare($total_query);
$total_stmt->execute();
$total_bookings = $total_stmt->fetchColumn();

// Calculate total pages
$total_pages = ceil($total_bookings / $items_per_page);

// Fetch bookings for the current page, including price and total_payment
$query = "SELECT room_number, full_name, email, phone_number, preferred_date_start, 
                 preferred_date_end, preferred_time, guests, check_in_out, price, total_payment 
          FROM booking_history LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$bookings = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/booking_history.css">
    <title>Booking History</title>
</head>
<body>

<div class="main-content" style="margin-left: 270px; margin-top: 80px;">

    <div class="booking-history">
        <h2>Booking History</h2>
        <table>
            <thead>
                <tr>
                    <th>Room No.</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone No.</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Time</th>
                    <th>Guests</th>
                    <th>Price</th>
                    <th>Payment</th>
                    <th>Status</th>

                </tr>
            </thead>
            <tbody>
                <?php if (count($bookings) > 0): ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['room_number']); ?></td>
                            <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['email']); ?></td>
                            <td><?php echo htmlspecialchars($booking['phone_number']); ?></td>
                            <td>
                                <?php echo date("D, d M", strtotime($booking['preferred_date_start'])); ?>
                            </td>
                            <td>
                                <?php echo date("D, d M", strtotime($booking['preferred_date_end'])); ?>
                            </td>
                            <td><?php echo htmlspecialchars($booking['preferred_time']); ?></td>
                            <td><?php echo htmlspecialchars($booking['guests']); ?></td>
                            <td>₱ <?php echo number_format(htmlspecialchars($booking['price']), 2); ?></td>
                            <td>₱ <?php echo number_format(htmlspecialchars($booking['total_payment']), 2); ?></td>
                            <td>
                                <span class="check-in-out">
                                    <?php echo htmlspecialchars($booking['check_in_out']); ?>
                                </span>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11">No booking history available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <a href="?page=1" class="pagination-link <?php echo $page == 1 ? 'disabled' : ''; ?>">First</a>
            <a href="?page=<?php echo max(1, $page - 1); ?>" class="pagination-link <?php echo $page == 1 ? 'disabled' : ''; ?>">Previous</a>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="pagination-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <a href="?page=<?php echo min($total_pages, $page + 1); ?>" class="pagination-link <?php echo $page == $total_pages ? 'disabled' : ''; ?>">Next</a>
            <a href="?page=<?php echo $total_pages; ?>" class="pagination-link <?php echo $page == $total_pages ? 'disabled' : ''; ?>">Last</a>
        </div>
    </div>

</div>

</body>
</html>



<style>

.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.pagination-link {
    padding: 10px 15px;
    margin: 0 5px;
    border: 1px solid #444; /* Dark border */
    text-decoration: none;
    color: #ccc; /* Lighter text color for dark theme */
    background-color: #333; /* Dark background */
    border-radius: 5px; /* Optional: adds rounded corners */
    transition: background-color 0.3s, color 0.3s;
}

.pagination-link:hover {
    background-color: #555; /* Darker background on hover */
    color: #fff; /* Light text on hover */
}

.pagination-link.active {
    background-color: #238636; /* Bright blue for active page */
    color: #fff;
}

.pagination-link.disabled {
    color: #666; /* Light grey for disabled pages */
    pointer-events: none; /* Disable clicks on disabled pages */
    background-color: #333; /* Same background as normal links */
}
.booking-history {
    background-color: #191c24;
    padding: 20px;
    border-radius: 2px;
    flex: 1;
    text-align: center;
    margin-bottom: 100px;
    overflow: hidden;
}
.main-content {
    margin-left: 270px;
    margin-top: 60px;
    height: 100vh;
    overflow-y: auto;
    padding: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    table-layout: fixed; 
}

thead {
    background-color: green;
    color: white;
    text-align: center;
}

tbody {
    display: table-row-group; /* Keeps rows aligned with headers */
    height: 300px; /* Optional: scrollable tbody */
    overflow-y: auto; /* Enables scrolling if tbody height is set */
    width: 100%; /* Ensures full width */
}

thead, tbody tr {
    display: table;
    width: 100%;
    table-layout: fixed; /* Ensure consistency with the table layout */
}

th, td {
    font-size: 14px;
    padding: 12px;
    border-bottom: 1px solid #4caf50;
    word-wrap: break-word;
}

th {
    background-color: green;
    color: white;
    text-align: center;
}

tr:hover {
    background-color: black;
}
/* Styling for the scrollbar */
tbody::-webkit-scrollbar {
    width: 1px; /* Set the width of the vertical scrollbar */
    background-color: #f1f1f1; /* Set background color for the scrollbar track */
}
tbody::-webkit-scrollbar-track {
    background: #2c2c2c;
}

tbody::-webkit-scrollbar-thumb {
    background-color: #888; /* Set color of the scrollbar thumb */
    border-radius: 5px; /* Round the corners of the scrollbar thumb */
}

tbody::-webkit-scrollbar-thumb:hover {
    background-color: #555; /* Darken the thumb color when hovered */
}


/* Styling for the check-in/out field to look like a button but be non-clickable */
.check-in-out {
    display: inline-block;
    background-color: #4CAF50; /* Green background */
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 14px;
    text-align: center;
    cursor: not-allowed; /* Show non-clickable cursor */
}

</style>

