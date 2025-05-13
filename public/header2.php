<?php
session_start();
require_once '../config/database.php';
require_once '../src/Auth.php';
$current_page = basename($_SERVER['PHP_SELF'], ".php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Reservation</title>
    <link rel="stylesheet"  href="./styles/header2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<body>


<header class="header">

    <div class="header-container">

            <!-- Hamburger Menu Icon -->
            <div class="hamburger" id="hamburgerMenu">
            <i class="fas fa-bars"></i>
        </div>

        <h1 class="header-title">Hotel Reservation</h1>
      
        <div class="design-line"></div>

        <!-- Navigation Links and Auth Buttons -->
        <nav class="nav-links" id="navLinks">
            <a href="index.php" class="<?php echo ($current_page == 'index') ? 'active' : ''; ?>">Home</a>
            <a href="rooms.php" class="<?php echo ($current_page == 'rooms') ? 'active' : ''; ?>">Rooms</a>
            <a href="contact.php" class="<?php echo ($current_page == 'contact') ? 'active' : ''; ?>">Contact</a>
        </nav>
        
        <div class="design-line"></div>

        <div class="auth-buttons" id="authButtons">
            <?php if (Auth::isLoggedIn()): ?>
                <?php 
                    $userId = $_SESSION['user_id'];
                    $stmt = $pdo->prepare('SELECT first_name, last_name, email, cellphone FROM users WHERE id = ?');
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC); 
                ?>
                <div class="user-info">
                    <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                </div>
                <div class="dropdown">
                    <span class="settings-icon" id="settingsIcon"><i class="fas fa-cog"></i></span>
                    <div class="dropdown-menu" id="settingsMenu" style="display: none;">
                        <a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Profile</a>
                        <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <button id="loginBtn" class="auth-button">Login</button>
                <button id="signupBtn" class="auth-button">Sign Up</button>
            <?php endif; ?>
            
        </div>
        
    </div>
    
</header>



<!-- Login Modal -->
<div id="loginModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span id="loginClose" class="close">&times;</span>
        <div class="modal-header">
            <h2 class="modal-title">Login</h2>
           
        </div>

        <form method="POST" action="login.php">

            <div class="input-group">
                <label for="loginEmail"></label>
                <input type="email" id="loginEmail" placeholder="Email" name="email">
                <p id="loginEmailMessage" class="validation-message"></p>
            </div>
            
            <div class="input-group">
                <label for="loginPassword"></label>
                <input type="password" id="loginPassword" placeholder="Password" name="password" minlength="8">
                <p id="loginPasswordMessage" class="validation-message"></p>
            </div>
            
            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember_me">
                    Remember me
                </label>
                <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
            </div>
            
            <button type="submit" class="btn-login">Login</button>
            <p id="loginMessage" class="validation-message"></p>
        </form>
        
        
        <div class="modal-footer"> 
            <p style="text-align: center;">Don't have an Account? <a href="#" id="showSignupModal">Sign Up</a></p>
        </div>
    </div>
</div>

<!-- Signup Modal -->
<div id="signupModal" class="modal">
    <div class="modal-content">
        <span id="signupClose" class="close">&times;</span>
        <div class="modal-header">
            <h2 class="modal-title">Sign Up</h2>
        </div>

        <form method="POST" action="signup.php">
            <div class="name-container">

                <div class="name-field">
                    <label for="signupFirstName"></label>
                    <input type="text" id="signupFirstName" placeholder="First Name" name="first_name">
                    <p id="signupFirstNameMessage" class="validation-message"></p>
                </div>
                
                <div class="name-field">
                    <label for="signupLastName"></label>
                    <input type="text" id="signupLastName" placeholder="Last Name" name="last_name">
                    <p id="signupLastNameMessage" class="validation-message"></p>
                </div>
            </div>
            
            <div class="input-group">
                <label for="signupEmail"></label>
                <input type="email" id="signupEmail" placeholder="Email" name="email" >
                <p id="signupEmailMessage" class="validation-message"></p>
            </div>

          
            <div class="cellphone-container">
                <label for="signupCellphone"></label>
                <div class="cellphone-field">
                    <select id="countryCode" name="country_code" class="form-select">
                        <option value="">Select Country</option>
                        <option value="+63" data-flag="fi fi-ph" selected>🇵🇭(+63)</option>
                        <option value="+1" data-flag="fi fi-us">🇺🇸(+1)</option>
                        <option value="+82" data-flag="fi fi-kr">🇰🇷(+82)</option>
                        <option value="+81" data-flag="fi fi-jp">🇯🇵(+81)</option>
                    </select>
                </div>
                <div class="cellphone-field">
                    <input type="text" id="signupCellphone" name="cellphone" placeholder="Phone Number"  maxlength="12" pattern="\d{3}-\d{3}-\d{4}">
                    
                </div>
            </div>
            <div class="*"><p style="margin-top:-20px; margin-bottom:25px;"  id="signupCellphoneError" class="validation-message"></p></div>

            <div class="input-group">
                <label for="signupPassword"></label>
                <input type="password" id="signupPassword" placeholder="Password" name="password" minlength="8">
                <p id="signupPasswordMessage" class="validation-message"></p>
            </div>

            <div class="input-group">
                <label for="signupConfirmPassword"></label>
                <input type="password" id="signupConfirmPassword" placeholder="Confirm Password" name="confirm_password"  minlength="8">
                <p id="signupConfirmPasswordMessage" class="validation-message"></p>
            </div>
            
            <label class="checkbox-label">
                <input type="checkbox" name="agree_to_conditions" required>
                I agree to the <a href="terms.php">terms and conditions</a>
            </label>
            
            <button type="submit" class="btn-signup">Signup</button>
            <p id="signupMessage" class="validation-message"></p>
        </form>
     
        <div class="modal-footer"> 
            <p style="text-align: center;">Already have an Account? <a href="#" id="showLoginModal">Sign In</a></p>
        </div>
    </div>
</div> 

<style>
    .validation-message {
    color: red;          /* Error message color */
    font-size: 0.85rem;  /* Smaller font size */
    margin-top: 4px;     /* Spacing between input and message */
    display: block;
}

</style>
<script>

    
    
document.getElementById("hamburgerMenu").addEventListener("click", function() {
    var navLinks = document.getElementById("navLinks");
    var authButtons = document.getElementById("authButtons");
    navLinks.classList.toggle("active");
    authButtons.classList.toggle("active");
});

// Slider JavaScript
document.addEventListener('DOMContentLoaded', function () {
    const slides = document.querySelectorAll('.hero-slider .slide');
    let currentSlide = 0;
    const totalSlides = slides.length;
    let autoSlideInterval;

    // Show the specified slide
    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.remove('active'); 
        });
        slides[index].classList.add('active'); 
    }

    // Show next slide
    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        showSlide(currentSlide);
    }

    // Show previous slide
    function prevSlide() {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
        showSlide(currentSlide);
    }

    // Start automatic sliding
    function startAutoSlide() {
        autoSlideInterval = setInterval(nextSlide, 3000);
    }

    // Stop automatic sliding
    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
    }

    // Button event listeners for next and previous slides
    document.getElementById('nextBtn').addEventListener('click', nextSlide);
    document.getElementById('prevBtn').addEventListener('click', prevSlide);

    // Start auto slide and set hover stop/start functionality
    startAutoSlide();
    document.querySelector('.hero-slider').addEventListener('mouseenter', stopAutoSlide);
    document.querySelector('.hero-slider').addEventListener('mouseleave', startAutoSlide);

    showSlide(currentSlide); // Initial slide display
});

// Modal JavaScript (Login and Signup)
const loginModal = document.getElementById('loginModal');
const signupModal = document.getElementById('signupModal');

// Handle Login Modal
const loginBtn = document.getElementById('loginBtn');
if (loginBtn) {
    loginBtn.addEventListener('click', function() {
        loginModal.style.display = 'block';
    });
}

const loginClose = document.getElementById('loginClose');
if (loginClose) {
    loginClose.addEventListener('click', function() {
        loginModal.style.display = 'none';
    });
}

// Handle Signup Modal
const signupBtn = document.getElementById('signupBtn');
if (signupBtn) {
    signupBtn.addEventListener('click', function() {
        signupModal.style.display = 'block';
    });
}

const signupClose = document.getElementById('signupClose');
if (signupClose) {
    signupClose.addEventListener('click', function() {
        signupModal.style.display = 'none';
    });
}

// Switch between Login and Signup modals
const showSignupModal = document.getElementById('showSignupModal');
if (showSignupModal) {
    showSignupModal.addEventListener('click', function(event) {
        event.preventDefault();
        signupModal.style.display = 'block';
        loginModal.style.display = 'none';
    });
}

const showLoginModal = document.getElementById('showLoginModal');
if (showLoginModal) {
    showLoginModal.addEventListener('click', function(event) {
        event.preventDefault();
        loginModal.style.display = 'block';
        signupModal.style.display = 'none';
    });
}

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    if (event.target === loginModal) {
        loginModal.style.display = 'none';
    } else if (event.target === signupModal) {
        signupModal.style.display = 'none';
    }
});

// Email Validation
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // General regex for valid email
    return emailRegex.test(email);
}

// Display validation messages for Login Form
document.getElementById('loginModal').querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    // Clear previous messages
    document.getElementById('loginEmailMessage').innerText = '';
    document.getElementById('loginPasswordMessage').innerText = '';

    let hasError = false;

    // Validate Email
    if (!email) {
        document.getElementById('loginEmailMessage').innerText = "Email is required.";
        hasError = true;
    } else if (!validateEmail(email)) {
        document.getElementById('loginEmailMessage').innerText = "Please enter a valid email address.";
        hasError = true;
    }

    // Validate Password
    if (!password) {
        document.getElementById('loginPasswordMessage').innerText = "Password is required.";
        hasError = true;
    } else if (password.length < 8) {
        document.getElementById('loginPasswordMessage').innerText = "Password must be at least 8 characters.";
        hasError = true;
    }

    // Submit if no error
    if (!hasError) {
        const formData = new FormData(this);
        fetch('login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
            showSuccessMessage('Login Successful', 2000, 'index.php');
        } else {
            messageElement.innerText = data.message;
        }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    }
});

// Display validation messages for Signup Form
document.getElementById('signupModal').querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    const firstName = document.getElementById('signupFirstName').value;
    const lastName = document.getElementById('signupLastName').value;
    const email = document.getElementById('signupEmail').value;
    const cellphone = document.getElementById('signupCellphone').value;
    const password = document.getElementById('signupPassword').value;
    const confirmPassword = document.getElementById('signupConfirmPassword').value;

    // Clear previous messages
    document.getElementById('signupFirstNameMessage').innerText = '';
    document.getElementById('signupLastNameMessage').innerText = '';
    document.getElementById('signupEmailMessage').innerText = '';
    document.getElementById('signupCellphoneError').innerText = '';
    document.getElementById('signupPasswordMessage').innerText = '';
    document.getElementById('signupConfirmPasswordMessage').innerText = '';

    let hasError = false;

    // Validate First Name
    if (!firstName) {
        document.getElementById('signupFirstNameMessage').innerText = "First Name is required.";
        hasError = true;
    }

    // Validate Last Name
    if (!lastName) {
        document.getElementById('signupLastNameMessage').innerText = "Last Name is required.";
        hasError = true;
    }

    // Validate Email
    if (!email) {
        document.getElementById('signupEmailMessage').innerText = "Email is required.";
        hasError = true;
    } else if (!validateEmail(email)) {
        document.getElementById('signupEmailMessage').innerText = "Please enter a valid email address.";
        hasError = true;
    }

    // Validate Cellphone
    const phonePattern = /^\d{3}-\d{3}-\d{4}$/;
    if (!cellphone) {
        document.getElementById('signupCellphoneError').innerText = "Phone number is required.";
        hasError = true;
    } else if (!phonePattern.test(cellphone)) {
        document.getElementById('signupCellphoneError').innerText = "Phone number format should be xxx-xxx-xxxx.";
        hasError = true;
    }

    // Validate Password
    if (!password) {
        document.getElementById('signupPasswordMessage').innerText = "Password is required.";
        hasError = true;
    } else if (password.length < 8) {
        document.getElementById('signupPasswordMessage').innerText = "Password must be at least 8 characters.";
        hasError = true;
    }

    // Confirm Password Match
    if (password !== confirmPassword) {
        document.getElementById('signupConfirmPasswordMessage').innerText = "Passwords do not match.";
        hasError = true;
    }

    // Submit if no error
    if (!hasError) {
        const formData = new FormData(this);
        fetch('signup.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = "index.php"; // Redirect on success
            } else {
                document.getElementById('signupMessage').innerText = data.message;
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    }
});

// Show Success Message and Redirect
function showSuccessMessage(message, delay, redirectUrl) {
    const messageElement = document.createElement('div');
    messageElement.innerText = message;
    messageElement.className = 'success-message';
    document.body.appendChild(messageElement);

    setTimeout(() => {
        messageElement.remove();
        window.location.href = redirectUrl;
    }, delay);
}


// Settings Menu Dropdown
document.addEventListener('DOMContentLoaded', function () {
    const settingsIcon = document.getElementById('settingsIcon');
    const settingsMenu = document.createElement('div');

    settingsMenu.classList.add('dropdown-menu', 'settings-dropdown');
    settingsMenu.innerHTML = `
        <a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Profile</a>
        <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    `;
    document.body.appendChild(settingsMenu);

    // Toggle dropdown visibility
    settingsIcon.addEventListener('click', (event) => {
        event.stopPropagation();
        const rect = settingsIcon.getBoundingClientRect();
        settingsMenu.style.top = `${rect.bottom + window.scrollY}px`;
        settingsMenu.style.left = `${rect.left}px`;
        settingsMenu.classList.toggle('show-dropdown');
    });

    // Close dropdown if clicked outside
    document.addEventListener('click', (event) => {
        if (!settingsIcon.contains(event.target) && !settingsMenu.contains(event.target)) {
            settingsMenu.classList.remove('show-dropdown');
        }
    });
});

// Cellphone Number Formatting
function formatCellphone(input) {
    const value = input.value.replace(/\D/g, ''); 
    const formattedValue = value.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
    input.value = formattedValue;
}

const signupCellphone = document.getElementById('signupCellphone');
if (signupCellphone) {
    signupCellphone.addEventListener('input', function() {
        formatCellphone(this);
    });
}


    </script>
</body>
</html>
