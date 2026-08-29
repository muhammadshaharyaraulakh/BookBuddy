<?php
require __DIR__ . "/../../config/config.php";
header('Content-Type: application/json');

$response = [
    "status" => "error",
    "message" => "Unexpected Error Occurred",
    "field" => "general"
];
try {
    $id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
    $check=$connection->prepare("SELECT * FROM book WHERE id=:id");
    $check->execute([
        ':id'=>$id
    ]);
    $result=$check->fetch(PDO::FETCH_OBJ);
    if(empty($result)){
        throw new Exception("Book doesnot exists"); 
    }
    $image=$result->coverImage;
    if ($image) {
        $image_path = __DIR__ . "/../../images/" . $image;
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    $delete=$connection->prepare("DELETE  FROM book WHERE id=:id");
    $delete->execute([
        ':id'=>$id
    ]);
    $response = [
        "status" => "success",
        "message" => "Book deleted successfully",
        "field" => "general"
    ];
}catch (PDOException $e) {
    $response['status']  = "error";
    $response['message'] = "Database Error: " . htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    $response['status']  = "error";
    $response['message'] = htmlspecialchars($e->getMessage());
}
echo json_encode($response);
exit;
?>