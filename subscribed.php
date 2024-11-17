<?php
require('inc/links.php');
// Check if user is logged in and session variables are set
if (!isset($_SESSION['uId'])) {
    die("User not logged in");
}


// Fetch subscription data for the logged-in user
$userId = $_SESSION['uId'];
$sql = "SELECT * FROM subscriptions WHERE user_id = $userId";
$result = mysqli_query($con, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYM- About Us</title> 
    <link rel="stylesheet"href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

    <!-- LINKS -->
    <style>

        .box{

            border-top-color: var(--blue) !important;

        }

        /* th{
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
    <?php require('inc/header.php') ?> 
    <!-- HEADER/NAVBAR --> 

    <div class="my-5 px-4">
        <h2 class="fw-bold text-center">MY SUBSCRIPTION</h2>
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
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Plan</th>
                                <th scope="col">Price</th>
                                <th scope="col">Start Date</th>
                                <th scope="col">End Date</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Check if records exist
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Determine if the subscription is active or expired
                                    $end_date = strtotime($row['end_date']);
                                    $current_date = time();
                                    $status = ($end_date > $current_date) ? 'Active' : 'Expired';
                                    $status_color = ($status === 'Active') ? 'green' : 'red'; 
                            ?>
                            <tr>
                                <th scope="row"><?= htmlspecialchars($row["id"]) ?></th>
                                <td><?= htmlspecialchars($row["name"]) ?></td>
                                <td><?= htmlspecialchars($row["email"]) ?></td>
                                <td><?= htmlspecialchars($row["plan"]) ?></td>
                                <td>₱<?= htmlspecialchars($row["price"]) ?></td>
                                <td><?= htmlspecialchars($row["created_at"]) ?></td>
                                <td><?= htmlspecialchars($row["end_date"]) ?></td>
                                <td style="color: <?= $status_color ?>; font-weight: bold;"><?= $status ?></td> 
                            </tr>
                            <?php
                                }
                            } else {
                                // Display a message if no subscriptions are found
                                echo "<tr><td colspan='8' class='text-center'>No subscriptions found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <?php require('inc/footer.php') ?>
    <!-- FOOTER -->

</body>
</html>