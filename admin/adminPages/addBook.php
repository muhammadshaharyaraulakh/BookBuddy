<?php 
require __DIR__."/../../config/config.php";
require __DIR__."/../../includes/dashboardHeader.php";
$allCategories=getCategories($connection);
?>

<div id="content">
    

   <div class="form-container">
    <form action="/admin/handlers/addBook.php" method="POST" enctype="multipart/form-data" class="ajax-form">

        <!-- Book Name & Stock -->
        <div class="form-row">
            <div class="form-group">
                <label>Book Name</label>
                <div class="input-wrapper">
                    <input type="text" class="book_name" name="book_name" placeholder="Programming C++">
                </div>
                <div class="error book_name"></div>
            </div>

            <div class="form-group">
                <label>Stock</label>
                <div class="input-wrapper">
                    <input type="number" class="stock" name="stock" placeholder="000">
                </div>
                <div class="error stock"></div>
            </div>
        </div>

        <!-- Author & ISBN -->
        <div class="form-row">
            <div class="form-group">
                <label>Author</label>
                <div class="input-wrapper">
                    <input type="text" class="author" name="author" placeholder="Shaharyar">
                </div>
                <div class="error author"></div>
            </div>

            <div class="form-group">
                <label>ISBN</label>
                <div class="input-wrapper">
                    <input type="number" class="isbn" name="isbn" placeholder="000">
                </div>
                <div class="error isbn"></div>
            </div>
        </div>

        <!-- Category & Price -->
        <div class="form-row">
            <div class="form-group">
                <label>Category</label>
                <div class="input-wrapper">
                    <select class="category" name="category">
                        <option value="" disabled selected>Select Category</option>
                        <?php foreach($allCategories as $category): ?>
                            <option value="<?= $category->id ?>"><?= $category->title ?></option>
                            <?php endforeach; ?>
                    </select>
                </div>
                <div class="error category"></div>
            </div>

            <div class="form-group">
                <label>Price ($)</label>
                <div class="input-wrapper">
                    <input type="number" class="price" name="price" placeholder="0.00">
                </div>
                <div class="error price"></div>
            </div>
        </div>

        <!-- Publisher & Publish Date -->
        <div class="form-row">
            <div class="form-group">
                <label>Publisher</label>
                <div class="input-wrapper">
                    <input type="text" class="publisher" name="publisher" placeholder="Stars Publishers">
                </div>
                <div class="error publisher"></div>
            </div>

            <div class="form-group">
                <label>Date of Publish</label>
                <div class="input-wrapper">
                    <input type="date" class="publish_date" name="publish_date">
                </div>
                <div class="error publish_date"></div>
            </div>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label>Description</label>
            <div class="input-wrapper">
                <input type="text" class="description_1" name="description_1" placeholder="Book Details">
            </div>
            <div class="error description_1"></div>
        </div>

        <div class="form-group">
            <div class="input-wrapper">
                <input type="text" class="description_2" name="description_2" placeholder="Book Details">
            </div>
            <div class="error description_2"></div>
        </div>

        <!-- Upload Cover -->
        <div class="form-group">
            <label>Upload Cover</label>
            <div class="file-upload-wrapper">
                <input type="file" class="cover_image" name="cover_image">
                <div class="file-custom-label">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Click to upload image</span>
                </div>
            </div>
            <div class="error cover_image"></div>

            <div class="image-preview-box" id="previewBox" style="display: none;">
                <img id="imagePreview" src="" alt="Cover Preview">
                <button type="button" id="removeImage">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-submit">
            Publish Book
        </button>
    </form>
</div>

</div>

</div> <script src="/assests/js/admin.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const coverInput = document.querySelector('.cover_image');
    const previewBox = document.getElementById('previewBox');
    const imagePreview = document.getElementById('imagePreview');
    const removeBtn = document.getElementById('removeImage');

    // When a file is selected
    coverInput.addEventListener('change', () => {
        const file = coverInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result; // Set preview image src
                previewBox.style.display = 'block';  // Show preview box
            }
            reader.readAsDataURL(file); // Read file as data URL
        } else {
            previewBox.style.display = 'none';
            imagePreview.src = '';
        }
    });

    // Remove the selected image
    removeBtn.addEventListener('click', () => {
        coverInput.value = '';            // Clear input
        imagePreview.src = '';            // Remove preview
        previewBox.style.display = 'none'; // Hide preview box
    });
});
</script>

</body>
</html>








