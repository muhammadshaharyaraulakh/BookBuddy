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

function sendOtpEmail(string $toEmail, string $toName, string $otp): bool 
{
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email: $toEmail");
        return false;
    }

    $safeName = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');

    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;   
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 2;

        $mail->setFrom(SMTP_USER, 'BookBuddy');
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Verification Code';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 25px; border: 1px solid #e0e0e0; border-radius: 10px; background-color: #ffffff;'>
                <h2 style='color: #6c5dd4; text-align: center; margin-bottom: 20px;'>BookBuddy</h2>
                <p style='color: #333; font-size: 15px;'>Hello <strong>{$safeName}</strong>,</p>
                <p style='color: #555; font-size: 14px; line-height: 1.5;'>You requested to reset your password. Use the 6 digit verification code below to proceed:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <span style='font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #6c5dd4; background: #f2f0fe; padding: 14px 28px; border-radius: 8px; display: inline-block; border: 1px dashed #6c5dd4;'>{$otp}</span>
                </div>
                <p style='color: #777; font-size: 13px;'>This code will expire in 5 minutes. If you did not request this password reset, please ignore this email.</p>
                <hr style='border: none; border-top: 1px solid #eeeeee; margin: 25px 0;'>
                <p style='color: #aaa; font-size: 11px; text-align: center;'>BookBuddy Online Bookstore</p>
            </div>
        ";
        $mail->AltBody = "Hello {$safeName}, your verification code is {$otp}. It will expire in 5 minutes.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('[Mailer OTP] Failed to send to ' . $toEmail . ': ' . $e->getMessage());
        return false;
    }
}
