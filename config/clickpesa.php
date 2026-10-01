<?php
// ============================================================
// CLICKPESA PAYMENT GATEWAY CONFIGURATION
// ============================================================

// API Credentials (from ClickPesa Dashboard)
define('CLICKPESA_CLIENT_ID', 'IDXWp0K05IYhQvaBE1X6AKVqV1UGPYl2');
define('CLICKPESA_API_KEY', 'SKQnwmdgYfxjMnh2y9BDMh5DqocqvhntXOOkt1zJmo');
define('CLICKPESA_CHECKSUM_KEY', 'CHK6wUQZOvyTyrRcCGTnObz4O1FGWIB7hyZr');

// API Endpoints
define('CLICKPESA_TOKEN_URL', 'https://api.clickpesa.com/third-parties/generate-token');
define('CLICKPESA_PAYMENT_URL', 'https://api.clickpesa.com/third-parties/payments/initiate-ussd-push-request');
define('CLICKPESA_PREVIEW_URL', 'https://api.clickpesa.com/third-parties/payments/preview-ussd-push-request');
define('CLICKPESA_STATUS_URL', 'https://api.clickpesa.com/third-parties/payments/query-payment-status');

// Currency
define('CLICKPESA_CURRENCY', 'TZS');

// Webhook URL
define('CLICKPESA_WEBHOOK_URL', 'https://aqueduct-thing-imply.ngrok-free.dev/quick-bite/payment-webhook.php');
?>