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

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$rooms = [];

// Fetch all rooms for display
$stmt = $pdo->query("SELECT * FROM rooms");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count the number of rooms based on availability
$availableCount = 0;
$unavailableCount = 0;
$bookedCount = 0;
$reservedCount = 0; // New variable for reserved rooms

if ($rooms) {
    foreach ($rooms as $room) {
        if ($room['availability'] == 'Available') {
            $availableCount++;
        } elseif ($room['availability'] == 'Unavailable') {
            $unavailableCount++;
        } elseif ($room['availability'] == 'Booked') {
            $bookedCount++;
        }

        // Count reserved rooms (you can adjust this based on your room statuses or logic)
        if ($room['availability'] == 'Reserved') {
            $reservedCount++;
        }
    }
    $totalRooms = count($rooms);  // Set the total room count
} else {
    $totalRooms = 0; // Ensure $totalRooms is defined even if no rooms are fetched
}

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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./styles/dashboards.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
</head>
<body>

<div class="main-content" style="margin-left: 270px; margin-top: 60px;"> 

    <div class="dashboard">
        <div id="room-list-container" class="room-list-container">
            <h1></h1>
            <ul id="room-list">
                <?php foreach ($rooms as $room): ?>
                    <li>Room <?= htmlspecialchars($room['room_number']) ?>: <span class="<?= strtolower(htmlspecialchars($room['availability'])) ?>"><?= htmlspecialchars($room['availability']) ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Availability</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?= htmlspecialchars($room['room_number']) ?></td>
                            <td class="availability <?= strtolower(htmlspecialchars($room['availability'])) ?>"><?= htmlspecialchars($room['availability']) ?></td>
                            <td>
                                <button class="update-availability-btn" data-room="<?= htmlspecialchars($room['room_number']) ?>">Update Availability</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="chart-wrapper">
        
        <div class="room-status-container">
        <div class="label"style="text-align:center; margin-bottom:10px">ROOM CHART</div>
            <canvas id="roomStatusChart" width="250" height="250"></canvas>
        </div>

        <!-- Bookings Status Pie Chart -->
        <div class="bookings-status-container">
        <div class="label"style="text-align:center; margin-bottom:10px;">BOOKINGS</div>
            <canvas id="bookingsStatusChart" width="250" height="250"></canvas>
        </div>
    </div>



    <div class="room-summary">
        <div class="summary-container">
            <div class="summary-box available-box">
                <div class="count"><?php echo $availableCount; ?></div>
                <div class="label">Available</div>
            </div>
            <div class="summary-box unavailable-box">
                <div class="count"><?php echo $unavailableCount; ?></div>
                <div class="label">Unavailable</div>
            </div>
            <div class="summary-box booked-box">
                <div class="count"><?php echo $bookedCount; ?></div>
                <div class="label">Booked</div>
            </div>
            <div class="summary-box reserved-box">
                <div class="count"><?php echo $reservedCount; ?></div>
                <div class="label">Reserved</div>
            </div>
            <div class="summary-box total-box">
                <div class="count"><?php echo  $totalRooms; ?></div>
                <div class="label">Total Rooms</div>
            </div>
        </div>
        <div class="button-container">
            <button class="modal-button" onclick="window.location.href='manage_rooms.php';">Add Room</button>
        </div>
    </div>

    <div class="room-summary2">
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
            <div class="summary-item count-users">
                <div class="count"><?php echo $totalUsers; ?></div>
                <div class="label">Total Users</div>
            </div>
        </div>
        <div class="button-container">
            <button class="modal-button" onclick="window.location.href='manage_bookings.php';">Manage Bookings</button>
        </div>
    </div>

</div>

</body>
</html>

<script>
    document.addEventListener("DOMContentLoaded", () => {
    const updateButtons = document.querySelectorAll(".update-availability-btn");
    const summary = document.getElementById("update-summary");
    const closeBtn = document.querySelector(".close-btn");
    const saveButton = document.getElementById("save-availability");
    const availabilityOptions = document.getElementById("availability-options");
    let selectedRoom = null;

    updateButtons.forEach((button) => {
        button.addEventListener("click", (event) => {
            selectedRoom = button.getAttribute("data-room");

            // Show the modal
            summary.style.display = "block";

            // Populate the current availability option
            const currentAvailability = button.closest("tr").querySelector(".availability").textContent;
            availabilityOptions.value = currentAvailability;
        });
    });

    // Close modal
    closeBtn.addEventListener("click", () => {
        summary.style.display = "none";
    });

    // Save updated availability
    saveButton.addEventListener("click", () => {
        const newAvailability = availabilityOptions.value;

        Swal.fire({
            title: "Are you sure?",
            text: `You are about to set the room to "${newAvailability}".`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, save it!",
            customClass: {
                popup: "swal2-popup",
                title: "swal2-title",
                htmlContainer: "swal2-html-container",
                confirmButton: "swal2-confirm",
                cancelButton: "swal2-cancel"
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Update the database with the new availability
                fetch("update_room.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ room_number: selectedRoom, availability: newAvailability }),
                })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        // Update the UI
                        const availabilityCell = document.querySelector(`button[data-room='${selectedRoom}']`).closest("tr").querySelector(".availability");
                        availabilityCell.textContent = newAvailability;

                        // Show success message
                        Swal.fire({
                            title: "Updated!",
                            text: "Room availability has been updated.",
                            icon: "success",
                            customClass: {
                                popup: "swal2-popup",
                                title: "swal2-title",
                                htmlContainer: "swal2-html-container",
                                confirmButton: "swal2-confirm"
                            }
                        });

                        // Close the modal
                        summary.style.display = "none";
                    } else {
                        Swal.fire({
                            title: "Error!",
                            text: "Failed to update room availability.",
                            icon: "error",
                            customClass: {
                                popup: "swal2-popup",
                                title: "swal2-title",
                                htmlContainer: "swal2-html-container",
                                confirmButton: "swal2-confirm"
                            }
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        title: "Error!",
                        text: "An unexpected error occurred.",
                        icon: "error",
                        customClass: {
                            popup: "swal2-popup",
                            title: "swal2-title",
                            htmlContainer: "swal2-html-container",
                            confirmButton: "swal2-confirm"
                        }
                    });
                });
            }
        });
    });

    // Close the modal if clicked outside of it
    window.addEventListener("click", (event) => {
        if (event.target === summary) {
            summary.style.display = "none";
        }
    });
});


// Pie chart data for room status
const roomStatusData = {
    labels: ['Available', 'Unavailable', 'Booked', 'Reserved'],
    datasets: [{
        label: 'Room Availability',
        data: [<?php echo $availableCount; ?>, <?php echo $unavailableCount; ?>, <?php echo $bookedCount; ?>, <?php echo $reservedCount; ?>],
        backgroundColor: ['#4caf50', '#f44336', '#ffeb3b', '#2196f3'],
        borderColor: ['#4caf50', '#f44336', '#ffeb3b', '#2196f3'],
        borderWidth: 1
    }]
};

// Doughnut chart configuration for room status
const roomStatusConfig = {
    type: 'doughnut',
    data: roomStatusData,
    options: {
        responsive: true,
        animation: {
            animateScale: true,
            animateRotate: true
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: {
                        size: 14
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        const total = tooltipItem.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((tooltipItem.raw / total) * 100);
                        return `${tooltipItem.label}: ${tooltipItem.raw} (${percentage}%)`;
                    }
                }
            },
            datalabels: {
                color: '#fff',
                font: {
                    weight: 'bold',
                    size: 16
                },
                formatter: (value, ctx) => {
                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = Math.round((value / total) * 100);
                    return `${percentage}%`;
                }
            },
            // Custom plugin to display total in the center of the doughnut chart
            beforeDraw: (chart) => {
                const ctx = chart.ctx;
                const total = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                const fontSize = 20;
                ctx.font = `${fontSize}px Arial`;
                ctx.fillStyle = '#000';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(`Total: ${total}`, chart.chartArea.left + chart.chartArea.width / 2, chart.chartArea.top + chart.chartArea.height / 2);
            }
        },
        cutout: '70%' // This makes the chart a ring
    }
};

// Create the room status doughnut chart
new Chart(
    document.getElementById('roomStatusChart'),
    roomStatusConfig
);

// Pie chart data for booking status
const bookingsStatusData = {
    labels: ['Confirmed', 'Pending', 'Cancelled'],
    datasets: [{
        label: 'Booking Status',
        data: [<?php echo $confirmedCount; ?>, <?php echo $pendingCount; ?>, <?php echo $cancelledCount; ?>],
        backgroundColor: ['#4caf50', '#ffeb3b', '#f44336'],
        borderColor: ['#4caf50', '#ffeb3b', '#f44336'],
        borderWidth: 1
    }]
};

// Doughnut chart configuration for booking status
const bookingsStatusConfig = {
    type: 'doughnut',
    data: bookingsStatusData,
    options: {
        responsive: true,
        animation: {
            animateScale: true,
            animateRotate: true
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: {
                        size: 14
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        const total = tooltipItem.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((tooltipItem.raw / total) * 100);
                        return `${tooltipItem.label}: ${tooltipItem.raw} (${percentage}%)`;
                    }
                }
            },
            datalabels: {
                color: '#fff',
                font: {
                    weight: 'bold',
                    size: 16
                },
                formatter: (value, ctx) => {
                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = Math.round((value / total) * 100);
                    return `${percentage}%`;
                }
            },
            // Custom plugin to display total in the center of the doughnut chart
            beforeDraw: (chart) => {
                const ctx = chart.ctx;
                const total = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                const fontSize = 20;
                ctx.font = `${fontSize}px Arial`;
                ctx.fillStyle = '#000';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(`Total: ${total}`, chart.chartArea.left + chart.chartArea.width / 2, chart.chartArea.top + chart.chartArea.height / 2);
            }
        },
        cutout: '70%' // This makes the chart a ring
    }
};

// Create the booking status doughnut chart
new Chart(
    document.getElementById('bookingsStatusChart'),
    bookingsStatusConfig
);
</script>
