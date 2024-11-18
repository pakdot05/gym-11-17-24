<?php 
session_start();
?>
<?php
require('inc/links.php');

// Check if the user is logged in and an order message is set
if (!isset($_SESSION['uId'])) {
    die("User not logged in");
}

if (!isset($_SESSION['message'])) {
    die("No order confirmation available");
}

// Fetch user information from the session (or from the database if needed)
$userId = $_SESSION['uId'];

// Query the database to get the user's name
$sql = "SELECT name FROM user_cred WHERE user_id = $userId";
$result = mysqli_query($con, $sql);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $userName = $user['name'];
} else {
    // Fallback in case no user is found
    $userName = 'Guest';
}
// Clear the message after displaying
$message = $_SESSION['message'];
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Lora:wght@400;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2, h3 {
            text-align: center;
            font-weight: bold;
            color: #343a40;
        }
        .message {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 10px;
        }
        .order-details {
            margin-top: 20px;
        }
        .btn {
            width: 100%;
        }
    </style>
</head>
<body>

    <!-- HEADER/NAVBAR --> 
    <?php require('inc/header.php'); ?>
    <!-- HEADER/NAVBAR --> 

    <div class="container">
        <h2>Order Confirmation</h2>

        <div class="message">
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>

        <h3>Thank you, <?= htmlspecialchars($userName) ?>!</h3>
        <p>Your order has been placed successfully and will be ready for cash pickup.</p>

        <div class="order-details">
            <h4>Order Details</h4>
            <p><strong>Order Number:</strong> #<?php echo rand(1000, 9999); // Example Order Number ?></p>
            <p><strong>Payment Method:</strong> Cash on Pickup</p>
            <!-- You can also include more order details here -->
            <p>For any inquiries, please contact us at minganillageafitnessgym@gmail.com.</p>
        </div>

        <a href="product.php" class="btn btn-primary mt-3">Continue Shopping</a>
    </div>

    <!-- FOOTER -->
    <?php require('inc/footer.php'); ?>
    <!-- FOOTER -->

</body>
</html>
