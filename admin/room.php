<?php
require_once '../config/database.php';

class Room {
    public function getAllRooms() {
        global $pdo;
        try {
            $stmt = $pdo->query("SELECT * FROM rooms");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error fetching rooms: " . $e->getMessage();
            return [];
        }
    }

    public function addRoom($data) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("INSERT INTO rooms (room_name, room_size, description, price, image_url, features, rating, availability) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['room_name'], $data['room_size'], $data['description'], $data['price'], $data['image_url'], $data['features'], $data['rating'], $data['availability']]);
        } catch (PDOException $e) {
            echo "Error adding room: " . $e->getMessage();
        }
    }

    public function deleteRoom($id) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            echo "Error deleting room: " . $e->getMessage();
        }
    }

    public function editRoom($data) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("UPDATE rooms SET room_name = ?, room_size = ?, description = ?, price = ?, image_url = ?, features = ?, rating = ?, availability = ? WHERE id = ?");
            $stmt->execute([$data['room_name'], $data['room_size'], $data['description'], $data['price'], $data['image_url'], $data['features'], $data['rating'], $data['availability'], $data['id']]);
        } catch (PDOException $e) {
            echo "Error updating room: " . $e->getMessage();
        }
    }

    public function getRoomById($id) {
        global $pdo; // Assuming you use $pdo for database connection
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
}
?>
