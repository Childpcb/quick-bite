<?php
session_start();
require_once __DIR__ . '/config/clickpesa.php';

echo "<h1>ClickPesa API Debug (Fixed)</h1>";
echo "<hr>";

echo "<h2>1. Check cURL</h2>";
if (function_exists('curl_init')) {
    echo "<p style='color: green;'>✅ cURL is enabled</p>";
} else {
    echo "<p style='color: red;'>❌ cURL is NOT enabled. Enable it in php.ini</p>";
    exit();
}

echo "<h2>2. Credentials</h2>";
echo "<p><strong>Client ID:</strong> " . CLICKPESA_CLIENT_ID . "</p>";
echo "<p><strong>API Key:</strong> " . substr(CLICKPESA_API_KEY, 0, 20) . "...</p>";
echo "<p><strong>Token URL:</strong> " . CLICKPESA_TOKEN_URL . "</p>";

echo "<hr>";

echo "<h2>3. API Call Test (Using Headers)</h2>";

$ch = curl_init(CLICKPESA_TOKEN_URL);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, '');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'client-id: ' . CLICKPESA_CLIENT_ID,
    'api-key: ' . CLICKPESA_API_KEY,
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> " . $httpCode . "</p>";
echo "<p><strong>cURL Error:</strong> " . ($curlError ? $curlError : 'None') . "</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

$data = json_decode($response, true);
if ($httpCode == 200 && isset($data['token'])) {
    echo "<p style='color: green;'>✅ Token obtained!</p>";
    echo "<p><strong>Token:</strong> " . substr($data['token'], 0, 50) . "...</p>";
    
    $_SESSION['clickpesa_token'] = $data['token'];
    $_SESSION['clickpesa_token_expiry'] = time() + 3500;
} else {
    echo "<p style='color: red;'>❌ Failed to get token</p>";
    echo "<p><strong>Parsed:</strong></p>";
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}
?>