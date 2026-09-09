<?php 
require __DIR__."/../../config/config.php";
require_once __DIR__ . "/../../handlers/expireDeals.php";
require __DIR__."/../../includes/dashboardHeader.php";
$allCategories = getCategories($connection);

if (empty($_GET['id'])) {
    header("Location: /books");
    exit;
}
$id = $_GET['id'];
$fetch = $connection->prepare("SELECT * FROM book WHERE id = :id");
$fetch->execute([':id' => $id]);
$result = $fetch->fetch(PDO::FETCH_OBJ);

if (!$result) {
    header("Location: /books");
    exit;
}

$checkDeal = $connection->prepare("
    SELECT id FROM deals 
    WHERE book_id = :id 
      AND status = 'active' 
      AND CURRENT_TIMESTAMP < end_time 
    LIMIT 1
");
$checkDeal->execute([':id' => $id]);
$isOnDeal = (bool)$checkDeal->fetch();

$returnPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$returnCategory = isset($_GET['category']) ? trim($_GET['category']) : '';
$returnFrom = isset($_GET['from']) && $_GET['from'] === 'outofstock' ? 'outofstock' : 'books';
?>

<div id="content">

   <div class="form-container">
    <?php if ($isOnDeal): ?>
        <div class="deal-active-notice" style="margin-bottom: 24px;">
            <i class="ph-bold ph-lock-key"></i>
            <div>
                <strong>Update Locked:</strong> This book is currently active in the Daily Deal. It cannot be updated or modified until the deal expires.
            </div>
        </div>
    <?php endif; ?>

    <form action="/admin/handlers/updateBook.php" method="POST" enctype="multipart/form-data" class="ajax-form">
        <input type="hidden" name="id" value="<?= $result->id ?>">
        <input type="hidden" name="return_page" value="<?= $returnPage ?>">
        <input type="hidden" name="return_category" value="<?= htmlspecialchars($returnCategory) ?>">
        <input type="hidden" name="return_from" value="<?= htmlspecialchars($returnFrom) ?>">

        <div class="form-row">
            <div class="form-group">
                <label>Book Name</label>
                <div class="input-wrapper">
                    <input type="text" class="book_name" name="book_name"
                           placeholder="Programming C++"
                           value="<?= htmlspecialchars($result->title) ?>" <?= $isOnDeal ? 'disabled' : '' ?>>
                </div>
                <div class="error book_name"></div>
            </div>

            <div class="form-group">
                <label>Stock</label>
                <div class="input-wrapper">
                    <input type="number" class="stock" name="stock"
                           placeholder="000"
                           value="<?= htmlspecialchars($result->Stock) ?>" <?= $isOnDeal ? 'disabled' : '' ?>>
                </div>
                <div class="error stock"></div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Author</label>
                <div class="input-wrapper">
                    <input type="text" class="author" name="author"
                           placeholder="Author Name"
                           value="<?= htmlspecialchars($result->author) ?>" <?= $isOnDeal ? 'disabled' : '' ?>>
                </div>
                <div class="error author"></div>
            </div>

            <div class="form-group">
                <label>ISBN</label>
                <div class="input-wrapper">
                    <input type="number" class="isbn" name="isbn"
                           placeholder="000"
                           value="<?= htmlspecialchars($result->ISBN) ?>" <?= $isOnDeal ? 'disabled' : '' ?>>
                </div>
                <div class="error isbn"></div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Category</label>
                <div class="input-wrapper">
                    <select class="category" name="category" <?= $isOnDeal ? 'disabled' : '' ?>>
                        <option value="" disabled>Select Category</option>
                        <?php foreach($allCategories as $category): ?>
                            <option value="<?= $category->id ?>"
                                <?= $category->id == $result->category_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category->title) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="error category"></div>
            </div>

            <div class="form-group">
                <label>Price ($)</label>
                <div class="input-wrapper">
                    <input type="number" class="price" name="price"
                           placeholder="0.00"
                           value="<?= htmlspecialchars($result->Original_Price) ?>" <?= $isOnDeal ? 'disabled' : '' ?>>
                </div>
                <div class="error price"></div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Publisher</label>
                <div class="input-wrapper">
                    <input type="text" class="publisher" name="publisher"
                           placeholder="Publisher"
                           value="<?= htmlspecialchars($result->Publisher) ?>" <?= $isOnDeal ? 'disabled' : '' ?>>
                </div>
                <div class="error publisher"></div>
            </div>

            <div class="form-group">
                <label>Date of Publish</label>
                <div class="input-wrapper">
                    <input type="date" class="publish_date" name="publish_date"
                           value="<?= htmlspecialchars($result->publishDate) ?>" <?= $isOnDeal ? 'disabled' : '' ?>>
                </div>
                <div class="error publish_date"></div>
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <div class="input-wrapper">
                <input type="text" class="description_1" name="description_1"
                       placeholder="Book Details"
                       value="<?= htmlspecialchars($result->description_para_1) ?>" <?= $isOnDeal ? 'disabled' : '' ?>>
            </div>
            <div class="error description_1"></div>
        </div>

        <div class="form-group">
            <div class="input-wrapper">
                <input type="text" class="description_2" name="description_2"
                       placeholder="Book Details"
                       value="<?= htmlspecialchars($result->description_para_2) ?>" <?= $isOnDeal ? 'disabled' : '' ?>>
            </div>
            <div class="error description_2"></div>
        </div>

        <div class="form-group">
            <label>Upload Cover</label>
            <div class="file-upload-wrapper">
                <input type="file" class="cover_image" name="cover_image" <?= $isOnDeal ? 'disabled' : '' ?>>
                <div class="file-custom-label">
                    <i class="ph-bold ph-upload-simple"></i>
                    <span>Click to upload image</span>
                </div>
            </div>
            <div class="error cover_image"></div>

            <div class="image-preview-box" id="previewBox"
                 style="<?= $result->coverImage ? 'display:block;' : 'display:none;' ?>">
                <img id="imagePreview"
                     src="<?= $result->coverImage ? '/images/' . htmlspecialchars($result->coverImage) : '' ?>"
                     alt="Cover Preview">
                <?php if (!$isOnDeal): ?>
                    <button type="button" id="removeImage">
                        <i class="ph-bold ph-x"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isOnDeal): ?>
            <button type="button" class="btn-submit" disabled style="opacity: 0.5; cursor: not-allowed;">
                Update Locked (On Daily Deal)
            </button>
        <?php else: ?>
            <button type="submit" class="btn-submit">
                Publish Book
            </button>
        <?php endif; ?>
    </form>

</div>

</div>

</div>
<script src="/assests/js/admin.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const coverInput = document.querySelector('.cover_image');
    const previewBox = document.getElementById('previewBox');
    const imagePreview = document.getElementById('imagePreview');
    const removeBtn = document.getElementById('removeImage');

    if (coverInput) {
        coverInput.addEventListener('change', () => {
            const file = coverInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    previewBox.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                previewBox.style.display = 'none';
                imagePreview.src = '';
            }
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', () => {
            coverInput.value = '';
            imagePreview.src = '';
            previewBox.style.display = 'none';
        });
    }
});
</script>

</body>
</html>
