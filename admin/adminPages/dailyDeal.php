<?php 
require __DIR__."/../../config/config.php";
require_once __DIR__ . "/../../handlers/expireDeals.php";
require __DIR__."/../../includes/dashboardHeader.php";

// Section 1: Fetch Eligible Books (Original_Price > 25, 0% Discount, In Stock, Not in Active Deal)
$fetch = $connection->prepare("
    SELECT id, title, author, Original_Price, Discount_Percentage, coverImage, Stock 
    FROM book 
    WHERE Original_Price > 25 
      AND (Discount_Percentage IS NULL OR Discount_Percentage = 0)
      AND Stock > 0
      AND NOT EXISTS (
          SELECT 1 FROM deals d 
          WHERE d.book_id = book.id 
            AND d.status = 'active' 
            AND CURRENT_TIMESTAMP < d.end_time
      )
    ORDER BY Original_Price DESC, title ASC
");

$fetch->execute();
$result = $fetch->fetchAll(PDO::FETCH_OBJ);

$fetchDeal = $connection->prepare("
    SELECT 
        b.*,
        d.id AS deal_id,
        d.discount_percentage,
        d.start_time,
        d.end_time
    FROM book b
    INNER JOIN deals d 
        ON b.id = d.book_id
       AND CURRENT_TIMESTAMP < d.end_time
       AND d.status = 'active'
    ORDER BY d.id DESC
    LIMIT 1
");
$fetchDeal->execute();
$dealBook = $fetchDeal->fetch(PDO::FETCH_OBJ);
$hasActiveDeal = !empty($dealBook);
?>

<div id="content">

    <div class="deal-page">
        <div class="form-wrapper">
            <div class="form-container">
                <h4 class="form-title">Set New Deal</h4>

                <?php if ($hasActiveDeal): ?>
                    <div class="deal-active-notice">
                        <i class="ph-bold ph-lock-key"></i>
                        <div>
                            <strong>Active Deal Running:</strong> Only 1 book can be on daily deal at a time. The deal form is locked and will automatically re-enable once the current active deal expires.
                        </div>
                    </div>
                <?php endif; ?>

                <form method="post" action="/admin/handlers/deals.php" class="ajax-form">
                    <div class="form-group">
                        <label style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
                            <span>Select Eligible Book</span>
                        </label>
                        <div class="input-wrapper" style="position: relative;">
                            <!-- Hidden input holding selected book ID for submission -->
                            <input type="hidden" name="id" id="selectedBookId" value="" <?= $hasActiveDeal ? 'disabled' : '' ?> required>

                            <!-- Neo-Brutalist Custom Dropdown Component -->
                            <div class="neo-custom-select-container <?= $hasActiveDeal ? 'disabled' : '' ?>" id="neoBookSelectContainer">
                                <button type="button" class="neo-custom-select-trigger" id="neoBookSelectTrigger" <?= $hasActiveDeal ? 'disabled' : '' ?> aria-haspopup="listbox" aria-expanded="false">
                                    <div class="neo-selected-placeholder" id="neoSelectDisplay">
                                        <i class="ph-bold ph-book-bookmark neo-select-icon"></i>
                                        <span class="placeholder-text">Select Book for Daily Deal</span>
                                    </div>
                                    <i class="ph-bold ph-caret-down neo-select-arrow" id="neoSelectArrow"></i>
                                </button>

                                <div class="neo-custom-select-menu" id="neoBookSelectMenu">
                                    <div class="neo-select-search-wrap">
                                        <i class="ph-bold ph-magnifying-glass"></i>
                                        <input type="text" id="neoBookSearchInput" placeholder="Filter eligible books by title or author..." autocomplete="off">
                                    </div>

                                    <div class="neo-select-options-list" id="neoBookOptionsList" role="listbox">
                                        <?php if (empty($result)): ?>
                                            <div class="neo-select-empty">
                                                <i class="ph-bold ph-info"></i>
                                                <span>No eligible books found ($25+ with 0% discount and in stock).</span>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach($result as $book): ?>
                                                <div class="neo-select-option" 
                                                     role="option"
                                                     data-id="<?= $book->id ?>" 
                                                     data-title="<?= htmlspecialchars($book->title, ENT_QUOTES) ?>" 
                                                     data-author="<?= htmlspecialchars($book->author ?? '', ENT_QUOTES) ?>" 
                                                     data-price="<?= number_format((float)$book->Original_Price, 2) ?>"
                                                     data-cover="<?= htmlspecialchars($book->coverImage ?? 'logo.png', ENT_QUOTES) ?>"
                                                     data-stock="<?= (int)$book->Stock ?>">
                                                    <img src="/images/<?= htmlspecialchars($book->coverImage ?? 'logo.png') ?>" alt="<?= htmlspecialchars($book->title) ?>" class="option-book-cover" onerror="this.src='/images/logo.png'">
                                                    <div class="option-book-details">
                                                        <div class="option-book-title"><?= htmlspecialchars($book->title) ?></div>
                                                        <div class="option-book-author"><?= htmlspecialchars($book->author ?? 'Standard Edition') ?> &bull; Stock: <?= (int)$book->Stock ?></div>
                                                    </div>
                                                    <div class="option-book-price">
                                                        $<?= number_format((float)$book->Original_Price, 2) ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="error id"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Discounted Percentage</label>
                        <div class="input-wrapper">
                            <input type="number" name="percentage" placeholder="70%" min="1" max="70" <?= $hasActiveDeal ? 'disabled' : '' ?> required>
                            <div class="error percentage"></div>
                        </div>
                    </div>

                    <?php if ($hasActiveDeal): ?>
                        <button type="submit" class="btn-submit orange" disabled>
                            Deal Active (Locked)
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn-submit orange">
                            Activate Deal
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <?php if ($hasActiveDeal && $dealBook): ?>
            <div class="active-deal-wrapper">
                <div class="deal-preview-card single-deal-card">
                    <h4 class="form-title">Currently Active Deal</h4>
                    <div class="deal-visual">
                        <img src="/images/<?= htmlspecialchars($dealBook->coverImage) ?>" alt="Book Cover">
                        <div class="deal-badge">-<?= htmlspecialchars($dealBook->discount_percentage) ?>%</div>
                    </div>
                    <div class="deal-info">
                        <h3><?= htmlspecialchars($dealBook->title) ?></h3>
                        <div class="price-box">
                            <span class="old-price">$<?= htmlspecialchars($dealBook->Original_Price) ?></span>
                            <span class="new-price">$<?= htmlspecialchars($dealBook->Discount_Price) ?></span>
                        </div>
                        <div class="countdown">
                            <i class="ph-bold ph-clock"></i> Ends in: <strong><?= htmlspecialchars($dealBook->end_time) ?></strong>
                        </div>
                        <div class="deal-actions">
                            <button type="button" class="btn btn-danger openRemoveDealModal" data-deal-id="<?= $dealBook->deal_id ?>" data-book-id="<?= $dealBook->id ?>" data-book-title="<?= htmlspecialchars($dealBook->title, ENT_QUOTES) ?>">
                                Remove from Daily Deal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

</div>

<div class="modal-overlay" id="removeDealOverlay"></div>

<div class="confirm-modal" id="removeDealModal">
    <div class="modal-header">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-warning-circle" style="font-size: 22px; color: var(--neo-pink);"></i>
            <h3>Remove from Daily Deal</h3>
        </div>
        <span class="close-modal" id="closeRemoveDeal">&times;</span>
    </div>
    <div class="confirm-modal-body">
        <p>Are you sure you want to remove <span class="confirm-book-badge" id="removeDealBookTitle"></span> from the Daily Deal?</p>
        <div class="confirm-modal-warning-box">
            <i class="ph-bold ph-info"></i>
            <p>This will immediately end the daily deal and restore the original price for this book. The deal form will be unlocked.</p>
        </div>
    </div>
    <form action="/admin/handlers/deleteDeal.php" method="POST" class="ajax-form" id="removeDealForm">
        <input type="hidden" name="deal_id" id="removeDealIdField">
        <input type="hidden" name="book_id" id="removeDealBookIdField">
        <div class="confirm-modal-actions">
            <button type="button" class="btn btn-secondary" id="cancelRemoveDealBtn">Cancel</button>
            <button type="submit" class="btn btn-danger" id="confirmRemoveDealBtn">Remove Deal</button>
        </div>
    </form>
</div>

</div>

<script>
    const removeDealModal = document.getElementById('removeDealModal');
    const removeDealOverlay = document.getElementById('removeDealOverlay');
    const closeRemoveDealBtn = document.getElementById('closeRemoveDeal');
    const cancelRemoveDealBtn = document.getElementById('cancelRemoveDealBtn');
    const removeDealIdField = document.getElementById('removeDealIdField');
    const removeDealBookIdField = document.getElementById('removeDealBookIdField');
    const removeDealBookTitle = document.getElementById('removeDealBookTitle');

    document.querySelectorAll('.openRemoveDealModal').forEach(btn => {
        btn.addEventListener('click', () => {
            const dealId = btn.getAttribute('data-deal-id');
            const bookId = btn.getAttribute('data-book-id');
            const bookTitle = btn.getAttribute('data-book-title');

            if (removeDealIdField) removeDealIdField.value = dealId;
            if (removeDealBookIdField) removeDealBookIdField.value = bookId;
            if (removeDealBookTitle) removeDealBookTitle.textContent = bookTitle;

            if (removeDealModal) removeDealModal.classList.add('active');
            if (removeDealOverlay) removeDealOverlay.classList.add('active');
        });
    });

    function closeRemoveDeal() {
        if (removeDealModal) removeDealModal.classList.remove('active');
        if (removeDealOverlay) removeDealOverlay.classList.remove('active');
    }

    if (closeRemoveDealBtn) closeRemoveDealBtn.addEventListener('click', closeRemoveDeal);
    if (cancelRemoveDealBtn) cancelRemoveDealBtn.addEventListener('click', closeRemoveDeal);
    if (removeDealOverlay) removeDealOverlay.addEventListener('click', closeRemoveDeal);

    // Section: Neo-Brutalist Custom Book Select Component Logic
    const selectContainer = document.getElementById('neoBookSelectContainer');
    const selectTrigger = document.getElementById('neoBookSelectTrigger');
    const selectMenu = document.getElementById('neoBookSelectMenu');
    const selectDisplay = document.getElementById('neoSelectDisplay');
    const selectedInput = document.getElementById('selectedBookId');
    const searchInput = document.getElementById('neoBookSearchInput');
    const optionsList = document.getElementById('neoBookOptionsList');
    const options = document.querySelectorAll('.neo-select-option');
    const errorDiv = document.querySelector('.error.id');

    if (selectTrigger && selectContainer && !selectContainer.classList.contains('disabled')) {
        function openDropdown() {
            selectContainer.classList.add('active');
            selectTrigger.setAttribute('aria-expanded', 'true');
            if (searchInput) {
                searchInput.value = '';
                filterOptions('');
                setTimeout(() => searchInput.focus(), 60);
            }
        }

        function closeDropdown() {
            selectContainer.classList.remove('active');
            selectTrigger.setAttribute('aria-expanded', 'false');
        }

        selectTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            if (selectContainer.classList.contains('active')) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        options.forEach(option => {
            option.addEventListener('click', (e) => {
                e.stopPropagation();
                const bookId = option.getAttribute('data-id');
                const bookTitle = option.getAttribute('data-title');
                const bookAuthor = option.getAttribute('data-author');
                const bookPrice = option.getAttribute('data-price');
                const bookCover = option.getAttribute('data-cover');

                selectedInput.value = bookId;
                if (errorDiv) errorDiv.innerText = '';

                selectDisplay.innerHTML = `
                    <div class="neo-selected-book-info">
                        <img src="/images/${bookCover}" alt="${bookTitle}" onerror="this.src='/images/logo.png'">
                        <div class="book-meta">
                            <div class="book-title">${bookTitle}</div>
                            <div class="book-author">${bookAuthor}</div>
                        </div>
                        <div class="book-price-pill">$${bookPrice}</div>
                    </div>
                `;

                options.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');

                closeDropdown();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                filterOptions(e.target.value.toLowerCase().trim());
            });

            searchInput.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

        function filterOptions(query) {
            let visibleCount = 0;
            options.forEach(option => {
                const title = (option.getAttribute('data-title') || '').toLowerCase();
                const author = (option.getAttribute('data-author') || '').toLowerCase();
                if (!query || title.includes(query) || author.includes(query)) {
                    option.style.display = 'flex';
                    visibleCount++;
                } else {
                    option.style.display = 'none';
                }
            });

            let noResultMsg = document.getElementById('neoSelectNoSearchMatch');
            if (visibleCount === 0) {
                if (!noResultMsg) {
                    noResultMsg = document.createElement('div');
                    noResultMsg.id = 'neoSelectNoSearchMatch';
                    noResultMsg.className = 'neo-select-empty';
                    noResultMsg.innerHTML = '<i class="ph-bold ph-magnifying-glass"></i><span>No matching eligible books found.</span>';
                    optionsList.appendChild(noResultMsg);
                }
                noResultMsg.style.display = 'flex';
            } else if (noResultMsg) {
                noResultMsg.style.display = 'none';
            }
        }

        document.addEventListener('click', (e) => {
            if (!selectContainer.contains(e.target)) {
                closeDropdown();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && selectContainer.classList.contains('active')) {
                closeDropdown();
            }
        });
    }
</script>

<script src="/assests/js/admin.js"></script>
</body>
</html>
