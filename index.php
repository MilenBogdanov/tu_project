<?php include 'includes/header.php'; ?>
<?php include 'includes/db_connect.php'; ?>
<?php
$is_logged_in = isset($_SESSION['user_id']);
$limit = 6;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
?>

<section class="hero">
    <div class="container">
        <h1>Find Your Ideal Rental Car</h1>
        <form class="search-form" action="search.php" method="GET">
            <div>
                <label for="pickup_date">Pickup Date</label>
                <input type="date" id="pickup_date" name="pickup_date" required>
            </div>
            <div>
                <label for="pickup_time">Pickup Time</label>
                <select id="pickup_time" name="pickup_time" required></select>
            </div>
            <div>
                <label for="return_date">Return Date</label>
                <input type="date" id="return_date" name="return_date" required>
            </div>
            <div>
                <label for="dropoff_time">Return Time</label>
                <select id="dropoff_time" name="dropoff_time" required></select>
            </div>
            <button type="submit">Search</button>
        </form>
    </div>
</section>

<script>
    function generateTimeOptions(selectElement) {
        selectElement.innerHTML = "";
        for (let h = 0; h < 24; h++) {
            for (let m = 0; m < 60; m += 30) {
                let hour = h.toString().padStart(2, "0");
                let minute = m.toString().padStart(2, "0");
                let time = `${hour}:${minute}`;
                let option = new Option(time, time);
                selectElement.appendChild(option);
            }
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        flatpickr("#pickup_date", { 
            dateFormat: "Y-m-d", 
            locale: "en",
            minDate: "today",
        });
        
        flatpickr("#return_date", { 
            dateFormat: "Y-m-d", 
            locale: "en",
            minDate: "today",
        });

        generateTimeOptions(document.getElementById("pickup_time"));
        generateTimeOptions(document.getElementById("dropoff_time"));

        const pickupDate = document.getElementById("pickup_date");
        const returnDate = document.getElementById("return_date");

        
        pickupDate.addEventListener("change", function () {
            returnDate.value = "";
            returnDate.min = pickupDate.value;
        });
    });

    function checkLogin(event) {
        const isLoggedIn = <?php echo json_encode($is_logged_in); ?>;
        if (!isLoggedIn) {
            event.preventDefault();
            showLoginModal();
        }
    }

    function showLoginModal() {
        document.getElementById('loginModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('loginModal').style.display = 'none';
    }

    window.onclick = function (event) {
        const modal = document.getElementById('loginModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const showMoreBtn = document.getElementById("showMoreBtn");
    const carsGrid = document.getElementById("carsGrid");
    const showMoreContainer = document.getElementById("showMoreContainer");

    if (showMoreBtn) {
        showMoreBtn.addEventListener("click", function () {
            const offset = parseInt(this.dataset.offset);
            fetch(`index.php?offset=${offset}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            })
            .then(res => res.text())
            .then(data => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data;

                const newCars = tempDiv.querySelectorAll(".car");
                if (newCars.length === 0) {
                    showMoreContainer.innerHTML = "<p class='no-more-cars'>No More Featured Cars.</p>";
                } else {
                    newCars.forEach(car => carsGrid.appendChild(car));
                    showMoreBtn.dataset.offset = offset + newCars.length;
                }
            });
        });
    }
});
</script>


<div id="loginModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>You need to log in</h2>
        <p>You must be logged into Rent A Car. Please log in first.</p>
        <a href="login.php" class="btn-login">Log In</a>
    </div>
</div>

<section class="featured-cars">
    <div class="container">
        <h2 class="featured-title">Discover the Hottest Rides of the Season</h2>
        <div class="cars-grid" id="carsGrid">
            <?php
            $sql = "SELECT c.car_id, m.model_name, b.brand_name, g.gearbox_name,
               c.year_manufacture, c.mileage, t.type_name,
               c.price_per_day, c.image_url,
               COUNT(r.rental_id) AS rental_count
        FROM rentals r
        JOIN cars c ON r.car_id = c.car_id
        JOIN models m ON c.model_id = m.model_id
        JOIN brands b ON m.brand_id = b.brand_id
        JOIN gearboxes g ON c.gearbox_id = g.gearbox_id
        JOIN types t ON c.type_id = t.type_id
        GROUP BY c.car_id
        ORDER BY rental_count DESC
        LIMIT ?, ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $offset, $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($car = $result->fetch_assoc()) {
                    echo "<div class='car'>
                        <img src='{$car['image_url']}' alt='{$car['brand_name']} {$car['model_name']}'>
                        <div class='car-content' style='display: flex; justify-content: space-between; align-items: center; padding: 15px;'>
                            <div class='car-info-left'>
                                <h3>{$car['brand_name']} {$car['model_name']}</h3>
                                <p><strong>Gearbox:</strong> {$car['gearbox_name']}</p>
                                <p><strong>Year:</strong> {$car['year_manufacture']}</p>
                                <p><strong>Type:</strong> {$car['type_name']}</p>
                                <p><strong>Mileage:</strong> " . number_format($car['mileage']) . " km</p>
                                <p><strong>Times Rented:</strong> {$car['rental_count']}</p>
                            </div>
                            <div class='car-info-right' style='text-align: right;'>
                                <div style='font-size: 22px; font-weight: bold; color: #0a9396; background-color: #e0f7f6; padding: 8px 14px; border-radius: 10px; margin-bottom: 10px; display: inline-block;'>
                                    {$car['price_per_day']} <span style='font-size: 14px; font-weight: normal; color: #555;'>BGN/day</span>
                                </div><br><br><br><br>
                                <a href='rent.php?car_id={$car['car_id']}' onclick='checkLogin(event)' style='display: inline-block; background-color: #f7b500; color: white; font-weight: bold; font-size: 15px; padding: 10px 20px; border: none; border-radius: 8px; text-decoration: none; transition: 0.3s ease; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                                    Rent
                                </a>
                            </div>
                        </div>
                    </div>";
                }
            } else {
                echo "<p class='no-more-cars'>There are no rented cars yet. Be the first to rent!</p>";
            }

            $stmt->close();

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                exit;
            }
            ?>
        </div>

        
        <div class="show-more" id="showMoreContainer">
            <button id="showMoreBtn" class="show-more-btn" data-offset="<?php echo $offset + $limit; ?>">Show More</button>
        </div>
    </div>
</section>
<section class="vip-cars">
    <div class="container">
        <h2>Exclusive Special Cars</h2>
        <div class="vip-cars-grid">
            <?php
            $stmt = $conn->prepare("CALL GetVipCars()");
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($car = $result->fetch_assoc()) {
                    echo "<div class='vip-car'>
                        <img src='{$car['image_url']}' alt='{$car['brand_name']} {$car['model_name']}'>
                        <div class='car-content' style='display: flex; justify-content: space-between; align-items: center; padding: 15px;'>
                            <div class='car-info-left'>
                                <h3>{$car['brand_name']} {$car['model_name']}</h3>
                                <p><strong>Gearbox:</strong> {$car['gearbox_name']}</p>
                                <p><strong>Year:</strong> {$car['year_manufacture']}</p>
                                <p><strong>Type:</strong> {$car['type_name']}</p>
                                <p><strong>Mileage:</strong> " . number_format($car['mileage']) . " km</p>
                            </div>
                            <div class='car-info-right' style='text-align: right;'>
                                <div style='font-size: 22px; font-weight: bold; color: #0a9396; background-color: #e0f7f6; padding: 8px 14px; border-radius: 10px; margin-bottom: 10px; display: inline-block; white-space: nowrap;'>
                                    {$car['price_per_day']} <span style='font-size: 14px; font-weight: normal; color: #555;'>BGN/day</span>
                                </div><br><br><br><br><br>
                                <a href='rent.php?car_id={$car['car_id']}' onclick='checkLogin(event)' style='display: inline-block; background-color: #f7b500; color: white; font-weight: bold; font-size: 15px; padding: 10px 20px; border: none; border-radius: 8px; text-decoration: none; transition: 0.3s ease; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                                    Rent
                                </a>
                            </div>
                        </div>
                    </div>";
                }
            } else {
                echo '<p class="no-vip-cars">No VIP cars available at the moment. Check back soon!</p>';
            }

            
            $conn->next_result();
            $stmt->close();
            ?>
        </div>
    </div>
</section>

<section class="contact-help">
    <div class="container">
        <h2>HAVE QUESTIONS?</h2>
        <p>
            Get free advice and assistance with choosing and receiving a car from an expert over the phone.
        </p>
        <div class="contact-phone">
            +359 87 625 1510
        </div>
        <div class="contact-buttons">
            <a href="viber://chat?number=+359876251510">Message on Viber</a>
            <a href="https://www.facebook.com/profile.php?id=100013392637031">Message on Facebook</a>
            <a href="https://mail.google.com/mail/?to=milenb53@gmail.com&subject=Delivery%20to&body=Hello%2C%0A%0AYour%20message%20here." target="_blank">Send an Email</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>