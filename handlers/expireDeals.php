<?php
// handlers/expireDeals.php

// ─── SECURITY CHECK 1: Direct browser access band karo ───
if (php_sapi_name() !== 'cli' && !defined('INTERNAL_CALL')) {
    http_response_code(403);
    die('Access Denied');
}

// ─── SECURITY CHECK 2: Script dobara chale to rok do ─────
$lockFile = __DIR__ . '/../storage/expire.lock';
if (file_exists($lockFile)) {
    $lockAge = time() - filemtime($lockFile);
    if ($lockAge < 300) { // 5 minute se kam purana hai
        exit; // Exit silently if already running
    }
}
file_put_contents($lockFile, time()); // Lock create karo

try {
    // Already included config.php so connection exists if INTERNAL_CALL
    if (!isset($connection)) {
        require_once __DIR__ . '/../config/config.php';
    }
    require_once __DIR__ . '/../function/mailer.php';

    // ─── STEP 1: Atomic lock — sirf ek process handle kare ───
    $token = bin2hex(random_bytes(16));

    $stmt = $connection->prepare("
        UPDATE deals 
        SET status = 'processing', lock_token = ?
        WHERE end_time < NOW() 
        AND status = 'active'
    ");
    $stmt->execute([$token]);

    if ($stmt->rowCount() === 0) {
        // Koi expired deal nahi mili
        unlink($lockFile);
        exit;
    }

    // ─── STEP 2: Sirf apna token wali deals lo ────────────
    $stmt = $connection->prepare("
        SELECT * FROM deals 
        WHERE status = 'processing' 
        AND lock_token = ?
    ");
    $stmt->execute([$token]);
    $expiredDeals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($expiredDeals as $deal) {

        // ─── STEP 3: Is deal ki cart items wale users lo ──
        $stmt = $connection->prepare("
            SELECT c.user_id, u.email, u.fullname, b.title
            FROM cart c
            JOIN user u ON c.user_id = u.id
            JOIN book b ON c.book_id = b.id
            WHERE c.book_id = ?
        ");
        $stmt->execute([$deal['book_id']]);
        $affectedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ─── STEP 4: Cart se remove karo ──────────────────
        $stmt = $connection->prepare("
            DELETE FROM cart WHERE book_id = ?
        ");
        $stmt->execute([$deal['book_id']]);

        // ─── STEP 5: Book discount reset karo ─────────────
        $stmt = $connection->prepare("
            UPDATE book SET Discount_Percentage = NULL WHERE id = ?
        ");
        $stmt->execute([$deal['book_id']]);

        // ─── STEP 6: Deal expired mark karo ───────────────
        $stmt = $connection->prepare("
            UPDATE deals 
            SET status = 'expired', processed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$deal['id']]);

        // ─── STEP 7: Emails bhejo ─────────────────────────
        foreach ($affectedUsers as $user) {
            sendDealExpiredEmail(
                $user['email'],
                $user['fullname'],
                $user['title']
            );
        }
    }

} catch (Exception $e) {
    // Error log karo, user ko mat dikhao
    error_log('[BookBuddy] Deal expiry error: ' . $e->getMessage());
} finally {
    // Lock hamesha remove karo chahe error ho ya na ho
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
}
