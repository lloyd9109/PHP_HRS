<?php  
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
    $guests = $_POST['guests'] ?? '';

    try {
        // Get the price of the selected room
        $stmt = $pdo->prepare("SELECT price FROM rooms WHERE room_number = ?");
        $stmt->execute([$roomNumber]);
        $roomData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$roomData) {
            // Room not found
            $_SESSION['confirmationMessage'] = [
                'type' => 'error',
                'title' => 'Error Booking',
                'text' => 'The selected room does not exist.'
            ];
        } else {
            $roomPrice = $roomData['price'];

            // Parse the preferred date range into start and end dates
            $dates = explode(' - ', $preferredDate);
            $checkIn = DateTime::createFromFormat('D, d M', trim($dates[0]));
            $checkOut = DateTime::createFromFormat('D, d M', trim($dates[1]));

            if ($checkIn && $checkOut) {
                $interval = $checkIn->diff($checkOut);
                $totalDays = $interval->days + 1; // Include the check-out day

                // Calculate total payment
                $totalPayment = $totalDays * $roomPrice;

                // Check if the user already has a pending booking for this room
                $stmt = $pdo->prepare("SELECT * FROM bookings WHERE room_number = ? AND user_id = ? AND status = 'pending'");
                $stmt->execute([$roomNumber, $user_id]);
                $pendingBooking = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($pendingBooking) {
                    // User already has a pending booking for this room
                    $pendingCheckIn = new DateTime($pendingBooking['preferred_date_start']);
                    $pendingCheckOut = new DateTime($pendingBooking['preferred_date_end']);
                    $pendingDatesFormatted = $pendingCheckIn->format('D, d M') . ' to ' . $pendingCheckOut->format('D, d M');
                    
                    $_SESSION['confirmationMessage'] = [
                        'type' => 'error',
                        'title' => 'Pending Booking',
                        'text' => 'You already have a pending booking for this room from ' . $pendingDatesFormatted . '. Please wait for the confirmation before booking again.'
                    ];
                } else {
                    // Check if the room is available for the selected date range
                    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE room_number = ? AND ((? BETWEEN preferred_date_start AND preferred_date_end) OR (? BETWEEN preferred_date_start AND preferred_date_end) OR (preferred_date_start BETWEEN ? AND ?))");
                    $stmt->execute([$roomNumber, $checkIn->format('Y-m-d'), $checkOut->format('Y-m-d'), $checkIn->format('Y-m-d'), $checkOut->format('Y-m-d')]);
                    $existingBooking = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($existingBooking) {
                        // Get the next available date after the existing booking
                        $nextAvailableDate = DateTime::createFromFormat('Y-m-d', $existingBooking['preferred_date_end']);
                        $nextAvailableDate->add(new DateInterval('P1D')); // Add 1 day to the end date
                        $nextAvailableDateFormatted = $nextAvailableDate->format('D, d M');

                        // Room already booked for the selected date range
                        $_SESSION['confirmationMessage'] = [
                            'type' => 'error',
                            'title' => 'Error Booking',
                            'text' => 'This room has already been booked for the selected dates. The room will be available on ' . $nextAvailableDateFormatted . '. Please choose another room or adjust your dates.'
                        ];
                        
                    } else {
                        // Proceed with the booking insertion
                        $stmt = $pdo->prepare("INSERT INTO bookings (room_number, room_name, full_name, email, phone_number, preferred_date_start, preferred_date_end, preferred_time, guests, user_id, price, total_payment, status) 
                                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
                        $stmt->execute([$roomNumber, $roomName, $fullName, $email, $phone, $checkIn->format('Y-m-d'), $checkOut->format('Y-m-d'), $preferredTime, $guests, $user_id, $roomPrice, $totalPayment]);

                        // Set a success message
                        $_SESSION['confirmationMessage'] = [
                            'type' => 'success',
                            'title' => 'Booking Successful',
                            'text' => 'Please wait for your booking to be confirmed.'
                        ];
                    }
                }
            } else {
                // Invalid date format
                $_SESSION['confirmationMessage'] = [
                    'type' => 'error',
                    'title' => 'Error Booking',
                    'text' => 'The preferred date format is incorrect.'
                ];
            }
        }

        // Redirect to the same page to prevent resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        $_SESSION['confirmationMessage'] = [
            'type' => 'error',
            'title' => 'Error Booking',
            'text' => 'Database error: ' . $e->getMessage()
        ];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Check if there's a confirmation message and trigger SweetAlert
if (isset($_SESSION['confirmationMessage'])) {
    $messageType = $_SESSION['confirmationMessage']['type'];
    $messageTitle = $_SESSION['confirmationMessage']['title'];
    $messageText = $_SESSION['confirmationMessage']['text'];
    
    // Set additional styles based on the message type
    $iconColor = $messageType === 'error' ? '#f27474' : '#a5dc86';  // Red for errors, green for success
    $backgroundColor = $messageType === 'error' ? 'white' : 'white'; // Light red for errors, light green for success
    $textColor = $messageType === 'error' ? '#721c24' : '#155724'; // Dark red for errors, dark green for success
    $confirmButtonColor = $messageType === 'error' ? '#d33' : '#3085d6'; // Red for errors, blue for success
    
    // Gold border for SweetAlert
    $borderStyle = '2px solid #0f969c'; // Gold border style
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon: '$messageType',
                title: '$messageTitle',
                text: '$messageText',
                iconColor: '$iconColor',
                background: '$backgroundColor',
                color: '$textColor',
                confirmButtonColor: '$confirmButtonColor',
                customClass: {
                    popup: 'swal-popup-custom',
                }
            });
        });
        
        // Add custom styles for SweetAlert with a gold border
        const style = document.createElement('style');
        style.innerHTML = ` 
            .swal-popup-custom {
                border: $borderStyle !important;
            }
        `;
        document.head.appendChild(style);
    </script>";
    
    unset($_SESSION['confirmationMessage']);
}

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
                    <div class="room-card" 
                        data-room-name="<?= strtolower($room['room_name']) ?>" 
                        data-room-number="<?= $room['room_number'] ?>" 
                        data-guests="<?= $room['guests'] ?>"> <!-- Add guests as a data attribute -->
                        <img src="<?= $room['image_url'] ?>" class="card-img-top" alt="<?= $room['room_name'] ?>">
                        <div class="card-overlay">
                            <h3 class="card-text"><?= $room['room_name'] ?></h3>
                            <h3 class="card-title"><?= $room['room_number'] ?></h3>
                            <button class="book-now-btn" onclick="showContainer(<?= $room['id'] ?>)">View Room</button>
                        </div>
                    
                        <span class="hidden-guests" style="display:none;"><?= $room['guests'] ?></span>
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
                            <li>Cebu, Mandaue City, Philippines 6014</li>
                        </ul>
                        
                        <h3>Contact No.</h3>
                        <ul>
                            <li>(+63) 52 732-7777</li>
                            <li>(+63) 917-886-8168</li>
                        </ul>
                        
                        <h3>Email Address</h3>
                        <ul>
                            <li>bookings@hotelhive.com</li>
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
                    <select id="guests2" class="guest-select" name="guests">
                        <option value="" selected hidden>Select number of guests</option>
                        <option value="1">1 Guest</option>
                        <option value="2">2 Guests</option>
                        <option value="3">3 Guests</option>
                        <option value="4">4 Guests</option>
                    </select>
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

document.getElementById("guests").addEventListener("change", function () {
    const selectedGuests = this.value; // Get selected number of guests
    const rooms = document.querySelectorAll(".room-card");
    const roomCardsContainer = document.getElementById("room-cards");
    let hasVisibleRooms = false;

    rooms.forEach(room => {
        const roomGuests = room.getAttribute("data-guests");
        if (selectedGuests === "" || roomGuests === selectedGuests) {
            room.style.display = "block"; // Show room
            hasVisibleRooms = true;
        } else {
            room.style.display = "none"; // Hide room
        }
    });

    // Handle "No Rooms Available" message
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
});

function closeContainer() {
    document.querySelector('.container').style.display = 'none';
    document.querySelector('.dimmed-background').style.display = 'none';
    document.body.classList.remove('modal-open'); // Allow scrolling again
}

function showContainer(roomId) {

    document.querySelector('.container').style.display = 'grid';
    document.querySelector('.dimmed-background').style.display = 'block';


    document.body.classList.add('modal-open');


    const room = getRoomDetailsById(roomId);


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
        document.getElementById('modalPrice').textContent = `₱ ${Number(room.price).toLocaleString('en-US', { minimumFractionDigits: 2 })} per night`;
        document.getElementById('modalAvailability').textContent = ` ${room.availability}`;
        document.getElementById('modalGuests').textContent = room.guests ? `Can accommodate: ${room.guests}` : 'No guests specified';
        document.getElementById('guests2').value = room.guests;

        const bookNowButton = document.querySelector('.container .button');
        if (room.availability === 'Unavailable') {
            bookNowButton.disabled = true;
            bookNowButton.style.backgroundColor = '#ccc'; 
            bookNowButton.style.cursor = 'not-allowed';
        } else {
            bookNowButton.disabled = false;
            bookNowButton.style.backgroundColor = ''; 
            bookNowButton.style.cursor = '';
        }

        document.getElementById('room').value = room.room_number;
        document.getElementById('room_name').value = room.room_name;
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
    const bookingModule = document.querySelector('.booking-module');
    const bookingBackground = document.querySelector('.booking-module-background');

    if (!isLoggedIn) {
        // Show SweetAlert alert for not logged-in users with a gold border theme
        Swal.fire({
            icon: 'warning',
            title: 'You need to log in!',
            text: 'Please log in to book a room.',
            confirmButtonText: 'Login',
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'gold-border-theme', // Add custom class for styling
                title: 'gold-title',       // Optional: custom title style
                confirmButton: 'gold-btn', // Optional: custom button style
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to the login page
                window.location.href = 'login.php';
            }
        });
    } else {
        // Show booking module if logged in
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
    const guestSelect = document.getElementById('guests2');

    // Get the room's guest limit from the currently selected room card
    const roomCard = document.querySelector(`.room-card[data-room-number="${document.getElementById('room').value}"]`);
    const guestLimit = parseInt(roomCard.getAttribute('data-guests'), 10);
    const selectedGuests = parseInt(guestSelect.value, 10);

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

    // Check if the number of guests exceeds the limit
    if (selectedGuests > guestLimit) {
        Swal.fire({
            title: 'Additional Payment Required',
            text: `The selected room can only accommodate up to ${guestLimit} guest(s). An additional payment will be required for extra guests.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Proceed',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Proceed to the final confirmation step
                showFinalConfirmation();
            }
        });
        return; // Stop execution until user confirms
    }

    // Prevent form submission if any field is invalid
    if (!isValid) {
        return false;
    }

    // Proceed with final confirmation if no guest limit issue
    showFinalConfirmation();
}

function showFinalConfirmation() {
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
    const rooms = document.querySelectorAll(".room-card");
    const noRoomsMessage = document.getElementById("no-rooms-message");
    let hasVisibleRooms = false;

    // Loop through each room and filter based on room name or room number
    rooms.forEach(room => {
        const roomName = room.getAttribute("data-room-name").toLowerCase();
        const roomNumber = room.getAttribute("data-room-number").toLowerCase();

        // Check if search query matches room name or room number
        if (roomName.includes(searchQuery) || roomNumber.includes(searchQuery)) {
            room.style.display = "block"; // Show room
            hasVisibleRooms = true;
        } else {
            room.style.display = "none"; // Hide room
        }
    });

    // Display "No Rooms Available" message if no rooms are visible
    const roomCardsContainer = document.getElementById("room-cards");
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
</script>
<style>
/* Custom SweetAlert Gold Border Theme */
.gold-border-theme {
    border: 3px solid #0f969c !important;
    border-radius: 15px;
    box-shadow: 0 0 15px rgba(20, 198, 207, 0.5);
}

.gold-title {
    color: #0f969c !important;
    font-weight: bold;
}

.gold-btn {
    background-color: #0f969c!important;
    color: black !important;
    font-weight: bold;
    border: none !important;
    padding: 10px 20px !important;
    border-radius: 5px !important;
}

.gold-btn:hover {
    background-color: #0c7075 !important;
    color: black !important;
}
.summary-container {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    max-width: 1500px;
    margin: 0 auto;
}
.room-summary{
    margin-top: 20px;
}
.summary-box {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    padding: 30px;
    text-align: center;
    width: calc(10% - 50px); /* Adjust width for 5 boxes in a row */
    min-width: 220px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
}

.summary-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.count {
    font-size: 32px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 12px;
    letter-spacing: 1px;
}

.label {
    font-size: 18px;
    color: #7f8c8d;
    text-transform: uppercase;
    font-weight: 500;
}

.available-box {
    background-color: #e0f7fa;
    border-left: 5px solid #00bcd4;
}

.unavailable-box {
    background-color: #fbe9e7;
    border-left: 5px solid #ff7043;
}

.booked-box {
    background-color: #fff3e0;
    border-left: 5px solid #ffb74d;
}

.reserved-box {
    background-color: #f1f8e9;
    border-left: 5px solid #8bc34a;
}

.total-box {
    background-color: #ffebee;
    border-left: 5px solid #f44336;
}

/* Media Query for Mobile Devices */
@media (max-width: 768px) {
    /* Adjust the logo size and position on mobile */
    .header .logo {
        width: 35px;
        height: 35px;
        top: 50%;
        left: 20px;  /* Adjust this value based on your design */
        transform: translateY(-50%) scale(1.2);  /* Smaller scale for mobile */
    }

    /* Display the hamburger menu on mobile */
    .hamburger {
        display: block;  /* Show hamburger icon */
        position: absolute;
        top: 50%;
        right: 20px;  /* Adjust this value based on your design */
        transform: translateY(-50%);
    }

    /* Optional: Adjust font size for mobile */
    .hamburger i {
        font-size: 1.2em;
    }
}

/* Media Query for Larger Screens */
@media (min-width: 769px) {
    .hamburger {
        display: none; /* Hide the hamburger menu on larger screens */
    }
}
</style>