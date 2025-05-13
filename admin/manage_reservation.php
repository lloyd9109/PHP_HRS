<?php
session_start();
require_once '../config/database.php';
require_once 'admin.php';

ob_start();
include 'header.php';

$admin = new Admin($pdo);

if (!$admin->isLoggedIn()) {
    header('Location: admin_login.php');
    exit();
}

// Define number of records per page
$records_per_page = 10;

// Get current page or set to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $records_per_page;

// Query to fetch data including 'preferred_date_start' and 'preferred_date_end' with LIMIT for pagination
$query = "SELECT room_number, full_name, email, phone_number, guests, check_in_out, preferred_date_start, preferred_date_end, preferred_time, price, total_payment FROM reserved LIMIT :start_from, :records_per_page";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':start_from', $start_from, PDO::PARAM_INT);
$stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();

// Fetch reservations for the current page
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of reservations to calculate the total pages
$total_query = "SELECT COUNT(*) FROM reserved";
$total_stmt = $pdo->prepare($total_query);
$total_stmt->execute();
$total_records = $total_stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./styles/process_reservation.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Manage Reservation</title>
</head>
<body>
    
<div class="main-content" > 

        <div class="container">
            <h2>Reservations</h2>

            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search reservations..." onkeyup="searchTable()">
            </div>

            <table id="reservationsTable">
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
                        <th>Check-in-out</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reservations): ?>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reservation['room_number']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['email']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['phone_number']); ?></td>
                                <td>
                                    <?php 
                                        $preferred_date_start = DateTime::createFromFormat('Y-m-d', $reservation['preferred_date_start']);
                                        echo $preferred_date_start ? $preferred_date_start->format('D, d M') : 'N/A';
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        $preferred_date_end = DateTime::createFromFormat('Y-m-d', $reservation['preferred_date_end']);
                                        echo $preferred_date_end ? $preferred_date_end->format('D, d M') : 'N/A';
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($reservation['preferred_time']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['guests']); ?></td>
                                <td>₱ <?php echo number_format(htmlspecialchars($reservation['price']), 2); ?></td>
                                <td>₱ <?php echo number_format(htmlspecialchars($reservation['total_payment']), 2); ?></td>
                                <td>
                                <?php
                                if ($reservation['check_in_out'] == 'check-in') {
                                    echo '<div class="button-group">';
                                    echo '<button class="btn check-in-btn" data-room="'.$reservation['room_number'].'">Check In</button>';
                                    echo '<button class="btn cancel-btn" data-room="'.$reservation['room_number'].'">Cancel</button>';
                                    echo '</div>';
                                } elseif ($reservation['check_in_out'] == 'check-out') {
                                    echo '<button class="btn check-out-btn" data-room="'.$reservation['room_number'].'">Check Out</button>';
                                } elseif ($reservation['check_in_out'] == 'complete') {
                                    echo '<span style="color: green; font-weight: bold;">Complete</span>';
                                } elseif ($reservation['check_in_out'] == 'cancelled') {
                                    // Display a non-clickable "Cancelled" button
                                    echo '<button class="btn cancelled-btn" disabled>Cancelled</button>';
                                } else {
                                    echo htmlspecialchars($reservation['check_in_out']);
                                }
                                ?>
                            </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align: center;">No reservations found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
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


<script>
function searchTable() {
var input, filter, table, tr, td, i, j, txtValue;
    input = document.getElementById("searchInput");
    filter = input.value.toLowerCase();
    table = document.getElementById("reservationsTable");
    tr = table.getElementsByTagName("tr");

    for (i = 1; i < tr.length; i++) {
        tr[i].style.display = "none"; // Hide the row initially
        td = tr[i].getElementsByTagName("td");
        for (j = 0; j < td.length; j++) {
            if (td[j]) {
                txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                    break; // If a match is found, no need to check further columns
                }
            }
        }
    }
}
$(document).ready(function() {
    // Event listener for check-in button
    $('.check-in-btn').click(function() {
        var roomNumber = $(this).data('room');
        
        // Show confirmation dialog
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to check in this reservation?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, check-in!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Update the button text and class
                $(this).removeClass('check-in-btn').addClass('check-out-btn').text('Check Out');

                // Send the update request to the server
                $.ajax({
                    url: 'update_checkin_checkout.php',
                    type: 'POST',
                    data: { room_number: roomNumber, status: 'check-out' },
                    success: function(response) {
                        // Show success message
                        Swal.fire('Success!', 'Reservation has been updated to check-out.', 'success').then(() => {
                            // Refresh the page after successful update
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire('Error!', 'There was an issue updating the reservation.', 'error');
                    }
                });
            }
        });
    });

    // Event listener for check-out button
    $('.check-out-btn').click(function() {
        var roomNumber = $(this).data('room');
        
        // Show confirmation dialog
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to check out this reservation?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, check-out!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Replace the button with text 'Complete'
                $(this).replaceWith('<span>Complete</span>');

                // Send the update request to the server
                $.ajax({
                    url: 'update_checkin_checkout.php',
                    type: 'POST',
                    data: { room_number: roomNumber, status: 'complete' },
                    success: function(response) {
                        // Show success message
                        Swal.fire('Success!', 'Reservation has been marked as complete.', 'success').then(() => {
                            // Refresh the page after successful update
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire('Error!', 'There was an issue updating the reservation.', 'error');
                    }
                });
            }
        });
    });
});
$(document).ready(function() {
    // Event listener for cancel button
    $('.cancel-btn').click(function() {
        var roomNumber = $(this).data('room');
        
        // Show confirmation dialog
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to cancel this reservation?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel it!',
            cancelButtonText: 'No, keep it',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Update the button or display text to reflect cancellation
                $(this).closest('tr').find('td:last').html('<span style="color: red; font-weight: bold;">Cancelled</span>');

                // Send the cancelation request to the server
                $.ajax({
                    url: 'update_checkin_checkout.php',
                    type: 'POST',
                    data: { room_number: roomNumber, status: 'cancelled' },
                    success: function(response) {
                        Swal.fire('Cancelled!', 'The reservation has been marked as cancelled.', 'success').then(() => {
                            // Optionally, refresh the page or update the table dynamically
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire('Error!', 'There was an issue cancelling the reservation.', 'error');
                    }
                });
            }
        });
    });
});
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
.main-content {
    margin-left: 270px;
    margin-top: 60px;
    height: 100vh;
    overflow-y: auto;
    padding: 20px;
}
.main-content::-webkit-scrollbar {
    width: 6px;
}

.main-content::-webkit-scrollbar-track {
    background: #2c2c2c;
}

.main-content::-webkit-scrollbar-thumb {
    background: #4a4a4a;
    border-radius: 10px;
}

.main-content::-webkit-scrollbar-thumb:hover {
    background: #757575;
}



/* Glowing effect on focus */
#searchInput:focus {
    outline: none; /* Remove default focus outline */
    border-color: #218838; /* Darker green when focused */
    box-shadow: 0 0 8px rgba(40, 167, 69, 0.7); /* Green glow effect */
}

#searchInput {
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: black;
    border: 1px solid #4caf50;
    border-radius: 30px;
    padding: 10px;
    border-radius: 25px;
    max-width: 300px;
    width: 80%;
    font-size: 16px;
    outline: none;
    color: white;
}

.container {
    background-color: #191c24;
    padding: 20px;
    border-radius: 2px;
    flex: 1;
    text-align: center;
    margin-bottom: 100px;
    overflow: hidden;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    table-layout: fixed; 
}
thead {
    display: table-header-group; /* Ensures proper alignment with tbody */
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

/* Button Styles */
.btn {
    padding: 8px 14px;
    font-size: 12px;
    border: none;
    cursor: pointer;
    border-radius: 3px;
    transition: background-color 0.3s ease;
    text-align: center;
}

/* Check-In Button */
.check-in-btn {
    background-color: #28a745; /* Green for check-in */
    color: white;
    width: 90px ;
}

.check-in-btn:hover {
    background-color: #218838;
}

/* Check-Out Button */
.check-out-btn {
    background-color: #dc3545; /* Red for check-out */
    color: white;
    width: 90px ;
}

.check-out-btn:hover {
    background-color: #c82333;
}

.btn.cancel-btn {
    
    text-align: center;
    background-color: #ff4d4d; 
    color: white;
    border: none;
    padding: 8px 14px;
    margin-top: 4px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
    width: 90px ;
}

.btn.cancel-btn:hover {
    background-color: #cc0000; /* Darker red on hover */
}

.btn.cancelled-btn {
    background-color:#cc0000; /* Gray background */
    color: white; /* White text */
    cursor: not-allowed; /* Disable pointer cursor */
}

/* Style for Complete text */
.complete-text {
    color: #6c757d; /* Gray for completed status */
    font-weight: bold;
    font-size: 16px;
}

/* Responsiveness */
@media (max-width: 768px) {
    th, td {
        padding: 8px;
        font-size: 12px;
    }

    table {
        font-size: 12px;
    }
}

    /* Custom black theme for SweetAlert */
    .swal2-popup {
        background-color: #333 !important;
        color: #fff !important;
        border-radius: 10px !important;
    }

    .swal2-title {
        color: #fff !important;
    }

    .swal2-content {
        color: #ddd !important;
    }

    .swal2-confirm, .swal2-cancel {
        background-color: #444 !important;
        color: #fff !important;
        border: 1px solid #666 !important;
    }

    .swal2-confirm:hover, .swal2-cancel:hover {
        background-color: #555 !important;
        border-color: #777 !important;
    }

    .swal2-styled.swal2-confirm {
        background-color: #007bff !important; /* Customize confirm button color */
    }

    .swal2-styled.swal2-cancel {
        background-color: #dc3545 !important; /* Customize cancel button color */
    }

    /* Black themed success message */
    .swal2-black-success {
        background-color: #333 !important;  /* Black background */
        color: white !important;
    }

    .swal2-black-success .swal2-title {
        color: #28a745 !important;  /* Green color for success title */
    }

    .swal2-black-success .swal2-content {
        color: #ddd !important;
    }

    .swal2-black-success .swal2-confirm {
        background-color: #28a745 !important;  /* Green button */
        border: 1px solid #28a745 !important;
    }

    .swal2-black-success .swal2-confirm:hover {
        background-color: #218838 !important;  /* Darker green on hover */
    }
</style>
