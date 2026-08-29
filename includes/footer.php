<?php
$allCategories = getCategories($connection);
?>
<footer>
  <div class="container">
    <div class="logo-description">
      <div class="logo">
        <div class="img">
          <img src="/images/logo.png" alt="logo" />
        </div>
        <div class="title">
          <h4>Book Buddy</h4>
          <small>Book Store Website</small>
        </div>
      </div>
      <div class="logo-body">
        <p>
          Discover a wide range of quality books, carefully curated for readers of all ages.
          Dive into stories, knowledge, and adventures that inspire and entertain.
        </p>
      </div>
      <div class="social-links">
        <h4>Follow Us</h4>
        <ul class="links">
          <li>
            <a href=""><i class="fa-brands fa-facebook-f"></i></a>
          </li>
          <li>
            <a href=""><i class="fa-brands fa-youtube"></i></a>
          </li>
          <li>
            <a href=""><i class="fa-brands fa-twitter"></i></a>
          </li>
          <li>
            <a href=""><i class="fa-brands fa-linkedin"></i></a>
          </li>
          <li>
            <a href=""><i class="fa-brands fa-instagram"></i></a>
          </li>
        </ul>
      </div>
    </div>
    <div class="categories list">
      <h4>Book Categories</h4>
      <ul>
        <?php foreach (array_slice($allCategories, 0, 8) as $category): ?>
          <li><a href="/pages/books.php"><?= htmlspecialchars($category->title) ?></a></li>
        <?php endforeach; ?>

      </ul>
    </div>
    <div class="quick-links list">
      <h4>Quick Links</h4>
      <ul>
        <li><a href="/index.php"> Home </a></li>
        <li><a href="/pages/books.php">Products</a></li>
        <li><a href="/auth/login/login.php">Login</a></li>
        <li><a href="/auth/registration/registration.php">Sign Up</a></li>
        <li><a href="/pages/cart.php">Cart</a></li>
        <li><a href="/pages/checkout.php">Checkout</a></li>
      </ul>
    </div>
    <div class="our-store list">
      <h4>Our Store</h4>
      <div class="map" style="margin-top: 1rem">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m10!1m8!1m3!1d54955.191000350685!2d72.9069055!3d30.5860291!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2s!4v1766900681993!5m2!1sen!2s"
          height="70"
          style="width: 100%; border: none; border-radius: 5px;"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>

      </div>
      <ul>
        <li>
          <a href=""><i class="fa-solid fa-location-dot"></i>Harappa Station ,Sahiwal ,57160 ,Punjab Pakistan</a>
        </li>
        <li>
          <a href=""><i class="fa-solid fa-phone"></i>+92 3451440747</a>
        </li>
        <li>

          <a><i class="fa-solid fa-envelope"></i>supportbookbuddy@gmail.com</a>
        </li>
      </ul>
    </div>
  </div>
</footer>
<button class="back-to-top"><i class="fa-solid fa-chevron-up"></i></button>
<script src="/assests/js/main.js"></script>

</html>