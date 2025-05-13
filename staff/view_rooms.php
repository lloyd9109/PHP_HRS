<?php
include '../config/database.php';
include 'staff_portal.php';

// Pagination settings
$limit = 10; // Number of rooms per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get the selected filter value from the request
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Modify the query based on the filter
$query = "SELECT room_name, room_number, room_size, description, price, availability, features, guests FROM rooms";
if ($filter && $filter !== 'All') {
    $query .= " WHERE availability = :filter";
}
$query .= " LIMIT :limit OFFSET :offset"; // Add LIMIT and OFFSET for pagination

$stmt = $pdo->prepare($query);

// Bind the filter value if a specific filter is selected
if ($filter && $filter !== 'All') {
    $stmt->bindParam(':filter', $filter);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of rows for pagination calculation
$totalQuery = "SELECT COUNT(*) FROM rooms";
if ($filter && $filter !== 'All') {
    $totalQuery .= " WHERE availability = :filter";
}
$totalStmt = $pdo->prepare($totalQuery);
if ($filter && $filter !== 'All') {
    $totalStmt->bindParam(':filter', $filter);
}
$totalStmt->execute();
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Summary counts
$summaryQuery = "SELECT availability, COUNT(*) as count FROM rooms GROUP BY availability";
$summaryStmt = $pdo->prepare($summaryQuery);
$summaryStmt->execute();
$summary = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);
$availabilityCounts = [];
foreach ($summary as $item) {
    $availabilityCounts[$item['availability']] = $item['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Rooms</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
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

    <div class="container">
        <h2>Rooms Availability</h2>

        <!-- Filter Buttons -->
        <form method="GET" action="" class="filter-bar">
            <button type="submit" name="filter" value="All" class="<?php echo $filter === 'All' || !$filter ? 'active' : ''; ?>">All</button>
            <button type="submit" name="filter" value="Available" class="<?php echo $filter === 'Available' ? 'active' : ''; ?>">Available</button>
            <button type="submit" name="filter" value="Unavailable" class="<?php echo $filter === 'Unavailable' ? 'active' : ''; ?>">Unavailable</button>
            <button type="submit" name="filter" value="Booked" class="<?php echo $filter === 'Booked' ? 'active' : ''; ?>">Booked</button>
            <button type="submit" name="filter" value="Reserved" class="<?php echo $filter === 'Reserved' ? 'active' : ''; ?>">Reserved</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Room Type</th>
                    <th>Room No.</th>
                    <th>Room Size</th>
                    <th>Price</th>
                    <th>Availability</th>
                    <th>Features</th>
                    <th>Guests</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($rooms) {
                    foreach ($rooms as $room) {
                        $availabilityStyle = '';
                        switch ($room['availability']) {
                            case 'Available':
                                $availabilityStyle = 'color: green; font-weight: bold;';
                                break;
                            case 'Unavailable':
                                $availabilityStyle = 'color: red; font-weight: bold;';
                                break;
                            case 'Booked':
                                $availabilityStyle = 'color: orange; font-weight: bold;';
                                break;
                            case 'Reserved':
                                $availabilityStyle = 'color: blue; font-weight: bold;';
                                break;
                        }
                        echo "<tr>
                                <td>{$room['room_name']}</td>
                                <td>{$room['room_number']}</td>
                                <td>{$room['room_size']}</td>
                                <td>₱" . number_format($room['price'], 2) . "</td>
                                <td style='{$availabilityStyle}'>{$room['availability']}</td>
                                <td>{$room['features']}</td>
                                <td>{$room['guests']}</td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No rooms available</td></tr>";
                }
                ?>
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
.filter-bar {
    margin-bottom: 20px;
    text-align: center;
}

.filter-bar button {
    margin: 0 5px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: bold;
    border: none;
    border-radius: 4px;
    background-color: #0f969c;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.filter-bar button:hover {
    background-color: #0c7075;
    transform: translateY(-2px);
}

.filter-bar button.active {
    background-color: #294d61;
    color: #fff;
    font-weight: bold;
}

.container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background-color: #ffffff; /* White background for clean look */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1); /* Soft shadow for 3D effect */
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

/* Optional: Add a responsive design for smaller screens */
@media (max-width: 768px) {
    table, th, td {
        font-size: 14px;
        padding: 10px;
    }

    table th {
        font-size: 16px;
    }
}

</style>