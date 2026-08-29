<?php
require __DIR__ . "/config/config.php";
require __DIR__ . "/includes/header.php";

$fetch = $connection->prepare("
    SELECT * 
    FROM user 
    WHERE id=:id
");
$fetch->execute([
  ':id' => $_SESSION['id']
]);
$user = $fetch->fetch(PDO::FETCH_OBJ);

$recommended = getBookDetails($connection, "Recommended For You");
$popular = getBookDetails($connection, "Popular in 2025");

$fetch = $connection->prepare("
SELECT 
    b.*,
    d.id AS deal_id,
    d.discount_percentage AS deal_discount,
    d.start_time,
    d.end_time,
    ROUND(b.Original_Price * (100 - d.discount_percentage) / 100) AS deal_price
FROM book b
INNER JOIN deals d 
    ON b.id = d.book_id
WHERE CURRENT_TIMESTAMP < d.end_time
");

$fetch->execute();
$deals = $fetch->fetchAll(PDO::FETCH_OBJ);

$onSale=$connection->prepare("SELECT *
FROM book b
WHERE b.Discount_Percentage IS NOT NULL
  AND b.id NOT IN (
      SELECT book_id 
      FROM deals
  );
");
$onSale->execute();
$sales=$onSale->fetchAll(PDO::FETCH_OBJ);

$statment = $connection->prepare("SELECT * FROM user WHERE role = :role");
$statment->execute(['role' => 'user']);
$customers = $statment->fetchAll(PDO::FETCH_OBJ);
$totalUsers = count($customers);


$statments = $connection->prepare("SELECT * FROM book");
$statments->execute();
$books = $statments->fetchAll(PDO::FETCH_OBJ);
$allBooks = count($books);


$fetch = $connection->prepare("SELECT b.*
FROM book b
JOIN categories c 
  ON b.category_id = c.id
WHERE c.title = 'Featured Books';
");
$fetch->execute();
$featuredBooks = $fetch->fetchAll(PDO::FETCH_OBJ);
?>
<section class="hero">
  <div class="main">
    <div class="content">
      <small>back-to-school</small>
      <p>use this promo to get</p>
      <h2>Special 50% Off</h2>
      <h5>for our student community</h5>
      <p>
        Explore our global collection of timeless classics and modern hits.
        Find your next favorite story inside today.
      </p>
      <div class="btns">
        <a href="/pages/books.php"><button>Explore Our Shop<i class="fa-solid fa-arrow-right"></i></button></a>
        <button><a href="pages/promos.php" class="promo">See other Promos</a></button>
      </div>
    </div>
    <div class="img">
      <img src="images/teenager-student-girl-yellow-pointing-finger-side-copy.png" alt="" />
    </div>
  </div>
  <div class="square-dot">
    <i class="fa-solid fa-square"></i>
    <i class="fa-solid fa-square"></i>
    <i class="fa-solid fa-square"></i>
    <i class="fa-solid fa-square"></i>
    <i class="fa-solid fa-square"></i>
    <i class="fa-solid fa-square"></i>
    <i class="fa-solid fa-square"></i>
    <i class="fa-solid fa-square"></i>
    <i class="fa-solid fa-square"></i>
    <i class="fa-solid fa-square"></i>
    <i class="fa-solid fa-square"></i>
    <i class="fa-solid fa-square"></i>
  </div>
  <div class="orange-circle"></div>
  <div class="blue-circle"></div>
</section>

<section class="service">
  <div class="service-container">
    <div class="service-card">
      <div class="icon">
        <i class="fa-solid fa-bolt-lightning"></i>
      </div>
      <div class="service-content">
        <h5>Quick Delivery</h5>
        <p>
          Your books are packed with care and shipped swiftly right to your door.
        </p>
      </div>
    </div>
    <div class="service-card">
      <div class="icon">
        <i class="fa-solid fa-shield"></i>
      </div>
      <div class="service-content">
        <h5>Secure Payment</h5>
        <p>
          All transactions are encrypted to keep your personal details protected.
        </p>
      </div>
    </div>
    <div class="service-card">
      <div class="icon">
        <i class="fa-solid fa-thumbs-up"></i>
      </div>
      <div class="service-content">
        <h5>Best Quality</h5>
        <p>
          We offer genuine, high-quality books so you always enjoy the best read.
        </p>
      </div>
    </div>
    <div class="service-card">
      <div class="icon">
        <i class="fa-solid fa-star"></i>
      </div>
      <div class="service-content">
        <h5>Return Guarantee</h5>
        <p>
          Not happy with your order? Our easy return policy has you fully covered.
        </p>
      </div>
    </div>
  </div>
</section>
<section class="suggestion">
  <div class="container">
    <div class="recommend">
      <h4>Recommended For You</h4>
      <p>
        Discover books chosen to match your tastes and interests. Find,
        enjoy reading.Discover new stories made for you.
      </p>
      <div class="book-container">
        <?php foreach (array_slice($recommended, 0, 4) as $book): ?>
          <div class="book">
            <figure>
              <a href="/pages/book-detail.php?id=<?= $book->id ?>"><img src="images/<?= $book->coverImage ?>" alt="book" /></a>
            </figure>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="circle-1"></div>
      <div class="circle-2"></div>
      <div class="square-dot">
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
      </div>
    </div>
    <div class="popular">
      <h4>Popular in 2025</h4>
      <p>
        Explore the titles everyone is loving this year. Find,
        your next favorite.
      </p>
      <div class="book-container">
        <?php foreach (array_slice($popular, 0, 4) as $book): ?>
          <div class="book">
            <figure>
              <a href="/pages/book-detail.php?id=<?= $book->id ?>"><img src="images/<?= $book->coverImage ?>" alt="book" /></a>
            </figure>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="circle-1"></div>
      <div class="circle-2"></div>
      <div class="square-dot">
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
        <i class="fa-solid fa-square"></i>
      </div>
    </div>
  </div>
</section>



<section class="sale">
  <div class="header">
    <h4>Flash Sale</h4>
    <p>
      Catch amazing discounts during our mega flash sale now. <br />
      Best prices updated daily for all users.
    </p>

  </div>
  <div class="timer">
    <?php foreach (array_slice($deals, 0, 1) as $book): ?>
      <p id="dealEnd"><?php echo $book->end_time; ?></p>
    <?php endforeach; ?>


    <div class="hours">
      <span id="hour">00</span>
      <small>Hours</small>
    </div>

    <div class="minutes">
      <span id="minute">00</span>
      <small>Minutes</small>
    </div>

    <div class="seconds">
      <span id="second">00</span>
      <small>Seconds</small>
    </div>
  </div>

  <div class="book-container">
    <?php
    foreach ($deals as $book):
    ?>
      <div class="book">
        <div class="img">
          <a href="/pages/book-detail.php?id=<?= $book->id ?>"><img src="/images/<?= $book->coverImage ?>" alt="bookImg" />
          </a>
        </div>
        <h5><?= htmlspecialchars($book->title) ?></h5>
        <div class="price">
          <span><?= $book->deal_price ?>$</span>
          <span><strike><?= $book->Original_Price ?>$</strike></span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="book-sale">
  <div class="heading">
    <h4>Books On Sale</h4>
    <div class="arrowbtn">
      <i id="left" class="fa-solid fa-angle-left"></i>
      <i id="right" class="fa-solid fa-angle-right"></i>
    </div>
  </div>
  <div class="book-container">
    <div class="wrapper">
      <ul class="carousel">
        <?php foreach($sales as $book): ?>
        <li class="card">
          <div class="img">
            <a href="/pages/book-detail.php?id=<?= $book->id ?>"><img src="/images/<?= $book->coverImage ?>" alt="book" /></a>
            <span class="badge"><?= $book->Discount_Percentage ?></span>
          </div>
          <h5><?= $book->title ?></h5>
          <div class="footer">
            <span class="star"><i class="fa fa-star"></i> 4.7</span>
            <div class="price">
              <span><?= $book->Discount_Price ?></span>
              <span><strike><?= $book->Original_Price ?></strike></span>
            </div>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<section class="feature">
  <div class="main">
    <div class="content">
      <h4>Featured Books</h4>
      <p>
        Lorem ipsum dolor sit amet consectetur adipisicing elit. <br />
        Molestias, illum? Doloremque eius quis officiis rerum pariatur.
      </p>
      <div class="featured-book-card">
        <?php foreach (array_slice($featuredBooks, 0, 1) as $book): ?>
        <div class="img">
          <a href="/pages/book-detail.php?id=<?= $book->id ?>"><img src="images/<?= $book->coverImage ?>" alt="book" /></a>
        </div>
        <div class="card-description">
          <div class="card-header">
            <div class="icon">
              <i class="fa-regular fa-bookmark"></i>
              <i class="fa fa-star"></i>
            </div>
            <div class="title">
              <h5><?= $book->title ?></h5>
              
            </div>
          </div>
          <div class="card-body">
            <h6>Synopsis</h6>
            <p>
              <?= substr($book->description, 0, 200) ?>...
            </p>
            <div class="author-year">
              <div class="author">
                <small>Written by</small>
                <strong><?= $book->author ?></strong>
              </div>
              <div class="year">
                <small>Year</small>
                <strong><?= $book->year ?></strong>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <div class="price">
              <span><?= $book->Discount_Price ?></span>
              <strike><?= $book->Original_Price ?></strike>
            </div>
            <div class="cartbtn">
              <button><a href="/pages/book-detail.php?id=<?= $book->id ?>">Buy </a></button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="book-section">
      <div class="container">
        <?php foreach (array_slice($featuredBooks, 1, 7) as $book): ?>
        <div class="img">
          <a href="/pages/book-detail.php?id=<?= $book->id ?>"><img src="images/<?= $book->coverImage ?>" alt="book" /></a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="circle-1"></div>
  <div class="circle-2"></div>
</section>


<section class="countdown">
  <div class="container">
    <div class="customer counter">
      <div class="icon">
        <i class="fa-solid fa-user-group"></i>
      </div>
      <div class="content">
        <h4 class="count"><?= $totalUsers ?></h4>
        <small>Happy Customers</small>
      </div>
    </div>
    <div class="book counter">
      <div class="icon">
        <i class="fa-solid fa-book"></i>
      </div>
      <div class="content">
        <h4 class="count"><?= $allBooks ?></h4>
        <small>Book Collections</small>
      </div>
    </div>
    <div class="store counter">
      <div class="icon">
        <i class="fa-solid fa-store"></i>
      </div>
      <div class="content">
        <h4 class="count">1</h4>
        <small>Our Stores</small>
      </div>
    </div>

  </div>
</section>
<section class="subscription">
  <?php if ($user->subscribed == "unsubscribed" || empty($_SESSION['id'])) : ?>
    <div class="container">
      <h4>
        Subscribe our newsletter for Latest <br />
        books updates
      </h4>
      <form action="/handlers/subscribe.php" method="post">
        <div class="input">
          <input type="text" name="email" placeholder="Type your email here" />
          <button>subscribe</button>
        </div>
      </form>
    </div>
    <div class="circle-1"></div>
    <div class="circle-2"></div>
  <?php else: ?>
    <div class="container">
      <h4>
        You are Subscribe to our newsletter for Latest <br />
        books updates.
      </h4>
      <form action="/handlers/unSubscribe.php" method="post">

        <button class="unsubscribe">UnSubscribe</button>

      </form>
    </div>
    <div class="circle-1"></div>
    <div class="circle-2"></div>
  <?php endif; ?>
</section>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const counters = document.querySelectorAll('.count');
    const speed = 200; // lower = faster

    const animateCounter = (counter) => {
      const updateCount = () => {
        const target = +counter.getAttribute('data-target') || +counter.textContent.replace(/,/g, '');
        const count = +counter.textContent.replace(/,/g, '');
        const increment = Math.ceil(target / speed);

        if (count < target) {
          counter.textContent = count + increment;
          setTimeout(updateCount, 20);
        } else {
          counter.textContent = target.toLocaleString();
        }
      };
      updateCount();
    };

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          observer.unobserve(entry.target); // animate only once
        }
      });
    }, {
      threshold: 0.5
    });

    counters.forEach(counter => {
      // store target in data attribute
      counter.setAttribute('data-target', counter.textContent.replace(/,/g, ''));
      counter.textContent = '0';
      observer.observe(counter);
    });
  });
</script>

<script>

 document.addEventListener("DOMContentLoaded", () => {
  const wrapper = document.querySelector(".wrapper");
  const carousel = document.querySelector(".carousel");
  const arrowBtns = document.querySelectorAll(".book-sale .arrowbtn i");

  if (!wrapper || !carousel) return;

  const cards = carousel.querySelectorAll(".card");
  if (!cards.length) return;

  let isDragging = false;
  let startX, startScrollLeft;
  let autoPlayInterval;

  const firstCardWidth = cards[0].offsetWidth;

  // Clone cards for infinite scroll
  const cardPerView = Math.max(1, Math.round(carousel.offsetWidth / firstCardWidth));
  const carouselChildren = Array.from(carousel.children);
  carouselChildren.slice(-cardPerView).reverse().forEach(card => {
    carousel.insertAdjacentHTML("afterbegin", card.outerHTML);
  });
  carouselChildren.slice(0, cardPerView).forEach(card => {
    carousel.insertAdjacentHTML("beforeend", card.outerHTML);
  });

  // Start at the real first card
  carousel.classList.add("no-transition");
  carousel.scrollLeft = carousel.offsetWidth;
  carousel.classList.remove("no-transition");

  // Arrow buttons
  arrowBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      carousel.scrollBy({
        left: btn.id === "left" ? -firstCardWidth : firstCardWidth,
        behavior: "smooth"
      });
    });
  });

  // Drag events
  const dragStart = (e) => {
    isDragging = true;
    startX = e.clientX || e.touches[0].clientX;
    startScrollLeft = carousel.scrollLeft;
    carousel.classList.add("dragging");
  };

  const dragging = (e) => {
    if (!isDragging) return;
    const x = e.clientX || e.touches[0].clientX;
    const walk = x - startX;
    carousel.scrollLeft = startScrollLeft - walk;
  };

  const dragStop = () => {
    isDragging = false;
    carousel.classList.remove("dragging");
  };

  carousel.addEventListener("mousedown", dragStart);
  carousel.addEventListener("touchstart", dragStart);
  carousel.addEventListener("mousemove", dragging);
  carousel.addEventListener("touchmove", dragging);
  document.addEventListener("mouseup", dragStop);
  document.addEventListener("touchend", dragStop);
  carousel.addEventListener("dragstart", e => e.preventDefault()); // prevent image drag

  // Infinite scroll
  const infiniteScroll = () => {
    if (carousel.scrollLeft <= 0) {
      carousel.classList.add("no-transition");
      carousel.scrollLeft = carousel.scrollWidth - (2 * carousel.offsetWidth);
      carousel.classList.remove("no-transition");
    } else if (carousel.scrollLeft >= carousel.scrollWidth - carousel.offsetWidth) {
      carousel.classList.add("no-transition");
      carousel.scrollLeft = carousel.offsetWidth;
      carousel.classList.remove("no-transition");
    }
  };
  carousel.addEventListener("scroll", infiniteScroll);

  // Auto-play with smooth scroll
  const startAutoPlay = () => {
    autoPlayInterval = setInterval(() => {
      if (!wrapper.matches(":hover")) {
        carousel.scrollBy({
          left: firstCardWidth,
          behavior: "smooth"
        });
      }
    }, 2500);
  };

  const stopAutoPlay = () => clearInterval(autoPlayInterval);

  wrapper.addEventListener("mouseenter", stopAutoPlay);
  wrapper.addEventListener("mouseleave", startAutoPlay);

  startAutoPlay();
});
</script>

<?php
require __DIR__ . "/includes/footer.php";
?>