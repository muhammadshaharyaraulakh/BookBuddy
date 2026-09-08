<?php
require __DIR__ . "/config/config.php";
require __DIR__ . "/includes/header.php";
require __DIR__ . "/includes/navigationbar.php";
?>

<div class="page-header-banner">
    <h1 class="page-header-title">Curated Book Catalog</h1>
    <p class="page-header-subtitle">Browse through our handpicked collection of transformative literature</p>
</div>

<div class="shop-layout">
    <aside class="shop-sidebar">
        <div>
            <h3 class="sidebar-title">
                <i class="ph-bold ph-funnel"></i> Genres
            </h3>
            <ul class="sidebar-filter-list" style="margin-top: 14px;">
                <li><a href="/shop.php" class="active"><span>All Books</span> <span>(25)</span></a></li>
                <li><a href="/shop.php?category=programming"><span>Programming</span> <span>(8)</span></a></li>
                <li><a href="/shop.php?category=scifi"><span>Sci-Fi &amp; Fantasy</span> <span>(5)</span></a></li>
                <li><a href="/shop.php?category=business"><span>Business</span> <span>(6)</span></a></li>
                <li><a href="/shop.php?category=biography"><span>Biographies</span> <span>(4)</span></a></li>
                <li><a href="/shop.php?category=classics"><span>World Classics</span> <span>(2)</span></a></li>
            </ul>
        </div>

        <div>
            <h3 class="sidebar-title">
                <i class="ph-bold ph-truck"></i> Delivery
            </h3>
            <div style="margin-top: 12px; font-size: 13px; font-weight: 600; color: #4B5563; line-height: 1.6;">
                Nationwide Cash on Delivery available across all cities in Pakistan. 2 to 4 business days.
            </div>
        </div>
    </aside>

    <main class="shop-main">
        <div class="books-grid-4" style="grid-template-columns: repeat(3, 1fr);">
            <article class="book-card">
                <div class="book-cover-wrap">
                    <img src="/images/logo.png" alt="The Pragmatic Programmer" class="book-cover-img" />
                </div>
                <div class="book-details">
                    <h3 class="book-title">The Pragmatic Programmer</h3>
                    <span class="book-author">David Thomas</span>
                    <div class="book-rating">
                        <i class="ph-bold ph-star"></i>
                        <span>4.9 (142)</span>
                    </div>
                    <div class="book-price-row">
                        <span class="book-current-price">$45.00</span>
                    </div>
                </div>
            </article>

            <article class="book-card">
                <span class="book-discount-badge">-15%</span>
                <div class="book-cover-wrap">
                    <img src="/images/logo.png" alt="Clean Code" class="book-cover-img" />
                </div>
                <div class="book-details">
                    <h3 class="book-title">Clean Code: A Handbook of Agile Software</h3>
                    <span class="book-author">Robert C. Martin</span>
                    <div class="book-rating">
                        <i class="ph-bold ph-star"></i>
                        <span>4.8 (210)</span>
                    </div>
                    <div class="book-price-row">
                        <span class="book-current-price">$42.50</span>
                        <span class="book-original-price">$50.00</span>
                    </div>
                </div>
            </article>

            <article class="book-card">
                <div class="book-cover-wrap">
                    <img src="/images/logo.png" alt="Dune" class="book-cover-img" />
                </div>
                <div class="book-details">
                    <h3 class="book-title">Dune (Deluxe Edition)</h3>
                    <span class="book-author">Frank Herbert</span>
                    <div class="book-rating">
                        <i class="ph-bold ph-star"></i>
                        <span>5.0 (380)</span>
                    </div>
                    <div class="book-price-row">
                        <span class="book-current-price">$20.00</span>
                    </div>
                </div>
            </article>

            <article class="book-card">
                <span class="book-discount-badge">-20%</span>
                <div class="book-cover-wrap">
                    <img src="/images/logo.png" alt="Thinking Fast and Slow" class="book-cover-img" />
                </div>
                <div class="book-details">
                    <h3 class="book-title">Thinking, Fast and Slow</h3>
                    <span class="book-author">Daniel Kahneman</span>
                    <div class="book-rating">
                        <i class="ph-bold ph-star"></i>
                        <span>4.9 (189)</span>
                    </div>
                    <div class="book-price-row">
                        <span class="book-current-price">$24.00</span>
                        <span class="book-original-price">$30.00</span>
                    </div>
                </div>
            </article>

            <article class="book-card">
                <div class="book-cover-wrap">
                    <img src="/images/logo.png" alt="Atomic Habits" class="book-cover-img" />
                </div>
                <div class="book-details">
                    <h3 class="book-title">Atomic Habits</h3>
                    <span class="book-author">James Clear</span>
                    <div class="book-rating">
                        <i class="ph-bold ph-star"></i>
                        <span>5.0 (520)</span>
                    </div>
                    <div class="book-price-row">
                        <span class="book-current-price">$25.00</span>
                    </div>
                </div>
            </article>

            <article class="book-card">
                <span class="book-discount-badge">-25%</span>
                <div class="book-cover-wrap">
                    <img src="/images/logo.png" alt="Design Patterns" class="book-cover-img" />
                </div>
                <div class="book-details">
                    <h3 class="book-title">Design Patterns Elements</h3>
                    <span class="book-author">Erich Gamma</span>
                    <div class="book-rating">
                        <i class="ph-bold ph-star"></i>
                        <span>4.7 (98)</span>
                    </div>
                    <div class="book-price-row">
                        <span class="book-current-price">$41.25</span>
                        <span class="book-original-price">$55.00</span>
                    </div>
                </div>
            </article>
        </div>
    </main>
</div>

<?php 
require __DIR__ . "/includes/footersection.php";
require __DIR__ . "/includes/footer.php";
?>
