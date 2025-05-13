<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Sidebar</title>
</head>
<body>
    <div class="sidebar">
        <!-- Dashboard -->
        <div class="menu-item">
            <div class="icon">
                <img src="https://via.placeholder.com/20" alt="Dashboard Icon">
            </div>
            <span class="menu-title">Dashboard</span>
        </div>

        <!-- Basic UI Elements with dropdown -->
        <div class="menu-item dropdown" onclick="toggleDropdown()">
            <div class="icon">
                <img src="https://via.placeholder.com/20" alt="UI Icon">
            </div>
            <span class="menu-title">Basic UI Elements</span>
            <div class="arrow">&#9662;</div>
        </div>
        <ul class="dropdown-menu">
            <li>Buttons</li>
            <li>Dropdowns</li>
            <li>Typography</li>
        </ul>

        <div class="menu-item">
            <div class="icon">
                <img src="https://via.placeholder.com/20" alt="Dashboard Icon">
            </div>
            <span class="menu-title">Dashboard</span>
        </div>
    </div>



    <script src="script.js"></script>
</body>
</html>

<script>
    // Script to toggle the dropdown menu
// Function to toggle the dropdown menu
function toggleDropdown() {
    const dropdown = document.querySelector('.menu-item.dropdown');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    if (dropdown.classList.contains('active')) {
        // Collapse the dropdown
        dropdown.classList.remove('active');
        dropdownMenu.style.maxHeight = '0';
    } else {
        // Expand the dropdown
        dropdown.classList.add('active');
        dropdownMenu.style.maxHeight = dropdownMenu.scrollHeight + 'px'; // Dynamically adjust height
    }
}


</script>
<style>
/* Reset */
body {
    margin: 0;
    font-family: 'Arial', sans-serif;
    background-color: #1e1e2d;
    color: #fff;
}

/* Sidebar Styling */
.sidebar {
    width: 250px;
    background-color: #1e1e2d;
    height: 100vh;
    padding: 10px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Menu Item Styling */
.menu-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.menu-item:hover {
    background-color: #27293d;
}

.icon img {
    width: 20px;
    height: 20px;
    margin-right: 10px;
}

.menu-title {
    font-size: 16px;
}

.arrow {
    font-size: 12px;
    transition: transform 0.3s ease;
}

/* Dropdown Menu */
.dropdown-menu {
    list-style: none;
    padding: 0;
    margin: 0 0 0 40px;
    display: flex;
    flex-direction: column;
    gap: 5px;

    max-height: 0; /* Start collapsed */
    overflow: hidden; /* Hide overflow content */
    transition: max-height 0.5s ease-in-out; /* Smooth transition */
}

.dropdown-menu li {
    font-size: 14px;
    padding: 5px 0;
    cursor: pointer;
    transition: color 0.3s;
}

.dropdown-menu li:hover {
    color: #ffa500;
}

/* Arrow Rotation for Active Dropdown */
.menu-item.active .arrow {
    transform: rotate(180deg); /* Rotate arrow when active */
}

/* Dropdown Active State */
.menu-item.active + .dropdown-menu {
    max-height: 150px; /* Adjust to the actual height of the content */
}


</style>