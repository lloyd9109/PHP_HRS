<?php
// Database connection
$host = "localhost"; // Change if needed
$user = "root"; // Your database username
$pass = ""; // Your database password
$dbname = "booking_system"; // Your database name

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the next room number
$sql = "SELECT MAX(room_number) AS max_room_number FROM rooms";
$result = $conn->query($sql);
$next_room_number = 1; // Default to 1 if no rooms exist

if ($result && $row = $result->fetch_assoc()) {
    $next_room_number = $row['max_room_number'] + 1;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $category = $_POST['category'];
    $room_size = $_POST['room_size'];
    $room_number = $_POST['room_number']; // Auto-generated
    $description = $_POST['description'];
    $price = $_POST['price'];
    $availability = $_POST['availability'];
    $guests = $_POST['guests'];
    $features = isset($_POST['features']) ? implode(", ", $_POST['features']) : '';
    $amenities = isset($_POST['amenities']) ? implode(", ", $_POST['amenities']) : '';

    // SQL Insert query
    $sql = "INSERT INTO rooms (category, room_size, room_number, description, price, availability, guests, features, amenities) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare and bind
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param(
        "ssssdsiss",
        $category,
        $room_size,
        $room_number,
        $description,
        $price,
        $availability,
        $guests,
        $features,
        $amenities
    );

    // Execute statement
    if ($stmt->execute()) {
        echo "Room added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Room</title>
</head>
<body>
    <h1>Add a New Room</h1>
    <form method="POST" action="">
        <label>Category:</label>
        <select name="category" required>
            <option value="Single">Single</option>
            <option value="Double">Double</option>
            <option value="Suite">Suite</option>
        </select><br>

        <label>Room Size:</label>
        <input type="text" name="room_size" required><br>

        <label>Room Number:</label>
        <input type="text" name="room_number" value="<?php echo $next_room_number; ?>" readonly><br>

        <label>Description:</label>
        <textarea name="description" required></textarea><br>

        <label>Price:</label>
        <input type="number" name="price" step="0.01" required><br>

        <label>Availability:</label>
        <select name="availability" required>
            <option value="Available">Available</option>
            <option value="Not Available">Not Available</option>
        </select><br>

        <label>Guests:</label>
        <input type="number" name="guests" required><br>

        <label>Features:</label><br>
        <input type="checkbox" name="features[]" value="WiFi"> WiFi<br>
        <input type="checkbox" name="features[]" value="TV"> TV<br>
        <input type="checkbox" name="features[]" value="Air Conditioning"> Air Conditioning<br>

        <label>Amenities:</label><br>
        <input type="checkbox" name="amenities[]" value="Pool"> Pool<br>
        <input type="checkbox" name="amenities[]" value="Gym"> Gym<br>
        <input type="checkbox" name="amenities[]" value="Parking"> Parking<br>

        <button type="submit">Add Room</button>
    </form>
</body>
</html>
