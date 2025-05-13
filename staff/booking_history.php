<?php
include '../config/database.php';
include 'staff_portal.php';

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Booking History</title>
</head>
<body>
<div class="content">
    <div class="container">
        <h2>Booking History</h2>
        <table>
            <thead>
                <tr>
                    <th>Room No.</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
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
                            <td>₱ <?php echo htmlspecialchars($booking['price']); ?></td>
                            <td>₱ <?php echo htmlspecialchars($booking['total_payment']); ?></td>
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
    background-color: #072e33; /* Dark background */
    border-radius: 5px; /* Optional: adds rounded corners */
    transition: background-color 0.3s, color 0.3s;
}

.pagination-link:hover {
    background-color: #555; /* Darker background on hover */
    color: #fff; /* Light text on hover */
}

.pagination-link.active {
    background-color: #0f969c; /* Bright blue for active page */
    color: #fff;
}

.pagination-link.disabled {
    color: #666; /* Light grey for disabled pages */
    pointer-events: none; /* Disable clicks on disabled pages */
    background-color: #072e33; /* Same background as normal links */
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    background-color: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border: 1px solid #0f969c;
    margin-top: 50px;
}
h2 {
    font-size: 2rem;
    margin-bottom: 20px;
    color: #0f969c;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

table, th, td {
    border: 1px solid #0f969c;
    font-size: 14px;
    padding: 12px;
    border-bottom: 1px solid black;
    word-wrap: break-word;
    text-align: center; /* Center-align both headers and data */
}


th {
    background-color: #05161a;
    color: white;
    text-transform: uppercase;
    font-weight: bold;
    text-align: center;
}

tbody tr:nth-child(even) {
    background-color: #d5deef;
}

tbody tr:hover {
    background-color: #6da5c0;
}


td {
    text-align: center;
}

/* Styling for empty room message */
table td[colspan="8"] {
    text-align: center;
    color: #f44336;
    font-size: 16px;
}

.check-in-out {
    display: inline-block;
    background-color: #4CAF50; /* Green background */
    color: white;
    padding: 8px 14px;
    border-radius: 4px;
    font-size: 12px;
    text-align: center;
    cursor: not-allowed; /* Show non-clickable cursor */
}
</style>