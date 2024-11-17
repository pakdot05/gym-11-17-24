<?php
require('inc/links.php');

// Check if the order_id is provided in the query parameters
if (isset($_GET['order_id'])) {
    $orderId = $_GET['order_id'];

    // Delete the order from the database
    $deleteQuery = "DELETE FROM orders WHERE order_id = $orderId";
    if (mysqli_query($con, $deleteQuery)) {
        // Success message
        $_SESSION['message'] = "Order deleted successfully!";
        header('Location:order.php'); // Redirect to the order page
        exit();
    } else {
        // Error message
        $_SESSION['error'] = "Error deleting order: " . mysqli_error($con);
    }
} else {
    // Error message
    $_SESSION['error'] = "Invalid order ID.";
    header('Location:order.php');
    exit();
}
?>