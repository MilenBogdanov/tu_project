<?php
include 'includes/header.php';
include 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'];
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $pickup_time = $_POST['pickup_time'];
    $dropoff_time = $_POST['dropoff_time'];
    $pickup_address = $_POST['pickup_location'];
    $payment_method = $_POST['payment_method'];

    if (!isset($_SESSION['user_id'])) {
        echo "<p>You must be logged in to make a booking.</p>";
        include 'includes/footer.php';
        exit;
    }
    $user_id = $_SESSION['user_id'];

    if (empty($car_id) || empty($pickup_date) || empty($return_date) || empty($pickup_time) || empty($dropoff_time)) {
        echo "<p>Missing required data. Please go back and try again.</p>";
        include 'includes/footer.php';
        exit;
    }

    
    $pickup = new DateTime($pickup_date);
    $return = new DateTime($return_date);
    $interval = $pickup->diff($return);
    $days = max(1, $interval->days);

    
    $price_sql = $conn->prepare("
        SELECT c.price_per_day, c.image_url, m.model_name, b.brand_name 
        FROM cars c
        JOIN models m ON c.model_id = m.model_id
        JOIN brands b ON m.brand_id = b.brand_id
        WHERE c.car_id = ?
    ");
    $price_sql->bind_param("i", $car_id);
    $price_sql->execute();
    $price_result = $price_sql->get_result();

    if ($price_result->num_rows === 0) {
        echo "<p>Invalid car selected.</p>";
        include 'includes/footer.php';
        exit;
    }

    $car = $price_result->fetch_assoc();
    $price_per_day = $car['price_per_day'];
    $car_brand = $car['brand_name'];
    $car_model = $car['model_name'];
    $car_image = $car['image_url'];
    $total_price = $price_per_day * $days;

   
    $rental_status_id = 2;
    $payment_method_id = 0;

    if ($payment_method === 'cash') {
        $payment_method_id = 2;
    } elseif ($payment_method === 'credit_card') {
        $payment_method_id = 1;
    }

    
    $stmt = $conn->prepare("
        INSERT INTO rentals (
            car_id, user_id, rental_date, return_date, total_price, rental_status_id,
            pickup_address, pickup_time, dropoff_time
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        die("<p>SQL Error: " . $conn->error . "</p>");
    }

    $stmt->bind_param(
        "iissdisss",
        $car_id,
        $user_id,
        $pickup_date,
        $return_date,
        $total_price,
        $rental_status_id,
        $pickup_address,
        $pickup_time,
        $dropoff_time
    );

    if ($stmt->execute()) {
        $rental_id = $stmt->insert_id;

        
        $payment_date = date('Y-m-d');
        $payment_stmt = $conn->prepare("
            INSERT INTO payments (rental_id, amount, payment_date, payment_method_id)
            VALUES (?, ?, ?, ?)
        ");
        if ($payment_stmt) {
            $payment_stmt->bind_param("idsi", $rental_id, $total_price, $payment_date, $payment_method_id);
            $payment_stmt->execute();
            $payment_stmt->close();
        } else {
            echo "<p>Payment SQL Error: " . $conn->error . "</p>";
        }

        
        $status_name = 'Approved';
        echo <<<HTML
        <div class='confirmation-box'>
            <h2>🎉 Rental Confirmed</h2>
            <p>Your rental has been successfully booked!</p>
            <hr>
            <p><strong>Car:</strong> {$car_brand} {$car_model}</p>
            <img src='{$car_image}' alt='{$car_brand} {$car_model}' style='max-width: 100%; border-radius: 12px; margin: 15px 0;' />
            <p><strong>Pickup:</strong> {$pickup_date} at {$pickup_time}<br>From: {$pickup_address}</p>
            <p><strong>Return:</strong> {$return_date} at {$dropoff_time}</p>
            <p><strong>Duration:</strong> {$days} day(s)</p>
            <p><strong>Price Per Day:</strong> \${$price_per_day}</p>
            <p><strong>Total Price:</strong> <span class='highlight'>\${$total_price}</span></p>
            <p><strong>Status:</strong> {$status_name}</p>
            <p><strong>Payment Date:</strong> {$payment_date}</p>
            <a href='index.php' class='btn rent-btn'>🏠 Back to Home</a>
        </div>
HTML;
    } else {
        echo "<p>Error: Could not save rental. " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<p>Invalid access.</p>";
}

include 'includes/footer.php';
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f4f7f9;
    margin: 0;
    padding: 0;
}

.confirmation-box {
    max-width: 600px;
    margin: 60px auto;
    background: #ffffff;
    padding: 35px 40px;
    border-radius: 16px;
    box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1);
    text-align: center;
    animation: fadeIn 0.5s ease-in-out;
}

.confirmation-box h2 {
    background: linear-gradient(45deg, #f7b500, #2ecc71);
    color: white;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 25px;
    font-size: 24px;
}

.confirmation-box p {
    font-size: 17px;
    margin: 10px 0;
    color: #333;
}

.confirmation-box hr {
    margin: 20px 0;
    border: none;
    border-top: 1px solid #e0e0e0;
}

.highlight {
    font-weight: bold;
    color: #27ae60;
    font-size: 18px;
}

.rent-btn {
    display: inline-block;
    padding: 12px 28px;
    margin-top: 25px;
    background: #f7b500;
    color: #fff;
    text-decoration: none;
    border-radius: 30px;
    font-weight: bold;
    transition: background 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.rent-btn:hover {
    background: #e6b800;
    transform: scale(1.05);
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 600px) {
    .confirmation-box {
        padding: 25px;
        margin: 20px;
    }

    .confirmation-box h2 {
        font-size: 20px;
    }

    .confirmation-box p {
        font-size: 15px;
    }

    .rent-btn {
        width: 100%;
    }
}
</style>