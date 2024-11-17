<?php
require('inc/links.php');
// Check if user is logged in and session variables are set
if (!isset($_SESSION['uId'])) {
    die("User not logged in");
}

// Fetch order data for the logged-in user
$userId = $_SESSION['uId'];
$sql = "SELECT * FROM orders WHERE user_id = $userId";
$result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MY ORDER</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <style>
        .box {
            border-top-color: var(--blue) !important;
        }

        /* th {
            background-color: #323232 !important;
        } */

        #time-remaining {
            font-size: 18px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">

    <!-- HEADER/NAVBAR --> 
    <?php require('inc/header.php'); ?> 
    <!-- HEADER/NAVBAR --> 

    <div class="my-5 px-4">
        <h2 class="fw-bold text-center">MY ORDER</h2>
        <div class="h-line bg-dark"></div>
    </div>

    <div class="container" style="height: 100vh;">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="table-responsive-md" style="height: 450px; overflow-y: scroll;">
                    <table class="table table-hover border" id="gymTable">
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
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Check if records exist
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Determine payment status color
                                    $status_color = $row['payment_status'] === 'Paid' ? 'green' : 'red';
                                    $status = $row['payment_status'] === 'Paid' ? 'Paid' : ($row['payment_status'] === 'Cancelled' ? 'Cancelled' : 'Pending');
                                    $claimed_color = $row['claimed'] === '1' ? 'green' : 'red';
                                    $claimed = $row['claimed'] === '1' ? '1' : '0';
                            ?>
                            <tr>
                            <th scope="row"><?= htmlspecialchars($row["order_id"]) ?></th>
                            <td><?= htmlspecialchars($row["product_name"]) ?></td>
                            <td><?= htmlspecialchars($row["user_name"]) ?></td>
                            <td><?= htmlspecialchars($row["quantity"]) ?></td>
                            <td>₱<?= number_format($row["total_price"], 2) ?></td>
                            <td><?= htmlspecialchars($row["address"]) ?></td>
                            <td><?= htmlspecialchars($row["payment_method"]) ?></td>
                            <td style="color: <?= $status_color ?>; font-weight: bold;"><?= $status ?></td>
                            <td style="color: <?= $claimed_color ?>; font-weight: bold;">
                                <?= $row['claimed'] === '1' ? 'Claimed' : 'Unclaimed' ?>
                            </td>
                            <td>
                            <?php if ($row['payment_status'] === 'Paid' || $row['payment_status'] === 'Cancelled') { ?>
                                <a href="delete.php?order_id=<?= $row['order_id'] ?>" class="btn btn-danger">Delete</a>
                            <?php } else { ?>
                                <a href="cancel.php?order_id=<?= $row['order_id'] ?>" class="btn btn-danger">Cancel</a>
                            <?php } ?>
                            </td>

                            </td>
                            </tr>
                            <?php
                                }
                            } else {
                                // Display a message if no orders are found
                                echo "<tr><td colspan='9' class='text-center'>No Order found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <?php require('inc/footer.php'); ?>
    <!-- FOOTER -->

</body>
</html>