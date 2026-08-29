<?php
// function/mailer.php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendDealExpiredEmail(string $toEmail, string $toName, string $bookTitle): bool 
{
    // ─── INPUT VALIDATION ──────────────────────────────────
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email: $toEmail");
        return false;
    }

    // ─── SANITIZE (XSS prevention in email body) ──────────
    $safeName      = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
    $safeBookTitle = htmlspecialchars($bookTitle, ENT_QUOTES, 'UTF-8');

    try {
        $mail = new PHPMailer(true);

        // ─── SMTP CONFIG (credentials config se lo) ───────
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;   
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // ─── EMAIL CONTENT ─────────────────────────────────
        $mail->setFrom(SMTP_USER, 'BookBuddy');
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = 'Deal Expired — Item Removed from Cart';
        $mail->Body    = "
            <p>Hi {$safeName},</p>
            <p>The 24-hour deal for <strong>{$safeBookTitle}</strong> 
            has expired and has been removed from your cart.</p>
            <p>Browse current deals at our website.</p>
            <p>— BookBuddy Team</p>
        ";
        $mail->AltBody = "Hi {$safeName}, the deal for {$safeBookTitle} has expired and was removed from your cart.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        // Log karo lekin crash mat karo
        error_log('[Mailer] Failed to send to ' . $toEmail . ': ' . $e->getMessage());
        return false;
    }
}
