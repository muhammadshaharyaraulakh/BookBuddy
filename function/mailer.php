<?php
// function/mailer.php
// Production Email API via Resend (https://resend.com) with PHPMailer SMTP Fallback

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send email using Resend REST API (https://resend.com)
 * Uses standard HTTPS (port 443) — no SMTP port blocking on Railway/cloud.
 */
if (!function_exists('sendEmailViaResend')) {
    function sendEmailViaResend(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool 
    {
        $apiKey = defined('RESEND_API_KEY') && !empty(RESEND_API_KEY) ? RESEND_API_KEY : getenv('RESEND_API_KEY');
        if (empty($apiKey)) {
            error_log("[Resend Mailer] Missing RESEND_API_KEY");
            return false;
        }

        $fromEmail = defined('RESEND_FROM_EMAIL') && !empty(RESEND_FROM_EMAIL) 
            ? RESEND_FROM_EMAIL 
            : (getenv('RESEND_FROM_EMAIL') ?: 'BookBuddy <onboarding@resend.dev>');

        $payload = [
            'from'    => $fromEmail,
            'to'      => [!empty($toName) ? "{$toName} <{$toEmail}>" : $toEmail],
            'subject' => $subject,
            'html'    => $htmlBody,
        ];
        if (!empty($textBody)) {
            $payload['text'] = $textBody;
        }

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            error_log("[Resend Mailer] cURL Error: " . $curlErr);
            return false;
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        error_log("[Resend Mailer] HTTP {$httpCode} Error: " . $response);
        return false;
    }
}

/**
 * Send email using local SMTP via PHPMailer (Fallback for local development)
 */
if (!function_exists('sendEmailViaPhpMailer')) {
    function sendEmailViaPhpMailer(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool 
    {
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            error_log("[PHPMailer] Library not loaded");
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = defined('SMTP_USER') ? SMTP_USER : '';
            $mail->Password   = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
            $mail->SMTPSecure = defined('SMTP_SECURE') && SMTP_SECURE === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;
            $mail->Timeout    = 5;

            $fromUser = defined('SMTP_USER') ? SMTP_USER : 'noreply@bookbuddy.com';
            $mail->setFrom($fromUser, 'BookBuddy');
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = !empty($textBody) ? $textBody : strip_tags($htmlBody);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('[PHPMailer] Failed to send to ' . $toEmail . ': ' . $e->getMessage());
            return false;
        }
    }
}

/**
 * Unified email sender: Dispatches via Resend API if API Key configured; falls back to PHPMailer
 */
if (!function_exists('dispatchBookBuddyEmail')) {
    function dispatchBookBuddyEmail(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool 
    {
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            error_log("[Mailer] Invalid recipient email: {$toEmail}");
            return false;
        }

        $resendApiKey = defined('RESEND_API_KEY') && !empty(RESEND_API_KEY) ? RESEND_API_KEY : getenv('RESEND_API_KEY');
        if (!empty($resendApiKey)) {
            return sendEmailViaResend($toEmail, $toName, $subject, $htmlBody, $textBody);
        }

        return sendEmailViaPhpMailer($toEmail, $toName, $subject, $htmlBody, $textBody);
    }
}

if (!function_exists('sendDealExpiredEmail')) {
    function sendDealExpiredEmail(string $toEmail, string $toName, string $bookTitle): bool 
    {
        $safeName      = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
        $safeBookTitle = htmlspecialchars($bookTitle, ENT_QUOTES, 'UTF-8');

        $subject  = 'Deal Expired — Item Removed from Cart';
        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 25px; border: 2.5px solid #0a0a0a; border-radius: 8px; background: #ffffff; box-shadow: 4px 4px 0 #0a0a0a;'>
                <h2 style='color: #0a0a0a; margin-top: 0;'>BookBuddy</h2>
                <p>Hi <strong>{$safeName}</strong>,</p>
                <p>The 24-hour flash deal for <strong>{$safeBookTitle}</strong> has expired and the item was removed from your active cart.</p>
                <p>You can browse our current catalog and active deals anytime on our website.</p>
                <hr style='border: none; border-top: 2px solid #0a0a0a; margin: 20px 0;'>
                <p style='color: #666; font-size: 12px;'>— BookBuddy Bookstore Team</p>
            </div>
        ";
        $textBody = "Hi {$safeName}, the deal for {$safeBookTitle} has expired and was removed from your cart. Browse our store for current deals.";

        return dispatchBookBuddyEmail($toEmail, $toName, $subject, $htmlBody, $textBody);
    }
}

if (!function_exists('sendOtpEmail')) {
    function sendOtpEmail(string $toEmail, string $toName, string $otp): bool 
    {
        $safeName = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');

        $subject  = 'Password Reset Verification Code';
        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 25px; border: 2.5px solid #0a0a0a; border-radius: 8px; background-color: #ffffff; box-shadow: 4px 4px 0 #0a0a0a;'>
                <h2 style='color: #0a0a0a; text-align: center; margin-bottom: 20px;'>BookBuddy</h2>
                <p style='color: #333; font-size: 15px;'>Hello <strong>{$safeName}</strong>,</p>
                <p style='color: #555; font-size: 14px; line-height: 1.5;'>You requested to reset your password. Use the 6-digit verification code below to proceed:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <span style='font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #0a0a0a; background: #FFDE59; padding: 14px 28px; border-radius: 6px; display: inline-block; border: 2.5px solid #0a0a0a; box-shadow: 3px 3px 0 #0a0a0a;'>{$otp}</span>
                </div>
                <p style='color: #777; font-size: 13px;'>This code will expire in 5 minutes. If you did not request this password reset, please ignore this email.</p>
                <hr style='border: none; border-top: 2px solid #0a0a0a; margin: 25px 0;'>
                <p style='color: #888; font-size: 11px; text-align: center; font-weight: 600;'>BookBuddy Online Bookstore</p>
            </div>
        ";
        $textBody = "Hello {$safeName}, your verification code is {$otp}. It will expire in 5 minutes.";

        return dispatchBookBuddyEmail($toEmail, $toName, $subject, $htmlBody, $textBody);
    }
}
