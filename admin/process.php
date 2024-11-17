<?php


if (isset($_GET['product_id'])) {
    $productId = $_GET['product_id'];

    // Fetch product details
    $stmt = $con->prepare("SELECT id, name, price, quantity, image, unit FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $productDetails = $result->fetch_assoc();
        echo json_encode(['success' => true, 'id' => $productDetails['id'], 'name' => $productDetails['name'], 'price' => $productDetails['price'], 'quantity' => $productDetails['quantity'], 'image' => $productDetails['image'], 'unit' => $productDetails['unit']]);
    } else {
        echo json_encode(['success' => false]);
    }

    $stmt->close();
    $con->close();
    exit;
}
?>