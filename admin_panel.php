<?php
include 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'admin') {
    header("Location: index.php");
    exit;
}

function fetchOptions($conn, $table, $id_col, $name_col) {
    $sql = "SELECT $id_col, $name_col FROM $table";
    $result = $conn->query($sql);

    if (!$result) {
        die("SQL Error in fetchOptions(): " . $conn->error . "<br>Query: $sql");
    }

    $options = [];
    while ($row = $result->fetch_assoc()) {
        $options[] = $row;
    }
    return $options;
}

$colors = fetchOptions($conn, 'colors', 'color_id', 'color_name');
$types = fetchOptions($conn, 'types', 'type_id', 'type_name');
$gearboxes = fetchOptions($conn, 'gearboxes', 'gearbox_id', 'gearbox_name');
$statuses = fetchOptions($conn, 'car_status', 'car_status_id', 'car_status_name');

$car_list_result = $conn->query("CALL GetCarList()");
$cars_for_deletion = [];
while ($row = $car_list_result->fetch_assoc()) {
    $cars_for_deletion[] = $row;
}
$car_list_result->free();
$conn->next_result();


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['brand_name'])) {
    $brand_name = trim($_POST['brand_name']);
    $model_name = trim($_POST['model_name']);
    $color_id = $_POST['color_id'];
    $type_id = $_POST['type_id'];
    $gearbox_id = $_POST['gearbox_id'];
    $year = $_POST['year_manufacture'];
    $mileage = $_POST['mileage'];
    $price = $_POST['price_per_day'];
    $status_id = $_POST['car_status_id'];

    $upload_dir = 'images/';
    $image_name = basename($_FILES['car_image']['name']);
    $target_file = $upload_dir . uniqid() . '_' . $image_name;

    if (move_uploaded_file($_FILES['car_image']['tmp_name'], $target_file)) {
        $image_url = $target_file;
    } else {
        $error_message = "Image upload failed!";
    }

    if (!isset($error_message)) {
        if (!preg_match('/^\d{4}$/', $year)) {
            $error_message = "Invalid year format!";
        } else {
            $brand_stmt = $conn->prepare("SELECT brand_id FROM brands WHERE brand_name = ?");
            $brand_stmt->bind_param("s", $brand_name);
            $brand_stmt->execute();
            $brand_result = $brand_stmt->get_result();

            if ($brand_result->num_rows > 0) {
                $row = $brand_result->fetch_assoc();
                $brand_id = $row['brand_id'];
            } else {
                $insert_brand = $conn->prepare("INSERT INTO brands (brand_name) VALUES (?)");
                $insert_brand->bind_param("s", $brand_name);
                $insert_brand->execute();
                $brand_id = $insert_brand->insert_id;
                $insert_brand->close();
            }
            $brand_stmt->close();

            $model_stmt = $conn->prepare("SELECT model_id FROM models WHERE model_name = ? AND brand_id = ?");
            $model_stmt->bind_param("si", $model_name, $brand_id);
            $model_stmt->execute();
            $model_result = $model_stmt->get_result();

            if ($model_result->num_rows > 0) {
                $row = $model_result->fetch_assoc();
                $model_id = $row['model_id'];
            } else {
                $insert_model = $conn->prepare("INSERT INTO models (model_name, brand_id) VALUES (?, ?)");
                $insert_model->bind_param("si", $model_name, $brand_id);
                $insert_model->execute();
                $model_id = $insert_model->insert_id;
                $insert_model->close();
            }
            $model_stmt->close();

            $stmt = $conn->prepare("INSERT INTO cars (model_id, color_id, type_id, gearbox_id, year_manufacture, mileage, price_per_day, car_status_id, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiisidis", $model_id, $color_id, $type_id, $gearbox_id, $year, $mileage, $price, $status_id, $image_url);

            if ($stmt->execute()) {
                $success_message = "Car added successfully!";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_car_id'])) {
    $delete_car_id = intval($_POST['delete_car_id']);

    $image_stmt = $conn->prepare("SELECT image_url FROM cars WHERE car_id = ?");
    if ($image_stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $image_stmt->bind_param("i", $delete_car_id);
    $image_stmt->execute();
    $image_stmt->bind_result($image_url);
    $image_stmt->fetch();
    $image_stmt->close();

    $delete_stmt = $conn->prepare("DELETE FROM cars WHERE car_id = ?");
    $delete_stmt->bind_param("i", $delete_car_id);

    if ($delete_stmt->execute()) {
        if (!empty($image_url) && file_exists($image_url)) {
            unlink($image_url);
        }
        $success_message = "Car deleted successfully!";
    } else {
        $error_message = "Deletion failed: " . $delete_stmt->error;
    }
    $delete_stmt->close();

    // Refresh cars for deletion
    $car_list_result = $conn->query("CALL GetCarList()");
    $cars_for_deletion = [];
    if ($car_list_result) {
        while ($row = $car_list_result->fetch_assoc()) {
            $cars_for_deletion[] = $row;
        }
        $car_list_result->free();
        $conn->next_result();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_car'])) {
    $car_id = intval($_POST['update_car_id']);
    $mileage = intval($_POST['mileage']);
    $year = intval($_POST['year_manufacture']);
    $price = floatval($_POST['price_per_day']);
    $status_id = intval($_POST['car_status_id']);

    $update_query = "UPDATE cars SET mileage = ?, year_manufacture = ?, price_per_day = ?, car_status_id = ?";

    if (!empty($_FILES['car_image']['name'])) {
        $upload_dir = 'images/';
        $image_name = basename($_FILES['car_image']['name']);
        $target_file = $upload_dir . uniqid() . '_' . $image_name;

        if (move_uploaded_file($_FILES['car_image']['tmp_name'], $target_file)) {
            $update_query .= ", image_url = ?";
        }
    }

    $update_query .= " WHERE car_id = ?";

    $stmt = $conn->prepare($update_query);

    if (!empty($_FILES['car_image']['name'])) {
        $stmt->bind_param("iiisi", $mileage, $year, $price, $status_id, $target_file, $car_id);
    } else {
        $stmt->bind_param("iiisi", $mileage, $year, $price, $status_id, $car_id);
    }

    if ($stmt->execute()) {
        echo "<div class='success'>Car updated successfully.</div>";
    } else {
        echo "<div class='error'>Error updating car: " . htmlspecialchars($stmt->error) . "</div>";
    }

    $stmt->close();
}
?>


<div class="admin-panels-wrapper">
    
    <div class="admin-panel-container">
        <h2 class="form-title">Add New Car</h2>

        <?php if (!empty($success_message)) echo "<p class='success'>$success_message</p>"; ?>
        <?php if (!empty($error_message)) echo "<p class='error'>$error_message</p>"; ?>

        <form method="POST" class="admin-form" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>Brand:</label>
                    <input type="text" name="brand_name" required>
                </div>

                <div class="form-group">
                    <label>Model:</label>
                    <input type="text" name="model_name" required>
                </div>

                <div class="form-group">
                    <label>Color:</label>
                    <select name="color_id" required>
                        <option value="">Select color</option>
                        <?php foreach ($colors as $color): ?>
                            <option value="<?= $color['color_id'] ?>"><?= htmlspecialchars($color['color_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Type:</label>
                    <select name="type_id" required>
                        <option value="">Select type</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type['type_id'] ?>"><?= htmlspecialchars($type['type_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Gearbox:</label>
                    <select name="gearbox_id" required>
                        <option value="">Select gearbox</option>
                        <?php foreach ($gearboxes as $gearbox): ?>
                            <option value="<?= $gearbox['gearbox_id'] ?>"><?= htmlspecialchars($gearbox['gearbox_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Year of Manufacture:</label>
                    <input type="number" name="year_manufacture" min="1900" max="2099" required>
                </div>

                <div class="form-group">
                    <label>Mileage (km):</label>
                    <input type="number" name="mileage" min="0" required>
                </div>

                <div class="form-group">
                    <label>Price per Day ($):</label>
                    <input type="number" step="0.01" name="price_per_day" min="0" required>
                </div>

                <div class="form-group full-width">
                    <label>Status:</label>
                    <select name="car_status_id" required>
                        <option value="">Select status</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?= $status['car_status_id'] ?>"><?= htmlspecialchars($status['car_status_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label>Upload Car Image:</label>
                    <input type="file" name="car_image" accept="image/*" required>
                </div>

                <div class="form-group full-width">
                    <button type="submit" class="btn-submit">Add Car</button>
                </div>
            </div>
        </form>
    </div>

    
    <div class="admin-panel-container">
        <h2 class="form-title">Delete Car</h2>

        <form id="deleteForm" method="POST" class="admin-form">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Select Car to Delete:</label>
                    <select id="carSelect" name="delete_car_id" required onchange="updateCarDetails()">
                        <option value="">Select a car</option>
                        <?php foreach ($cars_for_deletion as $car): ?>
                            <option value="<?= $car['car_id'] ?>"
                                data-brand="<?= htmlspecialchars($car['brand_name']) ?>"
                                data-model="<?= htmlspecialchars($car['model_name']) ?>"
                                data-year="<?= $car['year_manufacture'] ?>">
                                <?= htmlspecialchars("{$car['brand_name']} {$car['model_name']} ({$car['year_manufacture']})") ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group full-width">
                    <button type="button" class="btn-delete" onclick="showDeleteModal()">Delete Car</button>
                </div>
            </div>
        </form>
    </div>
    
    <div class="admin-panel-container">
    <h2 class="form-title">Change Car Information</h2>

    <form method="POST" enctype="multipart/form-data" class="admin-form">
        <div class="form-group full-width">
            <label for="selected_car_id">Select Car:</label>
            <select name="selected_car_id" id="selected_car_id" onchange="this.form.submit()">
                <option value="">-- Select a car --</option>
                <?php foreach ($cars_for_deletion as $car): ?>
                    <option value="<?= $car['car_id'] ?>" <?= isset($_POST['selected_car_id']) && $_POST['selected_car_id'] == $car['car_id'] ? 'selected' : '' ?>>
                        <?= $car['brand_name'] . ' ' . $car['model_name'] . ' (' . $car['year_manufacture'] . ')' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <?php
    if (isset($_POST['selected_car_id'])) {
        $selected_car_id = intval($_POST['selected_car_id']);

        $stmt = $conn->prepare("
            SELECT mileage, year_manufacture, price_per_day, image_url, car_status_id
            FROM cars 
            WHERE car_id = ?
        ");
        $stmt->bind_param("i", $selected_car_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $car = $result->fetch_assoc();
        $stmt->close();

        if ($car):
    ?>

    <form method="POST" enctype="multipart/form-data" class="admin-form">
        <input type="hidden" name="update_car_id" value="<?= $selected_car_id ?>">
        <br>

        <div class="form-group">
            <label>Mileage (km):</label>
            <input type="number" name="mileage" value="<?= htmlspecialchars($car['mileage']) ?>" required>
        </div>
        <br>

        <div class="form-group">
            <label>Year of Manufacture:</label>
            <input type="number" name="year_manufacture" value="<?= htmlspecialchars($car['year_manufacture']) ?>" min="1900" max="2099" required>
        </div>
        <br>

        <div class="form-group">
            <label>Price per Day ($):</label>
            <input type="number" name="price_per_day" value="<?= htmlspecialchars($car['price_per_day']) ?>" step="0.01" required>
        </div>
        <br>

        <div class="form-group">
            <label for="car_status_id">Car Status:</label>
            <select id="car_status_id" name="car_status_id" required>
                <option value="1" <?= ($car['car_status_id'] == 1) ? 'selected' : '' ?>>Budget</option>
                <option value="2" <?= ($car['car_status_id'] == 2) ? 'selected' : '' ?>>Standard</option>
                <option value="3" <?= ($car['car_status_id'] == 3) ? 'selected' : '' ?>>VIP</option>
                <option value="4" <?= ($car['car_status_id'] == 4) ? 'selected' : '' ?>>Luxury</option>
            </select>
        </div>
        <br>

        <div class="form-group full-width">
            <label>Change Car Image (optional):</label>
            <input type="file" name="car_image" accept="image/*">
        </div>
        <br>

        <div class="form-group full-width">
            <button type="submit" name="update_car" class="btn-submit">Update Car</button>
        </div>
    </form>

    <?php
        endif;
    }
?>
</div>

</div>


<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeDeleteModal()">&times;</span>
        <h3>Are you sure you want to delete this car?</h3>
        <div id="carInfo" class="car-info-box">
            <p><strong>Brand:</strong> <span id="carBrand"></span></p>
            <p><strong>Model:</strong> <span id="carModel"></span></p>
            <p><strong>Year:</strong> <span id="carYear"></span></p>
        </div>
        <div class="modal-buttons">
            <button class="btn-delete-confirm" onclick="submitDeleteForm()">Yes, Delete</button>
            <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
        </div>
    </div>
</div>



<?php include 'includes/footer.php'; ?>
<script>
    
    function showDeleteModal() {
        const deleteModal = document.getElementById('deleteModal');
        const carSelect = document.getElementById('carSelect');
        const selectedOption = carSelect.options[carSelect.selectedIndex];

        
        if (selectedOption.value) {
            
            document.getElementById('carBrand').textContent = selectedOption.getAttribute('data-brand');
            document.getElementById('carModel').textContent = selectedOption.getAttribute('data-model');
            document.getElementById('carYear').textContent = selectedOption.getAttribute('data-year');
        }

        deleteModal.style.display = "block";
    }

    
    function closeDeleteModal() {
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.style.display = "none";
    }

    
    function submitDeleteForm() {
        document.getElementById('deleteForm').submit();
    }

    
    function updateCarDetails() {
        const carSelect = document.getElementById('carSelect');
        const selectedOption = carSelect.options[carSelect.selectedIndex];

        if (selectedOption.value) {
            document.getElementById('carBrand').textContent = selectedOption.getAttribute('data-brand');
            document.getElementById('carModel').textContent = selectedOption.getAttribute('data-model');
            document.getElementById('carYear').textContent = selectedOption.getAttribute('data-year');
        }
    }

    
    window.onclick = function(event) {
        const deleteModal = document.getElementById('deleteModal');
        if (event.target == deleteModal) {
            closeDeleteModal();
        }
    }
</script>
<style>
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    background: #f5f5f5;
    color: #333;
}

.admin-panels-wrapper {
    display: flex;
    gap: 30px;
    justify-content: center;
    align-items: flex-start;
    flex-wrap: wrap;
    max-width: 1400px;
    margin: 40px auto;
}

.admin-panel-container {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 0 12px rgba(0,0,0,0.05);
    flex: 1 1 48%;
    min-width: 320px;
    max-width: 600px;
}

.form-title {
    text-align: center;
    font-size: 1.6em;
    margin-bottom: 25px;
    color: #444;
}

.admin-form .form-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.form-group {
    flex: 1 1 calc(50% - 20px);
    display: flex;
    flex-direction: column;
}

.full-width {
    flex: 1 1 100%;
}

.admin-form label {
    margin-bottom: 5px;
    font-weight: 500;
}

.admin-form input,
.admin-form select {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    outline: none;
    transition: border-color 0.3s;
}

.admin-form input:focus,
.admin-form select:focus {
    border-color: #f7b500;
}

.btn-submit,
.btn-delete,
.btn-cancel,
.btn-delete-confirm {
    margin-top: 25px;
    padding: 12px;
    font-weight: bold;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    width: 100%;
    transition: background-color 0.3s ease;
}

.btn-submit {
    background-color: #f7b500;
    color: #fff;
}
.btn-submit:hover {
    background-color: #c77f00;
}

.btn-delete {
    background-color: #e74c3c;
    color: #fff;
}
.btn-delete:hover {
    background-color: #c0392b;
}

.btn-cancel {
    background-color: #bdc3c7;
    color: #2c3e50;
}
.btn-cancel:hover {
    background-color: #95a5a6;
}

.btn-delete-confirm {
    background-color: #e74c3c;
    color: #fff;
}
.btn-delete-confirm:hover {
    background-color: #c0392b;
}

.success, .error {
    text-align: center;
    font-weight: bold;
    padding: 10px;
    margin-top: 15px;
    border-radius: 6px;
}
.success {
    background-color: #eafaf1;
    color: #2ecc71;
}
.error {
    background-color: #fdecea;
    color: #e74c3c;
}


.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background: white;
    margin: 10% auto;
    padding: 30px;
    width: 80%;
    max-width: 500px;
    border-radius: 12px;
    position: relative;
}

.modal .close {
    position: absolute;
    right: 20px;
    top: 15px;
    font-size: 26px;
    cursor: pointer;
    color: #888;
}
.modal .close:hover {
    color: #e74c3c;
}

.car-info-box {
    margin: 15px 0;
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
}

.modal-buttons {
    display: flex;
    gap: 10px;
    justify-content: space-between;
    flex-direction: column;
}

@media (min-width: 1024px) {
    .admin-panels-wrapper {
        flex-wrap: nowrap;
        align-items: flex-start;
    }
}


@media (max-width: 600px) {
    .form-group {
        flex: 1 1 100%;
    }

    .modal-content {
        width: 90%;
    }

    .modal-buttons {
        flex-direction: column;
    }
}
</style>