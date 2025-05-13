<?php
require_once '../src/Auth.php';
include 'header.php';
require_once '../admin/room.php';

$room = new room();
$rooms = $room->getAllRooms();

$current_page = basename($_SERVER['PHP_SELF'], ".php");

$signupSuccessMessage = $_SESSION['signup_success'] ?? '';
unset($_SESSION['signup_success']);

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
    <link rel="stylesheet" type="text/css" href="./styles/indexss.css">
</head>
<body>

<?php if ($signupSuccessMessage): ?>
        <div class="floating-message" id="successMessage"><?= htmlspecialchars($signupSuccessMessage) ?></div>
    <?php endif; ?>

<main>

        <!-- Hero Section with Slider -->
        <div class="hero">
            <div class="hero-slider">
                <div class="slide active">
                    <img src="../assets/luxurious.jpeg" alt="Hotel Room 1">
                    <div class="caption">Luxurious</div>
                </div>
                <div class="slide">
                    <img src="../assets/stunning.png" alt="Hotel Room 2">
                    <div class="caption">Stunning Views</div>
                </div>
                <div class="slide">
                    <img src="../assets/service.jpg" alt="Hotel Room 3">
                    <div class="caption">Exceptional Service</div>
                </div>
                <div class="slide">
                    <img src="../assets/relaxing.jpg" alt="Hotel Room 4">
                    <div class="caption">Relaxing Atmosphere</div>
                </div>
                <div class="slide">
                    <img src="../assets/elegant.jpg" alt="Hotel Room 5">
                    <div class="caption">Elegant Design</div>
                </div>
            </div>
            <button class="prev" id="prevBtn">&#10094;</button>
            <button class="next" id="nextBtn">&#10095;</button>
        </div>

        <div class="description">
                <h2>Welcome to HOTEL HIVE</h2>
                <p>Discover the perfect stay with our luxurious rooms and exceptional service. Book your stay today and experience comfort like never before.</p>
        </div>


        <div class="section-divider"></div>
    
        <div class="explore-section">
        <h2 class="section-title">Explore Our Rooms</h2>
            <div class="explore-grid-container">
                <button class="arrow left" onclick="moveLeft()">&#10094;</button>
                <div class="explore-grid" id="exploreGrid">
                <?php foreach ($rooms as $room): ?>
                    <div class="destination-card">
                        <img src="<?= $room['image_url'] ?>" alt="Boracay Island">
                        <h3><?= $room['room_name'] ?></h3>
                        <p>₱ <?= number_format($room['price'], 2) ?> /night</p>
                    </div>
                <?php endforeach; ?>
                    
                </div>
                <button class="arrow right" onclick="moveRight()">&#10095;</button>
            </div>
        </div>

        <div class="book-now-container">
            <a href="rooms.php" class="book-now-button">
                <i class="fa fa-bookmark"></i> Book Now
            </a>
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


        <div class="description">
                <h2>About Hotel Hive</h2>
                <p>Located in the heart of the city, Hotel Hive offers a blend of comfort and luxury with stunning views and world-class service. Whether you're here for business or leisure, our hotel promises an unforgettable experience.</p>

                <h2>Our Location</h2>
                <p>We are conveniently located just 15 minutes from the airport and within walking distance of major shopping and cultural destinations. Come enjoy the best of both worlds: city convenience and peaceful comfort. </p>
                <p>Cebu, Mandaue City <br>Philippines 6014</p>
        </div>  


</main>
   
</body>
</html>

<script src="../assets/script.js"></script>
<footer>
    <?php include 'footer.php'; ?>
</footer>

<script>
let currentPosition = 0;
const grid = document.getElementById('exploreGrid');
const cards = document.querySelectorAll('.destination-card');
const gridSize = cards.length; 

const leftArrow = document.querySelector('.arrow.left');
const rightArrow = document.querySelector('.arrow.right');

// Number of visible cards (1 card at a time)
const visibleCards = 1;

// Initial arrow check
checkArrows();

function checkArrows() {
    leftArrow.style.display = currentPosition === 0 ? 'none' : 'block';
    rightArrow.style.display = currentPosition >= gridSize - visibleCards ? 'none' : 'block';
}

function moveLeft() {
    if (currentPosition > 0) {
        currentPosition--;
        updateGridPosition();
        checkArrows();
    }
}

function moveRight() {
    if (currentPosition < gridSize - visibleCards) {
        currentPosition++;
        updateGridPosition();
        checkArrows();
    }
}

function updateGridPosition() {
    const offset = -(currentPosition * (cards[0].offsetWidth + 20)); // 20px is the margin between cards
    grid.style.transform = `translateX(${offset}px)`;
}

        // Show and hide the floating message
        document.addEventListener('DOMContentLoaded', () => {
            const message = document.getElementById('successMessage');
            if (message) {
                message.classList.add('show'); // Show the message
                setTimeout(() => {
                    message.classList.remove('show'); // Hide after 4 seconds
                }, 4000);
            }
        });
</script>

<style>


.summary-container {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    max-width: 1500px;
    margin: 0 auto;
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

@media (max-width: 1024px) {
    .summary-box {
        width: calc(25% - 30px); /* 4 boxes in a row on medium screens */
    }
}

@media (max-width: 768px) {
    .summary-box {
        width: calc(50% - 20px); /* 2 columns on small screens */
    }
}

@media (max-width: 480px) {
    .summary-box {
        width: 100%; /* 1 column on very small screens */
    }
}


.floating-message {
        position: fixed;
        top: 10%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #4caf50; /* Green background for success */
        color: white;
        padding: 15px 30px;
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        font-size: 16px;
        z-index: 1000;
        opacity: 0; /* Hidden by default */
        transition: opacity 0.5s ease-in-out, visibility 0.5s ease-in-out;
        visibility: hidden; /* Prevent focus when hidden */
    }

    .floating-message.show {
        opacity: 1;
        visibility: visible;
    }

.description {
    text-align: center;
    margin: 0 auto;
    padding: 20px;
    max-width: 800px;
    color: #333;
    line-height: 1.6;
}

.description h2 {
    font-size: 36px; 
    font-weight: bold; 
    margin-bottom: 15px; 
    color: #222; 
}

.description p {
    font-size: 18px;
    margin-top: 0;
    color: #555; 
}


.book-now-container {
    text-align: center; 
    margin-top: 20px;
    margin-bottom:20px
}

.book-now-button {
    background-color: #0f969c; /* Luxurious gold color */
    color: #fff; /* White text */
    font-size: 25px;
    font-weight: bold; /* Make the text stand out */
    padding: 25px 40px; /* Button size */
    border: none;
    border-radius: 50px; /* Rounded corners for an elegant look */
    text-decoration: none; /* Remove underline */
    display: inline-block;
    letter-spacing: 1px; /* Slightly spaced letters for a more refined appearance */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    transition: all 0.3s ease; /* Smooth transition effect */
}

.book-now-button:hover {
    background-color: #0c7075; /* Slightly darker gold on hover */
    transform: translateY(-2px); /* Lift the button up on hover */
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15); /* Slightly stronger shadow on hover */
}

.book-now-button:active {
    background-color: #0f969c; /* Even darker gold on click */
    transform: translateY(2px); /* Button pushes down when clicked */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Softer shadow when clicked */
}

.book-now-button:focus {
    outline: none; /* Remove default focus outline */
    border: 2px solid #0f969c; /* Add a gold border on focus */
}

.explore-section {
    padding: 40px 20px;
    max-width: 1200px;
    margin: 0 auto;
    position: relative;
}

.explore-section h2 {
    font-size: 28px;
    margin-bottom: 10px;
}

.explore-section p {
    font-size: 16px;
    margin-bottom: 30px;
    color: #666;
}

.explore-grid-container {
    overflow: hidden;
    position: relative;
    width: 100%;
}

.explore-grid {
    display: flex;
    transition: transform 0.5s ease;
    width: calc(20% * <?= count($rooms) ?>); /* Dynamically handle all cards */
}


.destination-card {
    width: 100%;  /* Each card will take up full width of the container */
    margin: 0 10px; /* Space between cards */
    box-sizing: border-box;
    text-align: center;
}


.destination-card img {
    width: 320px;
    height: 160px;
    object-fit: cover;  /* Ensures the image fills the container and is centered */
    border-radius: 8px;
    max-width: 100%;  /* Ensures the image is scaled down if necessary */
    max-height: 100%; /* Ensures the image is scaled down if necessary */
}

.destination-card h3 {
    font-size: 16px;
    margin: 10px 0 5px;
}

.destination-card p {
    font-size: 14px;
    color: #666;
}

/* Navigation Arrows */
.arrow {
    position: absolute;
    top: 35%;
    transform: translateY(-50%);
    background-color: white;
    color: black;
    border: none;
    padding: 10px;
    cursor: pointer;
    border-radius: 50%;
    font-size: 25px;
    z-index: 1;
    transition: background-color 0.3s, transform 0.3s;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.arrow.left {
    left: 1px;
}

.arrow.right {
    right: 1px;
}

.arrow:hover {
    background-color: rgba(240, 240, 240, 1);
    transform: translateY(-50%) scale(1.1);
}



</style>