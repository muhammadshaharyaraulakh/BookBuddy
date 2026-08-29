<?php
require __DIR__ . "/../../config/config.php";
header('Content-Type: application/json');

$response = [
    "status" => "error",
    "message" => "Unexpected Error Occurred",
    "field" => "general"
];

try {
    $name            = $_POST['book_name'] ?? '';
    $stock           = $_POST['stock'] ?? '';
    $author          = $_POST['author'] ?? '';
    $isbn            = $_POST['isbn'] ?? '';
    $category        = $_POST['category'] ?? '';
    $price           = $_POST['price'] ?? '';
    $publisher       = $_POST['publisher'] ?? '';
    $publish_date    = $_POST['publish_date'] ?? '';
    $description_1   = $_POST['description_1'] ?? '';
    $description_2   = $_POST['description_2'] ?? '';
    $image           = $_FILES['cover_image'] ?? null;

    if (empty($name) || !preg_match('/^[a-zA-Z0-9 ]+$/', $name)) {
        $response['field'] = "book_name";
        throw new Exception("Title contain letters/numbers only");
    }

    $checkBook = $connection->prepare("SELECT * FROM book WHERE title=:name LIMIT 1");
    $checkBook->execute([':name' => $name]);
    if ($checkBook->fetch(PDO::FETCH_OBJ)) {
        $response['field'] = "book_name";
        throw new Exception("Book already exists");
    }

    if ($stock === '' || $stock < 0) {
        $response['field'] = "stock";
        throw new Exception("Stock must be a non-negative number");
    }

    if (empty($author)) {
        $response['field'] = "author";
        throw new Exception("Author is required");
    }

    if (empty($isbn) || !ctype_digit($isbn) || strlen($isbn) != 13) {
        $response['field'] = "isbn";
        throw new Exception("ISBN must be a unique 13-digit number");
    }

    $checkISBN = $connection->prepare("SELECT * FROM book WHERE ISBN=:isbn LIMIT 1");
    $checkISBN->execute([':isbn' => $isbn]);
    if ($checkISBN->fetch(PDO::FETCH_OBJ)) {
        $response['field'] = "isbn";
        throw new Exception("ISBN must be unique");
    }

    if (empty($category)) {
        $response['field'] = "category";
        throw new Exception("Category is required");
    }

    if ($price === '' || $price < 0) {
        $response['field'] = "price";
        throw new Exception("Price must be non-negative");
    }

    if (empty($publisher) || !preg_match('/^[a-zA-Z0-9 ]+$/', $publisher)) {
        $response['field'] = "publisher";
        throw new Exception("Publisher is required and must contain letters/numbers only");
    }

    if (empty($publish_date)) {
        $response['field'] = "publish_date";
        throw new Exception("Publish date is required");
    }

    if (empty($description_1)) {
        $response['field'] = "description_1";
        throw new Exception("Description 1 is required ");
    }

    if (empty($description_2)) {
        $response['field'] = "description_2";
        throw new Exception("Description 2 is required ");
    }

    if (!$image || $image['error'] !== UPLOAD_ERR_OK || $image['size'] > 2000000) {
        $response['field'] = "cover_image";
        throw new Exception("Please upload an image less than 2MB.");
    }

    $allowedTypes = ['image/png', 'image/jpeg'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($image['tmp_name']);
    if (!in_array($mimeType, $allowedTypes)) {
        $response['field'] = "cover_image";
        throw new Exception("Only PNG or JPG images are allowed.");
    }

    $upload_dir = __DIR__ . "/../../images/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $image_name = time() . "_" . preg_replace('/[^a-zA-Z0-9]/', '', $name) . "." . pathinfo($image['name'], PATHINFO_EXTENSION);

    move_uploaded_file($image['tmp_name'], $upload_dir . $image_name);

    $insert = $connection->prepare("
        INSERT INTO book 
        (title, author, ISBN, publishDate, Publisher, Original_Price, Stock, description_para_1, description_para_2, coverImage, category_id)
        VALUES
        (:name, :author, :isbn, :publish_date, :publisher, :price, :stock, :description_1, :description_2, :image, :category_id)
    ");

    $insert->execute([
        ':name'          => $name,
        ':author'        => $author,
        ':isbn'          => $isbn,
        ':publish_date'  => $publish_date,
        ':publisher'     => $publisher,
        ':price'         => $price,
        ':stock'         => $stock,
        ':description_1' => $description_1,
        ':description_2' => $description_2,
        ':image'         => $image_name,
        ':category_id'   => $category
    ]);

    $response = [
        "status" => "success",
        "message" => "Book added successfully",
        "redirect" => "/admin/adminPages/book.php",
        "field" => "general"
    ];

} catch (PDOException $e) {
    $response['status']  = "error";
    $response['message'] = "Database Error: " . htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    $response['status']  = "error";
    $response['message'] = htmlspecialchars($e->getMessage());
}

echo json_encode($response);
exit;
