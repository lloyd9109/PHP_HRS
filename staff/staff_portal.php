<?php
session_start();

require_once '../config/database.php';
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch staff information from the database
$staff_id = $_SESSION['staff_id'];
$sql = "SELECT first_name, last_name FROM staff WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$staff_id]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

// Store the staff's first name and last name in variables
$first_name = $staff['first_name'];
$last_name = $staff['last_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./styles/staff_portal.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <button class="btn btn-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
            <i class="bi bi-list"></i>
        </button>
        <a class="navbar-brand fw-bold ms-2" href="#">Hotel Staff Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="staff_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_reservation.php">Manage Reservation</a>
                </li>
                <!-- Staff Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo $first_name . ' ' . $last_name; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>


    <!-- Sidebar -->
    <div class="sidebar bg-dark text-white d-none d-lg-block">  
        <h4 class="fw-bold text-center py-3">Menu</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="staff_dashboard.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'staff_dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a href="manage_reservation.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'manage_reservation.php' ? 'active' : '' ?>"><i class="bi bi-calendar-check"></i> Manage Reservation</a>
            </li>
            <li class="nav-item">
            <a href="view_rooms.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'view_rooms.php' ? 'active' : '' ?>"><i class="bi bi-door-open"></i> View Rooms</a>
            </li>
            <li class="nav-item">
            <a href="booking_history.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'booking_history.php' ? 'active' : '' ?>"><i class="fas fa-history"></i> Booking History</a>
            </li>
        </ul>
    </div>

    <!-- Sidebar (Offcanvas for small screens) -->
    <div class="offcanvas offcanvas-start bg-dark text-white" id="sidebarMenu" tabindex="-1" aria-labelledby="sidebarMenuLabel">
        <div class="offcanvas-header">
            <h4 class="offcanvas-title fw-bold" id="sidebarMenuLabel">Menu</h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white active" href="staff_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="manage_bookings.php"><i class="bi bi-calendar-check"></i> Manage Bookings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="manage_rooms.php"><i class="bi bi-door-open"></i> Manage Rooms</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Include Bootstrap JS and Icons -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</body>
</html>



<style>
body {
    font-family: 'Arial', sans-serif;
    background-color: #d5deef;
    padding-top: 56px; /* Adjust this value to match your navbar's height */
}

.navbar {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1030; /* Ensures the navbar stays above other elements */
}
.navbar-brand {
    font-size: 1.5rem;
    font-weight: bold;
}
/* Dropdown Styling */
.navbar .dropdown-menu {
    border-radius: 5px; /* Rounded corners for the dropdown */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Light shadow */
    background-color: #343a40; /* Dark background color for the dropdown */
    border: 1px solid #495057; /* Border to match the navbar */
}

/* Dropdown Toggle (Staff Name) */
.navbar .dropdown-toggle {
    color: #fff; /* White text color */
    font-weight: bold; /* Bold text */
    font-size: 1rem; /* Adjust font size */
    padding: 10px 15px; /* Adjust padding */
    background-color: transparent; /* Transparent background */
    border: none; /* No border */
    transition: color 0.3s ease; /* Smooth transition for hover effect */
}

.navbar .dropdown-toggle:hover {
    color: #0f969c; /* Change color on hover */
    text-decoration: underline; /* Underline on hover */
}

/* Dropdown Items */
.navbar .dropdown-item {
    color: #fff; /* White text color for items */
    padding: 8px 15px; /* Padding for each item */
    font-size: 0.9rem; /* Slightly smaller font size */
    border-radius: 5px; /* Rounded corners */
    transition: background-color 0.3s ease; /* Smooth transition for hover effect */
}

.navbar .dropdown-item:hover {
    background-color: #495057; /* Change background color on hover */
    color: #fff; /* Ensure text stays white on hover */
}

/* Hover on active dropdown item */
.navbar .dropdown-item.active {
    background-color: #0f969c; /* Green background for active item */
    color: #fff; /* White text color */
}


/* Sidebar */
.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    top: 10x;
    left: 0;
    overflow-y: auto;
    box-shadow: 2px 0 6px rgba(0, 0, 0, 0.2);
}
.sidebar .nav-link {
    font-size: 1rem;
    padding: 10px 15px;
    border-radius: 4px;
    transition: background-color 0.3s ease, color 0.3s ease;
}
.sidebar .nav-link:hover {
    background-color: #495057;
    color: #ffffff;
}
.sidebar .nav-link.active {
    background-color: #0f969c;
    color: #ffffff;
    font-weight: bold;
}

/* Content Area */
.content {
    margin-left: 250px; /* Matches sidebar width */
    padding: 20px;
}

@media (max-width: 768px) {
    .sidebar {
        display: none;
    }
    .content {
        margin-left: 0;
    }
}

</style>