<footer>
    <div class="footer-container">
        <div class="footer-section">
            <h3>Rent A Car</h3>
            <p>Find the perfect car rental for any destination or occasion. Whether it's a road trip, business, or vacation, we offer a wide range of vehicles to suit your needs. Enjoy flexible rental options, competitive prices, and reliable service from top-rated agencies. Book now and hit the road with ease!</p>
        </div>

        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="car.php">Cars</a></li>
                <li><a href="about_us.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="myreservations.php">My Reservations</a></li>
                <?php if (isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'admin'): ?>
                    <li><a href="admin_panel.php">Admin Panel</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="footer-bottom">
            <p>© <?php echo date("Y"); ?> Rent A Car. All rights reserved.</p>
        </div>
    </div>
</footer>