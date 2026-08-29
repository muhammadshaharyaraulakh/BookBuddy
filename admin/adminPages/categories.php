<?php 
require __DIR__."/../../config/config.php";
require __DIR__."/../../includes/dashboardHeader.php";
$allCategories=getCategories($connection);
?>

<div id="content">
    

    <div class="form-container add-cat-section">
        <h4 class="form-title">Add New Category</h4>
        <form class="inline-form ajax-form" action="/admin/handlers/addCategories.php" method="post">
            <div class="input-wrapper">
                <input type="text" name="category" placeholder="Enter Category Name">
                <div class="error title"></div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add
            </button>
        </form>
    </div>

    <div class="list-panel">
        <div class="panel-header">
            <h4>Existing Categories</h4>
        </div>
        
        <div class="category-list">
            <?php foreach ($allCategories as $category): ?>
    <div class="category-item" id="cat-item-<?= $category->id ?>">
        <div class="cat-content">
            <span class="cat-name"><?= htmlspecialchars($category->title) ?></span>
            <input type="text" class="edit-cat-input" value="<?= htmlspecialchars($category->title) ?>" style="display:none; height: 35px; border-radius: 5px; border: 1px solid #ccc; padding: 5px 10px;">
            <span class="badge warning"><?= getCount($connection, $category->id) ?> Books</span>
        </div>
        <div class="action-buttons">
            <button class="btn btn-primary edit-category-btn" data-id="<?= $category->id ?>">Edit</button>
            <button class="btn btn-success save-category-btn" data-id="<?= $category->id ?>" style="display:none; background: #2ecc71; color: white;">Save</button>
            <form action="/admin/handlers/deleteCategory.php" method="post" class="bookform ajax-form" style="display:inline-block;">
                <input type="hidden" name="id" value="<?= $category->id ?>">
                <button class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>

        </div>
    </div>

</div> </div> <script src="/assests/js/admin.js"></script>
</body>
</html>