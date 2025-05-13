<?php
include '../config/database.php';
include 'staff_portal.php';

// Set the number of results per page
$resultsPerPage = 10;

// Get the current page from the URL, or default to page 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startFrom = ($page - 1) * $resultsPerPage;

// Get the total number of rows in the database for pagination calculation
$totalQuery = "SELECT COUNT(*) FROM reserved";
$totalStmt = $pdo->prepare($totalQuery);
$totalStmt->execute();
$totalRecords = $totalStmt->fetchColumn();

// Calculate total number of pages
$totalPages = ceil($totalRecords / $resultsPerPage);

// Fetch the reservations for the current page
$query = "SELECT room_number, full_name, email, phone_number, guests, check_in_out, preferred_date_start, preferred_date_end, preferred_time, price, total_payment FROM reserved LIMIT $startFrom, $resultsPerPage";
$stmt = $pdo->prepare($query);
$stmt->execute();

$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$checkInCount = 0;
$checkOutCount = 0;
foreach ($reservations as $reservation) {
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
    <title>Staff Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./styles/process_reservation.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="content">
    <!-- Widget for displaying counts -->
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
                            <td>₱ <?php echo htmlspecialchars(number_format($reservation['price'], 2)) ?></td>
                            <td>₱ <?php echo htmlspecialchars(number_format($reservation['total_payment'], 2)) ?></td>
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

        <!-- Pagination -->
        <div class="pagination">
            <a href="?page=1" class="pagination-link <?php echo $page == 1 ? 'disabled' : ''; ?>">First</a>
            <a href="?page=<?php echo max(1, $page - 1); ?>" class="pagination-link <?php echo $page == 1 ? 'disabled' : ''; ?>">Previous</a>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="pagination-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <a href="?page=<?php echo min($totalPages, $page + 1); ?>" class="pagination-link <?php echo $page == $totalPages ? 'disabled' : ''; ?>">Next</a>
            <a href="?page=<?php echo $totalPages; ?>" class="pagination-link <?php echo $page == $totalPages ? 'disabled' : ''; ?>">Last</a>
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

.widget-container {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
    text-align: center;
    margin-top: 20px;
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

/* Search Bar Styling */
.search-container {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 30px;
    padding: 1px 10px;
    background-color: #f8f9fa;
    max-width: 300px;
    width: 80%;
}

#searchInput {
    background-color: #f8f9fa;
    border: none;
    padding: 10px;
    border-radius: 25px;
    width: 90%;
    font-size: 16px;
    outline: none;
}


.container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background-color: #ffffff; /* White background for clean look */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1); /* Soft shadow for 3D effect */
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
    width: 80px ;
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
    width: 80px ;
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
</style>
