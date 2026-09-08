<?php
require __DIR__ . "/config/config.php";
require __DIR__ . "/includes/header.php";
require __DIR__ . "/includes/navigationbar.php";
?>

<div class="book-detail-container">
    <nav class="book-breadcrumb">
        <a href="/index.php"><i class="ph-bold ph-house"></i> Home</a>
        <span>/</span>
        <a href="/shop.php">Shop</a>
        <span>/</span>
        <a href="/shop.php?category=programming">Programming</a>
        <span>/</span>
        <span class="active-crumb">Clean Code</span>
    </nav>

    <div class="book-detail-layout">
        <div class="book-detail-visual">
            <div class="book-detail-cover-card">
                <span class="book-discount-badge">-20%</span>
                <img src="/images/logo.png" alt="Clean Code Book Cover" class="book-detail-img" />
            </div>
            <div class="book-trust-pill">
                <i class="ph-bold ph-seal-check"></i>
                <span>100% Original Publisher Certified Print</span>
            </div>
        </div>

        <div class="book-detail-info">
            <div class="book-detail-header">
                <span class="detail-genre-badge">Programming</span>
                <h1 class="book-detail-title">Clean Code: A Handbook of Agile Software Craftsmanship</h1>
                <div class="book-detail-author-row">
                    <span class="detail-author-label">Author:</span>
                    <span class="detail-author-name">Robert C. Martin</span>
                </div>
                <div class="book-detail-rating-row">
                    <div class="testimonial-stars">
                        <i class="ph-bold ph-star"></i>
                        <i class="ph-bold ph-star"></i>
                        <i class="ph-bold ph-star"></i>
                        <i class="ph-bold ph-star"></i>
                        <i class="ph-bold ph-star"></i>
                    </div>
                    <span class="detail-rating-text">4.9 &bull; 128 Verified Reviews</span>
                </div>
            </div>

            <div class="book-detail-pricing">
                <span class="detail-price-current">$120.00</span>
                <span class="detail-price-original">$150.00</span>
                <span class="detail-price-saved">Save $30.00</span>
            </div>

            <div class="book-detail-synopsis">
                <h2 class="synopsis-title">Book Overview</h2>
                <p class="synopsis-text">
                    Even bad code can function. But if code isn't clean, it can bring a development organization to its knees. Every year, countless hours and significant resources are lost because of poorly written code. But it doesn't have to be that way. Clean Code is divided into three parts: principles, patterns, and practices of writing clean code; several case studies of increasing complexity; and a collection of heuristics gathered while creating the case studies.
                </p>
            </div>

            <div class="book-detail-perks">
                <div class="perk-item">
                    <i class="ph-bold ph-truck"></i>
                    <div>
                        <strong>Nationwide Cash on Delivery</strong>
                        <span>Delivered to your doorstep in 2 to 3 business days.</span>
                    </div>
                </div>
                <div class="perk-item">
                    <i class="ph-bold ph-shield-check"></i>
                    <div>
                        <strong>Doorstep Parcel Inspection</strong>
                        <span>Verify book authenticity and condition before paying cash.</span>
                    </div>
                </div>
                <div class="perk-item">
                    <i class="ph-bold ph-arrow-counter-clockwise"></i>
                    <div>
                        <strong>7-Day Easy Returns</strong>
                        <span>Hassle-free replacement if damaged in transit.</span>
                    </div>
                </div>
            </div>

            <div class="book-detail-actions">
                <div class="cart-qty-control">
                    <button type="button" class="cart-qty-btn">-</button>
                    <input type="text" class="cart-qty-input" value="1" readonly />
                    <button type="button" class="cart-qty-btn">+</button>
                </div>
                <a href="/cart.php" class="btn-detail-add-cart">
                    <i class="ph-bold ph-shopping-bag"></i> Add to Cart
                </a>
                <a href="/cart.php" class="btn-detail-buy-now">
                    <i class="ph-bold ph-lightning"></i> Buy with COD
                </a>
            </div>

            <div class="book-specs-card">
                <h3 class="specs-title">Book Specifications</h3>
                <div class="specs-grid">
                    <div class="spec-row">
                        <span class="spec-label">Publisher</span>
                        <span class="spec-value">Prentice Hall</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Format</span>
                        <span class="spec-value">Paperback, Pristine Print</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Language</span>
                        <span class="spec-value">English</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Pages</span>
                        <span class="spec-value">464 Pages</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">ISBN-13</span>
                        <span class="spec-value">978-0132350884</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Availability</span>
                        <span class="spec-value" style="color: #166534; font-weight: 800;">In Stock (Ships in 24h)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require __DIR__ . "/includes/footersection.php";
require __DIR__ . "/includes/footer.php";
?>
