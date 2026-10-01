<?php
// ============================================================
// SEND EMAIL USING PHPMailer (NO XAMPP CONFIG NEEDED)
// ============================================================

// Include PHPMailer files
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'alisonnaftali2003@gmail.com'; // BADILISHA
        $mail->Password   = 'YOUR_APP_PASSWORD'; // BADILISHA
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('alisonnaftali2003@gmail.com', 'Quick Bite');
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
        return false;
    }
}

// ============================================================
// TEST EMAIL
// ============================================================
$to = "alisonnaftali2003@gmail.com";
$subject = "TEST: Quick Bite Email";
$message = "
<html>
<head><title>Test Email</title></head>
<body>
    <h2>✅ Quick Bite Email Test</h2>
    <p>This email was sent using PHPMailer!</p>
    <p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>
    <hr>
    <p>Quick Bite Food Delivery System</p>
</body>
</html>
";

$result = sendEmail($to, $subject, $message);

if ($result) {
    echo "✅ Email sent successfully to $to";
} else {
    echo "❌ Email failed to send. Check error log.";
}
?>