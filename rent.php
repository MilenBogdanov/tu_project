<?php
include 'includes/header.php';
include 'includes/db_connect.php';

$car_id = intval($_GET['car_id']);


$sql = "SELECT c.car_id, m.model_name, b.brand_name, c.price_per_day, c.image_url 
        FROM cars c 
        JOIN models m ON c.model_id = m.model_id 
        JOIN brands b ON m.brand_id = b.brand_id 
        WHERE c.car_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Car not found.</p>";
    include 'includes/footer.php';
    exit;
}

$car = $result->fetch_assoc();


$sql = "SELECT c.car_id, m.model_name, b.brand_name, c.price_per_day, c.image_url 
        FROM cars c 
        JOIN models m ON c.model_id = m.model_id 
        JOIN brands b ON m.brand_id = b.brand_id 
        WHERE c.car_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Car not found.</p>";
    include 'includes/footer.php';
    exit;
}

$car = $result->fetch_assoc();


$sql_rentals = "SELECT rental_date, return_date 
                FROM rentals 
                WHERE car_id = ? 
                AND rental_status_id != 3";
$stmt_rentals = $conn->prepare($sql_rentals);
$stmt_rentals->bind_param("i", $car_id);
$stmt_rentals->execute();
$result_rentals = $stmt_rentals->get_result();

$rented_dates = [];
while ($rental = $result_rentals->fetch_assoc()) {
    $rental_date = $rental['rental_date'];
    $return_date = $rental['return_date'];

    
    $start_date = strtotime($rental_date);
    $end_date = strtotime($return_date);
    
    while ($start_date <= $end_date) {
        $rented_dates[] = date('Y-m-d', $start_date);
        $start_date = strtotime("+1 day", $start_date);
    }
}

?>

<section class="rent-car-section">
    <div class="rent-car-container flex-wrapper">
        <div class="rent-car-info">
            <h2 class="rent-car-title">Rent: <?php echo $car['brand_name'] . ' ' . $car['model_name']; ?></h2>
            <img src="<?php echo $car['image_url']; ?>" alt="<?php echo $car['brand_name']; ?>" class="rent-car-image">
            <p class="rent-car-price">Price: <?php echo number_format($car['price_per_day'], 2); ?> BGN per day</p>
        </div>
        <form action="confirm_rental.php" method="POST" class="rent-form" id="rental-form">
            <input type="hidden" name="car_id" value="<?php echo $car['car_id']; ?>">
            
    <div class="form-description">
        <h3 style="margin-bottom: 10px; font-size: 22px; font-weight: 600; color: #2c3e50;">Rental Details</h3>
        <p style="font-size: 16px; color: #555; margin-bottom: 25px;">
            Please fill out the form below to rent this vehicle. Make sure to select valid dates and times.
        </p>
    </div>
            <div class="form-group">
    <label for="pickup_date">Pickup Date:</label>
    <input type="text" name="pickup_date" id="pickup_date" class="date-input" required>
</div>

<div class="form-group">
    <label for="return_date">Return Date:</label>
    <input type="text" name="return_date" id="return_date" class="date-input" required>
</div>
            <div class="form-group">
    <label for="pickup_time">Pickup Time:</label>
    <input type="time" name="pickup_time" id="pickup_time" required>
</div>

<div class="form-group">
    <label for="dropoff_time">Dropoff Time:</label>
    <input type="time" name="dropoff_time" id="dropoff_time" required>
</div>
            <div class="form-group">
                <label for="pickup_location">Pickup Location:</label>
                <input type="text" name="pickup_location" required>
            </div>
            <p id="total-price" style="font-size: 20px; font-weight: bold; text-align: center; margin-top: 20px;"></p>
            <button type="submit" class="btn rent-btn">Confirm Rental</button>
            
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    const rentedDates = <?php echo json_encode($rented_dates); ?>;

    
    function initFlatpickr(inputId) {
        flatpickr(inputId, {
            minDate: "today",
            dateFormat: "Y-m-d",
            disable: rentedDates,
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                const date = dayElem.dateObj;
                const formatted = flatpickr.formatDate(date, "Y-m-d");
                if (rentedDates.includes(formatted)) {
                    dayElem.style.backgroundColor = "#f44336";
                    dayElem.style.color = "#fff";
                    dayElem.style.cursor = "not-allowed";
                }
            },
            onChange: function(selectedDates, dateStr, instance) {
                if (instance.element.id === "pickup_date" && selectedDates.length > 0) {
                    const pickupDate = selectedDates[0];
                    const returnPicker = document.getElementById("return_date")._flatpickr;
                    returnPicker.set("minDate", pickupDate);
                }
            }
        });
    }

    initFlatpickr("#pickup_date");
    initFlatpickr("#return_date");

    document.getElementById('rental-form').addEventListener('submit', function(event) {
    const pickupDate = document.getElementById('pickup_date').value;
    const returnDate = document.getElementById('return_date').value;
    const pickupTime = document.getElementById('pickup_time').value;
    const dropoffTime = document.getElementById('dropoff_time').value;

    if (new Date(returnDate) < new Date(pickupDate)) {
        alert("Return date cannot be earlier than pickup date.");
        event.preventDefault();
    } else if (pickupDate === returnDate && pickupTime >= dropoffTime) {
        alert("Dropoff time must be after pickup time on the same day.");
        event.preventDefault();
    }
});
</script>
<script>
    const pricePerDay = <?php echo $car['price_per_day']; ?>;

    function calculateTotalPrice() {
        const pickupVal = document.getElementById('pickup_date').value;
        const returnVal = document.getElementById('return_date').value;
        const totalDisplay = document.getElementById('total-price');

        if (pickupVal && returnVal) {
            const start = new Date(pickupVal);
            const end = new Date(returnVal);

            if (end >= start) {
                const diffTime = end.getTime() - start.getTime();
                let days = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                days = Math.max(1, days);

                const total = days * pricePerDay;
                totalDisplay.textContent = `Total Price: ${total.toFixed(2)} BGN for ${days} day(s)`;
            } else {
                totalDisplay.textContent = '';
            }
        } else {
            totalDisplay.textContent = '';
        }
    }

    document.getElementById('pickup_date').addEventListener('change', calculateTotalPrice);
    document.getElementById('return_date').addEventListener('change', calculateTotalPrice);
</script>

<style>

.rent-car-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: #f9f9f9;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 40px;
    margin-top: 20px;
}

.rent-car-container {
    display: flex;
    flex-direction: row;
    gap: 80px;
    justify-content: center;
    align-items: flex-start;
    flex-wrap: wrap;
    width: 100%;
    max-width: 1200px;
}

.rent-car-info {
    flex: 1;
    max-width: 450px;
    text-align: center;
}

.rent-car-title {
    font-size: 32px;
    font-weight: bold;
    color: #333;
}

.rent-car-image {
    width: 100%;
    max-width: 450px;
    height: auto;
    margin-top: 20px;
    border-radius: 12px;
}

.rent-car-price {
    font-size: 20px;
    font-weight: bold;
    color: #e74c3c;
    margin-top: 10px;
}

.rent-form {
    flex: 1;
    max-width: 500px;
    width: 100%;
    background-color: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    font-size: 16px;
    color: #333;
    display: block;
    margin-bottom: 5px;
}

.form-group input {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: #3498db;
}

.rent-btn {
    background-color: #f7b500;
    color: white;
    padding: 15px 25px;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    width: 100%;
    transition: 0.3s ease;
}

.rent-btn:hover {
    background-color: #e6b800;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .rent-car-container {
        flex-direction: column;
        gap: 20px;
    }

    .rent-car-info,
    .rent-form {
        max-width: 100%;
    }
}

.date-input {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-sizing: border-box;
}

#total-price {
    color: #27ae60;
}

.form-group input[type="time"] {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-sizing: border-box;
}
</style>