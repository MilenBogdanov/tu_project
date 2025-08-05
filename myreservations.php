<?php
date_default_timezone_set('Europe/Sofia');
include 'includes/header.php';
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "<p class='login-prompt'>You must be logged in to view your reservations. <a href='login.php'>Login here</a>.</p>";
    include 'includes/footer.php';
    exit();
}

$user_id = $_SESSION['user_id'];
$now = date('Y-m-d H:i:s');


$update_returned_sql = "
    UPDATE rentals 
    SET rental_status_id = 4 
    WHERE user_id = ?
      AND rental_status_id NOT IN (3, 4)
      AND CONCAT(return_date, ' ', dropoff_time) < ?;
";
$returned_stmt = $conn->prepare($update_returned_sql);
$returned_stmt->bind_param("is", $user_id, $now);
$returned_stmt->execute();
$returned_stmt->close();


$update_active_sql = "
    UPDATE rentals 
    SET rental_status_id = 1 
    WHERE user_id = ?
      AND rental_status_id NOT IN (1, 3, 4)
      AND CONCAT(rental_date, ' ', pickup_time) <= ?
      AND CONCAT(return_date, ' ', dropoff_time) >= ?;
";
$active_stmt = $conn->prepare($update_active_sql);
$active_stmt->bind_param("iss", $user_id, $now, $now);
$active_stmt->execute();
$active_stmt->close();

$status_id = isset($_GET['status_id']) ? $_GET['status_id'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to   = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$status_id = $status_id !== '' ? $status_id : null;
$date_from = $date_from !== '' ? $date_from : null;
$date_to   = $date_to !== '' ? $date_to : null;

$query = "SELECT 
            r.rental_id, 
            r.rental_date, 
            r.return_date, 
            r.pickup_time, 
            r.dropoff_time,
            r.total_price,
            r.rental_status_id,
            s.rental_status_name,
            b.brand_name, 
            m.model_name, 
            c.image_url
          FROM rentals r
          JOIN cars c ON r.car_id = c.car_id
          JOIN models m ON c.model_id = m.model_id
          JOIN brands b ON m.brand_id = b.brand_id
          JOIN rental_status s ON r.rental_status_id = s.rental_status_id
          WHERE r.user_id = ?";

if ($status_id) {
    $query .= " AND r.rental_status_id = ?";
}
if ($date_from) {
    $query .= " AND CONCAT(r.rental_date, ' ', r.pickup_time) >= ?";
}
if ($date_to) {
    $query .= " AND CONCAT(r.return_date, ' ', r.dropoff_time) <= ?";
}

$query .= " ORDER BY r.rental_date DESC, r.pickup_time DESC";

$stmt = $conn->prepare($query);

$types = "i";
$params = [$user_id];

if ($status_id) {
    $types .= "i";
    $params[] = $status_id;
}
if ($date_from) {
    $types .= "s";
    $params[] = $date_from . ' 00:00:00';
}
if ($date_to) {
    $types .= "s";
    $params[] = $date_to . ' 23:59:59';
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$reservations = $stmt->get_result();
?>

<section class="reservations-section">
    <div class="container">
        <h1 class="catalog-title">My Reservations</h1>

        <form method="GET" class="filter-panel">
            <div class="filter-group">
                <select name="status_id">
                    <option value="">All Statuses</option>
                    <?php
                    $status_result = $conn->query("SELECT rental_status_id, rental_status_name FROM rental_status");
                    while ($status = $status_result->fetch_assoc()) {
                        $selected = ($status_id == $status['rental_status_id']) ? 'selected' : '';
                        echo "<option value='{$status['rental_status_id']}' $selected>{$status['rental_status_name']}</option>";
                    }
                    ?>
                </select>

                <input type="text" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>" placeholder="From date">
                <input type="text" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>" placeholder="To date">

                <button type="submit">Apply Filters</button>
                <a href="myreservations.php" class="clear-btn">Clear</a>
            </div>
        </form>

        <div class="cars-grid">
<?php
if ($reservations && $reservations->num_rows > 0) {
    while ($row = $reservations->fetch_assoc()) {
        $pickup = $row['rental_date'] . ' ' . $row['pickup_time'];
        $dropoff = $row['return_date'] . ' ' . $row['dropoff_time'];

        echo "<div class='car'>";
        echo "<img src='{$row['image_url']}' alt='{$row['brand_name']} {$row['model_name']}'>";
        echo "<div class='car-content'>";
        echo "<h3>{$row['brand_name']} {$row['model_name']}</h3>";
        echo "<p><strong>Status:</strong> {$row['rental_status_name']}</p>";
        echo "<p><strong>Pickup:</strong> " . date('Y-m-d H:i', strtotime($pickup)) . "</p>";
        echo "<p><strong>Dropoff:</strong> " . date('Y-m-d H:i', strtotime($dropoff)) . "</p>";
        echo "<p><strong>Total Price:</strong> {$row['total_price']} BGN</p>";

        if ($row['rental_status_id'] == 2) {
            
            echo "<form action='cancel_reservation.php' method='POST'>
                    <input type='hidden' name='rental_id' value='{$row['rental_id']}'>
                    <button type='submit' class='rent-button'>Cancel Reservation</button>
                  </form>";
        } elseif ($row['rental_status_id'] == 1) {
            echo "<p class='status-active'>Currently Active</p>";
        } elseif ($row['rental_status_id'] == 3) {
            echo "<p class='status-cancelled'>Reservation Cancelled</p>";
        } elseif ($row['rental_status_id'] == 4) {
            echo "<p class='status-completed'>Rental Completed and Returned</p>";
        }

        echo "</div></div>";
    }
} else {
    echo "<p class='no-more-cars'>No reservations found.</p>";
}
$stmt->close();
?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    flatpickr("#date_from", {
        enableTime: false,
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
    });

    flatpickr("#date_to", {
        enableTime: false,
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
    });
});
</script>
<style>
    
.reservations-section {
    padding: 40px 0;
    background-color: #f9f9f9;
}

.container {
    width: 80%;
    margin: 0 auto;
    padding: 0 20px;
}

.catalog-title {
    font-size: 2.5em;
    color: #333;
    text-align: center;
    margin-bottom: 30px;
}


.filter-panel {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 30px;
    background-color: #ffffff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 20px;
}

.filter-group select,
.filter-group input {
    padding: 12px 15px;
    font-size: 1em;
    border-radius: 5px;
    border: 1px solid #ccc;
    width: 200px;
}

.filter-group button {
    background-color: #f7b500;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    font-size: 1em;
}

.filter-group button:hover {
    background: #e6b800;
    transform: scale(1.05);
}

.clear-btn {
    background-color: #555;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    font-size: 1em;
}

.clear-btn:hover {
    background-color: #444;
    transform: scale(1.05);
}


.cars-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
}

.car {
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out;
    overflow: hidden;
}

.car:hover {
    transform: translateY(-5px);
}

.car img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    display: block;
}

.car-content {
    padding: 20px;
}

.car h3 {
    font-size: 1.4em;
    color: #333;
    margin-bottom: 15px;
}

.car p {
    margin-bottom: 10px;
    font-size: 1em;
    color: #666;
}

.car p strong {
    color: #333;
}

.rent-button {
    background-color: #f44336;
    color: white;
    padding: 12px 18px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
    font-size: 1em;
}

.rent-button:hover {
    background-color: #e53935;
}


.no-more-cars {
    font-size: 1.5em;
    text-align: center;
    color: #777;
    margin-top: 30px;
}

.status-active {
    color: #28a745;
    font-weight: bold;
    background-color: #eafaf1;
    border-left: 5px solid #28a745;
    padding: 10px 15px;
    border-radius: 5px;
    margin-top: 10px;
}

.status-cancelled {
    color: #dc3545;
    font-weight: bold;
    background-color: #faeaea;
    border-left: 5px solid #dc3545;
    padding: 10px 15px;
    border-radius: 5px;
    margin-top: 10px;
}

</style>