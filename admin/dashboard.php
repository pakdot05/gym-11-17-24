<?php
    require('inc/db_config.php');
    require('inc/essentials.php');
    adminLogin();

    if(isset($_POST['get_approved_count'])) {
        $query = "SELECT COUNT(*) AS total FROM bookings WHERE status = 1";
        $result = select($query);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            echo json_encode(['count' => $row['total']]);
        } else {
            echo json_encode(['count' => 0]);
        }
        exit;
    }

    // Execute SQL query to count patients
    $patient_result = mysqli_query($con, "SELECT COUNT(*) AS patient_count FROM user_cred");
    $patient_row = mysqli_fetch_assoc($patient_result);
    $patient_count = $patient_row['patient_count'];

    $subscribe_result = mysqli_query($con, "SELECT COUNT(*) AS subscription_count FROM subscriptions");
    $subscribe_row = mysqli_fetch_assoc($subscribe_result);
    $subscribe_count = $subscribe_row['subscription_count'];

    // Execute SQL query to count unapproved bookings
    $product_result = mysqli_query($con, "SELECT COUNT(*) AS product_count FROM products");
    $product_row = mysqli_fetch_assoc($product_result);
    $product_count = $product_row['product_count'];

    $today_date = date('Y-m-d');
    $today_booking_result = mysqli_query($con, "SELECT COUNT(*) AS booking_count FROM bookings WHERE date = '$today_date'");
    $today_booking_row = mysqli_fetch_assoc($today_booking_result);
    $today_booking_count = $today_booking_row['booking_count'];
    
    // Execute SQL query to calculate total earnings from invoices
    $earnings_result = mysqli_query($con, "SELECT SUM(price) AS total_earnings FROM subscriptions");
    $earnings_row = mysqli_fetch_assoc($earnings_result);
    $total_earnings = $earnings_row['total_earnings'] ?? 0;

    $order_result = mysqli_query($con, "SELECT COUNT(*) AS order_count FROM orders");
    $order_row = mysqli_fetch_assoc($order_result);
    $order_count = $order_row['order_count'];

    $ordearnings_result = mysqli_query($con, "SELECT SUM(total_price) AS total_ordearnings FROM orders WHERE payment_status = 'paid'");
    $ordearnings_row = mysqli_fetch_assoc($ordearnings_result);
    $total_ordearnings = $ordearnings_row['total_ordearnings'] ?? 0;

    $equipment_result = mysqli_query($con, "SELECT COUNT(*) AS equipment_count FROM equipment");
    $equipment_row = mysqli_fetch_assoc($equipment_result);
    $equipment_count = $equipment_row['equipment_count']; 
    $sql = "SELECT 
                user_name,
                COUNT(*) AS total_activities
            FROM (
                SELECT 
                    o.user_name
                FROM 
                    orders o
                WHERE 
                    o.payment_status = 'paid'
                UNION ALL
                SELECT 
                    s.name AS user_name
                FROM 
                    subscriptions s
            ) AS combined_activities
            GROUP BY user_name
            ORDER BY total_activities DESC
            LIMIT 20";

    $top_users_result = mysqli_query($con, $sql);
    $top_users = [];
    $rank = 1; // Initialize rank
    while ($row = mysqli_fetch_assoc($top_users_result)) {
        $row['rank'] = $rank; // Add rank to each user
        $top_users[] = $row;
        $rank++;
    }

    mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link
      href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="../css/common.css">

    <?php require('inc/links.php');?>

    <style>
        :root {
            --blue: #40534C;
            --blue-hover: #096066;
            --red: #ff0000;
        }

        .dashboard-box {
            padding: 5px;
            background-color: var(--blue);
            border-radius: 8px;
            text-align: center;
            transition: background-color 0.3s ease;
        }


        .dashboard-box:hover {
            background-color: var(--blue-hover);
        }

        .dashboard-link {
            text-decoration: none;
            color: inherit;
        }



        #header {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2.5rem 2rem;
}



h1 {
  font-family: "Rubik", sans-serif;
  font-size: 1.7rem;
  color: #141a39;
  text-transform: uppercase;
  cursor: default;

}

#leaderboard {
  width: 60%;
  position: relative;
  margin: 0 auto; /* Center horizontally */
}
table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
  color: #141a39;
  cursor: default;
}

tr {
  transition: all 0.2s ease-in-out;
  border-radius: 0.2rem;
}

tr:not(:first-child):hover {
  background-color: #fff;
  transform: scale(1.1);
  box-shadow: 0px 5px 15px 8px #e4e7fb;
}

tr:nth-child(odd) {
  background-color: #f9f9f9;
}

td {
  height: 5rem;
  font-family: "Rubik", sans-serif;
  font-size: 1.4rem;
  padding: 1rem 2rem;
  position: relative;
}

.number {
  width: 1rem;
  font-size: 2.2rem;
  font-weight: bold;
  text-align: left;
}

.name {
  text-align: left;
  font-size: 1.2rem;
}

.points {
  font-weight: bold;
  font-size: 1.3rem;
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

.gold-medal {
  height: 3rem;
  margin-left: 1.5rem;
}

.ribbon {
  width: 100%;
  height: 5.5rem;
  top: -0.5rem;
  background-color: #5c5be5;
  position: absolute;
  left: -1rem;
  box-shadow: 0px 15px 11px -6px #7a7a7d;
}

.ribbon::before, .ribbon::after {
  content: "";
  height: 1.5rem;
  width: 1.5rem;
  background-color: #5c5be5;
  position: absolute;
  z-index: -1;
}

.ribbon::before {
  bottom: -0.8rem;
  left: 0.35rem;
  transform: rotate(45deg);
}

.ribbon::after {
  bottom: -0.8rem;
  right: 0.35rem;
  transform: rotate(45deg);
}

#buttons {
  width: 100%;
  margin-top: 3rem;
  display: flex;
  justify-content: center;
  gap: 2rem;
}

.exit {
  width: 11rem;
  height: 3rem;
  font-family: "Rubik", sans-serif;
  font-size: 1.3rem;
  color: #7e7f86;
  border: 0;
  background-color: #fff;
  border-radius: 2rem;
  cursor: pointer;
}

.exit:hover {
  border: 0.1rem solid #5c5be5;
}

.continue {
  width: 11rem;
  height: 3rem;
  font-family: "Rubik", sans-serif;
  font-size: 1.3rem;
  color: #fff;
  background-color: #5c5be5;
  border: 0;
  border-bottom: 0.2rem solid #3838b8;
  border-radius: 2rem;
  cursor: pointer;
}

.continue:active {
  border-bottom: 0;
}

@media (max-width: 740px) {
    * {
      font-size: 70%;
    }
}

@media (max-width: 500px) {
    * {
      font-size: 55%;
    }
}

@media (max-width: 390px) {
    * {
      font-size: 45%;
    }
}

    </style>


</head>
<body class="bg-light">

<?php require('inc/header.php');?>
<div class="container-fluid" id="main-content">
    <div class="row">
        <div class="col-lg-10 ms-auto p-4 overflow-hidden">
            <h3 class="mb-4">DASHBOARD</h3>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="book.php" class="dashboard-link">
                        <div class="dashboard-box">
                            <h5>Today's Appointment</h5>
                            <i class="fa fa-calendar m-2" style="font-size:30px;"></i>
                            <p>Total Patients: <?php echo $today_booking_count; ?></p>
                        </div>
                    </a>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="invoices.php" class="dashboard-link">
                        <div class="dashboard-box">
                            <h5>Subscriber</h5>
                            <i class="fa fa-bell m-2" style="font-size:30px;"></i>
                            <p>Total Subscriber: <?php echo $subscribe_count; ?></p>
                             <p>Total Earn: ₱<?php echo number_format($total_earnings, 2); ?></p>
                        </div>
                    </a>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="products.php" class="dashboard-link">
                        <div class="dashboard-box">
                            <h5>Product</h5>
                            <i class="fa fa-book m-2" style="font-size:30px;"></i>
                            <p>Total Product: <?php echo $product_count; ?></p>
                        </div>
                    </a>
                </div>

                <!-- Patients Box -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="users.php" class="dashboard-link">
                        <div class="dashboard-box">
                            <h5>Member</h5>
                            <i class="fa fa-user m-2" style="font-size:30px;"></i>
                            <p>Total Member: <?php echo $patient_count; ?></p>
                        </div>
                    </a>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="product_sales.php" class="dashboard-link">
                        <div class="dashboard-box">
                            <h5>Order</h5>
                            <i class="fa fa-shopping-cart m-2" style="font-size:30px;"></i>
                            <p>Total order: <?php echo $order_count; ?></p>
                             <p>Total Earn: ₱<?php echo number_format($total_ordearnings, 2); ?></p>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="equipment.php" class="dashboard-link">
                        <div class="dashboard-box">
                            <h5>Equipment</h5>
                            <i class="fa fa-cogs m-2" style="font-size:30px;"></i> 
                            <p>Equipment: <?php echo $equipment_count; ?></p>
                        </div>
                    </a>
                </div>

                <div class="main">
    <div id="header">
        <h1>Top Engagers</h1>
    </div>
    <div id="leaderboard">
        <div class="ribbon"></div>
        <table>
            <?php 
            foreach ($top_users as $user) {
                $rankClass = 'rank-' . $user['rank']; // Create rank class
                ?>
                <tr class="<?php echo $rankClass; ?>">
                    <td class="number"><?php echo $user['rank']; ?></td>
                    <td class="name"><?php echo $user['user_name']; ?></td>
                    <td class="points">
                        <?php echo $user['total_activities']; ?>
                        <?php if ($user['rank'] == 1): ?>
                            <img class="gold-medal" src="https://github.com/malunaridev/Challenges-iCodeThis/blob/master/4-leaderboard/assets/gold-medal.png?raw=true" alt="gold medal"/>
                        <?php elseif ($user['rank'] == 2): ?>
                    
                        <?php elseif ($user['rank'] == 3): ?>
                          
                        <?php endif; ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
  
</div>


<?php require('inc/scripts.php'); ?>
<script src="scripts/dashboard.js"></script>
</body>
</html>
