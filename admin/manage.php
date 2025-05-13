<?php 
session_start();
require_once '../src/Auth.php';
require_once '../config/database.php';
require_once 'admin.php';
require_once 'room.php';

$admin = new Admin($pdo);
$room = new Room();
$rooms = $room->getAllRooms();

if (!$admin->isLoggedIn()) {
    header('Location: admin_login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $targetDir = "assets";
        $targetFile = $targetDir . basename($_FILES["image_url"]["name"]);
        move_uploaded_file($_FILES["image_url"]["tmp_name"], $targetFile);
        $_POST['image_url'] = $targetFile; // Set the image URL to the file path
        $room->addRoom($_POST);
    } elseif (isset($_POST['delete'])) {
        $room->deleteRoom($_POST['id']);
    } elseif (isset($_POST['edit'])) {
        // Handle image upload
        if (!empty($_FILES["image_url"]["name"])) {
            $targetDir = "assets";
            $targetFile = $targetDir . basename($_FILES["image_url"]["name"]);
            move_uploaded_file($_FILES["image_url"]["tmp_name"], $targetFile);
            $_POST['image_url'] = $targetFile; // Set the new image URL
        } else {
            unset($_POST['image_url']); // Do not update image if not provided
        }
        $room->editRoom($_POST);
    }
    header("Location: manage_rooms.php");
}
?>
<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/manage_rooms.css">
    <title>Manage Bookings</title>
</head>
<body>

<div class="main-content" style="margin-left: 270px; margin-top: 60px;">
    <div class="form-container">
        <h2 class="text-center">Room Management</h2>

        <!-- Form to Add/Edit Rooms -->
        <form id="roomForm" method="POST" enctype="multipart/form-data" class="mb-4">
            <input type="hidden" name="id" id="roomId">

            <div class="form-row">
                <!-- First Column -->
                <div class="form-column">
                    <div class="form-group">
                        <label for="room_name">Room Name:</label>
                        <input type="text" class="form-control" name="room_name" id="room_name" required>
                    </div>

                    <div class="form-group">
                        <label for="room_size">Room Size (sq ft):</label>
                        <input type="number" class="form-control" name="room_size" id="room_size" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea class="form-control" name="description" id="description" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="price">Price (PHP):</label>
                        <input type="number" class="form-control" step="0.01" name="price" id="price" required>
                    </div>
                </div>

                <!-- Second Column -->
                <div class="form-column">

                    <div class="form-group">
                        <label for="features">Features:</label>
                        <textarea class="form-control" name="features" id="features"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="rating">Rating:</label>
                        <input type="number" class="form-control" step="0.1" name="rating" id="rating" min="1" max="5" required>
                    </div>

                    <div class="form-group">
                        <label for="availability">Availability:</label>
                        <select class="form-control" name="availability" id="availability" required>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="image_url">Upload Image:</label>
                        <input type="file" class="form-control-file" name="image_url" id="image_url" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="add" class="btn btn-primary">Add Room</button>
                        <button type="submit" name="edit" class="btn btn-success" style="display:none;">Update Room</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Room Cards -->
    <div class="cards-container">
        <?php foreach ($rooms as $room): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="<?= $room['image_url'] ?>" class="card-img-top" alt="<?= $room['room_name'] ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= $room['room_name'] ?></h5>
                        <p class="card-text">Size: <?= $room['room_size'] ?> sq ft</p>
                        <p class="card-text"><?= $room['description'] ?></p>
                        <p class="card-text">Price: ₱<?= number_format($room['price'], 2) ?></p>
                        <p class="card-text">Rating: <?= $room['rating'] ?></p>
                        <p class="card-text">Status: <?= $room['availability'] ?></p>
                        <button class="btn btn-warning edit-btn" data-id="<?= $room['id'] ?>">Edit</button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= $room['id'] ?>">
                            <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this room?');">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="script.js">
    document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        const roomId = this.getAttribute('data-id');

        // Fetch room data from the server (you may need to create an endpoint for this)
        fetch(`get_room.php?id=${roomId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('roomId').value = data.id;
                document.getElementById('room_name').value = data.room_name;
                document.getElementById('room_size').value = data.room_size;
                document.getElementById('description').value = data.description;
                document.getElementById('price').value = data.price;
                document.getElementById('features').value = data.features;
                document.getElementById('rating').value = data.rating;
                document.getElementById('availability').value = data.availability;

                // Show the edit button and hide the add button
                document.querySelector('button[name="edit"]').style.display = 'inline-block';
                document.querySelector('button[name="add"]').style.display = 'none';
            });
    });
});

</script>

</body>
</html>


<style>
.main-content {
    margin-top: 80px; /* Adjust this value based on the header height */
    max-height: calc(100vh - 80px); /* Make content height responsive to viewport */
    overflow-y: auto; /* Enable vertical scrolling */
    padding: 20px; /* Add some padding for aesthetic spacing */
}

.form-container {
    background-color: #29293d;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 40px;
    margin-left: auto;
    margin-right: auto;
}
h2 {
    color: #00d25b;
    text-align: center;
    margin-bottom: 20px;
    font-size: 28px;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
}

.form-row {
    display: flex;
    justify-content: space-between; /* Space out the columns */
}

.form-column {
    flex: 1; /* Allow each column to take up equal space */
    padding: 0 10px; /* Add some padding between columns */
}

/* Align input fields with labels */
.form-group {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    margin-bottom: 15px;
    
}

label {
    flex: 0 0 150px; /* Label width */
    color: #b0b0b0;
    font-size: 14px;
    margin-bottom:5px;
}

/* Input fields styling */
input[type="text"],
input[type="number"],
textarea,
select {
    width: 100%; /* Make input take full width of column */
    padding: 8px 12px;
    background-color: #2c2c38;
    border: 1px solid #444;
    border-radius: 8px;
    color: #fff;
    font-size: 14px;
    max-width: 250px; /* Reduce the maximum width of inputs */
}
/* File input styling */
input[type="file"] {
    background-color: #3c3c4d;
    border: none;
    padding: 10px;
    color: #fff;
    max-width: 250px; /* Align file input with others */
}


/* On focus, give a glowing effect */
input[type="text"]:focus,
input[type="number"]:focus,
textarea:focus,
select:focus {
    outline: none;
    border-color: #00d25b;
    box-shadow: 0 0 5px rgba(0, 210, 91, 0.5);
    background-color: #2f2f3e;
}

/* Textarea for description */
textarea {
    height: 100px;
    resize: none; /* Disable resizing for a cleaner look */
    max-width: 250px; /* Align textarea with other input fields */
}


/* Buttons */
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    color: white;
    font-size: 14px;
    transition: background-color 0.3s ease;
    margin-top: 10px; /* Add some space above buttons */
}


.btn-primary {
    background-color: #007bff;
}

.btn-primary:hover {
    background-color: #0069d9;
}

.btn-success {
    background-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
}

.btn-warning {
    background-color: #ffc107;
}

.btn-warning:hover {
    background-color: #e0a800;
}

.btn-danger {
    background-color: #dc3545;
}

.btn-danger:hover {
    background-color: #c82333;
}

/* Cards Design */
.cards-container {
    margin-top: 40px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Responsive grid */
    gap: 20px;
}

.card {
    background-color: #29293d;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    overflow: hidden;
}

.card-img-top {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.card-body {
    padding: 20px;
}

.card-title {
    font-size: 20px;
    margin-bottom: 10px;
}

.card-text {
    font-size: 14px;
    color: #b0b0b0;
}



    </style>