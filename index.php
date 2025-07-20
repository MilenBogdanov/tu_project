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

<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0; top: 0;
        width: 100%; height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: #fff;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
        border-radius: 10px;
        text-align: center;
        position: relative;
    }

    .close {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        position: absolute;
        right: 15px;
        top: 10px;
        cursor: pointer;
    }

    .close:hover { color: black; }

    .btn-login {
        background-color: #ffcc00;
        color: white;
        font-weight: bold;
        font-size: 16px;
        padding: 12px 20px;
        border-radius: 8px;
        text-decoration: none;
        margin-top: 20px;
        transition: background-color 0.3s ease;
    }

    .btn-login:hover {
        background-color: #005b56;
    }

    .show-more {
        text-align: center;
        margin-top: 20px;
    }

    .show-more-btn {
        background-color: #f7b500;
        color: white;
        font-weight: bold;
        font-size: 16px;
        padding: 12px 20px;
        border-radius: 8px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .show-more-btn:hover {
        background: #e6b800;
        transform: scale(1.05);
    }

    .no-more-cars {
        text-align: center;
        margin-top: 20px;
        font-size: 18px;
        color: #555;
    }
    
    .contact-help {
    background-color: #f7b500;
    padding: 40px 0;
    text-align: center;
}

.contact-help h2 {
    color: #1a1a1a;
    font-size: 32px;
    font-weight: 800;
}

.contact-help p {
    font-size: 20px;
    color: #1a1a1a;
    margin-bottom: 30px;
}

.contact-phone {
    font-size: 42px;
    font-weight: 900;
    color: #1a1a1a;
    margin-bottom: 30px;
}

.contact-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.contact-buttons a {
    padding: 12px 24px;
    border: 2px solid #1a1a1a;
    font-weight: bold;
    color: #1a1a1a;
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.contact-buttons a:hover {
    background-color: #222;
    color: #f7b500;
}


.vip-cars {
    background-color: #151515;
    color: #fff;
    padding: 30px 0;
}

.vip-cars .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.vip-cars h2 {
    font-size: 2.5em;
    text-align: center;
    color: #f7b500;
    margin-bottom: 40px;
    text-shadow: 1px 1px 3px #000;
}

.vip-cars-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.vip-car {
    background-color: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.6);
    transition: transform 0.3s ease;
}

.vip-car:hover {
    transform: translateY(-5px);
}

.vip-car img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    display: block;
}

.vip-car-content {
    padding: 20px;
}

.vip-car h3 {
    margin-top: 0;
    font-size: 22px;
    color: #bd8b02;
}

.vip-car p {
    margin: 8px 0;
    font-size: 15px;
    color: black;
}

.vip-price {
    font-size: 22px;
    font-weight: bold;
    color: #0a9396;
    background-color: #333;
    padding: 10px 15px;
    border-radius: 8px;
    margin-top: 10px;
    display: inline-block;
}

.vip-rent-btn {
    display: inline-block;
    background-color: #f7b500;
    color: #fff;
    font-weight: bold;
    padding: 10px 20px;
    margin-top: 15px;
    border-radius: 8px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.vip-rent-btn:hover {
    background-color: #e0a000;
}

.no-vip-cars {
    color: red;
    text-align: center;
    font-size: 18px;
    margin-top: 20px;
    font-weight: bold;
}

</style>
