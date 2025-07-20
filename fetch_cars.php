<?php
session_start();
include 'includes/db_connect.php';

$brand_id = $_POST['brand_id'] ?: NULL;
$model_id = $_POST['model_id'] ?: NULL;
$gearbox_id = $_POST['gearbox_id'] ?: NULL;
$type_id = $_POST['type_id'] ?: NULL;
$year_from = $_POST['year_from'] ?: NULL;
$year_to = $_POST['year_to'] ?: NULL;
$mileage_max = $_POST['mileage_max'] ?: NULL;
$price_max = $_POST['price_max'] ?: NULL;
$car_status_id = $_POST['car_status_id'] ?: NULL;

$stmt = $conn->prepare("CALL FilterCars(?, ?, ?, ?, ?, ?, ?, ?, ?)");

if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}

$stmt->bind_param("iiiiiiidd", $brand_id, $model_id, $gearbox_id, $type_id, $year_from, $year_to, $mileage_max, $price_max, $car_status_id);

$stmt->execute();

if ($stmt->error) {
    die('Execute error: ' . $stmt->error);
}

$result = $stmt->get_result();

if ($result === false) {
    die('MySQL query failed: ' . $stmt->error);
}

$cars_found = false;
$html = "<div class='cars-grid'>";

while ($car = $result->fetch_assoc()) {
    $cars_found = true;
    $html .= "<div class='car'>
        <div class='car-top'>
            <img src='{$car['image_url']}' alt='{$car['brand_name']} {$car['model_name']}'>
        </div>
        <div class='car-content'>
            <h3>{$car['brand_name']} {$car['model_name']}</h3>
            <p><strong>Gearbox:</strong> {$car['gearbox_name']}</p>
            <p><strong>Year:</strong> {$car['year_manufacture']}</p>
            <p><strong>Type:</strong> {$car['type_name']}</p>
            <p><strong>Mileage:</strong> " . number_format($car['mileage']) . " km</p>
            <div class='car-price'>
                {$car['price_per_day']} <span>BGN/day</span>
            </div>";

    if (isset($_SESSION['user_id'])) {
        $html .= "<a href='rent.php?car_id={$car['car_id']}' class='rent-button'>Rent</a>";
    } else {
        $html .= "<a href='login.php' class='rent-button'>Login to Rent</a>";
    }

    $html .= "</div>
    </div>";
}

$html .= "</div>";

if ($cars_found) {
    echo $html;
} else {
    echo "<div class='no-matching-cars'>🚫 No cars match your filters. Please try adjusting them.</div>";
}
?>

<style>
.no-matching-cars {
    text-align: center;
    font-size: 20px;
    color: #d9534f;
    background-color: #fff3f3;
    border: 1px solid #f5c6cb;
    padding: 20px 30px;
    margin: 40px auto;
    max-width: 600px;
    border-radius: 10px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
}
</style>