<?php
require __DIR__ . "/config/config.php";

$stmtCat = $connection->query("
    SELECT c.id, c.title, COUNT(b.id) AS book_count 
    FROM categories c 
    LEFT JOIN book b ON b.category_id = c.id 
        AND NOT EXISTS (
            SELECT 1 FROM deals d 
            WHERE d.book_id = b.id AND d.status = 'active' AND CURRENT_TIMESTAMP < d.end_time
        )
    GROUP BY c.id, c.title 
    ORDER BY c.title ASC
");
$categories = $stmtCat->fetchAll(PDO::FETCH_OBJ);

$totalAllBooks = (int)$connection->query("
    SELECT COUNT(*) FROM book b 
    WHERE NOT EXISTS (
        SELECT 1 FROM deals d 
        WHERE d.book_id = b.id AND d.status = 'active' AND CURRENT_TIMESTAMP < d.end_time
    )
")->fetchColumn();

$totalSaleBooks = (int)$connection->query("
    SELECT COUNT(*) FROM book b 
    WHERE b.Discount_Percentage IS NOT NULL AND b.Discount_Percentage > 0 
      AND NOT EXISTS (
          SELECT 1 FROM deals d 
          WHERE d.book_id = b.id AND d.status = 'active' AND CURRENT_TIMESTAMP < d.end_time
      )
")->fetchColumn();

$selectedCategory = null;
$categoryParam = trim($_GET['category'] ?? '');

if (!empty($categoryParam)) {
    foreach ($categories as $cat) {
        if (strcasecmp($cat->title, $categoryParam) === 0 || (is_numeric($categoryParam) && (int)$cat->id === (int)$categoryParam)) {
            $selectedCategory = $cat;
            break;
        }
    }
}

$isSaleFilter = (isset($_GET['filter']) && strtolower(trim($_GET['filter'])) === 'sale');

$limit = 10;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
if (!$page && isset($_GET['page'])) {
    $page = filter_var($_GET['page'], FILTER_VALIDATE_INT);
}
if (!$page || $page < 1) {
    $page = 1;
}

$whereConditions = [];
$params = [];

// Always exclude books that are featured in an active Daily Deal
$whereConditions[] = "NOT EXISTS (
    SELECT 1 FROM deals d 
    WHERE d.book_id = b.id AND d.status = 'active' AND CURRENT_TIMESTAMP < d.end_time
)";

if ($selectedCategory) {
    $whereConditions[] = "b.category_id = :cat_id";
    $params[':cat_id'] = $selectedCategory->id;
}

if ($isSaleFilter) {
    $whereConditions[] = "b.Discount_Percentage IS NOT NULL AND b.Discount_Percentage > 0";
}

$whereClause = "";
if (!empty($whereConditions)) {
    $whereClause = "WHERE " . implode(" AND ", $whereConditions);
}

$countStmt = $connection->prepare("SELECT COUNT(*) FROM book b $whereClause");
$countStmt->execute($params);
$totalBooks = (int)$countStmt->fetchColumn();

$totalPages = max(1, (int)ceil($totalBooks / $limit));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $limit;

$booksStmt = $connection->prepare("
    SELECT b.*, c.title AS category_title 
    FROM book b 
    LEFT JOIN categories c ON b.category_id = c.id 
    $whereClause 
    ORDER BY b.id DESC 
    LIMIT :limit OFFSET :offset
");
foreach ($params as $k => $v) {
    $booksStmt->bindValue($k, $v);
}
$booksStmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$booksStmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$booksStmt->execute();
$books = $booksStmt->fetchAll(PDO::FETCH_OBJ);

$queryParts = [];
if ($selectedCategory) {
    $queryParts[] = "category=" . urlencode(strtolower($selectedCategory->title));
}
if ($isSaleFilter) {
    $queryParts[] = "filter=sale";
}

if (!empty($queryParts)) {
    $paginationBaseUrl = "/shop.php?" . implode("&", $queryParts) . "&page=";
} else {
    $paginationBaseUrl = "/shop.php?page=";
}

if ($isSaleFilter && $selectedCategory) {
    $pageTitle = htmlspecialchars($selectedCategory->title) . " - On Sale";
    $pageSubtitle = "Showing discounted books in " . htmlspecialchars($selectedCategory->title) . ".";
} elseif ($isSaleFilter) {
    $pageTitle = "Books On Sale";
    $pageSubtitle = "Browse through our handpicked discounted literature.";
} elseif ($selectedCategory) {
    $pageTitle = htmlspecialchars($selectedCategory->title);
    $pageSubtitle = "Showing books in " . htmlspecialchars($selectedCategory->title) . "."; 
} else {
    $pageTitle = "Curated Book Catalog";
    $pageSubtitle = "Browse through our handpicked collection of transformative literature.";
}

require __DIR__ . "/includes/header.php";
require __DIR__ . "/includes/navigationBar.php";
?>

<div class="page-header-banner">
    <h1 class="page-header-title"><?= $pageTitle ?></h1>
    <p class="page-header-subtitle"><?= $pageSubtitle ?></p>
</div>

<div class="shop-layout">
    <aside class="shop-sidebar">
        <div class="sidebar-genre-widget">
            <h3 class="sidebar-title genre-collapse-toggle">
                <span class="genre-title-left">
                    <i class="ph-bold ph-funnel"></i> Genres
                </span>
                <i class="ph-bold ph-caret-down genre-collapse-icon"></i>
            </h3>
            <ul class="sidebar-filter-list genre-collapsible-content" style="margin-top: 14px;">
                <li>
                    <a href="/shop.php" class="<?= (!$selectedCategory && !$isSaleFilter) ? 'active' : '' ?>">
                        <span>All Books</span> <span>(<?= $totalAllBooks ?>)</span>
                    </a>
                </li>
                 <li>
                    <a href="/shop.php?filter=sale" class="<?= ($isSaleFilter && !$selectedCategory) ? 'active' : '' ?>">
                        <span style="display: inline-flex; align-items: center; gap: 8px;">
                             On Sale
                        </span>
                        <span>(<?= $totalSaleBooks ?>)</span>
                    </a>
                </li>
                
                <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="/shop.php?category=<?= urlencode(strtolower($cat->title)) ?>" class="<?= ($selectedCategory && $selectedCategory->id === $cat->id && !$isSaleFilter) ? 'active' : '' ?>">
                            <span><?= htmlspecialchars($cat->title) ?></span> <span>(<?= (int)$cat->book_count ?>)</span>
                        </a>
                    </li>
                <?php endforeach; ?>
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
        <?php if (empty($books)): ?>
            <div class="shop-empty-state">
                <i class="ph-bold ph-books"></i>
                <h3>No books found</h3>
                <p>Explore our other genres or check back later for new arrivals.</p>
                <a href="/shop.php" class="btn-shop-reset">
                    View All Books
                </a>
            </div>
        <?php else: ?>
            <div class="shop-books-grid">
                <?php foreach ($books as $book): ?>
                    <a href="/book?id=<?= $book->id ?>" class="book-card">
                        <?php if (!empty($book->Discount_Percentage) && (int)$book->Discount_Percentage > 0): ?>
                            <span class="book-discount-badge">-<?= (int)$book->Discount_Percentage ?>%</span>
                        <?php endif; ?>
                        <div class="book-cover-wrap">
                            <img src="/images/<?= htmlspecialchars($book->coverImage) ?>" alt="<?= htmlspecialchars($book->title) ?>" class="book-cover-img" onerror="this.src='/images/logo.png'" />
                        </div>
                        <div class="book-details">
                            <h3 class="book-title"><?= htmlspecialchars($book->title) ?></h3>
                            <span class="book-author"><?= htmlspecialchars($book->author) ?></span>
                            <div class="book-rating">
                                <i class="ph-bold ph-tag"></i>
                                <span><?= htmlspecialchars($book->category_title ?? 'General') ?></span>
                            </div>
                            <div class="book-price-row">
                                <?php if (!empty($book->Discount_Percentage) && (int)$book->Discount_Percentage > 0): ?>
                                    <span class="book-current-price">$<?= number_format((float)$book->Discount_Price, 2) ?></span>
                                    <span class="book-original-price">$<?= number_format((float)$book->Original_Price, 2) ?></span>
                                <?php else: ?>
                                    <span class="book-current-price">$<?= number_format((float)$book->Original_Price, 2) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="shop-pagination" aria-label="Page navigation">
                    <?php if ($page > 1): ?>
                        <a href="<?= $paginationBaseUrl . ($page - 1) ?>" class="pagination-btn pagination-prev">
                            <i class="ph-bold ph-caret-left"></i> Prev
                        </a>
                    <?php else: ?>
                        <span class="pagination-btn pagination-disabled">
                            <i class="ph-bold ph-caret-left"></i> Prev
                        </span>
                    <?php endif; ?>

                    <div class="pagination-pages">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="pagination-page-num active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= $paginationBaseUrl . $i ?>" class="pagination-page-num"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= $paginationBaseUrl . ($page + 1) ?>" class="pagination-btn pagination-next">
                            Next <i class="ph-bold ph-caret-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="pagination-btn pagination-disabled">
                            Next <i class="ph-bold ph-caret-right"></i>
                        </span>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</div>

<?php
require __DIR__ . "/includes/footerSection.php";
require __DIR__ . "/includes/footer.php";
?>
