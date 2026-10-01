<?php
$to = "alisonnaftali2003@gmail.com";
$subject = "TEST: Quick Bite Email";
$message = "
<html>
<head><title>Test Email</title></head>
<body>
    <h2>✅ Test Email Working!</h2>
    <p>This is a test email from Quick Bite.</p>
    <p>Time: " . date('Y-m-d H:i:s') . "</p>
</body>
</html>
";

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type:text/html;charset=UTF-8\r\n";
$headers .= "From: Quick Bite <orders@quickbite.com>\r\n";

$result = mail($to, $subject, $message, $headers);

if ($result) {
    echo "✅ Email sent successfully to $to";
} else {
    echo "❌ Email failed to send";
}
?>