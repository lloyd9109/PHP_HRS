<?php
include 'header.php';
require_once '../config/database.php';
require_once '../src/Auth.php';
$current_page = basename($_SERVER['PHP_SELF'], ".php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Facilities</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./styles/facilitiess.css">
</head>
<body>

<!-- Facilities Section -->
<section id="facilities-section" class="facilities-section">
    <div class="container">
        <h2 class="section-title">Our Hotel Facilities</h2>
        <div class="facilities-grid">
            <!-- Facility 1 -->
            <div class="facility">
                <i class="fas fa-swimming-pool facility-icon"></i>
                <h3 class="facility-title">Swimming Pool</h3>
                <p>Relax and unwind in our luxury outdoor swimming pool, available to guests throughout the day.</p>
            </div>
            <!-- Facility 2 -->
            <div class="facility">
                <i class="fas fa-spa facility-icon"></i>
                <h3 class="facility-title">Spa & Wellness</h3>
                <p>Indulge in a range of rejuvenating spa treatments designed to help you relax and refresh.</p>
            </div>
            <!-- Facility 3 -->
            <div class="facility">
                <i class="fas fa-gym facility-icon"></i>
                <h3 class="facility-title">Fitness Center</h3>
                <p>Stay fit with our state-of-the-art gym equipped with modern equipment for all your fitness needs.</p>
            </div>
            <!-- Facility 4 -->
            <div class="facility">
                <i class="fas fa-concierge-bell facility-icon"></i>
                <h3 class="facility-title">24/7 Concierge Service</h3>
                <p>Our dedicated concierge team is always ready to assist with your needs, day or night.</p>
            </div>
            <!-- Facility 5 -->
            <div class="facility">
                <i class="fas fa-wifi facility-icon"></i>
                <h3 class="facility-title">Free Wi-Fi</h3>
                <p>Enjoy high-speed internet access across the hotel, ensuring you're always connected.</p>
            </div>
            <!-- Facility 6 -->
            <div class="facility">
                <i class="fas fa-utensils facility-icon"></i>
                <h3 class="facility-title">Restaurant & Bar</h3>
                <p>Savor delicious meals at our onsite restaurant or enjoy a drink at our cozy bar.</p>
            </div>
        </div>
    </div>
</section>



<script>
// You can add any necessary JavaScript for dynamic features here
</script>

</body>
</html>
<footer>
    <?php include 'footer.php'; ?>
</footer>