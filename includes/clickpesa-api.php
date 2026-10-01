<?php
require_once __DIR__ . '/../config/clickpesa.php';

// ============================================================
// CLICKPESA API CLASS
// ============================================================

class ClickPesaAPI {
    
    private $clientId;
    private $apiKey;
    private $checksumKey;
    
    public function __construct() {
        $this->clientId = CLICKPESA_CLIENT_ID;
        $this->apiKey = CLICKPESA_API_KEY;
        $this->checksumKey = CLICKPESA_CHECKSUM_KEY;
    }
    
    // ============================================================
    // 1. GET ACCESS TOKEN (Using Headers)
    // ============================================================
    public function getAccessToken() {
        // Check session first
        if (isset($_SESSION['clickpesa_token']) && 
            isset($_SESSION['clickpesa_token_expiry']) && 
            $_SESSION['clickpesa_token_expiry'] > time()) {
            return $_SESSION['clickpesa_token'];
        }
        
        // Request new token using HEADERS
        $ch = curl_init(CLICKPESA_TOKEN_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'client-id: ' . $this->clientId,
            'api-key: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            $data = json_decode($response, true);
            if (isset($data['token'])) {
                $_SESSION['clickpesa_token'] = $data['token'];
                $_SESSION['clickpesa_token_expiry'] = time() + 3500;
                return $data['token'];
            }
        }
        
        return null;
    }
    
    // ============================================================
    // 2. CREATE CHECKSUM (HMAC-SHA256)
    // ============================================================
    public function createChecksum($payload) {
        ksort($payload);
        $payloadString = json_encode($payload, JSON_UNESCAPED_SLASHES);
        return hash_hmac('sha256', $payloadString, $this->checksumKey);
    }
    
    // ============================================================
    // 3. INITIATE PAYMENT (USSD Push)
    // ============================================================
    public function initiatePayment($amount, $phone, $orderReference) {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return ['success' => false, 'message' => 'Authentication failed'];
        }
        
        $payload = [
            'amount' => (string)$amount,
            'currency' => CLICKPESA_CURRENCY,
            'orderReference' => $orderReference,
            'phoneNumber' => $this->formatPhone($phone)
        ];
        
        $payload['checksum'] = $this->createChecksum($payload);
        
        $ch = curl_init(CLICKPESA_PAYMENT_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => ($httpCode == 200 || $httpCode == 201),
            'http_code' => $httpCode,
            'data' => json_decode($response, true),
            'raw_response' => $response
        ];
    }
    
    // ============================================================
    // 4. QUERY PAYMENT STATUS
    // ============================================================
    public function queryPaymentStatus($orderReference) {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return ['success' => false, 'message' => 'Authentication failed'];
        }
        
        $url = CLICKPESA_STATUS_URL . '?orderReference=' . urlencode($orderReference);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $token,
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => ($httpCode == 200),
            'data' => json_decode($response, true)
        ];
    }
    
    // ============================================================
    // 5. FORMAT PHONE NUMBER (255XXXXXXXXX)
    // ============================================================
    private function formatPhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (substr($phone, 0, 1) === '0') {
            $phone = '255' . substr($phone, 1);
        }
        
        if (substr($phone, 0, 3) !== '255') {
            $phone = '255' . $phone;
        }
        
        return $phone;
    }
}

// Helper function
function initiateClickPesaPayment($amount, $phone, $orderReference) {
    $api = new ClickPesaAPI();
    return $api->initiatePayment($amount, $phone, $orderReference);
}
?>