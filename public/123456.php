<?php  
// Start session and output buffering
session_start();
ob_start();

require_once '../src/Auth.php';
include 'header.php';
require_once '../admin/room.php';
require_once '../config/database.php';

$auth = new Auth();
$isLoggedIn = $auth->checkLoginStatus();

$room = new room();
$rooms = $room->getAllRooms();

$current_page = basename($_SERVER['PHP_SELF'], ".php");

$confirmationMessage = ''; // Initialize confirmation message variable

// Get the logged-in user's ID
$user_id = $isLoggedIn ? $auth->getUserId() : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collecting form data
    $roomNumber = $_POST['room'] ?? '';
    $roomName = $_POST['room_name'] ?? ''; 
    $fullName = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $preferredDate = $_POST['date'] ?? ''; 
    $preferredTime = $_POST['time'] ?? '';
    $guests = $_POST['guests2'] ?? '';

    // Check if the room is already pending
    try {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE room_number = ? AND status = 'pending'");
        $stmt->execute([$roomNumber]);
        $pendingBooking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pendingBooking) {
            // Room is pending; set the message
            $_SESSION['confirmationMessage'] = "This room is currently pending confirmation for another user. Please try again later or choose another room.";
        } else {
            // Proceed with the booking insertion
            $stmt = $pdo->prepare("INSERT INTO bookings (room_number, room_name, full_name, email, phone_number, preferred_date, preferred_time, guests, user_id, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$roomNumber, $roomName, $fullName, $email, $phone, $preferredDate, $preferredTime, $guests, $user_id]);

            // Set a success message
            $_SESSION['confirmationMessage'] = 'Booking successful!';
        }
        // Redirect to the same page to prevent resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        $_SESSION['confirmationMessage'] = 'Database error: ' . $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Retrieve confirmation message from session, if any
if (isset($_SESSION['confirmationMessage'])) {
    $confirmationMessage = $_SESSION['confirmationMessage'];
    unset($_SESSION['confirmationMessage']); // Clear the message after displaying
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/rooms.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <title>Document</title>
</head>
<body>
<div class="module">
    <div class="title">
         <h1>Welcome to Hotel Hive - Book at the Best Price</h1>
    </div>

    <div class="search-module">
        <div class="box room-search-bar">
            <label for="room-search"></label>
            <input type="text" id="room-search" placeholder="Search for Room" oninput="searchRooms()">
        </div>

        <div class="box date-picker">
            <label for="check-in-out-1"></label>
            <input type="text" id="check-in-out-1" placeholder="Check-in and Check-out">
        </div>

        <div class="box guest-room-selection">
            <label for="guests"></label>
            <select id="guests" class="guest-select">
                <option value="" selected hidden>Select number of guests</option>
                <option value="1">1 Guest</option>
                <option value="2">2 Guests</option>
                <option value="3">3 Guests</option>
                <option value="4">4 Guests</option>
            </select>
        </div>
        <button class="search-btn">Search</button>
    </div>

</div>


<div class="main-content">

<?php if (!empty($confirmationMessage)): ?>
    <div class="floating-message"><?= htmlspecialchars($confirmationMessage) ?></div>
<?php endif; ?>

<div class="box room-category">
    <label for="room-category">Room Category</label>
    <div class="filter-nav">
        <button class="filter-btn active" onclick="filterRooms('')">All</button>
        <button class="filter-btn" onclick="filterRooms('standard')">Standard</button>
        <button class="filter-btn" onclick="filterRooms('deluxe')">Deluxe</button>
        <button class="filter-btn" onclick="filterRooms('suite')">Suite</button>
        <button class="filter-btn" onclick="filterRooms('superior')">Superior</button>
        <button class="filter-btn" onclick="filterRooms('family')">Family</button>
    </div>
</div>

<div class="room-cards" id="room-cards">
    <?php if (empty($rooms)): ?>
        <div id="no-rooms-message">
            No Rooms Available
        </div>
    <?php else: ?>
        <?php foreach ($rooms as $room): ?>
            <div class="room-card" data-room-name="<?= strtolower($room['room_name']) ?>" data-room-number="<?= $room['room_number'] ?>">
                <img src="<?= $room['image_url'] ?>" class="card-img-top" alt="<?= $room['room_name'] ?>">
                <div class="card-overlay">
                    <h3 class="card-text"><?= $room['room_name'] ?></h3>
                    <h3 class="card-title"><?= $room['room_number'] ?></h3>
                    <button class="book-now-btn" onclick="showContainer(<?= $room['id'] ?>)">Book Now</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>



    <div class="dimmed-background">

        <div class="container" >
            
            <button class="close-btn" onclick="closeContainer()">X</button>

            <div class="title"></div>
            <img src="" alt="Room Image" class="image" id="modalImage">

            
            <div class="content">
                <div class="info">
                    <div class="details">
                        <div>
                            <h3>Room Size</h3>
                            <p id="modalSize"></p>
                        </div>
                        <div>
                            <h3>Guests</h3>
                            <p id="modalGuests"></p>
                        </div>
                    </div>
                    <div class="details">
                        <div>
                            <h3>Price</h3>
                            <p id="modalPrice"></p>
                        </div>
                        <div>
                            <h3>Availability</h3>
                            <p id="modalAvailability"></p>
                        </div>
                    </div>
                    <h3>Room Features</h3>
                    <ul class="two-column" id="modalFeatures"></ul>
                    
                    <h3>Amenities</h3>
                    <h4>Comfort and Indulgence</h4>
                    <ul class="two-column" id="modalAmenities"></ul>
                    
                    
                    <button class="button" onclick="handleBooking(<?= $isLoggedIn ? 'true' : 'false' ?>)">Book Now</button>
                </div>
                
                <div class="contact">
                    <h3>Location</h3>
                    <ul>
                        <li>Corner Imelda Roces Avenue, Barangay 38, Gogon, Legazpi City, Albay, Philippines 4500</li>
                    </ul>
                    
                    <h3>Contact No.</h3>
                    <ul>
                        <li>(+63) 52 732-7777</li>
                        <li>(+63) 917-886-8168</li>
                    </ul>
                    
                    <h3>Email Address</h3>
                    <ul>
                        <li>bookings@themaisonhotel.com</li>
                    </ul>
                    
                    <h3>Check-In / Check-Out</h3>
                    <ul>
                        <li>Check In: 2:00 PM</li>
                        <li>Check Out: 12:00 PM</li>
                    </ul>
                    
                    <h3>Payment</h3>
                    <ul>

                        <li>Walk in Payment</li>
                    </ul>
                </div>
            </div>
        </div> 
    </div>

    <div id="notificationMessage" class="notification">To Book a Room, You Need to Login First</div>

<div class="booking-module-background"></div> 
<div class="booking-module" style="display:none;">
<button class="close-btn" onclick="closeBookingModule()">X</button>
    <form class="booking-form" method="POST"  onsubmit="return showConfirmation(event);">


    <div class="title"></div>
        
        <div class="form-row">
            <div class="form-control half-width">
                <label for="room">Room Number</label>
                <input type="text" id="room" name="room" readonly>
            </div>

            <div class="form-control half-width">
                <label for="room_name">Room Type</label>
                <input type="text" id="room_name" name="room_name" readonly>
            </div>
        </div>

        <div class="form-control">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>" readonly>
        </div>


        <div class="form-control">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email address" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
        </div>

        <div class="form-control">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" placeholder="Enter your phone number"  value="<?php echo htmlspecialchars($user['cellphone']); ?>" readonly>
        </div>

        <div class="form-control">
            <label for="check-in-out-2">Preferred Date</label>
            <input type="text" id="check-in-out-2" class="custom-date-picker" placeholder="Check-in and Check-out" name="date" >
            <div id="dateError" class="error-message" style="color: red; display: none;">Please select a date.</div>
        </div>


        <div class="form-control">
            <label for="time">Preferred Time</label>
            <input type="time" id="time" name="time" >
            <div id="timeError" class="error-message" style="color: red; display: none;">Please select a time.</div>
        </div>

        <div class="form-control">
            <label for="guests2">Guest</label>
            <select id="guests2" class="guest-select" name="guests2" >
            <option value="" selected hidden>Select number of guests</option>
                <option value="1">1 Guest</option>
                <option value="2">2 Guests</option>
                <option value="3">3 Guests</option>
                <option value="4">4 Guests</option>
            </select>
            <div id="guestsError" class="error-message" style="color: red; display: none;">Please select the number of guests.</div>
        </div>
        <div class="form-control">
            <button type="submit" class="button">Book Now</button>
        </div>

    </form>

</div>
</div>
<footer>
    <?php include 'footer.php'; ?>
</footer>

</body>
</html>

<script>

function filterRooms(category) {
    const rooms = document.querySelectorAll(".room-card");
    const buttons = document.querySelectorAll(".filter-btn");
    const roomCardsContainer = document.getElementById("room-cards");
    
    // Remove active class from all buttons
    buttons.forEach(button => {
        button.classList.remove("active");
    });

    // Add active class to the clicked button
    if (category !== "") {
        const activeButton = document.querySelector(`.filter-btn[onclick="filterRooms('${category}')"]`);
        activeButton.classList.add("active");
    }

    let hasVisibleRooms = false;

    // Filter rooms
    rooms.forEach(room => {
        const roomName = room.getAttribute("data-room-name").toLowerCase();
        if (category === "" || roomName.includes(category.toLowerCase())) {
            room.style.display = "block"; // Show room
            hasVisibleRooms = true;
        } else {
            room.style.display = "none"; // Hide room
        }
    });

    // Display "No Rooms Available" message if no rooms are visible
    const noRoomsMessage = document.getElementById("no-rooms-message");
    if (!hasVisibleRooms) {
        if (!noRoomsMessage) {
            const message = document.createElement("div");
            message.id = "no-rooms-message";
            message.textContent = "No Rooms Available";
            roomCardsContainer.appendChild(message);
        }
    } else if (noRoomsMessage) {
        noRoomsMessage.remove();
    }
}


function closeContainer() {
    document.querySelector('.container').style.display = 'none';
    document.querySelector('.dimmed-background').style.display = 'none';
    document.body.classList.remove('modal-open'); // Allow scrolling again
}

function showContainer(roomId) {
    // Show the modal
    document.querySelector('.container').style.display = 'grid';
    document.querySelector('.dimmed-background').style.display = 'block';

    // Disable scrolling on body
    document.body.classList.add('modal-open');

    // Get the room details by ID
    const room = getRoomDetailsById(roomId);

    // Update the modal content
    if (room) {
        document.querySelector('.container .title').textContent = `${room.room_name} (${room.room_number})`;
        document.getElementById('modalImage').src = room.image_url;

        const featuresList = document.getElementById('modalFeatures');
        featuresList.innerHTML = ''; 
        if (room.features) {
            const features = room.features.split(',');
            features.forEach(feature => {
                const listItem = document.createElement('li');
                listItem.textContent = feature.trim();
                featuresList.appendChild(listItem);
            });
        } else {
            featuresList.innerHTML = '<li>No features available.</li>';
        }

        const amenitiesList = document.getElementById('modalAmenities');
        amenitiesList.innerHTML = ''; 
        if (room.amenities) {
            const amenities = room.amenities.split(',');
            amenities.forEach(amenity => {
                const listItem = document.createElement('li');
                listItem.textContent = amenity.trim();
                amenitiesList.appendChild(listItem);
            });
        } else {
            amenitiesList.innerHTML = '<li>No amenities available.</li>';
        }

        // Set dynamic room size, price, and availability
        document.getElementById('modalSize').textContent = `Size: ${room.room_size} sqm`;
        document.getElementById('modalPrice').textContent = `PHP ${Number(room.price).toLocaleString('en-US', { minimumFractionDigits: 2 })} per night`;
        document.getElementById('modalAvailability').textContent = ` ${room.availability}`;

        document.getElementById('modalGuests').textContent = room.guests ? `Can accommodate: ${room.guests}` : 'No guests specified';

        // Disable the "Book Now" button if the room is Unavailable or Booked
        const bookNowButton = document.querySelector('.container .button');
        if (room.availability === 'Unavailable' || room.availability === 'Booked') {
            bookNowButton.disabled = true;
            bookNowButton.style.backgroundColor = '#ccc'; // Optionally change color to indicate it's disabled
            bookNowButton.style.cursor = 'not-allowed'; // Optionally change cursor to indicate it's unclickable
        } else {
            bookNowButton.disabled = false;
            bookNowButton.style.backgroundColor = ''; // Reset button color
            bookNowButton.style.cursor = ''; // Reset cursor
        }

        // Populate the room number in the booking form
        document.getElementById('room').value = room.room_number;

        // Set the room name in the booking form "Room Type" field
        document.getElementById('room_name').value = room.room_name;

        // Set the room name and number in the booking form title
        const bookingFormTitle = document.querySelector('.booking-form .title');
        bookingFormTitle.textContent = `Book ${room.room_name} (Room ${room.room_number})`;
    } else {
        console.error("Room details not found for ID:", roomId);
    }
}


// Close the modal if the user clicks on the background (outside the container)
document.querySelector('.dimmed-background').addEventListener('click', function(event) {
    if (event.target === this) {
        closeContainer();
    }
});

function getRoomDetailsById(roomId) {
    const rooms = <?php echo json_encode($rooms); ?>;
    return rooms.find(room => room.id === roomId);
}

document.addEventListener('DOMContentLoaded', function() {
    const checkInOut1 = document.querySelector("#check-in-out-1");
    const checkInOut2 = document.querySelector("#check-in-out-2");
    const guestsSelect = document.getElementById('guests');
    const guests2Select = document.getElementById('guests2');

        // Synchronize guests dropdowns
        guestsSelect.addEventListener('change', function() {
        guests2Select.value = this.value; // Set guests2 to the selected value of guests
    });

    guests2Select.addEventListener('change', function() {
        guestsSelect.value = this.value; // Set guests to the selected value of guests2
    });

    // Common options for both date pickers
    const options = {
        mode: "range",
        altInput: false, // Disable flatpickr's alt formatting
        dateFormat: "Y-m-d",
        minDate: "today",
        onChange: function(selectedDates, dateStr, instance) {
            const formattedRange = formatDateRange(selectedDates);
            
            // Synchronize dates across both inputs
            if (instance.input === checkInOut1) {
                checkInOut2._flatpickr.setDate(selectedDates, false);
            } else {
                checkInOut1._flatpickr.setDate(selectedDates, false);
            }

            // Update values manually to avoid duplication
            checkInOut1.value = formattedRange;
            checkInOut2.value = formattedRange;
        }
    };

    flatpickr(checkInOut1, options);
    flatpickr(checkInOut2, options);

    function formatDateRange(selectedDates) {
        if (selectedDates.length === 2) {
            const options = { weekday: 'short', day: 'numeric', month: 'short' };
            const startDate = selectedDates[0].toLocaleDateString('en-GB', options);
            const endDate = selectedDates[1].toLocaleDateString('en-GB', options);
            return `${startDate} - ${endDate}`;
        }
        return "";
    }
});

function handleBooking(isLoggedIn) {
    const notification = document.getElementById('notificationMessage');
    const bookingModule = document.querySelector('.booking-module');
    const bookingBackground = document.querySelector('.booking-module-background');

    if (!isLoggedIn) {
        // Show notification if not logged in
        notification.style.display = 'block';
        notification.style.opacity = '1';
        
        // Timeout to fade out and then hide the notification
        setTimeout(() => {
            notification.style.opacity = '0'; // Fade out effect
            setTimeout(() => {
                notification.style.display = 'none'; // Hide completely after fade-out
            }, 500); // Duration of fade-out
        }, 3000); // Display time before fading out
    } else {
        bookingModule.style.display = 'block';
        bookingBackground.style.display = 'block'; // Show background
    }
}

function closeBookingModule() {
    document.querySelector('.booking-module').style.display = 'none';
    document.querySelector('.booking-module-background').style.display = 'none'; // Hide background
}

function showConfirmation(event) {
    // Prevent form submission temporarily
    event.preventDefault();

    // Get the input fields for date, time, and guests
    const dateField = document.getElementById('check-in-out-2');
    const timeField = document.getElementById('time');
    const guestsField = document.getElementById('guests2');

    // Initialize a flag for validation
    let isValid = true;

    // Check if the date is empty
    if (dateField.value.trim() === '') {
        document.getElementById('dateError').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('dateError').style.display = 'none';
    }

    // Check if the time is empty
    if (timeField.value.trim() === '') {
        document.getElementById('timeError').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('timeError').style.display = 'none';
    }

    // Check if the number of guests is empty
    if (guestsField.value.trim() === '') {
        document.getElementById('guestsError').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('guestsError').style.display = 'none';
    }

    // Prevent form submission if any field is invalid
    if (!isValid) {
        return false;
    }

    // Show SweetAlert confirmation popup
    Swal.fire({
        title: 'Confirm Booking',
        text: 'Are you sure you want to proceed with this booking?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, book it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit the form if confirmed
            submitForm();
        }
    });
}

function submitForm() {
    // Submit the form
    document.querySelector('.booking-form').submit();
}


function searchRooms() {
    const searchQuery = document.getElementById("room-search").value.toLowerCase();
    const roomCards = document.querySelectorAll(".room-card");

    roomCards.forEach(card => {
        const roomName = card.getAttribute("data-room-name");
        const roomNumber = card.getAttribute("data-room-number").toString();
        
        if (roomName.includes(searchQuery) || roomNumber.includes(searchQuery)) {
            card.style.display = "block"; // Show card
        } else {
            card.style.display = "none"; // Hide card
        }
    });
}
</script>
<style>

</style>