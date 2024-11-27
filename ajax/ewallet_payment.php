<?php
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');

session_start();
if (!isset($_SESSION['uId'])) {
    die("User not logged in");
}

// Get the logged-in user ID from the session
$userId = $_SESSION['uId'];

// Fetch user information from the database
$query = "SELECT name, email, address, phonenum FROM user_cred WHERE user_id = ?";
if ($stmt = $con->prepare($query)) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($userName, $userEmail, $userAddress, $userPhone);
    $stmt->fetch();
    $stmt->close();
} else {
    die("Error fetching user information.");
}

// Fetch product details from the database
$productId = isset($_GET['pid']) ? (int)$_GET['pid'] : 0; // Assuming this is passed in the URL
$productQuery = "SELECT name, price, quantity FROM products WHERE id = ?";
$productName = "Unknown Product"; // Default product name
$productPrice = 0; // Default price
$productQuantity = 0; // Default quantity

if ($stmt = $con->prepare($productQuery)) {
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->bind_result($productName, $productPrice, $productQuantity);
    if (!$stmt->fetch()) {
        die("Product not found.");
    }
    $stmt->close();
} else {
    die("Error fetching product information.");
}

// Check if product is available
if ($productQuantity <= 0) {
    die("Product is out of stock.");
}

// Get quantity from user (you may want to get this from user input)
$quantity = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;

// Check if enough stock is available
if ($quantity > $productQuantity) {
    die("Sorry, not enough stock available for $productName.");
}

// Calculate total price
$totalPrice = $productPrice * $quantity;

// Xendit API keys
$secret_api_key = 'xnd_development_vsjdbgeYHcsdOX1XlmpPLRHKoPCuLlmhQzKhMwIrG1nQH6FWe4TMI7MDWXqJLuaR';

// Function to initiate the Xendit invoice
function redirectToXendit($amount, $userName, $userEmail, $paymentId) {
    global $secret_api_key;

    // Xendit API endpoint
    $endpoint = 'https://api.xendit.co/v2/invoices';

    // Prepare the request data
    $data = [
        'external_id' => $paymentId,
        'amount' => $amount,
        'description' => 'Order Payment',
        'customer' => [
            'given_names' => $userName,
            'email' => $userEmail,
        ],
        'currency' => 'PHP',
        'success_redirect_url' => 'http://v8040gs8o4cgwkok8wo80csk.178.128.125.251.sslip.io/prodsuccess.php?paymentId=' . $paymentId,
        'failure_redirect_url' => 'http://127.0.0.1/failure.php',
    ];

    // JSON encode the data
    $jsonData = json_encode($data);

    // Initialize cURL
    $curl = curl_init();

    // Set cURL options
    curl_setopt_array($curl, [
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($secret_api_key . ':')
        ],
    ]);

    // Execute the cURL request
    $response = curl_exec($curl);

    // Check for errors
    if (curl_errno($curl)) {
        echo 'Error: ' . curl_error($curl);
        curl_close($curl);
        exit();
    }

    // Decode the response
    curl_close($curl);
    $responseData = json_decode($response, true);

    // Redirect the user to the Xendit payment URL
    if (isset($responseData['invoice_url'])) {
        header('Location: ' . $responseData['invoice_url']);
        exit();
    } else {
        echo 'Error creating invoice: ' . json_encode($responseData);
        exit();
    }
}

// Generate a unique payment ID
$paymentId = 'order_' . $userId . '_' . time();

// Prepare to insert order data into the database
$insertOrderQuery = "INSERT INTO orders (user_id, pid, product_name, quantity, price, total_price, payment_method, payment_status, user_name, user_email, contact_number, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

if ($stmt = $con->prepare($insertOrderQuery)) {
    $paymentMethod = 'E-Wallet'; 
    $paymentStatus = 'Paid'; 

    // Debugging: Log values to be inserted
    error_log("Inserting order: userId=$userId, productId=$productId, productName=$productName, quantity=$quantity, productPrice=$productPrice, totalPrice=$totalPrice, userName=$userName, userEmail=$userEmail, contactNumber=$userPhone, address=$userAddress");

    // Bind parameters and execute (added product_id)
    $stmt->bind_param("iissidssssss", $userId, $productId, $productName, $quantity, $productPrice, $totalPrice, $paymentMethod, $paymentStatus, $userName, $userEmail, $userPhone, $userAddress);
    
    if ($stmt->execute()) {
        // Reduce the product quantity in the products table
        $newQuantity = $productQuantity - $quantity; // Reduce the quantity based on user's order

        // Update the product quantity in the database
        $updateQuantityQuery = "UPDATE products SET quantity = ? WHERE id = ?";
        if ($updateStmt = $con->prepare($updateQuantityQuery)) {
            $updateStmt->bind_param("ii", $newQuantity, $productId);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            die("Error updating product quantity: " . $con->error);
        }

        // Redirect to Xendit payment page
        redirectToXendit($totalPrice, $userName, $userEmail, $paymentId);
        
    } else {
        die("Error inserting order into the database: " . $stmt->error);
    }
} else {
    die("Error preparing insert statement: " . $con->error);
}

?>
