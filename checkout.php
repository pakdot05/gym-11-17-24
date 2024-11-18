<?php 
session_start();
?>
<?php

require('inc/links.php');

// Check if user is logged in and session variables are set
if (!isset($_SESSION['uId'])) {
    die("User not logged in");
}

// Get the logged-in user ID from the session
$userId = $_SESSION['uId'];

// Fetch user information from the database, including the phonenum
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect data from the form
    $productId = $_POST['pid'];
    $productName = $_POST['name'];
    $productPrice = $_POST['price'];
    $productImage = $_POST['image']; // This retrieves the product image
    $quantity = $_POST['qty'];

    // Calculate total price
    $totalPrice = $productPrice * $quantity;

    // Handle order placement
    if (isset($_POST['place_order'])) {
        // Collect order data
        $paymentMethod = $_POST['payment_method']; // Get the payment method
        $paymentStatus = "pending"; // Set payment status to pending

        // Insert order into the database
        $insertQuery = "INSERT INTO orders (user_id, product_name, quantity, price, total_price, payment_method, payment_status, user_name, user_email, contact_number, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $con->prepare($insertQuery)) {
            $stmt->bind_param("isidissssss", $userId, $productName, $quantity, $productPrice, $totalPrice, $paymentMethod, $paymentStatus, $userName, $userEmail, $userPhone, $userAddress);
            
            if ($stmt->execute()) {
                if ($paymentMethod == 'ewallet') {
                    // Redirect to the e-wallet payment page
                    header('Location: ./ajax/ewallet_payment.php?pid=' . urlencode($productId) . '&name=' . urlencode($productName) . '&price=' . $productPrice . '&qty=' . $quantity . '&amount=' . $totalPrice . '&name=' . urlencode($userName) . '&email=' . urlencode($userEmail) . '&id=' . $userId);
                    exit();
                } else {
                    // Update product quantity in the database (for cash on pickup)
                    $updateQuery = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
                    if ($updateStmt = $con->prepare($updateQuery)) {
                        $updateStmt->bind_param("ii", $quantity, $productId);
                        if ($updateStmt->execute()) {
                            $_SESSION['message'] = "Order placed successfully!";
                            header('Location: order_confirmation.php'); // Redirect to a confirmation page for cash
                            exit();
                        } else {
                            $_SESSION['error'] = "Error updating product quantity: " . $updateStmt->error;
                        }
                        $updateStmt->close();
                    } else {
                        die("Error preparing update statement: " . $con->error);
                    }
                }
            } else {
                $_SESSION['error'] = "Error placing order: " . $stmt->error;
            }
            $stmt->close();
        } else {
            die("Error preparing insert statement: " . $con->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Lora:wght@400;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }
        .container {
            max-width: 500px !important;
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
        .summary {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #1f1f1f;
            color: white;
            border-radius: 10px;
        }
        .summary p {
            margin: 0;
            font-family: 'Lora', serif; /* Elegant font for summary details */
        }
        .summary .price {
            float: right;
            color: #ff5959;
            font-weight: bold;
        }
        .total {
            font-weight: bold;
            color: #ff5959;
            margin-top: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .btn-primary {
            width: 100%;
            background-color: #dc3545;
            border: none;
            padding: 10px;
            font-size: 18px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #c82333;
        }
        .user-info {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        .user-info p {
            margin: 5px 0;
            font-family: 'Lora', serif; /* Elegant font for user information */
        }
        .user-info i {
            margin-right: 10px;
            color: #888;
        }
    </style>
</head>
<body>

    <!-- HEADER/NAVBAR --> 
    <?php require('inc/header.php'); ?>
    <!-- HEADER/NAVBAR --> 

    <div class="container">
        <h2>ORDER SUMMARY</h2>

        <div class="summary">
            <h3>Product Details</h3>
            
            <!-- Display the product image -->
            <div style="text-align: center; margin-bottom: 15px;">
                <img src="images/<?= htmlspecialchars($productImage) ?>" alt="Product Image" style="max-width: 200px; height: auto; border-radius: 8px;">
            </div>

            <!-- Product details below the image -->
            <p><strong>Name:</strong> <?= htmlspecialchars($productName) ?></p>
            <p><strong>Quantity:</strong> <?= htmlspecialchars($quantity) ?></p>
            <p><strong>Price:</strong> ₱<?= htmlspecialchars($productPrice) ?></p>
            <p class="total"><strong>Total:</strong> ₱<?= htmlspecialchars($totalPrice) ?></p>
        </div>

        <!-- User Information -->
        <div class="user-info">
            <h3>My Information</h3>
            <p><i class="fas fa-user"></i><?= htmlspecialchars($userName) ?></p>
            <p><i class="fas fa-phone"></i><?= htmlspecialchars($userPhone) ?></p> <!-- Added the phone number here -->
            <p><i class="fas fa-envelope"></i><?= htmlspecialchars($userEmail) ?></p>
            <p><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($userAddress) ?></p>
        </div>

        <!-- Payment Section -->
        <form id="paymentForm" method="post">
            <div class="mt-3 pickup-box">
                <label class="form-check-label">
                    <input type="checkbox" checked disabled> SELF PICK-UP
                </label>
                <select class="form-select mb-3" name="payment_method" id="paymentMethod" required>
                    <option value="">Select Payment Method --</option>
                    <option value="cash">CASH ON PICK-UP</option>
                    <option value="ewallet">PAY WITH E-WALLET</option>
                </select>
                <input type="hidden" name="pid" value="<?= htmlspecialchars($productId) ?>">
                <input type="hidden" name="name" value="<?= htmlspecialchars($productName) ?>">
                <input type="hidden" name="price" value="<?= htmlspecialchars($productPrice) ?>">
                <input type="hidden" name="image" value="<?= htmlspecialchars($productImage) ?>">
                <input type="hidden" name="qty" value="<?= htmlspecialchars($quantity) ?>">
                <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
            </div>
        </form>
    </div>

    <!-- FOOTER -->
    <?php require('inc/footer.php'); ?>
    <!-- FOOTER -->

<script>
document.getElementById('paymentForm').addEventListener('submit', function(event) {
    const paymentMethod = document.getElementById('paymentMethod').value;

    if (paymentMethod === 'ewallet') {
        // If e-wallet is selected, prevent the form from submitting immediately
        event.preventDefault();

        // Fetch the product details from PHP variables
        const productId = "<?= $productId ?>";
        const productName = "<?= htmlspecialchars($productName) ?>";
        const productPrice = "<?= $productPrice ?>";
        const quantity = "<?= $quantity ?>";
        const totalPrice = "<?= $totalPrice ?>";
        const userName = "<?= urlencode($userName) ?>";
        const userEmail = "<?= urlencode($userEmail) ?>";
        const userId = "<?= $userId ?>";

        // Redirect to e-wallet payment page
        window.location.href = "./ajax/ewallet_payment.php?pid=" + productId + "&name=" + encodeURIComponent(productName) + "&price=" + productPrice + "&qty=" + quantity + "&amount=" + totalPrice + "&name=" + userName + "&email=" + userEmail + "&id=" + userId;
    }
});
</script>

</body>
</html>
