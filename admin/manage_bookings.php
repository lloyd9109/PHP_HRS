<?php
session_start();
require_once '../config/database.php';
require_once 'admin.php';

// Use output buffering to prevent header issues
ob_start();

include 'header.php';

$admin = new Admin($pdo);

if (!$admin->isLoggedIn()) {
    header('Location: admin_login.php');
    exit();
}

// Handle booking approval/denial
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];

    // Determine the new status based on the action
    if ($action === 'confirmed') {
        $new_status = 'confirmed';

        // Fetch booking details including price and total_payment
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = :id");
        $stmt->bindParam(':id', $booking_id, PDO::PARAM_INT);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($booking) {
            // Update room availability to 'reserved'
            $stmt = $pdo->prepare("UPDATE rooms SET availability = 'reserved' WHERE room_number = :room_number");
            $stmt->bindParam(':room_number', $booking['room_number'], PDO::PARAM_STR);
            $stmt->execute();

            // Insert into reserved table including price and total_payment
            $stmt = $pdo->prepare("INSERT INTO reserved (room_number, full_name, email, phone_number, guests, check_in_out, preferred_date_start, preferred_date_end, preferred_time, user_id, price, total_payment) 
                                VALUES (:room_number, :full_name, :email, :phone_number, :guests, 'check-in', :preferred_date_start, :preferred_date_end, :preferred_time, :user_id, :price, :total_payment)");
            $stmt->bindParam(':room_number', $booking['room_number'], PDO::PARAM_STR);
            $stmt->bindParam(':full_name', $booking['full_name'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $booking['email'], PDO::PARAM_STR);
            $stmt->bindParam(':phone_number', $booking['phone_number'], PDO::PARAM_STR);
            $stmt->bindParam(':guests', $booking['guests'], PDO::PARAM_INT);
            $stmt->bindParam(':preferred_date_start', $booking['preferred_date_start'], PDO::PARAM_STR);
            $stmt->bindParam(':preferred_date_end', $booking['preferred_date_end'], PDO::PARAM_STR);
            $stmt->bindParam(':preferred_time', $booking['preferred_time'], PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $booking['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':price', $booking['price'], PDO::PARAM_STR);
            $stmt->bindParam(':total_payment', $booking['total_payment'], PDO::PARAM_STR);
            $stmt->execute();
        }
    } elseif ($action === 'cancelled') {
        $new_status = 'cancelled';

        // Optional: Reset room availability to 'available' if necessary
        $stmt = $pdo->prepare("UPDATE rooms SET availability = 'available' WHERE room_number = :room_number");
        $stmt->bindParam(':room_number', $booking['room_number'], PDO::PARAM_STR);
        $stmt->execute();
    } else {
        exit('Invalid action');
    }

    // Update the status and timestamp in the bookings table
    $stmt = $pdo->prepare("UPDATE bookings SET status = :status, status_updated_at = NOW() WHERE id = :id");
    $stmt->bindParam(':status', $new_status, PDO::PARAM_STR);
    $stmt->bindParam(':id', $booking_id, PDO::PARAM_INT);
    $stmt->execute();

    // Redirect to the same page to avoid form resubmission
    $query = $_SERVER['QUERY_STRING']; // Get the current query string
    header("Location: " . $_SERVER['PHP_SELF'] . ($query ? "?$query" : ""));
    exit();
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10; // Number of bookings per page

// Calculate the offset for pagination
$offset = ($page - 1) * $items_per_page;

$sql = "SELECT * FROM bookings";
if ($status_filter !== 'all') {
    $sql .= " WHERE status = :status";
}
$sql .= " ORDER BY preferred_date_start DESC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);

// Bind the status parameter if necessary
if ($status_filter !== 'all') {
    $stmt->bindValue(':status', $status_filter, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count the total number of bookings for pagination calculation
$count_sql = "SELECT COUNT(*) AS total FROM bookings";
if ($status_filter !== 'all') {
    $count_sql .= " WHERE status = :status";
}
$count_stmt = $pdo->prepare($count_sql);
if ($status_filter !== 'all') {
    $count_stmt->bindValue(':status', $status_filter, PDO::PARAM_STR);
}
$count_stmt->execute();
$total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_count / $items_per_page);

// Count pending bookings
$pendingCountQuery = "SELECT COUNT(*) AS pending_count FROM bookings WHERE status = 'pending'";
$pendingStmt = $pdo->query($pendingCountQuery);
$pendingCount = $pendingStmt->fetch(PDO::FETCH_ASSOC)['pending_count'];

// Count confirmed bookings
$confirmedCountQuery = "SELECT COUNT(*) AS confirmed_count FROM bookings WHERE status = 'confirmed'";
$confirmedStmt = $pdo->query($confirmedCountQuery);
$confirmedCount = $confirmedStmt->fetch(PDO::FETCH_ASSOC)['confirmed_count'];

// Count cancelled bookings
$cancelledCountQuery = "SELECT COUNT(*) AS cancelled_count FROM bookings WHERE status = 'cancelled'";
$cancelledStmt = $pdo->query($cancelledCountQuery);
$cancelledCount = $cancelledStmt->fetch(PDO::FETCH_ASSOC)['cancelled_count'];

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/manage_bookingss.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Manage Bookings</title>
</head>
<body>
<div class="main-content">

<div class="bookings-summary">
    <div class="summary-item count-confirmed">
        <div class="count"><?php echo $confirmedCount; ?></div>
        <div class="label">Confirmed Bookings</div>
    </div>
    <div class="summary-item count-pending">
        <div class="count"><?php echo $pendingCount; ?></div>
        <div class="label">Pending Bookings</div>
    </div>
    <div class="summary-item count-cancelled">
        <div class="count"><?php echo $cancelledCount; ?></div>
        <div class="label">Cancelled Bookings</div>
    </div>
</div>

    <div class="filter-links">
        <a href="?status=all">All</a>
        <a href="?status=Pending">Pending</a>
        <a href="?status=confirmed">Confirmed</a>
        <a href="?status=cancelled">Cancelled</a>
    </div>

    <div class="container">
        <table id="bookingsTable">
        <h2>Booking List</h2>
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
            <?php if ($bookings): ?>
                <?php foreach ($bookings as $booking): ?>
                    <?php
                    // Format the dates
                    $preferred_date_start = DateTime::createFromFormat('Y-m-d', $booking['preferred_date_start'])->format('D, d M');
                    $preferred_date_end = DateTime::createFromFormat('Y-m-d', $booking['preferred_date_end'])->format('D, d M');
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['email']); ?></td>
                        <td><?php echo htmlspecialchars($booking['phone_number']); ?></td>
                        <td><?php echo $preferred_date_start; ?></td>
                        <td><?php echo $preferred_date_end; ?></td>
                        <td><?php echo htmlspecialchars($booking['preferred_time']); ?></td>
                        <td><?php echo htmlspecialchars($booking['guests']); ?></td>
                        <td>₱ <?php echo number_format(htmlspecialchars($booking['price']), 2); ?></td>
                        <td>₱ <?php echo number_format(htmlspecialchars($booking['total_payment']), 2); ?></td>
                        <td id="status-<?php echo htmlspecialchars($booking['id']); ?>">
                            <?php if (strtolower($booking['status']) === 'confirmed' || strtolower($booking['status']) === 'cancelled'): ?>
                                <span class="status-box <?php echo strtolower($booking['status']); ?>"><?php echo htmlspecialchars($booking['status']); ?></span>
                            <?php elseif ($booking['status'] === 'pending'): ?>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['id']); ?>">
                                    <button type="submit" name="action" value="confirmed" class="icon-button" title="confirm">
                                        <i class="fas fa-check icon-confirm"></i>
                                    </button>
                                    <button type="submit" name="action" value="cancelled" class="icon-button" title="cancel">
                                        <i class="fas fa-times icon-cancel"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="status-box"><?php echo htmlspecialchars($booking['status']); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No Bookings Found</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination">
            <a href="?page=1<?php echo $status_filter != 'all' ? '&status=' . urlencode($status_filter) : ''; ?>" class="pagination-link <?php echo $page == 1 ? 'disabled' : ''; ?>">First</a>
            <a href="?page=<?php echo max(1, $page - 1) . ($status_filter != 'all' ? '&status=' . urlencode($status_filter) : ''); ?>" class="pagination-link <?php echo $page == 1 ? 'disabled' : ''; ?>">Previous</a>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i . ($status_filter != 'all' ? '&status=' . urlencode($status_filter) : ''); ?>" class="pagination-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <a href="?page=<?php echo min($total_pages, $page + 1) . ($status_filter != 'all' ? '&status=' . urlencode($status_filter) : ''); ?>" class="pagination-link <?php echo $page == $total_pages ? 'disabled' : ''; ?>">Next</a>
            <a href="?page=<?php echo $total_pages . ($status_filter != 'all' ? '&status=' . urlencode($status_filter) : ''); ?>" class="pagination-link <?php echo $page == $total_pages ? 'disabled' : ''; ?>">Last</a>
        </div>


    </div>
</div>

</body>
</html>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const confirmButtons = document.querySelectorAll('.icon-confirm');
        const cancelButtons = document.querySelectorAll('.icon-cancel');

        confirmButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const form = button.closest('form');
                const bookingId = form.querySelector('[name="booking_id"]').value;

                // SweetAlert confirmation
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to confirm this booking?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Confirm',
                    cancelButtonText: 'No',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('booking_id', bookingId);
                        formData.append('action', 'confirmed');

                        const request = new XMLHttpRequest();
                        request.open('POST', '', true);
                        request.onload = function() {
                            if (request.status === 200) {
                                updateStatus(bookingId, 'confirmed');
                                // Success confirmation
                                Swal.fire({
                                    title: 'Booking Confirmed!',
                                    text: 'The booking has been successfully confirmed.',
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    customClass: 'swal2-black-success'
                                });
                            }
                        };
                        request.send(formData);
                    }
                });
            });
        });

        cancelButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const form = button.closest('form');
                const bookingId = form.querySelector('[name="booking_id"]').value;

                // SweetAlert cancellation
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to cancel this booking?',
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Confirm',
                    cancelButtonText: 'No',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('booking_id', bookingId);
                        formData.append('action', 'cancelled');

                        const request = new XMLHttpRequest();
                        request.open('POST', '', true);
                        request.onload = function() {
                            if (request.status === 200) {
                                updateStatus(bookingId, 'cancelled');
                                // Success cancellation
                                Swal.fire({
                                    title: 'Booking Cancelled!',
                                    text: 'The booking has been successfully cancelled.',
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    customClass: 'swal2-black-success'
                                });
                            }
                        };
                        request.send(formData);
                    }
                });
            });
        });
    });

    function updateStatus(bookingId, status) {
        // Create a new span element for the status
        const statusBox = document.createElement('span');
        statusBox.className = 'status-box ' + (status === 'confirmed' ? 'confirmed' : 'cancelled');
        statusBox.textContent = status.charAt(0).toUpperCase() + status.slice(1);

        // Replace the form element with the status box
        const statusCell = document.getElementById('status-' + bookingId);
        statusCell.innerHTML = ''; // Clear the cell
        statusCell.appendChild(statusBox); // Add the new status box
    }
</script>

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
</style>