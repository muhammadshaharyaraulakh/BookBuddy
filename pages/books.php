<?php
require __DIR__ . "/../config/config.php";
require __DIR__ . "/../includes/header.php";

$categoriesStmt = $connection->query("SELECT * FROM categories ORDER BY title ASC");
$allCategories = $categoriesStmt->fetchAll(PDO::FETCH_OBJ);

$booksStmt = $connection->query("SELECT * FROM book ORDER BY id DESC");
$allBooks = $booksStmt->fetchAll(PDO::FETCH_OBJ);

$onSale=$connection->prepare("SELECT * FROM book WHERE Discount_Percentage IS NOT NULL AND id NOT IN (SELECT book_id FROM deals)");
$onSale->execute();
$sales=$onSale->fetchAll(PDO::FETCH_OBJ);
?>

<div class="breadcrumb-container">
  <ul class="breadcrumb">
    <li><a href="/index.php">Home</a></li>
    <li><a href="#">Books</a></li>
  </ul>
</div>

<section class="filter">
  <div class="book-grid-container">
    <div class="filter-option">
      <div class="filter-group">
        <h4>Filter Options</h4>
        
        <div class="genre-category select-box">
          <div class="opt-title">
            <h4>Shop By Category</h4>
            <i class="fa-solid fa-caret-down"></i>
          </div>
          <div class="option">
            <?php foreach($allCategories as $cat): ?>
            <div class="category">
              <input type="checkbox" name="category_id[]" value="<?= $cat->id ?>" />
              <small><?= htmlspecialchars($cat->title) ?></small>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="range-slider dropdown">
          <div class="opt-title">
            <h4>Price Range</h4>
          </div>
          <div class="option">
            <div class="price-input">
              <div class="field">
                <span>Min</span>
                <input type="number" class="input-min" value="0">
              </div>
              <div class="separator">-</div>
              <div class="field">
                <span>Max</span>
                <input type="number" class="input-max" value="1000">
              </div>
            </div>
          </div>
        </div>

        <div class="footer-btn">
          <button>Refine Search</button>
          <button>Reset Filter</button>
        </div>
      </div>
    </div>
    
    <div class="book-collections">
      <h4>Books</h4>
      <div class="books">
        <?php foreach($allBooks as $book): ?>
        <div class="book-card">
          <div class="img">
            <a href="/pages/book-detail.php?id=<?= $book->id ?>"><img src="/images/<?= htmlspecialchars($book->coverImage) ?>" alt="book" /></a>
            <button class="like" id="likebtn">
              <i class="fa-regular fa-heart"></i>
            </button>
          </div>
          <h5><?= htmlspecialchars($book->title) ?></h5>
          <div class="star-rating">
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
          </div>
          <div class="price" style="display:flex; gap:10px; margin-top:10px;">
            <strong style="color: #6c5dd4;">$<?= htmlspecialchars($book->Discount_Price ?: $book->Original_Price) ?></strong>
            <?php if($book->Discount_Price && $book->Discount_Price != $book->Original_Price): ?>
                <strike style="color: gray;">$<?= htmlspecialchars($book->Original_Price) ?></strike>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
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
            <a href="/pages/book-detail.php?id=<?= $book->id ?>"><img src="/images/<?= htmlspecialchars($book->coverImage) ?>" alt="book" /></a>
            <span class="badge"><?= htmlspecialchars($book->Discount_Percentage) ?>%</span>
          </div>
          <h5><?= htmlspecialchars($book->title) ?></h5>
          <div class="footer">
            <span class="star"><i class="fa fa-star"></i> 4.7</span>
            <div class="price">
              <span>$<?= htmlspecialchars($book->Discount_Price) ?></span>
              <span><strike>$<?= htmlspecialchars($book->Original_Price) ?></strike></span>
            </div>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

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
  carousel.addEventListener("dragstart", e => e.preventDefault());

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

<?php require __DIR__ . "/../includes/footer.php"; ?>
