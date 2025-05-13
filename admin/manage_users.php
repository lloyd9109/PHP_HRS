<?php
ob_start();    

session_start();
require_once '../src/Auth.php';
require_once '../config/database.php';
require_once 'admin.php';
include 'header.php';

// Handle individual delete requests
if (isset($_POST['delete_user'])) {
    $email = $_POST['email'];
    $stmt = $pdo->prepare('DELETE FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    echo "<script>
        window.localStorage.setItem('deleteSuccess', 'true');
        window.location.href = '{$_SERVER['PHP_SELF']}';
    </script>";
    exit;
}

// Handle delete selected request
if (isset($_POST['delete_selected'])) {
    if (!empty($_POST['selected_users'])) {
        $emails = $_POST['selected_users'];
        $placeholders = implode(',', array_fill(0, count($emails), '?'));
        $stmt = $pdo->prepare("DELETE FROM users WHERE email IN ($placeholders)");
        $stmt->execute($emails);
    }
    echo "<script>
        window.localStorage.setItem('deleteSuccess', 'true');
        window.location.href = '{$_SERVER['PHP_SELF']}';
    </script>";
    exit;
}

// Pagination logic
$limit = 10; // Rows per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get the total number of users
$stmt = $pdo->query('SELECT COUNT(*) FROM users');
$totalRows = $stmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Fetch users for the current page
$stmt = $pdo->prepare('SELECT first_name, last_name, email, cellphone FROM users LIMIT :limit OFFSET :offset');
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="./styles/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="main-content" style="margin-left: 270px; margin-top: 60px;">
    <div class="card-container">
        <h2>Manage Users</h2>

        <!-- Container for Search Bar and Delete Button -->
        <div class="search-container">
            <input type="text" class="search-bar" id="searchInput" placeholder="Search users..." onkeyup="filterTable()">
            <button class="btn-delete-selected" type="button" id="deleteSelectedButton">Delete Selected</button>
        </div>

        <form id="userForm" method="POST" action="">
            <table id="usersTable">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = $offset + 1; // Adjust counter for the current page
                    foreach ($users as $user): ?>
                        <tr>
                            <td><input type="checkbox" name="selected_users[]" value="<?= htmlspecialchars($user['email']) ?>"></td>
                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['cellphone']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-delete" type="button" onclick="confirmDelete('<?= htmlspecialchars($user['email']) ?>')">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination Controls -->
            <div class="pagination">
                <a href="?page=1" class="pagination-link <?= $page == 1 ? 'disabled' : '' ?>">First</a>
                <a href="?page=<?= max(1, $page - 1) ?>" class="pagination-link <?= $page == 1 ? 'disabled' : '' ?>">Previous</a>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="pagination-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <a href="?page=<?= min($totalPages, $page + 1) ?>" class="pagination-link <?= $page == $totalPages ? 'disabled' : '' ?>">Next</a>
                <a href="?page=<?= $totalPages ?>" class="pagination-link <?= $page == $totalPages ? 'disabled' : '' ?>">Last</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>


<script>
// Select All Checkbox Functionality
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('#usersTable tbody input[type="checkbox"]');
    checkboxes.forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
}

// Search Functionality
function filterTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('usersTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        const td = tr[i].getElementsByTagName('td');
        let rowContainsKeyword = false;

        for (let j = 1; j < td.length - 1; j++) {
            if (td[j].textContent.toLowerCase().includes(filter)) {
                rowContainsKeyword = true;
                break;
            }
        }

        tr[i].style.display = rowContainsKeyword ? '' : 'none';
    }
}

// Show success message after page reload
document.addEventListener('DOMContentLoaded', function () {
    if (window.localStorage.getItem('deleteSuccess')) {
        Swal.fire('Deleted!', 'The user has been deleted successfully.', 'success');
        window.localStorage.removeItem('deleteSuccess');
    }
});

// Confirm Delete Individual
function confirmDelete(email) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You won\'t be able to revert this!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'email';
            input.value = email;
            form.appendChild(input);
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_user';
            form.appendChild(deleteInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Confirm Delete Selected
document.getElementById('deleteSelectedButton').addEventListener('click', function () {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You won\'t be able to revert this!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete them!',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('userForm').submit();
        }
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
    background-color: #007bff; /* Bright blue for active page */
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

.card-container {
        background-color: #191c24;
    padding: 20px;
    border-radius: 2px;
    flex: 1;
    text-align: center;
    margin-bottom: 100px;
    overflow: hidden;
}

    .card-container h2 {
        color: #00d25b;
        font-size: 28px;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
    }

    .search-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

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

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    table-layout: fixed;
}

thead {
    display: table-header-group;
    
}

    table thead th {
        background-color: #4caf50;
        color: white;
        padding: 12px;
        position: sticky;
        top: 0;
        z-index: 1;
    }
tbody {
    display: table-row-group;
    height: 300px;
    overflow-y: auto;
    width: 100%;
}

    table th, table td {
    padding: 12px;
    text-align: center; /* Center the text inside the cells */
    border-bottom: 1px solid #4caf50;
}
    table tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    table tr:hover {
        background-color: black;
    }
th:first-child, td:first-child {
    width: 40px; /* Set a smaller width for the checkbox column */
    padding: 5px; /* Reduce padding to decrease spacing */
    text-align: center; /* Center-align checkboxes */
}


    .btn-delete {
        background-color: #e74c3c;
        color: white;
        border: none;
        padding: 6px 12px;
        cursor: pointer;
        border-radius: 4px;
        transition: background-color 0.3s;
    }

    .btn-delete:hover {
        background-color: #c0392b;
    }

    .btn-delete-selected {
        background-color: #e74c3c;
        color: white;
        border: none;
        padding: 10px 20px;
        cursor: pointer;
        border-radius: 5px;
        font-size: 16px;
        transition: background-color 0.3s, transform 0.2s;
    }

    .btn-delete-selected:hover {
        background-color: #c0392b;
        transform: scale(1.05);
    }
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
    background-color: #007bff !important;
}

.swal2-styled.swal2-cancel {
    background-color: #dc3545 !important;
}

.swal2-black-success {
    background-color: #333 !important;
    color: white !important;
}

.swal2-black-success .swal2-title {
    color: #28a745 !important;
}

.swal2-black-success .swal2-content {
    color: #ddd !important;
}

.swal2-black-success .swal2-confirm {
    background-color: #28a745 !important;
    border: 1px solid #28a745 !important;
}

.swal2-black-success .swal2-confirm:hover {
    background-color: #218838 !important;
}

</style>