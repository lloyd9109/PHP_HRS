<?php
ob_start();    

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
    $availability = $_POST['availability'];
    $description = $_POST['description'];
    $features = isset($_POST['features']) ? implode(", ", $_POST['features']) : '';
    $amenities = isset($_POST['amenities']) ? implode(", ", $_POST['amenities']) : '';
    $guests = $_POST['guests'];

    // Handle image upload
    $image_url = '';
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['image_url']['name'], PATHINFO_EXTENSION));
        if (in_array($file_extension, $allowed_extensions)) {
            $target_dir = "../assets/img_url/";
            $image_url = $target_dir . basename($_FILES["image_url"]["name"]);
            move_uploaded_file($_FILES["image_url"]["tmp_name"], $image_url);
        } else {
            echo "<script>alert('Invalid file type. Please upload an image.');</script>";
            exit;
        }
    } else {
        echo "<script>alert('Image upload failed. Please try again.');</script>";
        exit;
    }

    // Fetch the next available room number for the selected room type
    $prefixMap = [
        "Standard" => "SD",
        "Deluxe" => "DX",
        "Suite" => "ST",
        "Superior" => "SPR",
        "Family" => "FM"
    ];

    $prefix = isset($prefixMap[$room_type]) ? $prefixMap[$room_type] : '';
    $stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING(room_number, LENGTH(?) + 1) AS UNSIGNED)) AS max_room_number FROM rooms WHERE room_name = ?");
    $stmt->execute([$prefix, $room_type]);
    $maxRoomNumber = $stmt->fetchColumn();

    $nextRoomNumber = str_pad(($maxRoomNumber + 1) ?: 1, 2, '0', STR_PAD_LEFT);
    $roomNumber = $prefix . $nextRoomNumber;

    // Insert new room with the generated room number
    $stmt = $pdo->prepare("INSERT INTO rooms (room_name, room_number, room_size, price, availability, description, features, amenities, image_url, guests) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$room_type, $roomNumber, $room_size, $price, $availability, $description, $features, $amenities, $image_url, $guests]);

    // Redirect to avoid resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit; 
}


// Handle Edit Room
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $room_type = $_POST['room_type']; 
    $room_size = $_POST['room_size'];
    $price = $_POST['price'];
    $availability = $_POST['availability'];
    $description = $_POST['description'];
    $features = isset($_POST['features']) ? implode(", ", $_POST['features']) : '';
    $amenities = isset($_POST['amenities']) ? implode(", ", $_POST['amenities']) : '';
    $guests = $_POST['guests'];


    // Check for new image upload
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
        $target_dir = "../assets/img_url"; 
        $image_url = $target_dir . basename($_FILES["image_url"]["name"]);
        move_uploaded_file($_FILES["image_url"]["tmp_name"], $image_url);
    } else {
        // Keep the existing image URL if no new image is uploaded
        $stmt = $pdo->prepare("SELECT image_url FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        $image_url = $stmt->fetchColumn(); 
    }

    $stmt = $pdo->prepare("UPDATE rooms SET room_name = ?, room_size = ?, price = ?, availability = ?, description = ?, features = ?, amenities = ?, image_url = ?, guests = ? WHERE id = ?");
    $stmt->execute([$room_type, $room_size, $price, $availability, $description, $features, $amenities, $image_url, $guests, $id]);
    

    // Redirect after editing
    header("Location: " . $_SERVER['PHP_SELF']);
    exit; 
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

    $newRoomNumber = 1; 
    foreach ($rooms as $room) {
        $stmtUpdate = $pdo->prepare("UPDATE rooms SET room_number = ? WHERE id = ?");
        $stmtUpdate->execute([str_pad($newRoomNumber, 3, '0', STR_PAD_LEFT), $room['id']]);
        $newRoomNumber++;
    }

    // Redirect with a success flag
    header("Location: " . $_SERVER['PHP_SELF'] );
    exit; 
}


    $rooms = [];

    // Fetch all rooms for display
    $stmt = $pdo->query("SELECT * FROM rooms");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count the number of rooms based on availability
    $availableCount = 0;
    $unavailableCount = 0;
    $bookedCount = 0;
    $reservedCount = 0;

    if ($rooms) {
        foreach ($rooms as $room) {
            if ($room['availability'] == 'Available') {
                $availableCount++;
            } elseif ($room['availability'] == 'Unavailable') {
                $unavailableCount++;
            } elseif ($room['availability'] == 'Booked') {
                $bookedCount++;
            } elseif ($room['availability'] == 'Reserved') {
                $reservedCount++;
            }
        }
        $totalRooms = count($rooms);  
    } else {
        $totalRooms = 0; 
    }
    
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/manage_roomss.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <title>Room Management</title>
</head>
<body>

 <div class="main-content" style="margin-left: 270px; margin-top: 60px;">

        <div class="dashboard">
            <div id="room-list-container" class="room-list-container">
                
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

    <div id="update-summary" class="summary-availability">
        <div class="summary-content">
            <button class="close-btn">&times;</button>
            <h2>Update Room Availability</h2>
            <select id="availability-options">
                <option value="Available">Available</option>
                <option value="Unavailable">Unavailable</option>
                <option value="Booked">Booked</option>
                <option value="Reserved">Reserved</option>
            </select>
            <button id="save-availability">Save</button>
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
                    <div class="count"><?php echo  $reservedCount; ?></div>
                    <div class="label">Reserved</div>
                </div>
                <div class="summary-box total-box">
                    <div class="count"><?php echo  $totalRooms;?></div>
                    <div class="label">Total Rooms</div>
                </div>
            </div>

            <div class="button-container">
                <button id="addRoomBtn" class="modal-button">Add Room</button>
            </div>
        </div>


        <div id="formModal" class="modal">
            <div class="modal-content"style="margin-left: 300px; margin-top: 100px;">
                <span class="close">&times;</span>
                <div class="form-container">
        <h2 class="text-center"></h2>
        <form id="roomForm" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <input type="hidden" name="id" id="roomId">
            <div class="form-layout">
                <div class="form-column">

                    <div class="form-group">
                        <label for="room_type">Room Type:</label>
                        <select class="form-control" name="room_type" id="room_type" >
                            <option value="" disabled selected>Select Room Type</option>
                            <option value="Standard">Standard</option>
                            <option value="Deluxe">Deluxe</option>
                            <option value="Suite">Suite</option>
                            <option value="Superior">Superior</option>
                            <option value="Family">Family</option>
                        </select>
                    </div>
                    <div id="room_type_error" class="error-message" style="color: red;"></div>

                    <div class="form-group">
                        <label for="guests">Number of Guests:</label>
                        <select class="form-control" name="guests" id="guests">
                            <option value="" disabled selected>Select Number of Guests</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                    <div id="guests_error" class="error-message" style="color: red;"></div>


                    <div class="form-group">
                        <label for="room_number">Room Number:</label>
                        <input type="text" class="form-control no-arrow" name="room_number" id="room_number" readonly>
                    </div>


                    <div class="form-group">
                        <label for="room_size">Room Size (sq ft):</label>
                        <input type="number" class="form-control no-arrow" name="room_size" id="room_size"  placeholder="Room Size">
                    </div>
                    <div id="room_size_error" class="error-message" style="color: red;"></div>

                    <div class="form-group">
                        <label for="price">Price (PHP):</label>
                        <input type="number" class="form-control no-arrow" step="0.01" name="price" id="price" placeholder="Room Price" >
                    </div>
                    <div id="price_error" class="error-message" style="color: red;"></div>

                    <div class="form-group">
                        <label for="availability">Availability:</label>
                        <select class="form-control" name="availability" id="availability">
                            <option value="" disabled selected>Select Availability</option>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                            <option value="Booked">Booked</option>
                            <option value="Reserved">Reserved</option>
                        </select>
                    </div>
                    <div id="availability_error" class="error-message" style="color: red;"></div>

                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <input type="text" class="form-control" name="description" id="description"  placeholder="Description">
                    </div>
                    <div id="description_error" class="error-message" style="color: red;"></div>

                <div class="form-group">
                    <label for="features" class="form-label">Features:</label>
                    <select class="form-control styled-select" name="features[]" id="features" multiple="multiple">
                        <option value="King Bed">King Bed</option>
                        <option value="Queen Bed">Queen Bed</option>
                        <option value="Sofa">Sofa</option>
                        <option value="Work Desk">Work Desk</option>
                        <option value="Gym">Gym</option>
                        <option value="Room Service">Room Service</option>
                        <option value="Spa">Spa</option>
                        <option value="Pet-Friendly Rooms">Pet-Friendly Rooms</option>
                        <option value="Mountain View">Mountain View</option>
                        <option value="Ocean View">Ocean View</option>
                        <option value="City View">City View</option>
                    </select>
                </div>
                <div id="features_error" class="error-message" style="color: red;"></div>

                <div class="form-group">
                    <label for="amenities" class="form-label">Amenities:</label>
                    <select class="form-control styled-select" name="amenities[]" id="amenities" multiple="multiple">
                        <option value="Free Wi-Fi">Free Wi-Fi</option>
                        <option value="Air Conditioning">Air Conditioning</option>
                        <option value="Bathroom Amenities">Bathroom Amenities</option>
                        <option value="Coffee Kit">Coffee Kit</option>
                        <option value="Beverage and Dining">Beverage and Dining</option>
                        <option value="Entertainment">Entertainment</option>
                        <option value="Comfort Items">Comfort Items</option>
                        <option value="Housekeeping Service">Housekeeping Service</option>
                        
                    </select>
                </div>
                <div id="amenities_error" class="error-message" style="color: red;"></div>


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
                    </div>
                    <div id="image_url_error" class="error-message" style="color: red;"></div>

                </div>
            </div>
            <div class="group-button"  style="display: flex; justify-content: center; margin-top: 20px;">
                <button type="submit" class="submit-button btn btn-primary" name="add">Add Room</button>
                <button type="submit" class="submit-button btn btn-success" name="edit" style="display:none;">Update Room</button>
            </div>

        </form>
    </div>

            </div>
        </div>
                        
        <div class="filter-bar">
            <label>Filter Room:</label>
            <div class="filter-nav">
                <button class="filter-btn" data-room-type="all">All</button>
                <button class="filter-btn" data-room-type="Standard">Standard</button>
                <button class="filter-btn" data-room-type="Deluxe">Deluxe</button>
                <button class="filter-btn" data-room-type="Suite">Suite</button>
                <button class="filter-btn" data-room-type="Superior">Superior</button>
                <button class="filter-btn" data-room-type="Family">Family</button>
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
                        <p class="card-text">Guests: <?= $room['guests'] ?> person(s)</p>
                        <p class="card-text">Price: ₱ <?= number_format($room['price'], 2) ?> /night</p>
                        <p class="card-text">Size: <?= $room['room_size'] ?> sq ft</p>
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
                            data-guests="<?= htmlspecialchars($room['guests']) ?>" 
                            data-room_size="<?= $room['room_size'] ?>" 
                            data-price="<?= $room['price'] ?>" 
                            data-availability="<?= $room['availability'] ?>" 
                            data-description="<?= htmlspecialchars($room['description']) ?>" 
                            data-features="<?= htmlspecialchars($room['features']) ?>"
                            data-amenities="<?= htmlspecialchars($room['amenities']) ?>"
                            data-image_url="<?= $room['image_url'] ?>"> 
                            Edit
                        </button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $room['id'] ?>">
                            <button type="button" class="btn delete-btn" onclick="confirmDelete(<?= $room['id'] ?>)">Delete</button>
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

<script src="manage_rooms.js"></script>

<script>

document.querySelectorAll('.filter-btn').forEach(button => {
    button.addEventListener('click', function () {
        const selectedType = this.getAttribute('data-room-type').toLowerCase();
        const cards = document.querySelectorAll('.cards-container .card');

        cards.forEach(card => {
            const roomType = card.querySelector('.card-text:nth-child(2)').textContent.toLowerCase();
            if (selectedType === 'all' || roomType.includes(selectedType)) {
                card.parentElement.style.display = ''; // Show card
            } else {
                card.parentElement.style.display = 'none'; // Hide card
            }
        });
    });
});

// Check for delete success in URL and show success alert
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('delete_success')) {
        Swal.fire({
            title: 'Deleted!',
            text: 'The room has been deleted successfully.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
        });
    }
};

function confirmDelete(roomId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',  // Red confirmation button
        cancelButtonColor: '#f39c12',  // Yellow cancel button
        confirmButtonText: 'Confirm',
        cancelButtonText: 'Cancel',
        background: '#333', // Dark background
        color: '#fff', // Light text color
        customClass: {
            popup: 'dark-theme-popup',
            title: 'dark-theme-title',
            content: 'dark-theme-content',
            confirmButton: 'dark-theme-button',
            cancelButton: 'dark-theme-button'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // If confirmed, submit the form to delete the room
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = ''; // Post to the same page
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'id';
            input.value = roomId;
            form.appendChild(input);
            
            let deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete';
            form.appendChild(deleteInput);

            document.body.appendChild(form);
            form.submit(); // Submit the form to delete the room
        }
    });
}

document.getElementById('roomForm').onsubmit = function (event) {
    event.preventDefault(); // Prevent immediate form submission

    const form = event.target; // Get the form element
    const isAdd = form.querySelector('button[name="add"]').style.display !== 'none';
    const isEdit = form.querySelector('button[name="edit"]').style.display !== 'none';

    // Determine action text
    let actionText = isAdd ? "add this new room" : "update this room";
    let buttonText = isAdd ? "Confirm" : "Confirm";

    // SweetAlert Confirmation with Dark Theme
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to ${actionText}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: buttonText,
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'swal-dark-popup',
            title: 'swal-dark-title',
            confirmButton: 'swal-dark-confirm-button',
            cancelButton: 'swal-dark-cancel-button',
        },
        background: '#2c2c2c', // Dark background
        color: '#ffffff', // White text
    }).then((result) => {
        if (result.isConfirmed) {
            if (validateForm()) {
                // Determine the action and dynamically append it
                const actionField = document.createElement('input');
                actionField.type = 'hidden';
                actionField.name = isAdd ? 'add' : 'edit';
                actionField.value = isAdd ? 'add' : 'edit';
                form.appendChild(actionField);

                // Submit the form
                form.submit();
            }
        }
    });
};

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

</script>

<style>
.filter-bar {
    display: flex;
    justify-content: flex-start; /* Align items to the left */
    align-items: center; /* Vertically align items in the center */
    margin-bottom: 20px;
    padding: 12px 20px;
    background-color: #191c24; /* Dark background for the filter bar */
    border-radius: 2px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3); /* Subtle shadow for a sleek look */
    gap: 10px; /* Add space between items */
}

.filter-nav {
    display: flex;
    gap: 12px;
    margin-left: 10px; /* Add space between the label and filter buttons */
}

.filter-bar label {
    font-size: 18px;
    font-weight: bold;
    color: #d4af37;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-family: 'Helvetica', sans-serif;
    margin-right: 10px; /* Space between label and filter buttons */
}

.filter-btn {
    text-decoration: none;
    color: #f5f5f5;
    padding: 10px 15px;
    border-radius: 8px;
    transition: background-color 0.3s, color 0.3s;
    font-weight: bold;
    font-size: 1rem;
    background-color: #333;
    color: #ffcc00;
}

.filter-btn:hover {
    background-color: #333;
    color: #ffcc00;
    box-shadow: 0px 2px 4px rgba(255, 204, 0, 0.6);
}

.filter-btn:focus {
    outline: none; /* Remove default focus outline */
    box-shadow: 0 0 8px rgba(0, 210, 91, 0.5); /* Green glow effect */
    border-color: #00d25b; /* Green border on focus */
}

.filter-btn:active {
    background-color: #00c24b; /* Darker green on active press */
    border-color: #00c24b; /* Darker green border when active */
}



    
/* Custom styles for the Swal dark theme */
.swal-dark-popup {
    border: 1px solid #444;
    background-color: #2c2c2c;
    color: #ffffff;
}

.swal-dark-title {
    color: #ffffff;
}

.swal-dark-confirm-button {
    background-color: #4caf50;
    color: #ffffff;
    border: none;
}

.swal-dark-cancel-button {
    background-color: #f44336;
    color: #ffffff;
    border: none;
}

.dark-theme-popup {
    background-color: #333 !important;  /* Dark background */
    color: #fff !important;  /* Light text color */
}

.dark-theme-title {
    color: #fff !important;  /* Title text color */
}

.dark-theme-content {
    color: #ccc !important;  /* Content text color */
}

.dark-theme-button {
    background-color: #444 !important;  /* Dark buttons */
    color: #fff !important;  /* Button text color */
    border: 1px solid #555 !important;  /* Button border */
}

.dark-theme-button:hover {
    background-color: #666 !important;  /* Hover effect */
}

.dark-theme-button.swal2-confirm {
    background-color: #d33 !important;  /* Red confirmation button */
}

.dark-theme-button.swal2-confirm:hover {
    background-color: #b02e2b !important;  /* Darker red on hover */
}

.dark-theme-button.swal2-cancel {
    background-color: #f39c12 !important;  /* Yellow cancel button */
    border: 1px solid #e67e22 !important;  /* Border to match yellow theme */
}

.dark-theme-button.swal2-cancel:hover {
    background-color: #e67e22 !important;  /* Darker yellow on hover */
}

/* Error message styles */
.error-message {
    margin-top:0px;
    margin-bottom: 10px; 
    margin-left:170px;
    color: red;
    font-size: 12px;
}


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

/* Room Availability Text Colors */
.dashboard .available {
    color: #28a745; /* Green for Available */
}

.dashboard .unavailable {
    color: #dc3545; /* Red for Unavailable */
}

.dashboard .reserved {
    color: #2196f3;
}

.dashboard .booked {
    color:  #ff9800;
}

/* Optional: Styling for the table container */
.table-container .availability {
    font-weight: bold;
}


.dashboard {
    max-width: 100%;
    margin-bottom: 20px;
    background-color:  #191c24;
    border-radius: 2px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
    padding: 20px;
    display: flex; /* Flexbox container */
    gap: 20px; /* Space between containers */
}


/* Room List Container */
.room-list-container {
    padding: 0px;
    width: 250px; /* Small width */
    margin-bottom: 0; /* Remove bottom margin */
    max-height: 400px; /* Set a maximum height */
    overflow-y: auto; /* Enable vertical scrolling */
    background-color: #191c24; /* Optional: add background for better contrast */

}

#room-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

/* Style the room list items */
#room-list li {
    padding: 10px;
    background-color: #333;
    margin-bottom: 5px;
    border-radius: 5px;
    font-size: 14px;
    color: #b3b3b3;
    display: flex; /* Optional: make content align neatly */
    justify-content: space-between; /* Align text and status */
}

/* Optional: style the availability span */
#room-list li span {
    font-weight: bold;
    color: #ff6f61; /* Default color for availability */
}

#room-list li span.available {
    color: #4caf50; /* Green for available */
}

#room-list li span.unavailable {
    color: #f44336; /* Red for unavailable */
}

#room-list li span.booked {
    color: #ff9800; /* Orange for booked */
}

#room-list li span.reserved {
    color: #2196f3; /* Blue for reserved */
}



.table-container {
    flex-grow: 1; /* Take up remaining space */
    max-height: 400px; /* Set a max height for the table container */
    overflow-y: auto; /* Enable vertical scrolling */
}
.table-container h2 {
    font-size: 18px;
    color: #ffffff;
    margin-bottom: 10px;
}

.update-availability-btn{
    margin: 0;
}

table {
    width: 100%;
    border-collapse: collapse;
    background-color: #252525;
    border-radius: 10px;
}
thead {
    background-color: #333;
    color: #ffffff;
    position: sticky; /* Keep the header fixed */
    top: 0; /* Stick to the top of the container */
    z-index: 1; /* Ensure header stays on top */
}

th, td {
    text-align: left;
    padding: 10px;
    border-bottom: 1px solid #444;
    color: #dcdcdc;
}

th {
    font-size: 14px;
    text-transform: uppercase;
}

tr:hover {
    background-color: #333;
}

.update-btn {
    background-color: #2196f3;
    color: white;
    border: none;
    padding: 8px 12px;
    font-size: 14px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.update-btn:hover {
    background-color: #1976d2;
}

/* Modal background */
.summary-availability {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7); /* Darker background for better contrast */
    overflow: auto;
}

.summary-content {
    background-color: #2c2c2c; /* Darker gray for the modal background */
    margin: 10% auto;
    padding: 25px;
    border-radius: 10px;
    width: 350px;
    max-width: 100%; /* Ensures responsiveness on smaller screens */
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.6); /* More pronounced shadow */
    color: #fff; /* White text for better visibility */
    position: relative; /* Required for the absolute position of the close button */
    text-align: center; /* Center-align the content */
}

/* Centered heading */
.summary-content h2 {
    margin: 0 0 15px; /* Add spacing below the heading */
    font-size: 1.5rem;
    color: #f5f5f5; /* Soft white color */
    font-weight: 600; /* Bold heading text */
}

/* Close button */
.close-btn {
    color: #fff; /* White color for the close button */
    font-size: 22px;
    font-weight: bold;
    position: absolute; /* Position relative to the modal content */
    top: -10px; /* Adjust the distance from the top of the modal content */
    right: 10px; /* Adjust the distance from the right of the modal content */
    background: none;
    border: none;
    cursor: pointer;
    z-index: 100; /* Ensure it stays on top of other modal elements */
    line-height: 1; /* Fix alignment issues if the button seems slightly off */
}

.close-btn:hover,
.close-btn:focus {
    color: #2196f3; /* Blue color on hover */
    outline: none; /* Remove outline on focus for cleaner design */
}

@media screen and (max-width: 600px) {
    .summary-content {
        width: 90%;
        padding: 20px;
    }

    .close-btn {
        top: 5px;
        right: 5px; /* Adjust positioning for smaller screens */
    }
}


/* Availability select dropdown */
#availability-options {
    width: 100%;
    padding: 12px;
    margin: 15px 0;
    background-color: #444; /* Dark background for select */
    color: #fff;
    border: 1px solid #666;
    border-radius: 5px;
}

/* Save button styling */
#save-availability {
    width: 100%;
    padding: 12px;
    background-color: #2196f3;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

#save-availability:hover {
    background-color: #1976d2;
}

/* Button styling for other buttons inside the modal */
button {
    margin-top: 15px;
    padding: 10px;
    background-color: #2196f3;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}
.room-list-container::-webkit-scrollbar {
    width: 4px; /* Width of the scrollbar */
}
.room-list-container::-webkit-scrollbar-track {
    background-color: #333; /* Dark background for the scrollbar track */
    border-radius: 10px; /* Rounded corners for the track */
}

.room-list-container::-webkit-scrollbar-thumb {
    background-color: #555; /* Dark color for the scrollbar thumb */
    border-radius: 10px; /* Rounded corners for the thumb */
    border: 2px solid #333; /* Add a border around the thumb for better contrast */
}

.room-list-container::-webkit-scrollbar-thumb:hover {
    background-color: #888; /* Lighter color on hover for the thumb */
}


/* Scrollbar styling */
.table-container::-webkit-scrollbar {
    width: 4px; /* Width of the scrollbar */
}

.table-container::-webkit-scrollbar-track {
    background-color: #333; /* Dark background for the scrollbar track */
    border-radius: 10px; /* Rounded corners for the track */
}

.table-container::-webkit-scrollbar-thumb {
    background-color: #555; /* Dark color for the scrollbar thumb */
    border-radius: 10px; /* Rounded corners for the thumb */
    border: 2px solid #333; /* Add a border around the thumb for better contrast */
}

.table-container::-webkit-scrollbar-thumb:hover {
    background-color: #888; /* Lighter color on hover for the thumb */
}

/* Optional: style the scrollbar for horizontal scrolling as well */
.table-container::-webkit-scrollbar:horizontal {
    height: 8px; /* Height of the horizontal scrollbar */
}

.table-container::-webkit-scrollbar-track:horizontal {
    background-color: #333; /* Dark background for the horizontal track */
}

.table-container::-webkit-scrollbar-thumb:horizontal {
    background-color: #555; /* Dark thumb color for horizontal scrollbar */
}


/* Custom styles for SweetAlert2 */
.swal2-popup {
    background-color: #333; /* Black theme */
    color: #fff; /* White text for visibility */
    border-radius: 10px;
    border: 1px solid #444;
}
.swal2-title {
    color: #fff; /* Ensure the title is white */
}
.swal2-html-container {
    color: #ddd; /* Slightly lighter text for the content */
}
.swal2-confirm {
    background-color: green;
    color: #fff;
    border: none;
    border-radius: 5px;
}
.swal2-cancel {
    background-color: #777;
    color: #fff;
    border: none;
    border-radius: 5px;
}
.swal2-confirm:hover, .swal2-cancel:hover {
    background-color: #444; /* Darker hover effect */
}
</style>

    </body>
    </html>
