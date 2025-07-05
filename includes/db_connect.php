<?php
$conn = new mysqli("localhost", "root", "", "car_rental");

if ($conn->connect_error) {
    die("Грешка при връзката с базата: " . $conn->connect_error);
}
?>