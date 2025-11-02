<?php
require __DIR__ . '/../vendor/autoload.php'; 

use Dotenv\Dotenv;

// The createImmutable() function defines where the .env file you want to load is located
// __DIR__ in PHP returns the directory of the PHP file containing __DIR__, in this case, config.php
$dotenv = Dotenv::createImmutable(__DIR__); // If the .env file is in the parent folder, use __DIR__ . '/..'
$dotenv->load();

// Function to get the API Key    
function getEasypayApiKey() {
    return getenv('EASYPAY_ENV') === 'prod'
        ? getenv('EASYPAY_API_KEY_PROD')
        : getenv('EASYPAY_API_KEY_SANDBOX');
}

// Function to get the endpoint URL
function getEasypayUrl($endpoint = 'payment') {
    $base = getenv('EASYPAY_ENV') === 'prod'
        ? 'https://api.easypay.pt/2.0/'
        : 'https://api.test.easypay.pt/2.0/';
    return $base . $endpoint; // . concatenates strings, here it appends the desired endpoint
}

// Function to create a PDO database connection using .env
// PDO (PHP Data Objects) is a PHP interface for working with databases safely and consistently
function getDbConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8mb4", 
            getenv('DB_USER'), 
            getenv('DB_PASS'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
            // In new PDO($dsn, $user, $pass, $options);
            // $options is an associative array controlling connection behavior such as error handling and result formatting
            // PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION - Throws an exception (PDOException) on error (e.g., connection failure, invalid query)
            // PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC - Returns query results as associative arrays only, accessible by column name
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("EASYPAY database connection failed: " . $e->getMessage()); // Logs the captured error
        http_response_code(500);
        exit("Database error");
    }
}

// Function to create a payment
function createPayment($name, $email, $amount, $method) {
    $methodMap = [
        "card" => "cc",
        "mbway" => "mbw",
        "multibanco" => "mb"
    ];
    $method = $methodMap[$method] ?? "cc";
    $orderId = uniqid("order_");
    
    $payload = [
        "amount" => $amount,
        "currency" => "EUR",
        "orderId" => $orderId,
        "customer" => [
            "name" => $name,
            "email" => $email
        ],
        "paymentMethod" => $method,
        "redirectUrl" => "https://url_do_website/checkout_success.php"
    ];

    $ch = curl_init(); // Initializes a new cURL session and returns a handle ($ch) for configuration
    curl_setopt($ch, CURLOPT_URL, getEasypayUrl('payment')); // Sets the URL to send the request
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Returns response as a string instead of printing it
    curl_setopt($ch, CURLOPT_POST, true); // Sends the request as POST, safer and invisible
    curl_setopt($ch, CURLOPT_HTTPHEADER, [ // Sets HTTP headers
        "Content-Type: application/json", // Request body is in JSON format
        "Authorization: Bearer " . getEasypayApiKey() // Sends API key as Bearer token for authentication
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload)); // Converts payload array to JSON for the request

    // Validate cURL execution:
    // - Check if cURL executed correctly
    // - Check HTTP status code of response (200, 400, 401, 500, ...)
    // - Ensure JSON response is valid
    // https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Network_Error_Logging

    $response = curl_exec($ch); // Executes the configured HTTP request
    curl_close($ch); // Closes the session to avoid memory leaks

    $data = json_decode($response, true); // Converts JSON response to PHP associative array
    // Without true → object
    // With true → associative array
    $entity = $data['method']['entity'] ?? null; // e.g., "12345" 
    $reference = $data['method']['reference'] ?? null; // null if missing

    // Save payment in the database
    savePayment($orderId, $name, $email, $amount, $method, 'pending', $entity, $reference);

    return $data;
}

// Function to save payment in the database depending on the payment method
function savePayment($orderId, $name, $email, $amount, $method, $status, $entity = null, $reference = null) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("INSERT INTO pagamentos (orderId, customerName, customerEmail, amount, paymentMethod, status, entity, reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"); 
    // $pdo->prepare() creates a prepared statement, ? are placeholders safely replaced by values
    $stmt->execute([$orderId, $name, $email, $amount, $method, $status, $entity, $reference]);
}

// Function to get payment status
function getPaymentStatus($paymentId) {
    $ch = curl_init(getEasypayUrl('payment/') . $paymentId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . getEasypayApiKey()
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Function to update payment via webhook
function updatePaymentStatus($orderId, $status) {
    $pdo = new PDO(
        "mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8mb4", 
        getenv('DB_USER'), 
        getenv('DB_PASS'),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    $stmt = $pdo->prepare("UPDATE pagamentos SET status = :status, dataConfirmacao = NOW() WHERE orderId = :orderId");
    $stmt->execute([':status' => $status, ':orderId' => $orderId]);
}
?>
