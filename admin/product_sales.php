<?php
require('inc/db_config.php');
require('inc/essentials.php');

// Initialize variables
$search_query = "";
if (isset($_GET['ajax_search'])) {
    $orders_data = []; // Array to hold order data
    if (count($orders) > 0) {
        foreach ($orders as $order) {
            $orders_data[] = $order; // Add order data to the array
        }
    }
    echo json_encode($orders_data); // Send the order data as JSON
    exit();
}
$query = "SELECT o.order_id, o.product_name, o.quantity, o.payment_status, 
                 o.address, u.name AS user_name, o.payment_method, o.price ,o.claimed,o.user_name
          FROM orders o
           LEFT JOIN user_cred u ON o.user_id = u.user_id";
if (!empty($search_query)) {
    $query .= " WHERE o.product_name LIKE ? OR u.name LIKE ? OR o.address LIKE ? OR o.payment_status LIKE ?";
}
$orders = [];
if ($stmt = $con->prepare($query)) {
    if (!empty($search_query)) {
        $search_term = "%" . $search_query . "%";  // Prepare search term for SQL LIKE
        $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
} else {
    die("Error fetching orders: " . $con->error);
}
foreach ($orders as $order) {
    if ($order['payment_status'] === 'Pending' && strtotime($order['order_date']) < strtotime('-3 days')) {
        $updateQuery = "UPDATE orders SET payment_status = 'Cancelled' WHERE order_id = ?";
        if ($stmt = $con->prepare($updateQuery)) {
            $stmt->bind_param("i", $order['order_id']);
            $stmt->execute();
            $stmt->close();
        }
      $updateProductQuery = "UPDATE products SET quantity = quantity + ? WHERE product_name = ?";
      if ($stmt = $con->prepare($updateProductQuery)) {
          $stmt->bind_param("is", $order['quantity'], $order['product_name']);
          $stmt->execute();
          $stmt->close();
      }
  }
}
if (isset($_GET['ajax_search'])) {
    if (count($orders) > 0) {
        foreach ($orders as $index => $order) {
            echo "<tr>
                    <td>" . ($index + 1) . "</td>
                    <td>{$order['product_name']}</td>
                    <td>{$order['user_name']}</td>
                    <td>{$order['quantity']}</td>
                    <td>{$order['price']}</td>
                    <td>{$order['address']}</td>
                    <td>{$order['payment_method']}</td>
                    <td>{$order['payment_status']}</td>
                    <td>";

                    if ($order['payment_status'] === 'Paid' && $order['claimed'] == 0) {
                        // Display claimant modal for paid but unclaimed orders
                        echo "<button class='btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#claimNameModal'
                                data-order-id='{$order['order_id']}'>
                                Claim
                              </button>";
                    } elseif ($order['payment_status'] === 'Paid' && $order['claimed'] == 1) {
                        // Disable button if claimed
                        echo "<button class='btn btn-success btn-sm' disabled>Claimed</button>";
                    } elseif ($order['payment_method'] === 'walk-in') {
                        echo "<button class='btn btn-secondary btn-sm' disabled>Claimed</button>"; 
                    } elseif ($order['payment_status'] === 'Cancelled') {
                        // Disable button if cancelled
                        echo "<button class='btn btn-secondary btn-sm' disabled>Cancelled</button>";
                    } else {
                        // Open payment modal for unpaid orders
                        echo "<button class='btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#paymentModal'
                                data-order-id='{$order['order_id']}' 
                                data-price='{$order['price']}'>
                                Claim
                              </button>";
                    }
                    
                    
            // Always display the delete button
            echo "
                <form method='post' action='product_sales.php' onsubmit='return confirm(\"Are you sure you want to delete this order?\");'>
                    <input type='hidden' name='order_id' value='{$order['order_id']}'>
                    <button type='submit' name='delete_order' class='btn btn-danger btn-sm'>Delete</button>
                </form>
            ";

            echo "</td></tr>";
        }
    } else {
        echo "<tr><td colspan='10' class='text-center'>No orders found</td></tr>";
    }
    exit();
}

if (isset($_POST['order_id']) && isset($_POST['amount'])) {
    $order_id = (int)$_POST['order_id'];
    $amount_received = (float)$_POST['amount'];

    // Fetch the order price
    $query = "SELECT price FROM orders WHERE order_id = ?";
    if ($stmt = $con->prepare($query)) {
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->bind_result($price);
        $stmt->fetch();
        $stmt->close();
    }

    // Update the order status if payment is sufficient
    if ($amount_received >= $price) {
        $updateQuery = "UPDATE orders SET payment_status = 'Paid' WHERE order_id = ?";
        if ($stmt = $con->prepare($updateQuery)) {
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $stmt->close();
        }
        echo "Payment successful";
    } else {
        echo "Insufficient amount";
    }
    exit();
}

if (isset($_POST['order_id']) && isset($_POST['claimant_name'])) {
    $order_id = (int)$_POST['order_id'];
    $claimant_name = $con->real_escape_string(trim($_POST['claimant_name']));

    // Ensure that the claimant_name is not empty
    if (!empty($claimant_name)) {
        // Update the order to mark it as claimed and save the claimant's name (if necessary)
        $updateQuery = "UPDATE orders SET claimed = 1, user_name = ? WHERE order_id = ?";
        if ($stmt = $con->prepare($updateQuery)) {
            $stmt->bind_param("si", $claimant_name, $order_id);
            if ($stmt->execute()) {
                echo "Claim updated successfully!";
            } else {
                echo "Error updating claim: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing update query: " . $con->error;
        }
    } else {
        echo "Claimant name is required.";
    }
    exit();
}

// If delete action is triggered
if (isset($_POST['delete_order'])) {
    $order_id = (int)$_POST['order_id'];

    $deleteQuery = "DELETE FROM orders WHERE order_id = ?";
    if ($stmt = $con->prepare($deleteQuery)) {
        $stmt->bind_param("i", $order_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Order successfully deleted.";
        } else {
            $_SESSION['error'] = "Error deleting order: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Error preparing delete statement: " . $con->error;
    }
    header('Location: product_sales.php');
    exit();
}

// Handle order submission
if (isset($_POST['action']) && $_POST['action'] === 'submit_order') {

    $sql = "SELECT MAX(CAST(SUBSTRING(user_id, 4) AS UNSIGNED)) AS max_user_id FROM orders WHERE user_id LIKE 'ord%'";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Generate the next user_id
    $nextUserId = isset($row['max_user_id']) ? $row['max_user_id'] + 1 : 1;
    $userId = 'ord' . $nextUserId;

    $productId = mysqli_real_escape_string($con, $_POST['product_id']);
    $quantity = mysqli_real_escape_string($con, $_POST['quantity']);
    $contactNumber = mysqli_real_escape_string($con, $_POST['contact_number'] ?? '');
    $userEmail = mysqli_real_escape_string($con, $_POST['user_email'] ?? '');
    $address = mysqli_real_escape_string($con, $_POST['address'] ?? '');
    $paymentMethod = 'walk-in';
    $paymentStatus = 'paid';
    $claimed = 1;
    $amountPaid = mysqli_real_escape_string($con, $_POST['amount_paid']);

    // Fetch product details
    $sql = "SELECT name, price FROM products WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $productResult = $stmt->get_result();

    if ($productResult->num_rows > 0) {
        $product = $productResult->fetch_assoc();
        $totalPrice = $product['price'] * $quantity;
        $amountPaid = isset($_POST['amount_paid']) && is_numeric($_POST['amount_paid']) ? (float) $_POST['amount_paid'] : 0;
        $totalPrice = (float) $totalPrice;  // Ensure totalPrice is a float
        $change = $amountPaid - $totalPrice;

        // Check if the amount paid is enough
        if ($amountPaid < $totalPrice) {
            // Return an error message
            echo json_encode(['error' => 'Insufficient amount paid.']);
            exit;
        }

        // Insert order details into the database
        $sql = "INSERT INTO orders 
        (user_id, product_name, quantity, price, total_price, payment_method, payment_status, user_name, user_email, contact_number, address, claimed, pid)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param(
            "isiddsssssiii", // Corrected type definition string
            $userId,  // String user_id (e.g., ord1, ord2)
            $product['name'],  // string
            $quantity, // integer
            $product['price'], // double
            $totalPrice, // double
            $paymentMethod, // string
            $paymentStatus, // string
            $_POST['user_name'], // string (user_name)
            $userEmail, // string
            $contactNumber, // string
            $address, // string
            $claimed, // integer
            $productId // integer
        );
        $success = $stmt->execute();

        // Check for database errors
        if ($stmt->error) {
            // Return error as JSON response
            echo json_encode(['error' => $stmt->error]);
        } else {
            // Redirect to product_sales.php
            header("Location: product_sales.php");
            exit;
        }

        // Close statement and connection
        $stmt->close();
        $con->close();
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sold Product</title>
    <?php require('inc/links.php'); ?>
  <style>
.product-image{
    width: 50%;
    height: auto; 
    border-radius: 5px; 
    object-fit: cover; 
    max-height: 300px;
    margin-bottom: 15px; 
    align-items: center; 
    justify-content: center; 
}
.modal-content {
    padding: 20px;
    border-radius: 10px;
}
.product-box {
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #f9f9f9;
}
</style>
   
</head>
<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-10 ms-auto p-4 overflow-hidden">
                <h3 class="mb-4">Sold Product</h3>

                <?php
                // Display success or error messages
                if (isset($_SESSION['message'])) {
                    echo "<div class='alert alert-success'>{$_SESSION['message']}</div>";
                    unset($_SESSION['message']); // Clear the message after displaying it
                }

                if (isset($_SESSION['error'])) {
                    echo "<div class='alert alert-danger'>{$_SESSION['error']}</div>";
                    unset($_SESSION['error']); // Clear the error after displaying it
                }
                ?>
<div class="text-end mb-4">
<button class="btn btn-dark" data-toggle="modal" data-target="#buyproductModal">Buy Product</button>
</div>
<section class="search-section my-4">
            <form action="" method="get" class="search-form">
            <input type="text" id="searchInput" name="search" placeholder="Search..." class="form-control shadow-none w-25 ms-auto" >
            </form>
            </section>

                <div class="table-responsive-md">
                    <table class="table table-hover border" id="ordersTable">
                        <thead class="sticky-top">
                            <tr class="bg-dark text-light">
                                <th scope="col">#</th>
                                <th scope="col">Product Name</th>
                                <th scope="col">Buyer Name</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">Price</th>
                                <th scope="col">Address</th>
                                <th scope="col">Method</th>
                                <th scope="col">Payment</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $index => $order): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo $order['product_name']; ?></td>
                                    <td><?php echo $order['user_name']; ?></td>
                                    <td><?php echo $order['quantity']; ?></td>
                                    <td><?php echo $order['price']; ?></td>
                                    <td><?php echo $order['address']; ?></td>
                                    <td><?php echo $order['payment_method']; ?></td>
                                    <td><?php echo $order['payment_status']; ?></td>
                                    <td>
                                    <?php if ($order['payment_status'] === 'Paid' && $order['claimed'] == 0): ?>
                                            <!-- Paid but unclaimed orders, show Claimant Name modal -->
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#claimNameModal"
                                                    data-order-id="<?php echo $order['order_id']; ?>">
                                                Claim
                                            </button>
                                        <?php elseif (($order['payment_status'] === 'Paid' && $order['claimed'] == 1) || $order['payment_status'] === 'Cancelled'): ?>
                                            <button class="btn btn-<?php echo ($order['payment_status'] === 'Paid') ? 'success' : 'secondary'; ?> btn-sm" disabled>
                                                <?php echo ($order['payment_status'] === 'Paid') ? 'Claimed' : 'Cancelled'; ?>
                                            </button>
                                            <?php elseif ($order['payment_method'] === 'walk-in'): ?>
                                            <button class="btn btn-success btn-sm" disabled>
                                                Claimed
                                            </button>
                                        <?php else: ?>
                                            <!-- Unpaid orders, show payment modal -->
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal"
                                                    data-order-id="<?php echo $order['order_id']; ?>"
                                                    data-price="<?php echo $order['price']; ?>">
                                                Claim
                                            </button>
                                        <?php endif; ?> <form method="post" action="product_sales.php" onsubmit="return confirm('Are you sure you want to delete this order?');" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                            <button type="submit" name="delete_order" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center">No orders found</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

   <!-- Payment Modal -->
   <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Payment Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Amount to be paid: <span id="paymentPrice"></span></p>
                <p>Enter received amount:</p>
                <input type="number" id="receivedAmount" class="form-control" min="0" step="0.01">
                <p id="changeDisplay" style="display:none;">Change: <span id="changeAmount"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmPaymentButton">Confirm Payment</button>
            </div>
        </div>
    </div>
</div>

<!-- Claim Name Modal -->
<div class="modal fade" id="claimNameModal" tabindex="-1" aria-labelledby="claimNameModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="claimNameModalLabel">Claim Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Please enter the name of the person claiming this order:</p>
                <input type="text" id="claimantName" class="form-control" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmClaimButton">Confirm Claim</button>
            </div>
        </div>
    </div>
</div>

<?php
// Initialize variables for selected product
$productDetails = null;
if (isset($_GET['product_id'])) {
    $productId = $_GET['product_id'];
    $stmt = $con->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $productDetails = $result->fetch_assoc();
    $stmt->close();
}
?>
    <div class="modal fade" id="buyproductModal" tabindex="-1" role="dialog" aria-labelledby="buyproductModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="buyproductModalLabel">Buy Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="buyProductForm" method="POST" action="product_sales.php">
                    <input type="hidden" name="action" value="submit_order">
                    <div class="modal-body">
                        <!-- Product Selection -->
                        <div class="form-group">
                            <label for="productSelect">Select Product</label>
                            <select class="form-control" id="productSelect" name="product_id" required onchange="window.location.href='product_sales.php?product_id=' + this.value">
                                <option value="">-- Select a Product --</option>
                                <?php
                                // Fetch products from the database
                                $result = $con->query("SELECT id, name, quantity FROM products");

                                if ($result->num_rows > 0) {
                                    while ($fetch_products = $result->fetch_assoc()) {
                                        $is_sold_out = $fetch_products['quantity'] <= 0;
                                        $disabled = $is_sold_out ? 'disabled' : '';
                                        $selected = ($fetch_products['id'] == ($_GET['product_id'] ?? '')) ? 'selected' : '';

                                        echo "<option value='{$fetch_products['id']}' $disabled $selected>" . htmlspecialchars($fetch_products['name']);
                                        echo $is_sold_out ? " (Sold Out)" : "";
                                        echo "</option>";
                                    }
                                } else {
                                    echo "<option disabled>No products available</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Product Details (Only display if a product is selected and available) -->
                        <?php if ($productDetails): ?>
                            <div class="product-box">
                                <img src="../images/<?= htmlspecialchars($productDetails['image']); ?>" alt="<?= htmlspecialchars($productDetails['name']); ?>" class="product-image">
                                <p><strong>Product Name:</strong> <?= htmlspecialchars($productDetails['name']); ?></p>
                                <p><strong>Unit:</strong> <?= htmlspecialchars($productDetails['unit']); ?></p>
                                <p><strong>Price:</strong> ₱<?= htmlspecialchars($productDetails['price']); ?></p>
                                <p><strong>Available:</strong> <?= htmlspecialchars($productDetails['quantity']); ?></p>

                                <!-- Hidden Inputs to Pass Product Info to Checkout -->
                                <input type="hidden" name="pid" value="<?= $productDetails['id']; ?>">
                                <input type="hidden" name="name" value="<?= $productDetails['name']; ?>">
                                <input type="hidden" name="price" value="<?= $productDetails['price']; ?>">
                                <input type="hidden" name="image" value="<?= $productDetails['image']; ?>">

                                <!-- Buyer Name Input -->
                                <div class="form-group">
                                    <label for="buyerName">Buyer Name</label>
                                    <input type="text" class="form-control" id="buyerName" name="user_name" required>
                                </div>

                                <!-- Quantity Input -->
                                <div class="form-group">
                                    <label for="quantity">Quantity</label>
                                    <input type="number" name="quantity" class="form-control qty" id="quantity" min="1" max="<?= htmlspecialchars($productDetails['quantity']); ?>" value="1">
                                </div>
                                <div class="form-group">
                                    <label for="totalPrice">Total Price</label>
                                    <input type="number" class="form-control" id="totalPrice" readonly> 
                                </div>
                                <!-- Payment Section -->
                                <div class="form-group">
                                    <label for="amountPaid">Amount Paid</label>
                                    <input type="number" name="amount_paid" class="form-control" id="amountPaid" min="0" required>
                                </div>

                                <div class="form-group">
                                    <label for="change">Change</label>
                                    <input type="text" class="form-control" id="change" >
                                </div>

                                <!-- Optional Information -->
                                <div class="form-group">
                                    <label for="contactNumber">Contact Number</label>
                                    <input type="text" class="form-control" id="contactNumber" name="contact_number" placeholder="Optional">
                                </div>
                                <div class="form-group">
                                    <label for="userEmail">Email</label>
                                    <input type="email" class="form-control" id="userEmail" name="user_email" placeholder="Optional">
                                </div>
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <input type="text" class="form-control" id="address" name="address" placeholder="Optional">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-dark">Submit Order</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeModalButton">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div> 
    <?php require('inc/scripts.php'); ?>
    <script>
       document.addEventListener('DOMContentLoaded', function () {
    var orderIdToPay, totalPrice, orderIdToClaim;

    // AJAX search function
    function search_user(query) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "product_sales.php?ajax_search=1&search=" + query, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var ordersData = JSON.parse(xhr.responseText); // Parse the JSON response
            updateOrdersTable(ordersData); // Update the table with the search results
        }
    };
    xhr.send();
}
var paymentModal = document.getElementById('paymentModal');
    paymentModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        orderIdToPay = button.getAttribute('data-order-id');
        totalPrice = parseFloat(button.getAttribute('data-price'));

        document.getElementById('paymentPrice').innerText = totalPrice.toFixed(2);
        document.getElementById('changeDisplay').style.display = 'none'; // Hide change display initially
        document.getElementById('receivedAmount').value = ''; // Clear previous input
    });

    // Display change based on received amount in Payment Modal
    document.getElementById('receivedAmount').addEventListener('input', function () {
        var receivedAmount = parseFloat(this.value);
        if (!isNaN(receivedAmount) && receivedAmount >= totalPrice) {
            var change = receivedAmount - totalPrice;
            document.getElementById('changeDisplay').style.display = 'block';
            document.getElementById('changeAmount').innerText = change.toFixed(2);
        } else {
            document.getElementById('changeDisplay').style.display = 'none'; // Hide change display if input is invalid
        }
    });

    // Confirm Payment
    document.getElementById('confirmPaymentButton').addEventListener('click', function () {
        var receivedAmount = parseFloat(document.getElementById('receivedAmount').value);
        if (isNaN(receivedAmount) || receivedAmount < totalPrice) {
            alert('Please enter a valid amount equal to or greater than the price.');
            return;
        }

        // Send AJAX request to update payment status
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "product_sales.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                location.reload(); // Reload page after payment
            }
        };
        xhr.send("order_id=" + orderIdToPay + "&amount=" + receivedAmount);
    });

    // Claim Name Modal
    var claimNameModal = document.getElementById('claimNameModal');
    claimNameModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        orderIdToClaim = button.getAttribute('data-order-id');
        document.getElementById('claimantName').value = ''; // Clear previous input
    });

    // Confirm Claim
    document.getElementById('confirmClaimButton').addEventListener('click', function () {
        var claimantName = document.getElementById('claimantName').value.trim();
        if (claimantName === '') {
            alert('Please enter a valid name.');
            return;
        }

        // Send AJAX request to claim order with claimant's name
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "product_sales.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                location.reload(); // Reload page after claiming
            }
        };
        xhr.send("order_id=" + orderIdToClaim + "&claimant_name=" + encodeURIComponent(claimantName));
    });
});
window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const productId = urlParams.get('product_id');
            
            if (productId) {
                document.getElementById('closeModalButton').addEventListener('click', function() {
               document.getElementById('buyproductModal').classList.add('show');
               document.getElementById('buyproductModal').style.display = 'block';
            });
        }
        };
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.getElementById('quantity');
            const amountPaidInput = document.getElementById('amountPaid');
            const totalPriceInput = document.getElementById('totalPrice');
            const changeInput = document.getElementById('change');
            const buyProductForm = document.getElementById('buyProductForm');

            function calculateChange() {
                const quantity = parseFloat(quantityInput.value) || 0;
                const price = parseFloat(document.querySelector('input[name="price"]').value) || 0; // Access price using name
                const amountPaid = parseFloat(amountPaidInput.value) || 0;

                const totalPrice = quantity * price;
                const change = amountPaid - totalPrice;

                totalPriceInput.value = totalPrice.toFixed(2);
                changeInput.value = change.toFixed(2);

                if (amountPaid < totalPrice) {
                    changeInput.classList.add("text-danger");
                    buyProductForm.onsubmit = function(event) {
                        event.preventDefault();
                        alert("Insufficient amount paid.");
                    };
                } else {
                    changeInput.classList.remove("text-danger");
                    buyProductForm.onsubmit = null; // Enable form submission
                }
            }

            // Event listeners for quantity and amountPaid changes
            quantityInput.addEventListener('change', calculateChange);
            amountPaidInput.addEventListener('change', calculateChange);

            // Trigger change event on load
            calculateChange();
        });
        const searchInput = document.getElementById('searchInput');
const boxContainer = document.querySelector('.table');
const emptyMessage = document.querySelector('.empty');

searchInput.addEventListener('input', searchEquipments);

function searchEquipments() {
  const filter = searchInput.value.toUpperCase();
  const boxes = boxContainer.getElementsByTagName('tr');

  let equipmentFound = false;

  for (let i = 1; i < boxes.length; i++) {
    const name = boxes[i].getElementsByTagName('td')[1].textContent.toUpperCase();
    const description = boxes[i].getElementsByTagName('td')[2].textContent.toUpperCase();

    if (name.includes(filter) || description.includes(filter)) {
      boxes[i].style.display = '';
      equipmentFound = true;
    } else {
      boxes[i].style.display = 'none';
    }
  }

  if (equipmentFound) {
    emptyMessage.style.display = 'none';
  } else {
    emptyMessage.style.display = 'block';
  }
}

 </script><script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  
</body>
</html>
