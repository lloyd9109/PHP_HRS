<?php
ob_start();
require_once '../src/Auth.php'; // Ensure this handles session and authentication
include 'header.php';
require_once '../config/database.php';

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get the current page from URL or default to 1
$records_per_page = 10; // Set the number of records per page
$offset = ($current_page - 1) * $records_per_page; // Calculate the offset for the query

ob_end_flush();

// Assuming the user is logged in and their user_id is stored in the session
$user_id = $_SESSION['user_id']; // Retrieve the logged-in user's ID from the session

// Fetch booking history data for the logged-in user with limit and offset
try {
    // Count the total number of records for pagination
    $countQuery = "SELECT COUNT(*) AS total FROM booking_history WHERE user_id = :user_id";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $countStmt->execute();
    $total_records = $countStmt->fetch(PDO::FETCH_ASSOC)['total']; // Get the total number of records

    // Calculate total pages
    $total_pages = ceil($total_records / $records_per_page);

    // Fetch the actual records for the current page
    $query = "SELECT room_number, full_name, email, phone_number, preferred_date_start, preferred_date_end, preferred_time, guests, check_in_out, price, total_payment 
              FROM booking_history 
              WHERE user_id = :user_id
              LIMIT :offset, :records_per_page"; // Fetch with limit and offset
    $statement = $pdo->prepare($query);
    $statement->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $statement->bindParam(':offset', $offset, PDO::PARAM_INT); // Bind the offset
    $statement->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT); // Bind the records per page
    $statement->execute();
    $bookingHistory = $statement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching booking history: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./styles/booking_historyss.css">
</head>
<body>
    <main>

        <div class="hotel-title">
            <h1>Hotel Hive</h1>
        </div>

        <section>
            <div class="links-container">

                <div class="book-room-link">
                    <a href="rooms.php" class="book-room-btn">
                        <i class="fas fa-bed"></i> Book Room
                    </a>
                </div>

                <h2>Booking History</h2>
       
                <div class="booking-history-link">
                    <a href="bookings.php" class="booking-history-btn">
                        <i class="fas fa-calendar-check"></i> Bookings
                    </a>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Number</th>
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
                <?php if (!empty($bookingHistory)): ?>
                    <?php foreach ($bookingHistory as $booking): ?>
                        <tr>
                            <td data-label="Room Number"><?= htmlspecialchars($booking['room_number']) ?></td>
                            <td data-label="Full Name"><?= htmlspecialchars($booking['full_name']) ?></td>
                            <td data-label="Email"><?= htmlspecialchars($booking['email']) ?></td>
                            <td data-label="Phone Number"><?= htmlspecialchars($booking['phone_number']) ?></td>
                            <td data-label="Preferred Date Start">
                                <?= htmlspecialchars(date('D, d M', strtotime($booking['preferred_date_start']))) ?>
                            </td>
                            <td data-label="Preferred Date End">
                                <?= htmlspecialchars(date('D, d M', strtotime($booking['preferred_date_end']))) ?>
                            </td>
                            <td data-label="Preferred Time"><?= htmlspecialchars($booking['preferred_time']) ?></td>
                            <td data-label="Guests"><?= htmlspecialchars($booking['guests']) ?></td>
                            <td data-label="Price">₱<?= htmlspecialchars(number_format($booking['price'], 2)) ?></td>
                            <td data-label="Total Payment">₱<?= htmlspecialchars(number_format($booking['total_payment'], 2)) ?></td>
                            <td data-label="Status">
                                <?php if (strtolower($booking['check_in_out']) == 'complete'): ?>
                                    <span class="complete"><?= htmlspecialchars($booking['check_in_out']) ?></span>
                                <?php else: ?>
                                    <?= htmlspecialchars($booking['check_in_out']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="text-center">No booking history available.</td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>

            <div class="pagination">
                <a href="?page=1" class="pagination-link <?php echo $current_page == 1 ? 'disabled' : ''; ?>">First</a>
                <a href="?page=<?php echo max(1, $current_page - 1); ?>" class="pagination-link <?php echo $current_page == 1 ? 'disabled' : ''; ?>">Previous</a>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="pagination-link <?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <a href="?page=<?php echo min($total_pages, $current_page + 1); ?>" class="pagination-link <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">Next</a>
                <a href="?page=<?php echo $total_pages; ?>" class="pagination-link <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">Last</a>
            </div>

        </section>
    </main>
</body>

<footer>
    <?php include 'footer.php'; ?>
</footer>

</html>
