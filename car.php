<?php include 'includes/header.php'; ?>
<?php include 'includes/db_connect.php'; ?>

<section class="car-catalog">
    <div class="container">
        <h1 class="catalog-title">Explore Our Car Collection</h1>
        <p class="catalog-description">
            Discover a diverse fleet of vehicles, from fuel-efficient economy cars to high-end luxury models—carefully selected to suit every journey, style, and budget.
        </p>

        <?php
        if (!isset($_SESSION['user_id'])) { 
            echo "<p class='login-prompt'>You must be logged in to rent a car. <a href='login.php'>Login here</a>.</p>";
        }
        ?>
        
        
        <form id="filter-form" class="filter-panel">
            <div class="filter-group">
                <select name="brand_id" id="brand_id">
                    <option value="">All Brands</option>
                    <?php
                    $brands = $conn->query("SELECT brand_id, brand_name FROM brands ORDER BY brand_name");
                    while ($b = $brands->fetch_assoc()) {
                        echo "<option value='{$b['brand_id']}'>{$b['brand_name']}</option>";
                    }
                    ?>
                </select>

                <select name="model_id" id="model_id">
                    <option value="">All Models</option>
                </select>

                <select name="gearbox_id">
                    <option value="">Any Gearbox</option>
                    <?php
                    $gearboxes = $conn->query("SELECT gearbox_id, gearbox_name FROM gearboxes");
                    while ($g = $gearboxes->fetch_assoc()) {
                        echo "<option value='{$g['gearbox_id']}'>{$g['gearbox_name']}</option>";
                    }
                    ?>
                </select>

                <select name="type_id">
                    <option value="">Any Type</option>
                    <?php
                    $types = $conn->query("SELECT type_id, type_name FROM types");
                    while ($t = $types->fetch_assoc()) {
                        echo "<option value='{$t['type_id']}'>{$t['type_name']}</option>";
                    }
                    ?>
                </select>
                
                <select name="car_status_id">
    <option value="">Any Status</option>
    <?php
    $statuses = $conn->query("SELECT car_status_id, car_status_name FROM car_status ORDER BY car_status_name");
    while ($status = $statuses->fetch_assoc()) {
        echo "<option value='{$status['car_status_id']}'>{$status['car_status_name']}</option>";
    }
    ?>
</select>
                
                <input type="number" name="year_from" placeholder="Year From" min="1990" max="2025">
                <input type="number" name="year_to" placeholder="Year To" min="1990" max="2025">
                <input type="number" name="mileage_max" placeholder="Max Mileage (km)">
                <input type="number" name="price_max" placeholder="Max Price (BGN)">
                <button type="submit">Filter</button>
                <button type="button" id="clear-filters" class="clear-btn" style="
    background-color: #f5f5f5;
    color: #333;
    font-weight: bold;
    font-size: 16px;
    padding: 12px 25px;
    border: 2px solid #ccc;
    border-radius: 10px;
    text-decoration: none;
    transition: background-color 0.3s ease, transform 0.3s ease;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    min-width: 120px;
    margin-left: 15px;
    cursor: pointer;
">

Clear Filters</button>
            </div>
        </form>

        <div class="cars-grid">
            <?php
// Get the selected car status from the filter form (if any)
$car_status_id = isset($_POST['car_status_id']) ? (int)$_POST['car_status_id'] : NULL;

// Call the modified stored procedure with the car_status_id
$query = "CALL GetAllCars(?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $car_status_id); // Bind the car_status_id parameter
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($car = $result->fetch_assoc()) {
        echo "<div class='car'>
    <div class='car-image-wrapper'>
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
            echo "<a href='rent.php?car_id={$car['car_id']}' class='rent-button'>Rent</a>";
        } else {
            echo "<a href='login.php' class='rent-button'>Login to Rent</a>";
        }

    echo "</div>
</div>";
    }
} else {
    echo "<p class='no-more-cars'>There are no cars in the catalog at this moment.</p>";
}

$stmt->close();
?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
document.getElementById('filter-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('fetch_cars.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(html => {
        document.querySelector('.cars-grid').innerHTML = html;
    });
});


document.getElementById('brand_id').addEventListener('change', function() {
    const brandId = this.value;
    fetch('get_models.php?brand_id=' + brandId)
        .then(res => res.json())
        .then(models => {
            const modelSelect = document.getElementById('model_id');
            modelSelect.innerHTML = '<option value="">All Models</option>';
            models.forEach(m => {
                modelSelect.innerHTML += `<option value="${m.model_id}">${m.model_name}</option>`;
            });
        });
});


document.getElementById('clear-filters').addEventListener('click', function() {
    const filterForm = document.getElementById('filter-form');
    
    
    filterForm.reset();

    
    const modelSelect = document.getElementById('model_id');
    modelSelect.innerHTML = '<option value="">All Models</option>';

    
    const brandId = document.getElementById('brand_id').value;
    if (brandId) {
        fetch('get_models.php?brand_id=' + brandId)
            .then(res => res.json())
            .then(models => {
                modelSelect.innerHTML = '<option value="">All Models</option>';
                models.forEach(m => {
                    modelSelect.innerHTML += `<option value="${m.model_id}">${m.model_name}</option>`;
                });
            });
    }
    
    
    document.getElementById('filter-form').submit();
});
</script>
<script>
document.getElementById('clear-filters').addEventListener('mouseover', function() {
    this.style.backgroundColor = '#e0e0e0';
    this.style.transform = 'scale(1.05)';
});

document.getElementById('clear-filters').addEventListener('mouseout', function() {
    this.style.backgroundColor = '#f5f5f5';
    this.style.transform = 'scale(1)';
});

document.getElementById('clear-filters').addEventListener('focus', function() {
    this.style.boxShadow = '0 0 0 3px rgba(10, 147, 150, 0.2)';
});

document.getElementById('clear-filters').addEventListener('blur', function() {
    this.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.1)';
});
</script>

<style>
.cars-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 0;
}

.car {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.car:hover {
    transform: translateY(-8px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.car-top {
    background: url('/path/to/your/gold_background.png') center/cover no-repeat;
    padding: 20px;
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.car-top img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 12px;
}

.car-content {
    padding: 20px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.car-content h3 {
    font-size: 22px;
    margin: 0 0 10px;
    color: #222;
    font-weight: 700;
}

.car-content p {
    font-size: 15px;
    color: #555;
    margin: 4px 0;
}

.car-content p strong {
    color: #333;
}

.car-price {
    font-size: 24px;
    font-weight: bold;
    color: #008080;
    background-color: #e0f7f6;
    padding: 10px;
    border-radius: 12px;
    margin: 12px 0;
    text-align: center;
}

.car-price span {
    font-size: 14px;
    font-weight: normal;
}

.rent-button {
    display: inline-block;
    margin-top: auto;
    background-color: #f7b500;
    color: white;
    font-weight: bold;
    font-size: 16px;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    text-align: center;
    text-decoration: none;
    transition: background 0.3s ease, transform 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
}

.rent-button:hover {
    background: #e6b800;
    transform: scale(1.05);
}

.catalog-title {
    font-size: 40px;
    font-weight: 800;
    color: #1a1a1a;
    text-align: center;
    margin-bottom: 10px;
}

.catalog-description {
    font-size: 18px;
    color: #555;
    text-align: center;
    max-width: 700px;
    margin: 0 auto 40px;
    line-height: 1.6;
}

.login-prompt {
    text-align: center;
    font-size: 18px;
    color: #f44336;
    margin-top: 20px;
}

.login-prompt a {
    color: #2196F3;
    text-decoration: none;
    font-weight: bold;
}

.login-prompt a:hover {
    text-decoration: underline;
}

.filter-panel {
    margin: 40px auto;
    display: flex;
    justify-content: center;
}

.filter-group {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
    justify-content: center;
}

.filter-panel select,
.filter-panel input {
    padding: 12px 14px;
    font-size: 15px;
    border-radius: 10px;
    border: 1px solid #ccc;
    min-width: 160px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.filter-panel select:focus,
.filter-panel input:focus {
    border-color: #0a9396;
    outline: none;
    box-shadow: 0 0 0 3px rgba(10, 147, 150, 0.2);
}

.filter-panel button {
    background: #f7b500;
    color: white;
    padding: 12px 24px;
    border: none;
    font-weight: bold;
    font-size: 15px;
    border-radius: 10px;
    cursor: pointer;
    transition: 0.3s ease;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    min-width: auto;
    white-space: nowrap;
}

.filter-panel button:hover {
    background: #e6b800;
    transform: scale(1.05);
}
</style>