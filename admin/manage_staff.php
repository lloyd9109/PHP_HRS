<?php
ob_start();    

session_start();
require_once '../src/Auth.php';
require_once '../config/database.php';
require_once 'admin.php';
include 'header.php';

// Set the number of records per page
$recordsPerPage = 10;

// Get the current page number from the URL, default to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;

// Get the search term from the query string
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base query for staff data and total records count
$query = "SELECT * FROM staff";
$totalQuery = "SELECT COUNT(*) FROM staff";

// Modify the query if search term exists
if ($search) {
    $searchWildcard = "%$search%";
    $query .= " WHERE first_name LIKE :search OR last_name LIKE :search OR email LIKE :search";
    $totalQuery .= " WHERE first_name LIKE :search OR last_name LIKE :search OR email LIKE :search";
}

// Add pagination to the query
$query .= " LIMIT :offset, :recordsPerPage";

// Prepare and execute the staff data query
$stmt = $pdo->prepare($query);
if ($search) {
    $stmt->bindParam(':search', $searchWildcard, PDO::PARAM_STR);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':recordsPerPage', $recordsPerPage, PDO::PARAM_INT);
$stmt->execute();
$staff = $stmt->fetchAll();

// Prepare and execute the total records count query
$totalStmt = $pdo->prepare($totalQuery);
if ($search) {
    $totalStmt->bindParam(':search', $searchWildcard, PDO::PARAM_STR);
}
$totalStmt->execute();
$totalRecords = $totalStmt->fetchColumn();
$totalPages = ceil($totalRecords / $recordsPerPage);

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.5.0/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.5.0/dist/sweetalert2.min.css">
</head>
<body>
<div class="main-content">
    <div class="container" >
        <h1>Manage Staff</h1>
        <!-- Button to Trigger Create Staff Modal -->
        <button id="createStaffBtn" class="btn btn-primary">Create Staff Account</button>
        
        <!-- Container for Search Bar and Delete Button -->
        <div class="search-container" style="margin-top: 20px;">
            <input type="text" class="search-bar" id="searchInput" placeholder="Search staff..." onkeyup="filterTable()">
            <button id="deleteSelectedBtn" class="btn-delete-selected">Delete Selected</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll" onclick="toggleCheckboxes()"></th>

                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $counter = 1; foreach ($staff as $staffs): ?>
                    <tr data-staff-id="<?php echo $staffs['id']; ?>">
                        <td><input type="checkbox" class="selectRow"></td>
                        
                        <td><?php echo htmlspecialchars($staffs['first_name'] . ' ' . $staffs['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($staffs['email']); ?></td>
                        <td><button class="deleteBtn btn btn-danger">Delete</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <a href="?page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="pagination-link <?php echo $page == 1 ? 'disabled' : ''; ?>">First</a>
            <a href="?page=<?php echo max(1, $page - 1) . ($search ? '&search=' . urlencode($search) : ''); ?>" class="pagination-link <?php echo $page == 1 ? 'disabled' : ''; ?>">Previous</a>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i . ($search ? '&search=' . urlencode($search) : ''); ?>" class="pagination-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <a href="?page=<?php echo min($totalPages, $page + 1) . ($search ? '&search=' . urlencode($search) : ''); ?>" class="pagination-link <?php echo $page == $totalPages ? 'disabled' : ''; ?>">Next</a>
            <a href="?page=<?php echo $totalPages . ($search ? '&search=' . urlencode($search) : ''); ?>" class="pagination-link <?php echo $page == $totalPages ? 'disabled' : ''; ?>">Last</a>
        </div>


    </div>
</div>

<!-- Modal -->
<div id="modalOverlay" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Create Staff Account</span>
            <button class="modal-close" id="modalCloseBtn">&times;</button>
        </div>
        <form id="createStaffForm">
            <div class="modal-body">
                <div class="form-group">
                    <label for="firstName"></label>
                    <input type="text" id="firstName" name="first_name" placeholder="First Name">
                    <span class="error-message" id="firstNameError"></span>
                </div>
                <div class="form-group">
                    <label for="lastName"></label>
                    <input type="text" id="lastName" name="last_name" placeholder="Last Name" >
                    <span class="error-message" id="lastNameError"></span>
                </div>
                <div class="form-group">
                    <label for="email"></label>
                    <input type="email" id="email" name="email" placeholder="Email" >
                    <span class="error-message" id="emailError"></span>
                </div>
                <div class="form-group">
                    <label for="password"></label>
                    <input type="password" id="password" name="password" placeholder="Password" >
                    <span class="error-message" id="passwordError"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="modalCancelBtn">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Staff</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>



<script>
    const createStaffBtn = document.getElementById('createStaffBtn');
    const modalOverlay = document.getElementById('modalOverlay');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const modalCancelBtn = document.getElementById('modalCancelBtn');

    createStaffBtn.addEventListener('click', () => {
        modalOverlay.classList.add('active');
    });

    const closeModal = () => {
        modalOverlay.classList.remove('active');
    };

    modalCloseBtn.addEventListener('click', closeModal);
    modalCancelBtn.addEventListener('click', closeModal);

    modalOverlay.addEventListener('click', (event) => {
        if (event.target === modalOverlay) {
            closeModal();
        }
    });

    function validateForm() {
        let isValid = true;

        const firstName = document.getElementById('firstName');
        const lastName = document.getElementById('lastName');
        const email = document.getElementById('email');
        const password = document.getElementById('password');

        const firstNameError = document.getElementById('firstNameError');
        const lastNameError = document.getElementById('lastNameError');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');

        // Reset error messages
        firstNameError.textContent = '';
        lastNameError.textContent = '';
        emailError.textContent = '';
        passwordError.textContent = '';

        // Validate each field
        if (!firstName.value.trim()) {
            firstNameError.textContent = 'First name is required.';
            isValid = false;
        }

        if (!lastName.value.trim()) {
            lastNameError.textContent = 'Last name is required.';
            isValid = false;
        }

        if (!email.value.trim()) {
            emailError.textContent = 'Email is required.';
            isValid = false;
        } else if (!/\S+@\S+\.\S+/.test(email.value)) {
            emailError.textContent = 'Please enter a valid email address.';
            isValid = false;
        }

        if (!password.value.trim()) {
            passwordError.textContent = 'Password is required.';
            isValid = false;
        }

        return isValid;
    }

    document.getElementById('createStaffForm').addEventListener('submit', function (event) {
        event.preventDefault();

        if (!validateForm()) {
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to create this staff account?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, create it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(this);

                fetch('createStaffHandler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data === 'success') {
                        Swal.fire({
                            title: 'Success',
                            text: 'Staff account created successfully!',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else if (data === 'email_exists') {
                        document.getElementById('emailError').textContent = 'Email already exists. Please use a different email.';
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'There was an issue creating the staff account.',
                            icon: 'error',
                            confirmButtonText: 'Try Again'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Something went wrong. Please try again later.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            }
        });
    });

    // Function to toggle all checkboxes
    function toggleCheckboxes() {
        const checkboxes = document.querySelectorAll('.selectRow');
        checkboxes.forEach(checkbox => checkbox.checked = document.getElementById('selectAll').checked);
    }

    // Delete button click event for each row
    document.querySelectorAll('.deleteBtn').forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            const staffId = row.getAttribute('data-staff-id'); // Get the staff ID from the data attribute
            
            // Show confirmation before deleting
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to delete the staff from the database
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'deleteStaff.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            if (xhr.responseText === 'success') {
                                // Remove the row from the table
                                row.remove();
                                Swal.fire(
                                    'Deleted!',
                                    'The staff member has been deleted.',
                                    'success'
                                );
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'There was an issue deleting the staff member.',
                                    'error'
                                );
                            }
                        }
                    };
                    xhr.send('staff_id=' + staffId);
                }
            });
        });
    });

    // Delete Selected button click event
    document.getElementById('deleteSelectedBtn').addEventListener('click', function () {
        const selectedIds = [];
        const checkboxes = document.querySelectorAll('.selectRow:checked'); // Get all checked checkboxes
        checkboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const staffId = row.getAttribute('data-staff-id');
            selectedIds.push(staffId); // Add the staff ID to the array
        });

        if (selectedIds.length > 0) {
            // Show confirmation before deleting selected staff
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to delete the selected staff from the database
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'deleteSelectedStaff.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            if (xhr.responseText === 'success') {
                                // Remove the selected rows from the table
                                selectedIds.forEach(id => {
                                    const row = document.querySelector(`tr[data-staff-id='${id}']`);
                                    if (row) {
                                        row.remove();
                                    }
                                });
                                Swal.fire(
                                    'Deleted!',
                                    'Selected staff members have been deleted.',
                                    'success'
                                );
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'There was an issue deleting the selected staff members.',
                                    'error'
                                );
                            }
                        }
                    };
                    xhr.send('staff_ids=' + JSON.stringify(selectedIds)); // Send the array of selected IDs
                }
            });
        } else {
            Swal.fire(
                'No selection!',
                'Please select at least one staff member to delete.',
                'info'
            );
        }
    });

document.getElementById('searchInput').addEventListener('input', function () {
    const searchValue = this.value.trim();
    const xhr = new XMLHttpRequest();
    const page = new URLSearchParams(window.location.search).get('page') || 1;

    xhr.open('GET', `?page=${page}&search=${encodeURIComponent(searchValue)}`, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(xhr.responseText, 'text/html');
            const tbody = doc.querySelector('table tbody');
            const pagination = doc.querySelector('.pagination');

            // Update the table and pagination dynamically
            document.querySelector('table tbody').innerHTML = tbody.innerHTML;
            document.querySelector('.pagination').innerHTML = pagination.innerHTML;
        }
    };
    xhr.send();
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

.error-message {
    color: red;
    font-size: 0.9em;
    margin-top: 5px;
}
/* Modal Background */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6); /* Slightly darker for better contrast */
    z-index: 1000;
}

/* Modal Container */
.modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 80%; /* Responsive width */
    max-width: 400px; /* Max width for desktop */
    background-color: #0d1117; /* Dark black background */
    border-radius: 12px; /* Softer corners */
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.8); /* Stronger shadow for contrast */
    z-index: 1001;
    overflow: hidden; /* Prevent overflow of child elements */
    animation: fadeIn 0.3s ease-out; /* Subtle animation */
    color: #c9d1d9; /* Light text for readability */
}

/* Modal Header */
.modal-header {
    background-color: #161b22; /* Darker black for distinction */
    color: #c9d1d9; /* Light text for contrast */
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #30363d; /* Subtle divider */
}

.modal-title {
    font-size: 18px;
    font-weight: bold;
}

.modal-close {
    font-size: 20px;
    cursor: pointer;
    border: none;
    background: none;
    color: #c9d1d9;
    transition: color 0.2s;
}

.modal-close:hover {
    color: #f85149; /* Red hover effect */
}

/* Modal Body */
.modal-body {
    padding: 20px;
    font-size: 14px;
    color: #8b949e; /* Muted light text for contrast */
}

/* Form Group */
.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 8px;
    color: #c9d1d9;
}

input {
    width: 100%;
    padding: 10px 12px;
    font-size: 14px;
    border: 1px solid #30363d; /* Subtle border */
    border-radius: 6px;
    background-color: #161b22; /* Input background matches modal */
    color: #c9d1d9; /* Light text */
    transition: border-color 0.2s;
    box-sizing: border-box;
}

input:focus {
    border-color: #00d25b;
    outline: none;
    box-shadow: 0 0 5px rgba(0, 210, 91, 0.5);
}

/* Modal Footer */
.modal-footer {
    background-color: #161b22; /* Matches header for consistency */
    padding: 16px 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px; /* Spacing between buttons */
    border-top: 1px solid #30363d; /* Subtle divider */
}

/* Buttons */
.btn {
    padding: 10px 20px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    border-radius: 6px;
    border: none;
    transition: background-color 0.2s, transform 0.2s;
}

.btn-primary {
    background-color: #238636; /* Green for primary actions */
    color: white;
}

.btn-primary:hover {
    background-color: #2ea043; /* Brighter green */
    transform: scale(1.05); /* Slight grow effect */
}

.btn-secondary {
    background-color: #6e7681; /* Muted gray */
    color: white;
}

.btn-secondary:hover {
    background-color: #8b949e; /* Brighter gray */
}

/* Show Modal */
.modal-overlay.active {
    display: block;
}

/* Fade-in animation */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}


.main-content {
    margin-left: 270px;
    margin-top: 60px;
    height: 100vh;
    overflow-y: auto;
    padding: 40px 20px;
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

.container {
    background-color: #191c24;
    padding: 20px;
    border-radius: 2px;
    flex: 1;
    text-align: center;
    margin-bottom: 100px;
    overflow: hidden;
}
.container h1 {
    color: #00d25b;
    font-size: 28px;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
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

tbody {
    display: table-row-group;
    height: 300px;
    width: 100%;
}

thead, tbody tr {
    display: table;
    width: 100%;
    table-layout: fixed;
}

th, td {
    font-size: 16px;
    padding: 10px;
    border-bottom: 1px solid #4CAF50; /* Green border for contrast */
    word-wrap: break-word;
    color: #f5f5f5; /* Light text for better readability */
}


th {
    color: white;
    text-align: center;
    background-color: #4caf50;
    padding: 12px;
    position: sticky;
    top: 0;
    z-index: 1;
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


.deleteBtn {
    background-color: #e74c3c;
    color: white;
    border: none;
    padding: 6px 12px;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.deleteBtn:hover {
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
