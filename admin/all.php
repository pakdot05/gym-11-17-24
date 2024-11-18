<?php
    require('inc/essentials.php');
    require('inc/db_config.php');
    adminLogin();
 if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $sql = "SELECT * FROM orders WHERE order_id = $id";
    $result = mysqli_query($con, $sql);

    if (mysqli_num_rows($result) > 0) {
        // If found in orders, delete it
        $sql = "DELETE FROM orders WHERE order_id = $id";
        if (mysqli_query($con, $sql)) {
            echo 'Transaction deleted successfully from orders.';
        } else {
            echo 'Error deleting transaction from orders: ' . mysqli_error($con);
        }
    }
    $sql = "SELECT * FROM subscriptions WHERE id = $id";
    $result = mysqli_query($con, $sql);

    if (mysqli_num_rows($result) > 0) {
        // If found in subscriptions, delete it
        $sql = "DELETE FROM subscriptions WHERE id = $id";
        if (mysqli_query($con, $sql)) {
            echo 'Transaction deleted successfully from subscriptions.';
        } else {
            echo 'Error deleting transaction from subscriptions: ' . mysqli_error($con);
        }
    }
    if (mysqli_num_rows($result) === 0) {
        echo 'Invalid transaction ID.';
    }
    exit; 
}
$monthlySales = getMonthlySales($con);
$monthlyUserCounts = getMonthlyUserCounts($con); // New function to get user counts


function getMonthlySales($con) {
    $currentYear = date('Y');
    $salesData = array_fill(1, 12, 0); // Initialize with 0 for each month

    // Query for orders
    $sqlOrders = "SELECT 
                    MONTH(o.created_at) AS month,
                    SUM(o.price) AS total_sales
                FROM 
                    orders o
                WHERE 
                    o.payment_status = 'paid' AND YEAR(o.created_at) = $currentYear
                GROUP BY month";
    $resultOrders = mysqli_query($con, $sqlOrders);

    if (mysqli_num_rows($resultOrders) > 0) {
        while ($row = mysqli_fetch_assoc($resultOrders)) {
            $salesData[$row['month']] += $row['total_sales']; // Add to existing value
        }
    }

    // Query for subscriptions
    $sqlSubscriptions = "SELECT 
                        MONTH(s.created_at) AS month,
                        SUM(s.price) AS total_sales
                    FROM 
                        subscriptions s
                    WHERE 
                        YEAR(s.created_at) = $currentYear
                    GROUP BY month";
    $resultSubscriptions = mysqli_query($con, $sqlSubscriptions);

    if (mysqli_num_rows($resultSubscriptions) > 0) {
        while ($row = mysqli_fetch_assoc($resultSubscriptions)) {
            $salesData[$row['month']] += $row['total_sales']; // Add to existing value
        }
    }

    return $salesData;
}

// Function to retrieve monthly user counts
function getMonthlyUserCounts($con) {
    $currentYear = date('Y');
    $userCounts = array_fill(1, 12, 0); // Initialize with 0 for each month

    // Query for orders, counting unique usernames even if user_id is missing
    $sqlOrders = "SELECT 
                    MONTH(o.created_at) AS month,
                    COUNT(DISTINCT o.user_name) AS user_count 
                FROM 
                    orders o
                WHERE 
                    o.payment_status = 'paid' AND YEAR(o.created_at) = $currentYear
                GROUP BY month";
    $resultOrders = mysqli_query($con, $sqlOrders);

    if (mysqli_num_rows($resultOrders) > 0) {
        while ($row = mysqli_fetch_assoc($resultOrders)) {
            $userCounts[$row['month']] += $row['user_count'];
        }
    }

    // Query for subscriptions, counting unique usernames
    $sqlSubscriptions = "SELECT 
                        MONTH(s.created_at) AS month,
                        COUNT(DISTINCT s.name) AS user_count
                    FROM 
                        subscriptions s
                    WHERE 
                        s.payment_status = 'paid' AND YEAR(s.created_at) = $currentYear
                    GROUP BY month";
    $resultSubscriptions = mysqli_query($con, $sqlSubscriptions);

    if (mysqli_num_rows($resultSubscriptions) > 0) {
        while ($row = mysqli_fetch_assoc($resultSubscriptions)) {
            $userCounts[$row['month']] += $row['user_count'];
        }
    }

    return $userCounts;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment</title>
    <?php require('inc/links.php');?>
</head>
<body class="bg-light">
    <?php require('inc/header.php');?>
    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-10 ms-auto p-4 overflow-hidden">
                <h3 class="mb-4">All Transactions</h3>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                    <h3 class="mb-4">Monthly Sales Analytics</h3>

                    <canvas id="monthlySalesChart"></canvas>
                    <section class="search-section my-4">
            <form action="" method="get" class="search-form">
            <input type="text" id="searchInput" name="search" placeholder="Search..." class="form-control shadow-none w-25 ms-auto" >
            </form>
            </section>

                        <div class="table-responsive-md" style="height: 450px; overflow-y: scroll;">
                            <table class="table table-hover border">
                                <thead class="sticky-top">
                                    <tr class="bg-dark text-light ambot">
                                        <th scope="col">#</th>
                                        <th scope="col">Customer Name </th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Product/Plan</th>
                                        <th scope="col">Price</th>
                                        <th scope="col">Payment Method</th>
                                        <th scope="col">Payment Status</th>
                                        <th scope="col" class="text-center">Created At</th>  
                                        <th scope="col" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="users-data">
                                    <?php
                                        $search = isset($_GET['search']) ? $_GET['search'] : '';

                                        $sql = "SELECT 
                                                    o.order_id,
                                                    o.user_name,
                                                    o.user_email,
                                                    o.product_name,
                                                    o.price,
                                                    o.payment_method,
                                                    o.payment_status,
                                                    o.created_at
                                                FROM 
                                                    orders o
                                                WHERE 
                                                    o.payment_status = 'paid'
                                                UNION ALL
                                                SELECT 
                                                    s.id AS order_id,
                                                    s.name AS user_name,
                                                    s.email AS user_email,
                                                    s.plan AS product_name,
                                                    s.price,
                                                    CASE
                                                        WHEN s.payment_id LIKE '%Walk-in%' THEN 'Walk-in'
                                                        ELSE 'E-Wallet'
                                                    END AS payment_method,
                                                    'Paid' AS payment_status,
                                                    s.created_at
                                                FROM 
                                                    subscriptions s
                                                ORDER BY user_name ASC";

                                        if (!empty($search)) {
                                            $sql .= " AND (
                                                        o.user_name LIKE '%$search%' OR
                                                        o.user_email LIKE '%$search%' OR
                                                        o.product_name LIKE '%$search%' OR
                                                        s.name LIKE '%$search%' OR
                                                        s.email LIKE '%$search%' OR
                                                        s.plan LIKE '%$search%'
                                                    )";
                                        }

                                        $result = mysqli_query($con, $sql);

                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                ?>
                                                <tr>
                                                    <td><?php echo $row['order_id']; ?></td>
                                                    <td><?php echo $row['user_name']; ?></td>
                                                    <td><?php echo $row['user_email']; ?></td>
                                                    <td><?php echo $row['product_name']; ?></td>
                                                    <td><?php echo $row['price']; ?></td>
                                                    <td><?php echo $row['payment_method']; ?></td>
                                                    <td>
                                                        <span class="badge bg-success">
                                                            <?php echo $row['payment_status']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center"><?php echo $row['created_at']; ?></td>
                                                    <td class="text-center">
                                                        <a href="#" class="btn btn-sm btn-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                           data-id="<?php echo $row['order_id']; ?>"
                                                           data-name="<?php echo $row['user_name']; ?>">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            ?>
                                            <tr>
                                                <td colspan="9" class="text-center">No transactions found.</td>
                                            </tr>
                                            <?php
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this transaction? This action cannot be undone.</p>
                    <input type="hidden" id="delete-id">
                    <input type="hidden" id="delete-name">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="deleteTransaction()">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <?php require('inc/scripts.php');?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Get references to the modal input fields
        const deleteIdInput = document.getElementById('delete-id');
        const deleteNameInput = document.getElementById('delete-name');
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-id');
                const userName = this.getAttribute('data-name');
                deleteIdInput.value = orderId;
                deleteNameInput.value = userName;
            });
        });
        function deleteTransaction() {
            const id = deleteIdInput.value;
            const name = deleteNameInput.value;
            $.ajax({
                url: '', // Same file handles the delete
                method: 'POST',
                data: { id: id },  // Send only the ID to the server for deletion
                success: function(response) {
                    console.log('Transaction deleted successfully: ' + response);
                    $('#deleteModal').modal('hide'); // Close the modal
                    location.reload(); // Reload the page to update the table
                },
                error: function(error) {
                    console.error('Error deleting transaction: ' + error);
                }
            });
        }
            window.onload = function() {
        const monthlySalesChart = document.getElementById('monthlySalesChart').getContext('2d');
        const chart = new Chart(monthlySalesChart, {
            type: 'bar',
            data: {
                
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    {
                        label: 'Monthly Sales',
                        data: <?php echo json_encode(array_values($monthlySales)); ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)',
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    },
                    {
                        label: 'Monthly Users',
                        data: <?php echo json_encode(array_values($monthlyUserCounts)); ?>,
                        backgroundColor: 'rgba(0, 123, 255, 0.5)', // Add a distinct color for user count
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1,
                        type: 'line', // Make this dataset a line chart to overlay over the bars
                        fill: false // Don't fill the line
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.parsed.y.toLocaleString('en-US');
                            }
                        }
                    },
                    title: {
                        display: true,
                        font: {
                            size: 14,
                            weight: 'bold'
                        },
                        callback: function(context) {
                            const totalPaid = context.chart.data.datasets[0].data[context.dataIndex];
                            return '₱' + totalPaid.toLocaleString('en-US');
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: function(value, context) {
                            // Get the user count from the second dataset (line chart)
                            const userCount = context.chart.data.datasets[1].data[context.dataIndex];
                            return userCount > 0 ? userCount : ''; // Only display if userCount is greater than 0
                        },
                        color: 'black',
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            }
        });
    };
    const searchInput = document.getElementById('searchInput');
const usersData = document.getElementById('users-data');

searchInput.addEventListener('input', function () {
    const query = searchInput.value;

    // Use AJAX to send the search query to the server
    const xhr = new XMLHttpRequest();
    xhr.open('GET', search_users.php?search=${encodeURIComponent(query)}, true);

    xhr.onload = function () {
        if (xhr.status === 200) {
            usersData.innerHTML = xhr.responseText;
        } else {
            console.error('Error fetching search results');
        }
    };

    xhr.send();
});

    </script>
</body>
</html>
