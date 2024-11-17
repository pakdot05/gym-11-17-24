<?php
require('inc/links.php');

// Check if the order_id is provided in the query parameters
if (isset($_GET['order_id'])) {
    $orderId = $_GET['order_id'];

    // Fetch order details from the database
    $sql = "SELECT product_name, quantity, payment_method FROM orders WHERE order_id = $orderId";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);

    // Update product quantity if the payment method is "cash"
    if ($row['payment_method'] === 'cash') {
        $productName = $row['product_name'];
        $quantity = $row['quantity'];

        // Update the product quantity back in the 'products' table
        $updateQuery = "UPDATE products SET quantity = quantity + $quantity WHERE name = '$productName'";
        if (mysqli_query($con, $updateQuery)) {
            // Update the order status to "Cancelled"
            $updateOrderStatusQuery = "UPDATE orders SET payment_status = 'Cancelled' WHERE order_id = $orderId";
            if (mysqli_query($con, $updateOrderStatusQuery)) {
                // Success message
                $_SESSION['message'] = "Order cancelled successfully!";
                header('Location: order.php'); // Redirect to the order page
                exit();
            } else {
                // Error message
                $_SESSION['error'] = "Error cancelling order: " . mysqli_error($con);
            }
        } else {
            // Error message
            $_SESSION['error'] = "Error updating product quantity: " . mysqli_error($con);
        }
    } else {
        // If payment method is not "cash"
        $_SESSION['error'] = "This order cannot be cancelled.";
        header('Location: order.php');
        exit();
    }
} else {
    // Error message
    $_SESSION['error'] = "Invalid order ID.";
    header('Location: order.php');
    exit();
}
?>