<?php
require __DIR__ . "/../../config/config.php";
header('Content-Type: application/json');

$response = [
    "status" => "error",
    "message" => "Unexpected Error Occurred",
    "field" => "general"
];

try {

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if(!$id){
        throw new Exception("Invalid book");
    }

    $fetch = $connection->prepare("SELECT * FROM book WHERE id=:id LIMIT 1");
    $fetch->execute([':id' => $id]);
    $result = $fetch->fetch(PDO::FETCH_OBJ);

    if(!$result){
        throw new Exception("Book not found");
    }

    $checkDeal = $connection->prepare("
        SELECT id FROM deals 
        WHERE book_id = :id 
          AND status = 'active' 
          AND CURRENT_TIMESTAMP < end_time 
        LIMIT 1
    ");
    $checkDeal->execute([':id' => $id]);
    if ($checkDeal->fetch()) {
        throw new Exception("This book cannot be updated while it is active in the daily deal.");
    }

    $name            = trim($_POST['book_name'] ?? '');
    $stock           = $_POST['stock'] ?? '';
    $author          = trim($_POST['author'] ?? '');
    $isbn            = trim($_POST['isbn'] ?? '');
    $category        = $_POST['category'] ?? '';
    $price           = $_POST['price'] ?? '';
    $publisher       = trim($_POST['publisher'] ?? '');
    $publish_date    = $_POST['publish_date'] ?? '';
    $description_1   = trim($_POST['description_1'] ?? '');
    $description_2   = trim($_POST['description_2'] ?? '');
    $imageFile       = $_FILES['cover_image'] ?? null;

    if (empty($name)) {
        $response['field'] = "book_name";
        throw new Exception("Title is required");
    }

    if ($stock === '' || $stock < 0) {
        $response['field'] = "stock";
        throw new Exception("Stock must be a non-negative number");
    }

    if (empty($author)) {
        $response['field'] = "author";
        throw new Exception("Author is required");
    }

    if (!ctype_digit($isbn) || strlen($isbn) != 13) {
        $response['field'] = "isbn";
        throw new Exception("ISBN must be a 13-digit number");
    }

    $checkISBN = $connection->prepare("
        SELECT id FROM book WHERE ISBN=:isbn AND id!=:id LIMIT 1
    ");
    $checkISBN->execute([':isbn'=>$isbn, ':id'=>$id]);
    if($checkISBN->fetch()){
        $response['field'] = "isbn";
        throw new Exception("ISBN already exists");
    }

    if (empty($category)) {
        $response['field'] = "category";
        throw new Exception("Category required");
    }

    if ($price === '' || $price < 0) {
        $response['field'] = "price";
        throw new Exception("Price must be non-negative");
    }

    if (empty($publish_date)) {
        $response['field'] = "publish_date";
        throw new Exception("Publish date required");
    }

    if (empty($description_1)) {
        $response['field'] = "description_1";
        throw new Exception("Description 1 required");
    }

    $newImageName = $result->coverImage;  

    if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {

        if($imageFile['size'] > 2000000){
            $response['field'] = "cover_image";
            throw new Exception("Image must be of 2MB");
        }

        $allowed = ['image/jpeg','image/png'];
        $f = new finfo(FILEINFO_MIME_TYPE);
        if(!in_array($f->file($imageFile['tmp_name']), $allowed)){
            $response['field'] = "cover_image";
            throw new Exception("Only JPG/PNG allowed");
        }

        $upload_dir = __DIR__ . "/../../images/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $ext = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
        $newImageName = time()."_".preg_replace('/[^a-zA-Z0-9]/','',$name).".".$ext;

        move_uploaded_file($imageFile['tmp_name'], $upload_dir.$newImageName);

        if($result->coverImage && file_exists($upload_dir.$result->coverImage)){
            unlink($upload_dir.$result->coverImage);
        }
    }

    $update = $connection->prepare("
        UPDATE book SET
            title               = :name,
            author              = :author,
            ISBN                = :isbn,
            publishDate         = :publish_date,
            Publisher           = :publisher,
            Original_Price      = :price,
            Stock               = :stock,
            description_para_1  = :description_1,
            description_para_2  = :description_2,
            coverImage          = :image,
            category_id         = :category
        WHERE id = :id
    ");

    $update->execute([
        ':name'          => $name,
        ':author'        => $author,
        ':isbn'          => $isbn,
        ':publish_date'  => $publish_date,
        ':publisher'     => $publisher,
        ':price'         => $price,
        ':stock'         => $stock,
        ':description_1' => $description_1,
        ':description_2' => $description_2,
        ':image'         => $newImageName,
        ':category'      => $category,
        ':id'            => $id
    ]);

    $response = [
        "status"  => "success",
        "message" => "Book updated successfully",
        "redirect" => "/books"
    ];

} catch (PDOException $e) {
    $response['message'] = "DB Error: ".htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    $response['message'] = htmlspecialchars($e->getMessage());
}

echo json_encode($response);
exit;
