<?php
    session_start();
    require_once '../config/database.php';
    require_once 'admin.php';
    include 'header.php';

    $admin = new Admin($pdo);

    if (!$admin->isLoggedIn()) {
        header('Location: admin_login.php');
        exit();
    }

// Handle Add Room
if (isset($_POST['add'])) {
    $room_type = $_POST['room_type'];
    $room_size = $_POST['room_size'];
    $price = $_POST['price'];
    $rating = $_POST['rating'];
    $availability = $_POST['availability'];
    $description = $_POST['description'];
    $features = isset($_POST['features']) ? implode(", ", $_POST['features']) : '';
    $amenities = isset($_POST['amenities']) ? implode(", ", $_POST['amenities']) : '';


    // Handle image upload
    $image_url = '';
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
        $target_dir = "../assets/img_url"; // Make sure this directory exists and is writable
        $image_url = $target_dir . basename($_FILES["image_url"]["name"]);
        move_uploaded_file($_FILES["image_url"]["tmp_name"], $image_url);
    }

    // Get the next available room number (increment highest existing one)
    $stmt = $pdo->query("SELECT MAX(CAST(room_number AS UNSIGNED)) AS max_room_number FROM rooms");
    $maxRoomNumber = $stmt->fetchColumn();
    
    // If no rooms exist yet, start from '001'
    $nextRoomNumber = str_pad(($maxRoomNumber + 1) ?: 1, 3, '0', STR_PAD_LEFT);

    // Insert new room with generated room number
    $stmt = $pdo->prepare("INSERT INTO rooms (room_name, room_number, room_size, price, rating, availability, description, features, amenities, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$room_type, $nextRoomNumber, $room_size, $price, $rating, $availability, $description, $features, $amenities, $image_url]);
    

    // Redirect to avoid resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit; // Ensure no further code is executed
}

// Handle Edit Room
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $room_type = $_POST['room_type']; // Changed from room_name to room_type
    $room_size = $_POST['room_size'];
    $price = $_POST['price'];
    $rating = $_POST['rating'];
    $availability = $_POST['availability'];
    $description = $_POST['description'];
    $features = isset($_POST['features']) ? implode(", ", $_POST['features']) : '';
    $amenities = isset($_POST['amenities']) ? implode(", ", $_POST['amenities']) : '';


    // Check for new image upload
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
        $target_dir = "../assets/img_url"; // Make sure this directory exists and is writable
        $image_url = $target_dir . basename($_FILES["image_url"]["name"]);
        move_uploaded_file($_FILES["image_url"]["tmp_name"], $image_url);
    } else {
        // Keep the existing image URL if no new image is uploaded
        $stmt = $pdo->prepare("SELECT image_url FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        $image_url = $stmt->fetchColumn(); // Retain existing image URL
    }

    $stmt = $pdo->prepare("UPDATE rooms SET room_name = ?, room_size = ?, price = ?, rating = ?, availability = ?, description = ?, features = ?, amenities = ?, image_url = ? WHERE id = ?");
    $stmt->execute([$room_type, $room_size, $price, $rating, $availability, $description, $features, $amenities, $image_url, $id]);
    

    // Redirect after editing
    header("Location: " . $_SERVER['PHP_SELF']);
    exit; // Ensure no further code is executed
}

// Handle Delete Room
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    
    // Delete the selected room
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->execute([$id]);

    // Renumber the remaining rooms after deletion
    $stmt = $pdo->query("SELECT id, room_number FROM rooms ORDER BY CAST(room_number AS UNSIGNED)");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $newRoomNumber = 1; // Start renumbering from 1
    foreach ($rooms as $room) {
        $stmtUpdate = $pdo->prepare("UPDATE rooms SET room_number = ? WHERE id = ?");
        $stmtUpdate->execute([str_pad($newRoomNumber, 3, '0', STR_PAD_LEFT), $room['id']]);
        $newRoomNumber++;
    }

    // Redirect after deleting and renumbering
    header("Location: " . $_SERVER['PHP_SELF']);
    exit; // Ensure no further code is executed
}


    $rooms = [];

    // Fetch all rooms for display
    $stmt = $pdo->query("SELECT * FROM rooms");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count the number of rooms based on availability
    $availableCount = 0;
    $unavailableCount = 0;
    $bookedCount = 0;

    if ($rooms) {
        foreach ($rooms as $room) {
            if ($room['availability'] == 'Available') {
                $availableCount++;
            } elseif ($room['availability'] == 'Unavailable') {
                $unavailableCount++;
            } elseif ($room['availability'] == 'Booked') {
                $bookedCount++;
            }
        }
        $totalRooms = count($rooms);  // Set the total room count
    } else {
        $totalRooms = 0; // Ensure $totalRooms is defined even if no rooms are fetched
    }

    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="./styles/manage_rooms.css">
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Include Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Include Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <title>Room Management</title>
    </head>
    <body>

 <div class="main-content" style="margin-left: 270px; margin-top: 60px;">

        <div class="room-summary">
            <div class="summary-container">
                <div class="summary-box available-box">
                    <h4>Available</h4>
                    <p><?= $availableCount ?></p>
                </div>
                <div class="summary-box unavailable-box">
                    <h4>Unavailable</h4>
                    <p><?= $unavailableCount ?></p>
                </div>
                <div class="summary-box booked-box">
                    <h4>Booked</h4>
                    <p><?= $bookedCount ?></p>
                </div>
                <div class="summary-box total-box">
                    <h4>Total Rooms</h4>
                    <p><?= $totalRooms ?></p>
                </div>
            </div>

            <!-- Button to trigger modal -->
            <div class="button-container">
                <button id="addRoomBtn" class="modal-button">Add Room</button>
            </div>
        </div>


        <!-- Form Container as Modal -->
        <div id="formModal" class="modal">
            <div class="modal-content"style="margin-left: 300px; margin-top: 100px;">
                <span class="close">&times;</span>
                <div class="form-container">
        <h2 class="text-center">Add / Edit Room</h2>
        <form id="roomForm" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <input type="hidden" name="id" id="roomId">
            <div class="form-layout">
                <div class="form-column">

                    <div class="form-group">
                        <label for="room_type">Room Type:</label>
                        <select class="form-control" name="room_type" id="room_type" >
                            <option value="" disabled selected>Select Room Type</option>
                            <option value="Single">Single</option>
                            <option value="Double">Double</option>
                            <option value="Suite">Suite</option>
                            <option value="Deluxe">Deluxe</option>
                            <option value="Family">Family</option>
                            <!-- Add more room types as needed -->
                        </select>
                    </div>


                    <div class="form-group">
                        <label for="room_number">Room Number:</label>
                        <input type="number" class="form-control no-arrow" name="room_number" id="room_number" readonly>
                    </div>


                    <div class="form-group">
                        <label for="room_size">Room Size (sq ft):</label>
                        <input type="number" class="form-control no-arrow" name="room_size" id="room_size" >
                    </div>

                    <div class="form-group">
                        <label for="price">Price (PHP):</label>
                        <input type="number" class="form-control no-arrow" step="0.01" name="price" id="price" >
                    </div>

                    <div class="form-group">
                        <label for="rating">Rating:</label>
                        <input type="number" class="form-control no-arrow" step="0.1" name="rating" id="rating" min="1" max="5" >
                    </div>

                    <div class="form-group">
                        <label for="availability">Availability:</label>
                        <select class="form-control" name="availability" id="availability" >
                            <option value="" disabled selected>Select Availability</option>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                            <option value="Booked">Booked</option>
                        </select>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <input type="text" class="form-control" name="description" id="description" >
                    </div>

                    <div class="form-group">
    <label for="features" class="form-label">Features:</label>
    <select class="form-control styled-select" name="features[]" id="features" multiple="multiple">
        <option value="Free Wi-Fi">Free Wi-Fi</option>
        <option value="Swimming Pool">Swimming Pool</option>
        <option value="Gym">Gym</option>
        <option value="Restaurant">Restaurant</option>
        <option value="Room Service">Room Service</option>
        <option value="Spa">Spa</option>
        <option value="Parking">Parking</option>
        <option value="Conference Room">Conference Room</option>
    </select>
</div>

<div class="form-group">
    <label for="amenities" class="form-label">Amenities:</label>
    <select class="form-control styled-select" name="amenities[]" id="amenities" multiple="multiple">
        <option value="Air Conditioning">Air Conditioning</option>
        <option value="Mini-Bar">Mini-Bar</option>
        <option value="Balcony">Balcony</option>
        <option value="Kitchenette">Kitchenette</option>
        <option value="Television">Television</option>
        <option value="Coffee Maker">Coffee Maker</option>
        <option value="In-room Safe">In-room Safe</option>
        <option value="Hair Dryer">Hair Dryer</option>
    </select>
</div>


                    <div class="form-group">
                        <label for="image_url">Upload Image:</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="image_url" id="image_url" accept="image/*" style="display: none;" >
                            <button type="button" id="uploadBtn" class="upload-btn">Upload Image</button>
                        </div>
                        <div id="previewContainer" style="display: none; position: relative; text-align: center;">
                            <img id="preview" src="" alt="Image Preview" style="margin-top: 10px; max-width: 200px; max-height: 200px; border-radius: 5px;"/>
                            <button id="removeBtn" style="position: absolute; top: 0; right: 0; background: none; border: none; color: red; font-size: 20px; cursor: pointer;">&times;</button>
                        </div>
                        <div id="error-message" style="color: red; display: none;"></div>
                    </div>

                </div>
            </div>
            <div class="group-button"  style="display: flex; justify-content: center; margin-top: 20px;">
                <button type="submit" class="submit-button btn btn-primary" name="add">Add Room</button>
                <button type="submit" class="submit-button btn btn-success" name="edit" style="display:none;">Update Room</button>
            </div>
            <div id="formValidationMessage" style="color: red; display: none; text-align: center; margin-top: 10px;">
                All fields must be filled out.
            </div>
        </form>
    </div>

            </div>
        </div>

<div class="cards-container">
    <?php if ($rooms): ?>
        <?php foreach ($rooms as $room): ?>
            <div class="col-md-4 mb-4">
                <div class="card <?= strtolower($room['availability']) ?>-border" onclick="toggleDetails(this)">
                    <img src="<?= $room['image_url'] ?>" class="card-img-top" alt="<?= $room['room_name'] ?>" onclick="openFullScreen('<?= $room['image_url'] ?>')">
                    <div class="card-body">
                        <h5 class="card-title">Room <?= $room['room_number'] ?></h5>
                        <p class="card-text">Room Type: <?= $room['room_name'] ?></p>
                        <p class="card-text">Price: PHP <?= number_format($room['price'], 2) ?> /night</p>
                        <p class="card-text">Size: <?= $room['room_size'] ?> sq ft</p>
                        <p class="card-text">Rating: <?= $room['rating'] ?> / 5</p>
                        <p class="card-text">Availability: <?= $room['availability'] ?></p>
                        <p class="card-text">Description: <?= $room['description'] ?></p>
                        <p class="card-text">Features: <?= $room['features'] ?></p>
                        <p class="card-text">Amenities: <?= $room['amenities'] ?></p>

                    </div>
                    <div class="button-group">
                        <button class="btn edit-btn" 
                            data-id="<?= $room['id'] ?>" 
                            data-room_type="<?= htmlspecialchars($room['room_name']) ?>" 
                            data-room_number="<?= $room['room_number'] ?>" 
                            data-room_size="<?= $room['room_size'] ?>" 
                            data-price="<?= $room['price'] ?>" 
                            data-rating="<?= $room['rating'] ?>" 
                            data-availability="<?= $room['availability'] ?>" 
                            data-description="<?= htmlspecialchars($room['description']) ?>" 
                            data-features="<?= htmlspecialchars($room['features']) ?>"
                            data-amenities="<?= htmlspecialchars($room['amenities']) ?>"
                            data-image_url="<?= $room['image_url'] ?>"> 
                            Edit
                        </button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $room['id'] ?>">
                            <button type="submit" name="delete" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this room?');">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No rooms available.</p>
    <?php endif; ?>
</div>

    <div id="fullScreenImageContainer" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.9); z-index: 9999; justify-content: center; align-items: center;">
        <span id="closeImage" style="position: absolute; top: 20px; right: 30px; color: white; font-size: 30px; cursor: pointer;">&times;</span>
        <img id="fullScreenImage" src="" alt="Full Screen" style="max-width: 90%; max-height: 90%; object-fit: contain;">
    </div>
</div>



    <script src="manage_roomss.js"></script>
<style>

.select2-container {
    width: 100% !important; /* Set width to 100% of the parent container */
}

/* Change background color for the dropdown */
.custom-dropdown .select2-results__options {
    background-color: black; /* Change to any color you like */
}

/* Customize the appearance of selected tags */
.select2-selection__choice {
    background-color: #007bff; /* Tag background color */
    color: white;              /* Text color */
}

/* Customize the appearance of the "..more" text */
.select2-selection__choice:contains("..more") {
    background-color: #6cd757;  /* Background color for "..more" */
    color: white;               /* Text color for "..more" */
}


    
   /* Style for the Select2 dropdown */
   .select2-container .select2-selection--multiple {
        background-color: #2f2f3e; /* Light background color */
        border: 1px solid #444;
        border-radius: 5px; /* Rounded corners */
        padding: 8px; /* Padding inside the dropdown */
        font-size: 15px; /* Font size */
        
    }

   /* When the Select2 input is focused */
    .select2-container--focus .select2-selection--multiple {
        border-color: #007bff; /* Blue border on focus */
        box-shadow: 0 0 5px rgba(0, 210, 91, 0.5);
    }

    /* Style for the dropdown tags */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #29293d; /* Blue background for selected items */
        border: 1px solid #00d25b; /* Blue border for selected items */
        color: white; /* White text for selected items */
        border-radius: 4px; /* Rounded corners for the tags */
        padding: 3px 8px; /* Padding inside the tags */
        margin: 3px 5px 3px 0; /* Spacing between tags */
        font-size: 10px; /* Font size for tags */
    }

    /* Style for hover effect on the selected tags */
    .select2-container--default .select2-selection--multiple .select2-selection__choice:hover {
        background-color: #00d25b; 
        border-color: #00d25b; 
    }
    
    /* Custom styles for Select2 dropdown */
.select2-container--default .select2-selection--multiple {
    border: 1px solid none; /* Border color */
    border-radius: 0.25rem; /* Rounded corners */
    padding: 0.5rem; /* Padding */
}

/* Dropdown styles */
.select2-container--default .select2-results {
    max-height: 200px; /* Maximum height for dropdown */
    overflow-y: auto; /* Enable vertical scrolling */
    overflow-x: hidden; /* Disable horizontal scrolling */
}

/* Scrollbar styles */
.select2-container--default .select2-results::-webkit-scrollbar {
    width: 8px; /* Width of the scrollbar */
}

.select2-container--default .select2-results::-webkit-scrollbar-thumb {
    background-color: #007bff; /* Scrollbar color */
    border-radius: 10px; /* Rounded scrollbar */
}

.select2-container--default .select2-results::-webkit-scrollbar-thumb:hover {
    background-color: #0056b3; /* Darker color on hover */
}

/* For Firefox */
.select2-container--default .select2-results {
    scrollbar-width: thin; /* Make scrollbar thin */
    scrollbar-color: #007bff #f0f0f0; /* Scrollbar color and background */
}

#formModal {
    overflow: hidden; /* Prevent scrollbars */
}
</style>

    </body>
    </html>
