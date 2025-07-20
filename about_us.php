<?php include 'includes/header.php'; ?>

<section class="about-hero">
    <div class="overlay">
        <div class="container">
            <h1>About Our Company</h1>
            <p>Reliable. Affordable. Fast. Your journey starts here.</p>
        </div>
    </div>
</section>

<section class="about-content">
    <div class="container">
        <h2>Who We Are</h2>
        <p>
            We’re a passionate team committed to making car rental simple, transparent, and stress-free. 
            With years of experience in the automotive and rental industry, our goal is to offer 
            the perfect car for every customer, whether it's a weekend getaway, a business trip, or a family vacation.
        </p>

        <h2>What We Offer</h2>
        <ul>
            <li>Wide range of vehicles from economy to luxury</li>
            <li>Transparent pricing, no hidden fees</li>
            <li>24/7 customer support</li>
            <li>Easy online booking process</li>
            <li>Flexible pick-up & drop-off locations</li>
        </ul>

        <h2>Our Mission</h2>
        <p>
            To provide exceptional rental experiences by consistently delivering value, quality, 
            and personalized service that earns the trust and loyalty of our customers.
        </p>
    </div>
</section>


<section class="autopark-section">
    <div class="autopark-overlay">
        <div class="container">
            <h2>Our Modern Auto Park</h2>
            <p>
                Our large and modern auto park is packed with the latest models, well-maintained, clean, and always ready for the road.
                We pride ourselves on safety, style, and variety to meet every customer’s need.
            </p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<style>
    .about-hero {
        background: url('backgr_images/background.jpg') no-repeat center center/cover;
        position: relative;
        height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-bottom: -50px;
    }

    .about-hero .overlay {
        background-color: rgba(0, 0, 0, 0.6);
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 0 20px;
    }

    .about-hero h1 {
        font-size: 48px;
        margin-bottom: 10px;
    }

    .about-hero p {
        font-size: 22px;
        color: #ffcc00;
    }

    .about-content {
        background-color: #fff;
        padding: 60px 20px;
    }

    .about-content .container {
        max-width: 1000px;
        margin: auto;
    }

    .about-content h2 {
        font-size: 28px;
        color: #1a1a1a;
        margin-top: 40px;
    }

    .about-content p,
    .about-content ul {
        font-size: 18px;
        line-height: 1.7;
        color: #333;
    }

    .about-content ul {
        text-align: left;
    }

    .about-content li {
        margin-bottom: 10px;
    }

    .autopark-section {
        background: url('backgr_images/autopark.webp') no-repeat center center/cover;
        padding: 80px 20px;
        color: white;
        position: relative;
        text-align: center;
    }

    .autopark-overlay {
        background-color: rgba(0, 0, 0, 0.6);
        padding: 60px 20px;
    }

    .autopark-section h2 {
        font-size: 36px;
        margin-bottom: 20px;
        color: #ffcc00;
    }

    .autopark-section p {
        font-size: 20px;
        max-width: 800px;
        margin: auto;
        line-height: 1.6;
    }
</style>