<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../config/config.php";

header('Content-Type: application/json');

$response = [
    "status"  => "error",
    "message" => "Unexpected error occurred",
    "field"   => "general"
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = "Invalid request method";
    echo json_encode($response);
    exit;
}

try {
    // Section 1: Authentication Verification
    if (empty($_SESSION['id'])) {
        $response['auth_required'] = true;
        throw new Exception("Please log in to manage your addresses.");
    }

    $user_id = (int)$_SESSION['id'];

    // Section 2: Input Sanitization
    $address_id = filter_var($_POST['address_id'] ?? '', FILTER_VALIDATE_INT);
    $province   = trim($_POST['province'] ?? '');
    $district   = trim($_POST['district'] ?? '');
    $city       = trim($_POST['city'] ?? '');
    $postcode   = trim($_POST['postcode'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $contact    = trim($_POST['contact'] ?? '');

    // Section 3: Validation Rules
    if ($province === '') {
        $response['field'] = 'province';
        throw new Exception("Please select a province.");
    }

    if ($district === '') {
        $response['field'] = 'district';
        throw new Exception("Please enter your district.");
    }

    if ($city === '') {
        $response['field'] = 'city';
        throw new Exception("Please enter your city.");
    }

    if ($address === '') {
        $response['field'] = 'address';
        throw new Exception("Permanent delivery address is required.");
    }

    // Postal code: exactly 5 digits
    if (!preg_match('/^[0-9]{5}$/', $postcode)) {
        $response['field'] = 'postcode';
        throw new Exception("Postal code must be exactly 5 digits (e.g. 54000).");
    }

    // Contact number: exactly 11 digits
    if (!preg_match('/^[0-9]{11}$/', $contact)) {
        $response['field'] = 'contact';
        throw new Exception("Contact number must be exactly 11 digits (e.g. 03001234567).");
    }

    // Ensure database column 'district' exists in user_address
    try {
        $colCheck = $connection->query("SHOW COLUMNS FROM user_address LIKE 'district'");
        if ($colCheck->rowCount() === 0) {
            $connection->exec("ALTER TABLE user_address ADD COLUMN district VARCHAR(100) DEFAULT NULL AFTER province");
        }
    } catch (Exception $e) {
        throw new Exception("Error while updating address.");
    }

    // Section 4: Insert or Update Address
    if ($address_id && $address_id > 0) {
        // Ownership verification
        $checkStmt = $connection->prepare("SELECT id FROM user_address WHERE id = :aid AND user_id = :uid LIMIT 1");
        $checkStmt->execute([':aid' => $address_id, ':uid' => $user_id]);
        if (!$checkStmt->fetch()) {
            throw new Exception("Address not found or unauthorized.");
        }

        $updateStmt = $connection->prepare("
            UPDATE user_address 
            SET province = :province, district = :district, city = :city, postcode = :postcode, address = :address, contact = :contact 
            WHERE id = :aid AND user_id = :uid
        ");
        $updateStmt->execute([
            ':province' => $province,
            ':district' => $district,
            ':city'     => $city,
            ':postcode' => $postcode,
            ':address'  => $address,
            ':contact'  => $contact,
            ':aid'      => $address_id,
            ':uid'      => $user_id
        ]);

        $finalId = $address_id;

        // Check if this address was locked to active orders
        $orderCheck = $connection->prepare("
            SELECT COUNT(*) 
            FROM orders 
            WHERE shipping_address_id = :aid AND order_status IN ('processing', 'shipped')
        ");
        $orderCheck->execute([':aid' => $address_id]);
        $hasActiveOrders = ((int)$orderCheck->fetchColumn() > 0);

        $successMsg = $hasActiveOrders
            ? "Address updated successfully! Note: Previously placed active orders will be delivered to the address confirmed at order time."
            : "Address updated successfully.";

    } else {
        // Insert new address
        $insertStmt = $connection->prepare("
            INSERT INTO user_address (user_id, province, district, city, postcode, address, contact) 
            VALUES (:uid, :province, :district, :city, :postcode, :address, :contact)
        ");
        $insertStmt->execute([
            ':uid'      => $user_id,
            ':province' => $province,
            ':district' => $district,
            ':city'     => $city,
            ':postcode' => $postcode,
            ':address'  => $address,
            ':contact'  => $contact
        ]);
        $finalId = (int)$connection->lastInsertId();
        $successMsg = "Address added successfully.";
    }

    $response = [
        "status"     => "success",
        "message"    => $successMsg,
        "address_id" => $finalId,
        "address"    => [
            "id"       => $finalId,
            "province" => $province,
            "district" => $district,
            "city"     => $city,
            "postcode" => $postcode,
            "address"  => $address,
            "contact"  => $contact
        ]
    ];

} catch (Exception $e) {
    $response['status']  = "error";
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
