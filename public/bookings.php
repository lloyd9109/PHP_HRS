<?php
ob_start(); 
require_once '../src/Auth.php';
include 'header.php';
require_once '../config/database.php';

// Check if the user is logged in
if (isset($_SESSION['user_email'])) {
    $userEmail = $_SESSION['user_email']; // Get the logged-in user's email

    // Check if a status is set in the URL
    $statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all'; // Default to 'all'

    // Pagination settings
    $items_per_page = 10; // Number of items per page
    $current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $current_page = max($current_page, 1); // Ensure page is at least 1
    $offset = ($current_page - 1) * $items_per_page;

    // Modify the query based on the status filter and pagination
    if ($statusFilter == 'all') {
        $query = "SELECT SQL_CALC_FOUND_ROWS id, room_name, room_number, preferred_date_start, preferred_date_end, status, price, total_payment 
                  FROM bookings 
                  WHERE email = :email 
                  LIMIT $items_per_page OFFSET $offset";
    } else {
        $query = "SELECT SQL_CALC_FOUND_ROWS id, room_name, room_number, preferred_date_start, preferred_date_end, status, price, total_payment 
                  FROM bookings 
                  WHERE email = :email AND status = :status 
                  LIMIT $items_per_page OFFSET $offset";
    }

    // Prepare and execute the query using PDO
    $stmt = $pdo->prepare($query);
    if ($statusFilter == 'all') {
        $stmt->execute(['email' => $userEmail]);
    } else {
        $stmt->execute(['email' => $userEmail, 'status' => $statusFilter]);
    }

    // Fetch the results
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get the total number of rows
    $total_items_stmt = $pdo->query("SELECT FOUND_ROWS()");
    $total_items = $total_items_stmt->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);
} else {
    $result = [];
    $statusFilter = 'all'; 
    $total_pages = 0;
    $current_page = 1;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['booking_id']) && isset($_SESSION['user_email'])) {
    $bookingId = $_POST['booking_id'];

    try {
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND status = 'pending'");
        $stmt->execute([$bookingId]);
        $_SESSION['swal_message'] = [
            'type' => 'success',
            'text' => 'Booking successfully cancelled!'
        ];
    } catch (PDOException $e) {
        $_SESSION['swal_message'] = [
            'type' => 'error',
            'text' => 'Failed to cancel booking. Please try again.'
        ];
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_booking_id']) && isset($_SESSION['user_email'])) {
    $deleteBookingId = $_POST['delete_booking_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ? AND email = ?");
        $stmt->execute([$deleteBookingId, $userEmail]);
        $_SESSION['swal_message'] = [
            'type' => 'success',
            'text' => 'Booking successfully deleted!'
        ];
    } catch (PDOException $e) {
        $_SESSION['swal_message'] = [
            'type' => 'error',
            'text' => 'Failed to delete booking. Please try again.'
        ];
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

$current_page_file = basename($_SERVER['PHP_SELF'], ".php");

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/booking.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>My Bookings</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            <h2>Your Reservations</h2>

            <div class="booking-history-link">
                <a href="booking_history.php" class="booking-history-btn">
                    <i class="fas fa-history"></i> View Booking History
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="floating-message <?= strpos($_SESSION['message'], 'successfully') !== false ? 'success' : 'error' ?>">
                <?= $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

            <div class="filter-bar">
                <a href="?status=all" class="<?= $statusFilter == 'all' ? 'active' : '' ?>">All</a>
                <a href="?status=pending" class="<?= $statusFilter == 'pending' ? 'active' : '' ?>">Pending</a>
                <a href="?status=confirmed" class="<?= $statusFilter == 'confirmed' ? 'active' : '' ?>">Confirmed</a>
                <a href="?status=cancelled" class="<?= $statusFilter == 'cancelled' ? 'active' : '' ?>">Cancelled</a>
            </div>
        <div class="table-container">
            <table>
            <thead>
                <tr>
                    <th>Room Name</th>
                    <th>Room Number</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Price</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($result) > 0) {
                    foreach ($result as $row) {
                        // Format the dates
                        $preferredDateStart = date('D, d M', strtotime($row['preferred_date_start']));
                        $preferredDateEnd = date('D, d M', strtotime($row['preferred_date_end']));
                ?>
                <tr>
                    <td data-label="Room Name"><?php echo htmlspecialchars($row['room_name']); ?></td>
                    <td data-label="Room Number"><?php echo htmlspecialchars($row['room_number']); ?></td>
                    <td data-label="Check-in"><?php echo $preferredDateStart; ?></td>
                    <td data-label="Check-out"><?php echo $preferredDateEnd; ?></td>
                    <td data-label="Price">₱ <?php echo htmlspecialchars(number_format($row['price'], 2)); ?></td>
                    <td data-label="Payment">₱ <?php echo htmlspecialchars(number_format($row['total_payment'], 2)); ?></td>
                    <td data-label="Status" class='status <?php echo htmlspecialchars($row['status']); ?>'>
                        <?php echo htmlspecialchars($row['status']); ?>
                    </td>
                    <td data-label="Action">
                        <div class="action-buttons">
                            <?php if ($row['status'] == 'pending') { ?>
                                <form method="POST" action="bookings.php" id="cancel-form-<?php echo $row['id']; ?>">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                    <button type="button" class="cancel-btn" onclick="confirmCancellation(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-times-circle"></i> Cancel
                                    </button>
                                </form>
                            <?php } ?>
                            <?php if ($row['status'] != 'confirmed') { ?>
                                <form method="POST" action="bookings.php" id="delete-form-<?php echo $row['id']; ?>">
                                    <input type="hidden" name="delete_booking_id" value="<?php echo $row['id']; ?>">
                                    <button type="button" class="delete-btn" onclick="confirmDeletion(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='8' class='no-bookings'>No bookings found. Start planning your stay today!</td></tr>";
                }
                ?>
            </tbody>
            </table>
        </div>

            <div class="pagination">
                <a href="?page=1<?php echo $statusFilter != 'all' ? '&status=' . urlencode($statusFilter) : ''; ?>" class="pagination-link <?php echo $current_page == 1 ? 'disabled' : ''; ?>">First</a>
                <a href="?page=<?php echo max(1, $current_page - 1) . ($statusFilter != 'all' ? '&status=' . urlencode($statusFilter) : ''); ?>" class="pagination-link <?php echo $current_page == 1 ? 'disabled' : ''; ?>">Previous</a>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i . ($statusFilter != 'all' ? '&status=' . urlencode($statusFilter) : ''); ?>" class="pagination-link <?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <a href="?page=<?php echo min($total_pages, $current_page + 1) . ($statusFilter != 'all' ? '&status=' . urlencode($statusFilter) : ''); ?>" class="pagination-link <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">Next</a>
                <a href="?page=<?php echo $total_pages . ($statusFilter != 'all' ? '&status=' . urlencode($statusFilter) : ''); ?>" class="pagination-link <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">Last</a>
            </div>

        </section>
    </main>
</body>

<footer>
    <?php include 'footer.php'; ?>
</footer>

</html>



<script>
    function confirmCancellation(bookingId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to cancel this booking?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            cancelButtonText: 'No',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('cancel-form-' + bookingId).submit();
            }
        });
    }

    function confirmDeletion(bookingId) {
        // First, check the booking status before deletion
        fetch('check_booking_status.php', {
            method: 'POST',
            body: JSON.stringify({ bookingId: bookingId }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'pending') {
                Swal.fire({
                    title: 'Cancel Booking First',
                    text: "You need to cancel this booking before deleting it.",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Cancel Booking',
                    cancelButtonText: 'No, Keep Booking',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('cancel-form-' + bookingId).submit();
                    }
                });
            } else if (data.status === 'confirmed') {
                Swal.fire({
                    title: 'Booking Confirmed',
                    text: "This booking cannot be deleted as it has already been confirmed.",
                    icon: 'error',
                    confirmButtonText: 'Okay'
                });
            } else {
                // Proceed with deletion if status is neither 'pending' nor 'confirmed'
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to delete this booking? This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + bookingId).submit();
                    }
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: "An error occurred while checking the booking status.",
                icon: 'error',
                confirmButtonText: 'Okay'
            });
        });
    }
</script>

<?php if (isset($_SESSION['swal_message'])): ?>
    <script>
        Swal.fire({
            icon: '<?= $_SESSION['swal_message']['type'] ?>',
            title: '<?= $_SESSION['swal_message']['text'] ?>',
            showConfirmButton: true,
            timer: 3000
        });
    </script>
    <?php unset($_SESSION['swal_message']); ?>
<?php endif; ?>

<style>

.pagination {
    display: flex;
    justify-content: center;
    flex-wrap: wrap; /* Allows wrapping to a new line */
    margin-top: 20px;
    padding: 10px; /* Adds padding to the container */
    gap: 5px; /* Adds spacing between links */
}

.pagination-link {
    padding: 8px 12px; /* Slightly smaller padding for better responsiveness */
    margin: 0 3px;
    border: 1px solid #444; /* Dark border */
    text-decoration: none;
    color: #ccc; /* Lighter text color for dark theme */
    background-color: #072e33; /* Dark background */
    border-radius: 5px; /* Optional: adds rounded corners */
    transition: background-color 0.3s, color 0.3s;
    font-size: 14px; /* Slightly smaller font size */
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

@media screen and (max-width: 768px) {
    .pagination {
        flex-direction: row; /* Stack items vertically on smaller screens */
        flex-wrap: wrap;
        gap: 8px; /* Increase spacing for better touch targets */
    }

    .pagination-link {
        padding: 10px; /* Larger padding for easier touch on small screens */
        font-size: 12px; /* Adjust font size */
    }
}

@media screen and (max-width: 480px) {
    .pagination {
        flex-direction: column; /* Stack links vertically */
        align-items: center; /* Center align pagination links */
    }

    .pagination-link {
        width: 100%; /* Full width for links */
        text-align: center;
        padding: 12px;
        margin: 2px 0; /* Add vertical spacing */
        font-size: 14px; /* Slightly larger font for readability */
    }
}

</style>
