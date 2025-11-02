# Easypay Integration Guide (Sandbox to Production)
## 1. Prepare Environment
### A. Set Up Folder Structure
3. Create the files:
    /payments
    ├─ index.php # Checkout form
    ├─ checkout.php # Creates payment via API
    ├─ checkout_success.php # Success page
    ├─ checkout_fail.php # Failure page
    ├─ webhook.php # Endpoint to receive payment notifications
    ├─ config.php # Secure configurations
    ├─ .env # Private credentials, ideally placed outside /payments for security
    ├─ .htaccess # Protects .env and other folders
    
> **What is the public root?**  
> The folder on the server where files accessible via browser are stored.  
> Everything inside can be accessed via URL, e.g.: https://website.pt/index.php
 
/public_html <- public root of the website
├─ /payments
│ ├─ index.php # Checkout form
│ ├─ checkout.php # Creates payment via API
│ ├─ checkout_success.php # Success page
│ ├─ checkout_fail.php # Failure page
│ ├─ webhook.php # Secure configurations
│ ├─ config.php # Private credentials, ideally outside /payments
│ └─ .htaccess # Protects .env and other folders
├─ .env

LATER:
├─ composer.json
├─ composer.lock
└─ /vendor ← created by Composer locally   
You need to copy these to the server whenever dependencies change

**Note:** You must have Composer (a PHP dependency manager) installed locally:  
- Check with `composer --version`  
- If not installed, [download here](https://getcomposer.org/download/)


2. In the terminal, inside the /payments root folder, install **Dotenv** to use `.env`:
```bash
composer require vlucas/phpdotenv
```
This will create vendor/, composer.lock, and composer.json
Inside vendor/ will be the phpdotenv library your config.php will use

> Dotenv stores environment variables in .env files and automatically loads them into PHP
> Environment variables hold sensitive data or configuration you don't want hardcoded
> Hardcoded means the value is written directly in the code rather than loaded from an external configuration (like a .env, database, or environment variable)


3. On Windows:
- Open Notepad or a new file in VS Code
- Insert:
<Files .env>
  Order allow,deny
    Deny from all
</Files>
- Save as .htaccess, choosing "All Files" as the type

---
### B. Prepare the Server
**Upload files to the server (via FTP)**
- Install an FTP client like [filezilla](https://filezilla-project.org/)
- Your hosting provider gives credentials (Hostinger, Namecheap, OVH, etc.)

| Parameter       | Example                     | Description                                |
|------------------|-----------------------------|-------------------------------------------|
| **Host**         | `ftp.teusite.pt` or `185.123.45.67` | Server address                    |
| **Port**        | `21 (FTP)` or `22 (SFTP)`   | Communication protocol (FTP or secure SFTP) |
| **Username**   | `user@teusite.pt`           | FTP account username           |
| **Password**     | `********`                  | FTP account password                |
| **Remote path** | `/public_html/`           | Main folder where the site “lives”        |
> Podes encontrar isto no painel do teu alojamento, normalmente em: "Contas FTP" → "Gerir" → "Detalhes de acesso"

- Open FileZilla, enter credentials, click "Quickconnect"
- Drag files to the server, following the structure above

**If local:**
- XAMPP → start **Apache** and **MySQL**
**If hosted (Netlify, Namecheap, etc.):**
- Access via browser: your_server_link

> FTP = work on files
> URL = access site in browser

---
### C. Set Up Database
**Check compatibility:**
- PHP 7.4+ (recommended PHP 8.1) on local/remote server
- MySQL or MariaDB running
- cURL enabled in PHP (phpinfo() should show curl)

> The code below assumes a MySQL database called pagamentos and a pagamentos table to store order and payment status information
> Remember to [install mySQL](https://dev.mysql.com/downloads/installer)


**Start Connection**
1. Connect to MySQL server
```bash
mysql -u utilizador -p -h host_do_cliente
```
> -u → username
> -p → prompts for password
> -h → server address (localhost if you are running locally)

2. Create the database (if it doesn’t exist)
```bash
CREATE DATABASE IF NOT EXISTS payments
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE payments;
``` 

3. Create the pagamentos table
```bash
-- Use the database
USE payments;

-- Creates the table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orderId VARCHAR(50) NOT NULL UNIQUE,       -- unique order identifier
    customerName VARCHAR(100) DEFAULT NULL,    -- customer name
    customerEmail VARCHAR(100) DEFAULT NULL,   -- customer email
    amount DECIMAL(10,2) NOT NULL,            -- payment amount
    paymentMethod VARCHAR(10) NOT NULL,       -- 'mb', 'mbw', 'cc'
    status VARCHAR(20) DEFAULT 'pending',     -- 'pending', 'paid', 'failed'
    entity VARCHAR(10) DEFAULT NULL,          -- MB entity (just for Multibanco)
    reference VARCHAR(20) DEFAULT NULL,       -- MB reference (just for Multibanco)
    creationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmationDate TIMESTAMP NULL
);
```

4. Verify if the table was created
```bash
SHOW TABLES;
DESCRIBE payments;
```

> PDO (PHP Data Objects) is the standard way for PHP to securely and efficiently communicate with databases (usually MySQL, but also PostgreSQL, SQLite, etc.)

---
## 2. Create Easypay Sandbox Account - in Client bealf
1. Go to [easypay](https://www.easypay.pt)  
2. Click **"Customer Area" → "Create Account" → "Sandbox" (or "Test Account")**
3. Fill basic info (company, email, tax ID)
4. After logging into Sandbox Dashboard:
   - Go to Integrations → API Keys
   - Create a new API Key (type: test / sandbox)
   - Copy the key (e.g., sk_test_xxxxxxxxxx)

---
## 3. Update Files
**Checkout Form** - page where users enter payment details sent via POST to checkout.php
index.php
```php
// The <form> action defines where (which URL or file) the form data will be sent when the user clicks submit
// The action is the destination, and the method specifies how the data is sent (POST in this case, keeping it hidden since it is sensitive data)
<form action="checkout.php" method="POST"> 

    <label>Name:</label> 
    // <label> → descriptive text for the form field, allows screen readers to announce the label when the input is focused

    <input type="text" name="name" required> // required → makes the field mandatory before submission

    <label>Email:</label>
    <input type="email" name="email" required>
    // Could also use <label for="email">Email:</label> - Here, for="email" links the label to the input with id="email"
    // Clicking the text “Email:” automatically focuses the input

    <label>Amount (€):</label>
    <input type="number" name="amount" step="0.01" required>

    <label>Payment Method:</label>
    <select name="method"> // <select name="method"> → creates a list of payment options
        <option value="card">Card</option>
        <option value="mbway">MB WAY</option>
        <option value="multibanco">Multibanco</option>
    </select>

    <button type="submit">Pay</button> // sends the data to the defined action (checkout.php)
</form>
```


**Checkout Processing** - receives POST data from index.php, validates it, and creates the payment via EasyPay API
checkout.php
```php
<?php
require 'config.php'; // includes the config.php file in your current script, making all functions, constants, variables, and configurations available in the script where require is called

// Retrieves the values submitted from the index.php form via POST method
$name = $_POST['name'];
$email = $_POST['email'];
$amount = $_POST['amount'];
$method = $_POST['method'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // filter_var() with FILTER_VALIDATE_EMAIL is a native PHP function to validate email addresses
    exit("Invalid email");
}

if ($amount < 0) {
    exit("Invalid amount");
}

$paymentData = createPayment($name, $email, $amount, $method);

// Redirects to the EasyPay payment page
if (isset($paymentData['paymentUrl'])) { // isset() checks if the key 'paymentUrl' exists in the $paymentData array (this array is received from the EasyPay API after calling createPayment(), i.e., it checks if a valid payment URL was returned)
    header("Location: " . $paymentData['paymentUrl']); // header("Location: ...") sends an HTTP redirect header to the browser
    exit;
} else {
    header("Location: checkout_fail.php?error=" . urlencode($paymentData['error'] ?? 'Unknown error'));
    // Redirects to checkout_fail.php
    // ?error=... passes an error message as a GET query parameter to the destination page, making it available in the PHP global $_GET variable and visible in the URL
    // urlencode() ensures the error message is safe to include in the URL
    // $paymentData['error'] ?? 'Unknown error' means: if $paymentData['error'] exists, use it; otherwise, use the string 'Unknown error'
    exit;
}
?>
```
> Here we assume that the EasyPay API response includes a `paymentUrl` where the user will make the payment
> Check the official API documentation to verify the exact field name, if it exists (in EasyPay’s case, it does)


**Sucess Page** - checkout_sucess.php
```php
<?php
echo "<h1>Payment Completed!</h1>";
echo "<p>Thank you for your payment.</p>";
?>
```
This page will be used as the `redirectUrl` in your API, meaning that in the createPayment() function you pass the link to this page, where the user is redirected after completing the payment


**Failure Page** - checkout_fail.php
```php
<?php
$error = isset($_GET['error']) ? urldecode($_GET['error']) : 'A payment error occurred'; // gets the value of the "error" parameter from the URL; if the parameter does not exist, returns the default message
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Payment Error</title>
</head>
<body>
    <h1>Payment Not Completed</h1>
    <p><?= htmlspecialchars($error) ?></p>
    // Converts special characters to safe HTML, preventing XSS attacks (e.g., if someone tries to send <script> in the query string, it will be displayed as text and not executed)
    <a href="index.php">Back to Checkout</a> // we can go back to the checkout
</body>
</html>
```


**Receive notifications via webhook**
**Endpoint** receives notifications from Easypay whenever a Multibanco payment is confirmed and allows displaying the payment status in real time (requires a database, where the status will be updated from pending to paid). With the webhook, the status changes on the server, but the client will only see the update if the page is refreshed. For real-time feedback in the browser, you need to add AJAX polling or SSE
> Note that your `webhook.php` must be accessible by Easypay; if you are working locally, you can use ngrok to generate a temporary HTTPS URL

**Webhook** - webhook.php
```php
<?php
require 'config.php';

// EasyPay sends JSON in the body and then...
$input = file_get_contents("php://input"); // reads the JSON content
$data = json_decode($input, true); // converts it into a PHP associative array

if (isset($data['orderId']) && isset($data['status'])) { // checks if Easypay sent "orderId" and "status"
    updatePaymentStatus($data['orderId'], $data['status']); // calls the function "updatePaymentStatus" with the provided information
    http_response_code(200);
    echo "OK";
} else {
    http_response_code(400);
    echo "Invalid Data"; // …or some other error
}
```
Set the webhook URL in Easypay to: https://your_website_url/webhook.php

---
## 4. Test Payments (Sandbox)
1. Open browser: https://your_website_url/checkout.php
2. Fill the form (name, email, payment method)
3. Click "Pay"
4. You will be redirected to Easypay sandbox page. Complete or cancel the payment. Redirects:
   - checkout_success.php → if successful
   - checkout_fail.php → if failed/cancelled

### Before going to productions:
- Test Multibanco → pending → confirmed
- Test MB Way → approved and rejected
- Test Card → success and failure
- Test checkout_success.php and checkout_fail.php
- Test webhook or SSE/AJAX for real-time status updates
- Verify database records (status, orderId, entity, reference)
- Test unauthorized access (altering paymentId)
- Validate HTTPS redirections

---
## 5. Go to production
Change:
**URL:**
```bash
https://api.test.easypay.pt/... → https://api.easypay.pt/... ou https://api.prod.easypay.pt/...
```
**API Key:**
```bash
sk_test_... → sk_live_...
```
Switch from sandbox → real production