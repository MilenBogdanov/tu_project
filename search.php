<?php
include 'includes/header.php';
include 'includes/db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pickup_date = isset($_GET['pickup_date']) ? $_GET['pickup_date'] : '';
$pickup_time = isset($_GET['pickup_time']) ? $_GET['pickup_time'] : '';
$return_date = isset($_GET['return_date']) ? $_GET['return_date'] : '';
$return_time = isset($_GET['dropoff_time']) ? $_GET['dropoff_time'] : '';


$car_type = isset($_GET['car_type']) ? $_GET['car_type'] : '';
$gearbox_type = isset($_GET['gearbox_type']) ? $_GET['gearbox_type'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';


if ($pickup_date && $return_date && $pickup_time && $return_time) {
    $pickup_datetime = "$pickup_date $pickup_time";
    $return_datetime = "$return_date $return_time";

    if (strtotime($pickup_datetime) >= strtotime($return_datetime)) {
        echo "<div style='background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 15px; margin: 20px 0; border-radius: 8px; font-size: 1rem; font-weight: bold; text-align: center; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);'>
        <strong>Error:</strong> Please make sure the pickup date/time is before the return date/time.
        </div>";
        exit;
    }
}
?>
<div class="search-heading">
    <h2>🔍 Results for Your Search</h2>
    <p>Refine your car rental options using the filters below.</p>
</div>
<div class="filter-form-container">
    <form action="search.php" method="GET">
        <div class="filter-form">
            <div class="filter-group">
                <label for="pickup_date">Pickup Date:</label>
                <input type="text" name="pickup_date" id="pickup_date" value="<?= htmlspecialchars($pickup_date) ?>" required>
            </div>
            <div class="filter-group">
                <label for="pickup_time">Pickup Time:</label>
                <input type="text" name="pickup_time" id="pickup_time" value="<?= htmlspecialchars($pickup_time) ?>" required>
            </div>
            <div class="filter-group">
                <label for="return_date">Return Date:</label>
                <input type="text" name="return_date" id="return_date" value="<?= htmlspecialchars($return_date) ?>" required>
            </div>
            <div class="filter-group">
                <label for="dropoff_time">Dropoff Time:</label>
                <input type="text" name="dropoff_time" id="dropoff_time" value="<?= htmlspecialchars($return_time) ?>" required>
            </div>
            <div class="filter-group">
                <label for="car_type">Car Type:</label>
                <select name="car_type" id="car_type">
                    <option value="">Select Type</option>
                    <?php
                    $types_sql = "SELECT * FROM types";
                    $types_result = $conn->query($types_sql);
                    while ($type = $types_result->fetch_assoc()) {
                        $selected = ($type['type_id'] == $car_type) ? "selected" : "";
                        echo "<option value='{$type['type_id']}' $selected>{$type['type_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="gearbox_type">Gearbox Type:</label>
                <select name="gearbox_type" id="gearbox_type">
                    <option value="">Select Gearbox</option>
                    <?php
                    $gearbox_sql = "SELECT * FROM gearboxes";
                    $gearbox_result = $conn->query($gearbox_sql);
                    while ($gearbox = $gearbox_result->fetch_assoc()) {
                        $selected = ($gearbox['gearbox_id'] == $gearbox_type) ? "selected" : "";
                        echo "<option value='{$gearbox['gearbox_id']}' $selected>{$gearbox['gearbox_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="price_range">Price Range (BGN/day):</label>
                <div style="display: flex; gap: 5px;">
                    <input type="number" name="min_price" id="min_price" placeholder="Min" value="<?= htmlspecialchars($min_price) ?>">
                    <input type="number" name="max_price" id="max_price" placeholder="Max" value="<?= htmlspecialchars($max_price) ?>">
                </div>
            </div>
            <button type="submit" class="filter-btn">Apply Filters</button>
            <button type="button" onclick="clearFilters()" class="clear-filters-btn">Clear Filters</button>
        </div>
    </form>
</div>
<script>
function clearFilters() {
    document.getElementById('car_type').value = '';
    document.getElementById('gearbox_type').value = '';
    document.getElementById('min_price').value = '';
    document.getElementById('max_price').value = '';
}
</script>
<?php
if ($pickup_date && $return_date && $pickup_time && $return_time) {
    $pickup_datetime = "$pickup_date $pickup_time";
    $return_datetime = "$return_date $return_time";

    $sql = "SELECT c.car_id, m.model_name, b.brand_name, g.gearbox_name,
               c.year_manufacture, c.mileage, t.type_name,
               c.price_per_day, c.image_url,
               COUNT(r.rental_id) AS rental_count
        FROM cars c
        LEFT JOIN rentals r ON c.car_id = r.car_id AND (
            NOT (
                r.return_date <= ? OR
                r.rental_date >= ?
            )
            AND r.rental_status_id != 3
        )
        JOIN models m ON c.model_id = m.model_id
        JOIN brands b ON m.brand_id = b.brand_id
        JOIN gearboxes g ON c.gearbox_id = g.gearbox_id
        JOIN types t ON c.type_id = t.type_id
        WHERE r.rental_id IS NULL";

    if ($car_type) $sql .= " AND t.type_id = ?";
    if ($gearbox_type) $sql .= " AND g.gearbox_id = ?";
    if ($min_price) $sql .= " AND c.price_per_day >= ?";
    if ($max_price) $sql .= " AND c.price_per_day <= ?";

    $sql .= " GROUP BY c.car_id ORDER BY rental_count DESC";

    $stmt = $conn->prepare($sql);
    $params = [$pickup_datetime, $return_datetime];
    $types = "ss";
    if ($car_type) { $types .= "s"; $params[] = $car_type; }
    if ($gearbox_type) { $types .= "s"; $params[] = $gearbox_type; }
    if ($min_price) { $types .= "i"; $params[] = $min_price; }
    if ($max_price) { $types .= "i"; $params[] = $max_price; }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div class='car-listing-container'>";
        while ($car = $result->fetch_assoc()) {
            echo "<div class='car'>
                    <div class='card-image-container'>
                        <img src='{$car['image_url']}' alt='{$car['brand_name']} {$car['model_name']}'>
                    </div>
                    <div class='car-details'>
                        <h3>{$car['brand_name']} {$car['model_name']}</h3>
                        <p><strong>Gearbox:</strong> {$car['gearbox_name']}</p>
                        <p><strong>Year:</strong> {$car['year_manufacture']}</p>
                        <p><strong>Type:</strong> {$car['type_name']}</p>
                        <p><strong>Mileage:</strong> " . number_format($car['mileage']) . " km</p>
                    </div>
                    <div class='car-price'>
                        <div class='price'>{$car['price_per_day']} <span class='currency'>BGN/day</span></div>
                        <a href='rent.php?car_id={$car['car_id']}' class='rent-button'>Rent</a>
                    </div>
                  </div>";
        }
        echo "</div>";
    } else {
        echo "<div class='no-cars-message'>
                <p>No cars available for the selected dates and times. Please try adjusting your filters.</p>
              </div>";
    }
    $stmt->close();
} else {
    echo "<p>Please select valid dates and times.</p>";
}
include 'includes/footer.php';
?>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
const pickupDatePicker = flatpickr("#pickup_date", {
    dateFormat: "Y-m-d",
    minDate: "today",
    onChange: function(selectedDates, dateStr, instance) {
        returnDatePicker.set('minDate', dateStr);
    }
});

const returnDatePicker = flatpickr("#return_date", {
    dateFormat: "Y-m-d",
    minDate: "today"
});

flatpickr("#pickup_time", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i"
});

flatpickr("#dropoff_time", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i"
});
</script>
<style>

.clear-filters-btn {
    background-color: #6c757d;
    color: white;
    border: 1px solid #6c757d;
    padding: 10px 18px;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease, border-color 0.3s ease;
}


.clear-filters-btn:hover {
    background-color: #5a6268;
    border-color: #5a6268;
}


.clear-filters-btn:focus {
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.5);
}
.filter-form-container {
    margin: 30px auto;
    padding: 20px;
    max-width: 90%;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
    overflow-x: auto;
}

.filter-form {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 25px;
    justify-content: center;
}

.filter-group {
    display: flex;
    flex-direction: column;
    min-width: 160px;
}

.filter-group label {
    font-size: 0.8rem;
    color: #333;
    margin-bottom: 4px;
}

.filter-group input,
.filter-group select {
    padding: 6px 10px;
    font-size: 0.85rem;
    border: 1px solid #ccc;
    border-radius: 6px;
    background-color: #f9f9f9;
    width: 100%;
}

.filter-btn {
    padding: 10px 18px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #fff;
    background-color: #f7b500;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.filter-btn:hover {
    background: #e6b800;
    transform: scale(1.05);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.car-listing-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
    justify-content: center;
}

.car {
    max-width: 400px;
    margin: 0 auto;
}


.card-image-container img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
}

.card-image-container img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.car-details {
    padding: 15px;
}

.car-details h3 {
    margin: 0;
    font-size: 1.5em;
    color: #333;
}

.car-details p {
    margin: 10px 0;
    font-size: 1em;
    color: #555;
}

.car-price {
    background-color: #f8f8f8;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #ddd;
}

.car-price .price {
    font-size: 1.2em;
    font-weight: bold;
    color: green;
}

.rent-button {
    background-color: #f7b500;
    color: #fff;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.rent-button:hover {
    background: #e6b800;
    transform: scale(1.05);
}

.currency {
    font-size: 0.8em;
    color: #666;
}

.search-heading {
    text-align: center;
    margin: 40px auto 10px;
    padding: 10px;
    color: #333;
    animation: fadeIn 0.5s ease-in-out;
}

.search-heading h2 {
    font-size: 2rem;
    color: #1a1a1a;
    margin-bottom: 5px;
    font-weight: bold;
}

.search-heading p {
    font-size: 1rem;
    color: #666;
}

.no-cars-message {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    padding: 20px;
    margin-top: 20px;
    border-radius: 8px;
    font-size: 1.2rem;
    font-weight: bold;
    text-align: center;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>