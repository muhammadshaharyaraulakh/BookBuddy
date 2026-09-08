<?php
require __DIR__ . "/config/config.php";
require __DIR__ . "/includes/header.php";
require __DIR__ . "/includes/navigationbar.php";
?>

<section class="hero-section">
    <div class="hero-container">
        <div class="hero-content">
            <h1 class="hero-title">
                Explore Stories That Inspire And Enrich Your <span>Mind</span>
            </h1>
            <p class="hero-desc">
                Step into an expansive universe of books crafted to spark curiosity, broaden horizons, and nourish the imagination. From celebrated world classics and contemporary fiction to insightful biographies, self growth literature, and profound historical accounts, our shelves offer remarkable journeys for every passionate reader.
            </p>
            <div class="hero-actions">
                <a href="/shop.php" class="btn-hero-primary">
                    Explore Books <i class="ph-bold ph-arrow-right"></i>
                </a>
                <a href="#deals" class="btn-hero-secondary">
                    Today's Deal <i class="ph-bold ph-tag"></i>
                </a>
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-img-frame">
                <img src="/images/hero.png" alt="Featured Reader Sanctuary" />
            </div>
        </div>
    </div>
</section>

<section class="section-container" id="arrivals">
    <div class="section-title-wrap">
        <h2 class="section-title-main">New Arrivals</h2>
    </div>

    <div class="books-grid-4">
        <a href="/book.php" class="book-card">
            <div class="book-cover-wrap">
                <img src="/images/logo.png" alt="Clean Code" class="book-cover-img" />
            </div>
            <div class="book-details">
                <h3 class="book-title">Clean Code: A Handbook of Agile Software</h3>
                <span class="book-author">Robert C. Martin</span>
                <div class="book-rating">
                    <i class="ph-bold ph-star"></i>
                    <span>4.9 (128)</span>
                </div>
                <div class="book-price-row">
                    <span class="book-current-price">$120</span>
                </div>
            </div>
        </a>

        <a href="/book.php" class="book-card">
            <span class="book-discount-badge">-20%</span>
            <div class="book-cover-wrap">
                <img src="/images/logo.png" alt="The Pragmatic Programmer" class="book-cover-img" />
            </div>
            <div class="book-details">
                <h3 class="book-title">The Pragmatic Programmer: 20th Anniversary Edition</h3>
                <span class="book-author">David Thomas &amp; Andrew Hunt</span>
                <div class="book-rating">
                    <i class="ph-bold ph-star"></i>
                    <span>4.8 (95)</span>
                </div>
                <div class="book-price-row">
                    <span class="book-current-price">$240</span>
                    <span class="book-original-price">$260</span>
                </div>
            </div>
        </a>

        <a href="/book.php" class="book-card">
            <div class="book-cover-wrap">
                <img src="/images/logo.png" alt="Atomic Habits" class="book-cover-img" />
            </div>
            <div class="book-details">
                <h3 class="book-title">Atomic Habits: Proven Way to Build Good Habits</h3>
                <span class="book-author">James Clear</span>
                <div class="book-rating">
                    <i class="ph-bold ph-star"></i>
                    <span>5.0 (310)</span>
                </div>
                <div class="book-price-row">
                    <span class="book-current-price">$180</span>
                </div>
            </div>
        </a>

        <a href="/book.php" class="book-card">
            <span class="book-discount-badge">-30%</span>
            <div class="book-cover-wrap">
                <img src="/images/logo.png" alt="Design Patterns" class="book-cover-img" />
            </div>
            <div class="book-details">
                <h3 class="book-title">Design Patterns: Elements of Reusable Object Software</h3>
                <span class="book-author">Erich Gamma &amp; Richard Helm</span>
                <div class="book-rating">
                    <i class="ph-bold ph-star"></i>
                    <span>4.7 (84)</span>
                </div>
                <div class="book-price-row">
                    <span class="book-current-price">$130</span>
                    <span class="book-original-price">$160</span>
                </div>
            </div>
        </a>
    </div>

    <div class="section-btn-wrap">
        <a href="/shop.php" class="btn-view-all">
            View All <i class="ph-bold ph-arrow-right"></i>
        </a>
    </div>
</section>

<section class="daily-deal-section" id="deals">
    <div class="daily-deal-card">
        <div class="deal-visual-side">
            <div class="deal-badge-spotlight">
                <i class="ph-bold ph-fire"></i> Flash Deal 25% Off
            </div>
            <img src="/images/logo.png" alt="Thinking Fast and Slow" class="deal-book-img" />
        </div>

        <div class="deal-content-side">
            <h2 class="deal-title">Thinking, Fast and Slow</h2>
            <div class="deal-meta-specs">
                <span><i class="ph-bold ph-user-circle"></i> Daniel Kahneman</span>
                <span><i class="ph-bold ph-tag"></i> Behavioral Economics</span>
                <span><i class="ph-bold ph-star" style="color: #FFAE00;"></i> 4.9 Rating</span>
            </div>
            <p class="deal-desc">
                The international bestseller that explores the two mental engines driving our decisions: fast intuition versus conscious deliberation. A transformative masterpiece for curious readers.
            </p>

            <div class="deal-countdown-box">
                <span class="deal-countdown-label">
                    <i class="ph-bold ph-timer"></i> Offer Ends In:
                </span>
                <div class="deal-timer-pills">
                    <span class="timer-unit" id="deal-hours">14h</span>
                    <span>:</span>
                    <span class="timer-unit" id="deal-mins">35m</span>
                    <span>:</span>
                    <span class="timer-unit" id="deal-secs">12s</span>
                </div>
            </div>

            <div class="deal-price-cta">
                <div class="deal-price-numbers">
                    <span class="deal-price-now">$24.00</span>
                    <span class="deal-price-was">$32.00</span>
                </div>
                <a href="/cart.php" class="btn-grab-deal">
                    Grab Deal Now <i class="ph-bold ph-shopping-bag"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="section-container" id="genres">
    <div class="bento-section-box">
        <h2 class="bento-title">Browse By Genre</h2>

        <div class="bento-grid">
            <div class="bento-row bento-row-1">
                <a href="/shop.php?category=programming" class="bento-card bento-card-programming">
                    <div class="bento-card-header">
                        <div class="bento-label-pill">Programming</div>
                        <div class="genre-icon-pill">
                            <i class="ph-bold ph-code"></i>
                        </div>
                    </div>
                    <div class="genre-card-info">
                        <h3 class="genre-name-title">Programming &amp; Tech</h3>
                        <p class="genre-name-desc">Clean architecture, systems design, software patterns, and modern dev stacks.</p>
                    </div>
                    <div class="genre-action-row">
                        <span class="genre-explore-link">Explore Shelf <i class="ph-bold ph-arrow-right"></i></span>
                    </div>
                </a>

                <a href="/shop.php?category=philosophy" class="bento-card bento-card-philosophy">
                    <div class="bento-card-header">
                        <div class="bento-label-pill">Philosophy</div>
                        <div class="genre-icon-pill">
                            <i class="ph-bold ph-brain"></i>
                        </div>
                    </div>
                    <div class="genre-card-info">
                        <h3 class="genre-name-title">Philosophy &amp; Logic</h3>
                        <p class="genre-name-desc">Classical thinkers, ethics, metaphysics, epistemology, and existential inquiries.</p>
                    </div>
                    <div class="genre-action-row">
                        <span class="genre-explore-link">Explore Shelf <i class="ph-bold ph-arrow-right"></i></span>
                    </div>
                </a>
            </div>

            <div class="bento-row bento-row-2">
                <a href="/shop.php?category=religious" class="bento-card bento-card-religious">
                    <div class="bento-card-header">
                        <div class="bento-label-pill">Religious</div>
                        <div class="genre-icon-pill">
                            <i class="ph-bold ph-book-bookmark"></i>
                        </div>
                    </div>
                    <div class="genre-card-info">
                        <h3 class="genre-name-title">Religious &amp; Spiritual</h3>
                        <p class="genre-name-desc">Theological texts, classical commentaries, Islamic history, and divine wisdom.</p>
                    </div>
                    <div class="genre-action-row">
                        <span class="genre-explore-link">Explore Shelf <i class="ph-bold ph-arrow-right"></i></span>
                    </div>
                </a>

                <a href="/shop.php?category=biography" class="bento-card bento-card-biographies">
                    <div class="bento-card-header">
                        <div class="bento-label-pill">Biographies</div>
                        <div class="genre-icon-pill">
                            <i class="ph-bold ph-user-circle"></i>
                        </div>
                    </div>
                    <div class="genre-card-info">
                        <h3 class="genre-name-title">Biographies &amp; Memoirs</h3>
                        <p class="genre-name-desc">Inspiring chronicles of groundbreaking pioneers, leaders, and thinkers.</p>
                    </div>
                    <div class="genre-action-row">
                        <span class="genre-explore-link">Explore Shelf <i class="ph-bold ph-arrow-right"></i></span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="section-container" id="testimonials">
    <div class="section-title-wrap">
        <h2 class="section-title-main">Happy Readers</h2>
    </div>

    <div class="testimonials-slider-outer">
        <div class="testimonials-track">
            <article class="testimonial-card">
                <div class="testimonial-header">
                    <span class="testimonial-name">Ali Zafar</span>
                    <i class="ph-bold ph-check-circle testimonial-icon"></i>
                </div>
                <div class="testimonial-stars">
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                </div>
                <p class="testimonial-quote">
                    "The delivery was remarkably prompt. Ordered on Tuesday and received the books on Thursday in perfect packaging. Cash on Delivery was completely seamless!"
                </p>
            </article>

            <article class="testimonial-card">
                <div class="testimonial-header">
                    <span class="testimonial-name">Hamza Ahmed</span>
                    <i class="ph-bold ph-check-circle testimonial-icon"></i>
                </div>
                <div class="testimonial-stars">
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                </div>
                <p class="testimonial-quote">
                    "Finding authentic programming and tech literature in Pakistan used to be challenging. BookBuddy has become my go-to library sanctuary. Outstanding quality!"
                </p>
            </article>

            <article class="testimonial-card">
                <div class="testimonial-header">
                    <span class="testimonial-name">Sara Khan</span>
                    <i class="ph-bold ph-check-circle testimonial-icon"></i>
                </div>
                <div class="testimonial-stars">
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                </div>
                <p class="testimonial-quote">
                    "The daily deal discounts are genuine and the paper quality is crisp. Plus, the retro Neo-Brutalist interface is super delightful to use!"
                </p>
            </article>

            <article class="testimonial-card">
                <div class="testimonial-header">
                    <span class="testimonial-name">Usman Tariq</span>
                    <i class="ph-bold ph-check-circle testimonial-icon"></i>
                </div>
                <div class="testimonial-stars">
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                </div>
                <p class="testimonial-quote">
                    "I ordered rare philosophy and history titles. They were packaged with care, corners intact, and pristine print pages. Highly recommended bookstore!"
                </p>
            </article>

            <article class="testimonial-card">
                <div class="testimonial-header">
                    <span class="testimonial-name">Fatima Noor</span>
                    <i class="ph-bold ph-check-circle testimonial-icon"></i>
                </div>
                <div class="testimonial-stars">
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                </div>
                <p class="testimonial-quote">
                    "Doorstep parcel inspection gave me total confidence before paying the courier. Customer support answered my questions within minutes on WhatsApp."
                </p>
            </article>

            <article class="testimonial-card">
                <div class="testimonial-header">
                    <span class="testimonial-name">Bilal Malik</span>
                    <i class="ph-bold ph-check-circle testimonial-icon"></i>
                </div>
                <div class="testimonial-stars">
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                </div>
                <p class="testimonial-quote">
                    "The biographies collection is exquisite. Hardcover bindings at reasonable prices with nationwide cash on delivery. BookBuddy never disappoints."
                </p>
            </article>

            <article class="testimonial-card">
                <div class="testimonial-header">
                    <span class="testimonial-name">Zainab Riaz</span>
                    <i class="ph-bold ph-check-circle testimonial-icon"></i>
                </div>
                <div class="testimonial-stars">
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                </div>
                <p class="testimonial-quote">
                    "The hardcover books arrived in pristine condition without a single crease. Packaging was robust with bubble wrap. Super impressed with BookBuddy!"
                </p>
            </article>

            <article class="testimonial-card">
                <div class="testimonial-header">
                    <span class="testimonial-name">Danial Qureshi</span>
                    <i class="ph-bold ph-check-circle testimonial-icon"></i>
                </div>
                <div class="testimonial-stars">
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                </div>
                <p class="testimonial-quote">
                    "Finding genuine engineering and software architecture manuals locally used to take weeks. BookBuddy delivered in 48 hours via COD. Outstanding service!"
                </p>
            </article>

            <article class="testimonial-card">
                <div class="testimonial-header">
                    <span class="testimonial-name">Ayesha Siddiqui</span>
                    <i class="ph-bold ph-check-circle testimonial-icon"></i>
                </div>
                <div class="testimonial-stars">
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                    <i class="ph-bold ph-star"></i>
                </div>
                <p class="testimonial-quote">
                    "Customer support on WhatsApp was extremely helpful in tracking my parcel. Cash on Delivery was easy and hassle-free. Will definitely order again!"
                </p>
            </article>
        </div>
    </div>

    <div class="testimonials-controls">
        <button type="button" class="slider-ctrl-btn" id="slider-prev-btn" aria-label="Previous Reviews">
            <i class="ph-bold ph-caret-left"></i>
        </button>
        <button type="button" class="slider-ctrl-btn" id="slider-next-btn" aria-label="Next Reviews">
            <i class="ph-bold ph-caret-right"></i>
        </button>
    </div>
</section>

<section class="section-container" id="faq" style="padding-top: 0;">
    <div class="section-title-wrap">
        <h2 class="section-title-main">Frequently Asked Questions</h2>
    </div>

    <div class="faq-section-wrap">
        <div class="faq-item active">
            <button type="button" class="faq-question-btn">
                <span>How does Cash on Delivery (COD) work across Pakistan?</span>
                <span class="faq-icon-pill"><i class="ph-bold ph-caret-down"></i></span>
            </button>
            <div class="faq-answer-content">
                You can browse and order books without entering online card credentials. When our trusted courier rider arrives at your doorstep, you inspect the parcel and pay the exact invoice amount in cash directly to the rider.
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question-btn">
                <span>What is the standard delivery timeline for my order?</span>
                <span class="faq-icon-pill"><i class="ph-bold ph-caret-down"></i></span>
            </button>
            <div class="faq-answer-content">
                Orders dispatched to major cities (Lahore, Karachi, Islamabad, Rawalpindi) typically arrive within 2 to 3 business days. Deliveries to other cities and regional districts take between 3 to 4 business days.
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question-btn">
                <span>Are all books guaranteed to be original prints?</span>
                <span class="faq-icon-pill"><i class="ph-bold ph-caret-down"></i></span>
            </button>
            <div class="faq-answer-content">
                Yes, 100%. We source exclusively from authorized publishers and verified distributors. Every book features crisp paper, high-grade binding, and original ISBN certification.
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question-btn">
                <span>Can I open and inspect the parcel before paying the courier?</span>
                <span class="faq-icon-pill"><i class="ph-bold ph-caret-down"></i></span>
            </button>
            <div class="faq-answer-content">
                Yes. We explicitly support doorstep inspection with our courier partners so you can verify that the correct title has arrived in pristine condition before handing over the cash payment.
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question-btn">
                <span>What is your return or replacement policy?</span>
                <span class="faq-icon-pill"><i class="ph-bold ph-caret-down"></i></span>
            </button>
            <div class="faq-answer-content">
                If your book arrives damaged in transit or with printing defects, notify us within 7 days of delivery for an immediate free replacement or a full refund without questions asked.
            </div>
        </div>
    </div>
</section>

<?php 
require __DIR__ . "/includes/footersection.php";
require __DIR__ . "/includes/footer.php";
?>