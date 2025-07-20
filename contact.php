<?php include 'includes/header.php'; ?>

<section class="contact-hero">
    <div class="overlay">
        <div class="container">
            <h1>Contact Us</h1>
            <p>We’re here to help - reach out anytime.</p>
        </div>
    </div>
</section>

<section class="contact-content">
    <div class="container">
        <h2>Contact Information</h2>
        <p><strong>Address:</strong> Varna Center, Odessos, Blvd. "Tsar Osvoboditel" 17, 9002 Varna</p>
        <p><strong>Phone:</strong> +359 87 625 1510</p>
        <p><strong>Email:</strong> 
            <a href="https://mail.google.com/mail/?to=milenb53@gmail.com&subject=Delivery%20to&body=Hello%2C%0A%0AYour%20message%20here." 
               target="_blank" class="email-link">milenb53@gmail.com</a>
        </p>

        <h2>Need Help?</h2>
        <p>Get free advice from an expert over the phone. We’re happy to help with choosing and receiving your car.</p>

        <div class="contact-phone">+359 87 625 1510</div>

        <h2>Quick Contact</h2>
        <div class="contact-buttons">
            <a href="viber://contact?number=+359887435500" target="_blank">Message on Viber</a>
            <a href="https://www.facebook.com/profile.php?id=100013392637031" target="_blank">Message on Facebook</a>
            <a href="https://mail.google.com/mail/?to=milenb53@gmail.com&subject=Delivery%20to&body=Hello%2C%0A%0AYour%20message%20here." 
               target="_blank">Send an Email</a>
        </div>
    </div>
</section>
<div class="map-container">
    <iframe 
        src="https://www.google.com/maps/embed?pb=!4v1744144655671!6m8!1m7!1sXLP_86b88wUwI4hox5fLlw!2m2!1d43.2080845947318!2d27.92312103375735!3f230.6841478980338!4f-7.339150225069602!5f0.4000000000000002" 
        width="70%" 
        height="500" 
        style="border:0; display: block; margin: 0 auto; background: transparent;" 
        allowfullscreen="" 
        loading="lazy" 
        referrerpolicy="no-referrer-when-downgrade">
    </iframe>
</div>
<?php include 'includes/footer.php'; ?>


<style>
    .contact-hero {
    background: url('backgr_images/background.jpg') no-repeat center center/cover;
    position: relative;
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-bottom: -50px;
}

.contact-hero .overlay {
    background-color: rgba(0, 0, 0, 0.6);
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 0 20px;
}

.contact-hero h1 {
    font-size: 48px;
    margin-bottom: 10px;
}

.contact-hero p {
    font-size: 22px;
    color: #ffcc00;
}

.contact-content {
    background-color: #fff;
    padding: 60px 20px;
}

.contact-content .container {
    max-width: 1000px;
    margin: auto;
}

.contact-phone {
    font-size: 20px;
    font-weight: bold;
    margin: 20px 0;
    color: #000;
}

.contact-buttons {
    margin-top: 25px;
}

.contact-buttons a {
    background-color: #f7b500;
    color: #222;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 16px;
    display: inline-block;
    margin: 6px 10px 6px 0;
    transition: all 0.3s ease;
    border: 1px solid transparent;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.contact-buttons a:hover {
    background-color: #e6b800;
    color: #000;
    transform: translateY(-4px);
    border-color: #f7b500;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
}

.email-link {
    color: #f7b500;
    font-weight: 600;
    text-decoration: none;
    position: relative;
    display: inline-block;
    transition: color 0.3s ease, text-shadow 0.3s ease;
}

.email-link::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -3px;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, #00bcd4, #f7b500);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
}

.email-link:hover {
    color: #f7b500;
    text-shadow: 0 0 6px rgba(247, 181, 0, 0.6);
}

.email-link:hover::after {
    transform: scaleX(1);
}

.map-container {
    margin-top: auto;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
    background-color: #fff;
    margin-bottom: 20px;
}

.map-container iframe {
    border: 0;
    background: transparent;
    display: block;
    margin: 0 auto;
}
</style>
