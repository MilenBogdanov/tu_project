<?php
include 'includes/header.php';

function getUserEmailFromSession() {
    if (isset($_SESSION['user_email'])) {
        return $_SESSION['user_email'];
    } else {
        return null;
    }
}

if (!isset($_SESSION['user_id'])) {
    echo "<p>You need to be logged in to confirm the rental.</p>";
    include 'includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'];
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $pickup_time = $_POST['pickup_time'];
    $dropoff_time = $_POST['dropoff_time'];
    $pickup_location = $_POST['pickup_location'];

    $customer_email = getUserEmailFromSession();

    if ($customer_email === null) {
        echo "<p>Error: User email is missing. Please log in again.</p>";
        include 'includes/footer.php';
        exit;
    }

} else {
    echo "<p>Invalid access.</p>";
    include 'includes/footer.php';
    exit;
}

?>

<section class="payment-section">
    <div class="payment-container">
        <h2>Choose Payment Method</h2>

        <form action="finalize_rental.php" method="POST" id="payment-form">
            
            <?php
            foreach ($_POST as $key => $value) {
                echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">' . "\n";
            }
            ?>

            <div class="form-group">
                <label for="payment_method">Payment Method:</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="">-- Select Method --</option>
                    <option value="cash">Cash</option>
                    <option value="credit_card">Credit Card</option>
                </select>
            </div>

            <div id="credit-card-fields" style="display: none;">
                <div class="form-group">
                    <label for="card_name">Cardholder Name:</label>
                    <input type="text" name="card_name" id="card_name">
                </div>
                <div class="form-group">
                    <label for="card_number">Card Number:</label>
                    <input type="text" name="card_number" id="card_number" maxlength="16" pattern="\d{16}">
                </div>
                <div class="form-group">
                    <label for="expiry_date">Expiry Date (MM/YY):</label>
                    <input type="text" name="expiry_date" id="expiry_date" placeholder="MM/YY" pattern="\d{2}/\d{2}">
                </div>
                <div class="form-group">
                    <label for="cvv">CVV:</label>
                    <input type="text" name="cvv" id="cvv" maxlength="3" pattern="\d{3}">
                </div>
            </div>

            <div id="cash-note" style="display: none; color: #27ae60; margin-bottom: 20px;">
                <p>You have selected <strong>cash</strong>. Please be prepared to pay at the pickup location.</p>
            </div>

            <button type="submit" class="confirm-btn">Confirm Payment</button>
        </form>
    </div>
</section>

<script>
    const paymentSelect = document.getElementById('payment_method');
    const cardFields = document.getElementById('credit-card-fields');
    const cashNote = document.getElementById('cash-note');

    paymentSelect.addEventListener('change', function () {
        if (this.value === 'credit_card') {
            cardFields.style.display = 'block';
            cashNote.style.display = 'none';
        } else if (this.value === 'cash') {
            cardFields.style.display = 'none';
            cashNote.style.display = 'block';
        } else {
            cardFields.style.display = 'none';
            cashNote.style.display = 'none';
        }
    });
</script>

<?php include 'includes/footer.php'; ?> 

<script>
    const paymentSelect = document.getElementById('payment_method');
    const cardFields = document.getElementById('credit-card-fields');
    const cashNote = document.getElementById('cash-note');

    paymentSelect.addEventListener('change', function () {
        if (this.value === 'credit_card') {
            cardFields.style.display = 'block';
            cashNote.style.display = 'none';
        } else if (this.value === 'cash') {
            cardFields.style.display = 'none';
            cashNote.style.display = 'block';
        } else {
            cardFields.style.display = 'none';
            cashNote.style.display = 'none';
        }
    });
</script>

<?php include 'includes/footer.php'; ?> 


<style>
.payment-section {
    padding: 40px;
    background-color: #fdfdfd;
    display: flex;
    justify-content: center;
}
.payment-container {
    width: 100%;
    max-width: 500px;
    background-color: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.payment-container h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #333;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    font-size: 16px;
    margin-bottom: 5px;
    color: #444;
}
.form-group input, .form-group select {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-sizing: border-box;
}
.form-group input:focus, .form-group select:focus {
    border-color: #3498db;
    outline: none;
}
.confirm-btn {
    width: 100%;
    padding: 14px 24px;
    font-size: 17px;
    font-weight: bold;
    color: #fff;
    background-color: #f7b500;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    margin-top: 20px;
}

.confirm-btn:hover {
    background-color: #e6b800;
    transform: translateY(-2px);
}

.confirm-btn:active {
    background-color: #1e8449;
    transform: translateY(0);
}
</style>

<?php include 'includes/fooconfirmter.php'; ?>