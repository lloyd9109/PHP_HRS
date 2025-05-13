<?php
ob_start();
require_once '../src/Auth.php';
include 'header.php';
require_once '../config/database.php';

$userId = $_SESSION['user_id']; // Fetch the logged-in user's ID

// Fetch user details (including new fields)
$stmt = $pdo->prepare("SELECT first_name, last_name, email, cellphone, address, age, gender, profile_image, password_hash FROM users WHERE id = :id");
$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $firstName = $user['first_name'];
    $lastName = $user['last_name'];
    $email = $user['email'];
    $cellphone = $user['cellphone'];
    $address = $user['address'];
    $age = $user['age'];
    $gender = $user['gender'];
    $profileImage = $user['profile_image'];
    $passwordHash = $user['password_hash'];
} else {
    echo "User data not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the updated form data
    $firstName = $_POST['first-name'];
    $lastName = $_POST['last-name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $profileImage = $profileImage; // Set the current profile image by default


    // Check if a new profile image was uploaded
    if (!empty($_FILES['profile-image']['name'])) {
        // Upload the new image and store it on the server
        $targetDir = "../assets/img_url/";
        $targetFile = $targetDir . basename($_FILES["profile-image"]["name"]);
        move_uploaded_file($_FILES["profile-image"]["tmp_name"], $targetFile);
        $profileImage = $targetFile; // Update profile image path
    }

    // If there's a cropped image (from the modal)
    if (isset($_POST['cropped-image'])) {
        $croppedImage = $_POST['cropped-image']; // Base64 image data

        // Convert the base64 data into an image file
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $croppedImage));
        $fileName = $userId . '-profile-image.png'; // Set a name for the image
        $filePath = "../assets/img_url/" . $fileName;

        // Save the cropped image to the server
        file_put_contents($filePath, $imageData);
        $profileImage = $filePath; // Store the file path of the cropped image
    }

    // Validate the data (you can add more validation as needed)
    if (!empty($firstName) && !empty($lastName) && !empty($email) && !empty($phone)) {
        // Update the user details in the database
        $stmt = $pdo->prepare("UPDATE users SET 
            first_name = :first_name, 
            last_name = :last_name, 
            email = :email, 
            cellphone = :cellphone, 
            address = :address, 
            age = :age, 
            gender = :gender, 
            profile_image = :profile_image 
            WHERE id = :id");
        
        // Bind the parameters
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':cellphone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':age', $age);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':profile_image', $profileImage);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

        // Execute the update query
        if ($stmt->execute()) {
            echo "<p class='success'>Profile updated successfully!</p>";
            // Redirect to avoid form resubmission on page refresh
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            echo "<p class='error'>Error updating profile. Please try again.</p>";
        }
    } else {
        echo "<p class='error'>All fields are!</p>";
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="stylesheet" href="./styles/profiles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="profile-container">
        <h2>Profile</h2>
        
        <form action="#" method="POST" enctype="multipart/form-data" id="profile-form">
            <div class="profile-image-container">
                <div id="cropper-container">
                    <img id="profile-img" src="<?php echo !empty($profileImage) ? $profileImage : 'default-avatar.png'; ?>" alt="Profile Image" class="profile-img">
                </div>
                <label for="profile-image" class="change-image-btn" style="display:none;">Change Image</label>
                <input type="file" id="profile-image" name="profile-image" accept="image/*" onchange="previewImage(event)" disabled>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="first-name">First Name</label>
                    <input type="text" id="first-name" name="first-name" 
                        value="<?php echo htmlspecialchars($firstName); ?>" readonly
                        oninput="this.value = this.value.replace(/^\s+/g, '').replace(/[^a-zA-Z\s]/g, '')">
                    <span class="error-message" id="first-name-error"></span> <!-- Error message -->
                </div>
                <div class="form-group">
                    <label for="last-name">Last Name</label>
                    <input type="text" id="last-name" name="last-name" 
                        value="<?php echo htmlspecialchars($lastName); ?>" readonly
                        oninput="this.value = this.value.replace(/^\s+/g, '').replace(/[^a-zA-Z\s]/g, '')">
                    <span class="error-message" id="last-name-error"></span> <!-- Error message -->
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                        value="<?php echo htmlspecialchars($email); ?>" readonly
                        oninput="this.value = this.value.replace(/^\s+/g, '').replace(/[^a-zA-Z0-9@._-]/g, '')">
                    <span class="error-message" id="email-error"></span> <!-- Error message -->
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" 
                        value="<?php echo htmlspecialchars($address); ?>" readonly
                        oninput="this.value = this.value.replace(/^\s+/g, '').replace(/[^a-zA-Z0-9@._-]/g, '')">
                    <span class="error-message" id="address-error"></span> <!-- Error message -->
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($cellphone); ?>" readonly maxlength="12" pattern="9\d{2}-\d{3}-\d{4}">
                    <span class="error-message" id="phone-error"></span> <!-- Error message -->
                </div>
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($age); ?>" readonly>
                    <span class="error-message" id="age-error"></span> <!-- Error message -->
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" disabled>
                        <option value="male" <?php if ($gender === 'male') echo 'selected'; ?>>Male</option>
                        <option value="female" <?php if ($gender === 'female') echo 'selected'; ?>>Female</option>
                        <option value="other" <?php if ($gender === 'other') echo 'selected'; ?>>Other</option>
                    </select>
                    <span class="error-message" id="gender-error"></span> <!-- Error message -->
                </div>
            </div>
            <div class="button-container">
                <button type="button" class="cancel-btn" id="cancel-btn" style="display:none;">Cancel</button>
                <button type="submit" class="submit-btn" style="display:none;">Save Changes</button>
            </div>

        </form>
        <button id="edit-profile-btn" class="edit-btn">Edit Profile</button> <!-- Edit Profile Button -->
        <button id="change-password-btn" class="change-password-btn" onclick="location.href='change_password.php';">Change Password</button> <!-- Change Password Button -->
    </div>

    <div id="cropper-modal" class="modal">
        <div class="modal-content">
            <div id="modal-cropper-container">
                <img id="modal-img" src="" alt="Crop Image">
            </div>
            <div class="modal-actions">
                <button id="save-btn" class="save-btn">Save</button>
                <button id="cancel-crop-btn" class="cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>
</html>


<script>
const editBtn = document.getElementById('edit-profile-btn');
const submitBtn = document.querySelector('.submit-btn');
const cancelBtn = document.getElementById('cancel-btn');
const formFields = document.querySelectorAll('input, select');
const changeImageBtn = document.querySelector('.change-image-btn');
const profileForm = document.getElementById('profile-form');

editBtn.addEventListener('click', () => {
    formFields.forEach(field => field.removeAttribute('readonly'));
    formFields.forEach(field => field.removeAttribute('disabled'));
    submitBtn.style.display = 'block';
    cancelBtn.style.display = 'inline-block';
    editBtn.style.display = 'none';
    changeImageBtn.style.display = 'inline-block'; // Show the Change Image button
});


cancelBtn.addEventListener('click', () => {
    formFields.forEach(field => {
        field.value = field.defaultValue;
        if (field.tagName === 'SELECT') {
            field.setAttribute('disabled', 'disabled'); // Re-disable dropdown
        } else {
            field.setAttribute('readonly', 'readonly'); // Re-enable readonly for inputs
        }
    });
    submitBtn.style.display = 'none';
    cancelBtn.style.display = 'none';
    editBtn.style.display = 'inline-block';
    changeImageBtn.style.display = 'none'; // Hide the Change Image button
});

let cropper;

function previewImage(event) {
    const file = event.target.files[0];

    // Check if the selected file is an image
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function() {
            const modalImg = document.getElementById('modal-img');
            modalImg.src = reader.result;
            document.getElementById('cropper-modal').style.display = 'flex';
            if (cropper) {
                cropper.destroy();
            }
            cropper = new Cropper(modalImg, {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: 0.8,
                responsive: true,
                checkOrientation: false,
                movable: true,
                rotatable: true,
                scalable: true,
                zoomable: true,
            });
        };
        reader.readAsDataURL(file);
    } else {
        // Show SweetAlert if the uploaded file is not an image
        Swal.fire({
            icon: 'error',
            title: 'Invalid File',
            text: 'Please upload a valid image file.',
        });

        // Clear the file input to reset
        document.getElementById('profile-image').value = '';
    }
}



document.getElementById('save-btn').addEventListener('click', function() {
    if (cropper) {
        const canvas = cropper.getCroppedCanvas({
            width: 200,
            height: 200,
        });
        const croppedImage = canvas.toDataURL();
        document.getElementById('profile-img').src = croppedImage;
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'cropped-image';
        hiddenInput.value = croppedImage;
        document.forms[0].appendChild(hiddenInput);
    }
    document.getElementById('cropper-modal').style.display = 'none';
});

document.getElementById('cancel-crop-btn').addEventListener('click', function() {
    document.getElementById('cropper-modal').style.display = 'none';
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
});

profileForm.addEventListener('submit', function(event) {
    event.preventDefault();
    const errorMessages = document.querySelectorAll('.error-message');
    errorMessages.forEach((msg) => msg.textContent = '');

    let isValid = true;

    const firstName = document.getElementById('first-name');
    if (!firstName.value.trim()) {
        document.getElementById('first-name-error').textContent = 'First Name is required.';
        isValid = false;
    }

    const lastName = document.getElementById('last-name');
    if (!lastName.value.trim()) {
        document.getElementById('last-name-error').textContent = 'Last Name is required.';
        isValid = false;
    }

    const email = document.getElementById('email');
    if (!email.value.trim()) {
        document.getElementById('email-error').textContent = 'Email is required.';
        isValid = false;
    }

    const phone = document.getElementById('phone');
    if (!phone.value.trim()) {
        document.getElementById('phone-error').textContent = 'Phone number is required.';
        isValid = false;
    }

    const address = document.getElementById('address');
    if (!address.value.trim()) {
        document.getElementById('address-error').textContent = 'Address is required.';
        isValid = false;
    }

    const age = document.getElementById('age');
    if (!age.value.trim()) {
        document.getElementById('age-error').textContent = 'Age is required.';
        isValid = false;
    }

    const gender = document.getElementById('gender');
    if (!gender.value.trim()) {
        document.getElementById('gender-error').textContent = 'Gender is required.';
        isValid = false;
    }

    if (isValid) {
        // SweetAlert confirmation before submitting the form
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to save these changes?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, save it!',
            cancelButtonText: 'No, cancel!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Success message after form submission
                Swal.fire(
                    'Saved!',
                    'Your profile has been updated.',
                    'success'
                ).then(() => {
                    this.submit();
                });
            }
        });
    }
});


document.getElementById('phone').addEventListener('input', function(event) {
    let phone = event.target.value;

    // Remove non-numeric characters
    phone = phone.replace(/\D/g, '');

    // Ensure the phone number starts with 9
    if (phone[0] !== '9') {
        phone = '9' + phone.slice(1);
    }

    // Format the phone number as xxx-xxx-xxxx
    if (phone.length >= 4 && phone.length <= 6) {
        phone = phone.slice(0, 3) + '-' + phone.slice(3);
    } else if (phone.length > 6) {
        phone = phone.slice(0, 3) + '-' + phone.slice(3, 6) + '-' + phone.slice(6, 10);
    }

    // Set the formatted phone number in the input field
    event.target.value = phone;
});

</script>

<style>
#age::-webkit-outer-spin-button,
#age::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>