<?php
require('inc/db_config.php');

// Handle the form submission to add a new subscription
if (isset($_POST['add_subscription'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = isset($_POST['email']) ? mysqli_real_escape_string($con, $_POST['email']) : null;
    $price = floatval($_POST['price']);
    $plan = 'day'; // Set to 'day'
    $interval = 'none'; // Set to 'none'
    $description = 'none'; // Set to 'none'
    $payment_status = 'complete'; // Set to 'complete'
    $created_at = date('Y-m-d'); // Set to current date
    $end_date = date('Y-m-d', strtotime('+1 day')); // Set end date 1 day later

    // Insert into subscriptions table
    $insert_query = "INSERT INTO subscriptions (user_id, name, email, plan, price, `interval`, description, payment_id, payment_status, created_at, end_date)
    VALUES (0, ?, ?, ?, ?, ?, ?, 'WALK-IN', ?, ?, ?)";

$stmt = mysqli_prepare($con, $insert_query);
mysqli_stmt_bind_param($stmt, 'sssdsdsss', $name, $email, $plan, $price, $interval, $description, $payment_status, $created_at, $end_date);

    if (mysqli_stmt_execute($stmt)) {
        header('Location: invoices.php?message=Subscription added successfully');
        exit;
    } else {
        header('Location: invoices.php?error=Failed to add subscription');
        exit;
    }
}

// Handle deletion request before any HTML is output
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    // Delete query
    $delete_query = "DELETE FROM subscriptions WHERE id = ?";
    $delete_stmt = mysqli_prepare($con, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, 'i', $delete_id);

    if (mysqli_stmt_execute($delete_stmt)) {
        // Redirect with success message
        header('Location: invoices.php?message=Subscription deleted successfully');
        exit;
    } else {
        // Redirect with error message
        header('Location: invoices.php?error=Failed to delete subscription');
        exit;
    }
}

// Query to select all records from 'subscriptions' table
$query = "SELECT * FROM subscriptions";
$result = mysqli_query($con, $query); // Execute query

// Initialize total price and plan counts
$total_price = 0;
$plans = []; // To hold counts of each plan

// Check if records exist
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $total_price += $row["price"]; // Accumulate total price
        
        // Count the occurrences of each plan
        $plan = $row["plan"];
        if (!isset($plans[$plan])) {
            $plans[$plan] = 0;
        }
        $plans[$plan]++;
    }
}
// Query to select all records from 'subscriptions' table
$query = "SELECT * FROM subscriptions";
$result = mysqli_query($con, $query); // Execute query

// Initialize total price and plan counts
$total_price = 0;
$plans = []; // To hold counts of each plan

// Check if records exist
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $total_price += $row["price"]; // Accumulate total price
        
        // Count the occurrences of each plan
        $plan = $row["plan"];
        if (!isset($plans[$plan])) {
            $plans[$plan] = 0;
        }
        $plans[$plan]++;
    }
}

$currentYear = date("Y"); // Get the current year
$subscriptions = []; // This will hold the filtered subscription data
$sql = "SELECT * FROM subscriptions WHERE YEAR(created_at) = ? OR YEAR(end_date) = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $currentYear, $currentYear); // Bind the current year to the query parameters
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fetch the results
while ($row = mysqli_fetch_assoc($result)) {
    $subscriptions[] = $row;
}

// Group the data by plan name
$plans = [];
foreach ($subscriptions as $subscription) {
    $plans[$subscription['plan']] = isset($plans[$subscription['plan']]) ? $plans[$subscription['plan']] + 1 : 1;
}

// Pass the filtered data to JavaScript
echo '<script>';
echo 'var plans = ' . json_encode($plans) . ';';
echo '</script>';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription</title>
    <?php require('inc/links.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js -->
    <style>
        /* Center the chart container */
        .chart-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 300px;
            margin-bottom: 20px;
        }
        #analyticsChart {
            width: 600px !important;
            height: 300px !important;
        }
    </style>
</head>
<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-10 ms-auto p-4 overflow-hidden">
                <h3 class="mb-4">Invoices</h3>

                <!-- Show success/error messages -->
                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($_GET['message']) ?>
                    </div>
                <?php elseif (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <!-- Total Price Section -->
                <div class="mb-4">
                    <h5>Total Price of Subscriptions:</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th>Total Earn</th>
                            <td id="totalPriceCell">₱<?= number_format($total_price, 2) ?></td>
                        </tr>
                    </table>
                </div>

                <div class="chart-container">

                <canvas id="analyticsChart"></canvas>
</div>
<div class="text-end mb-4">
    <button class="btn btn-dark" data-toggle="modal" data-target="#addSubscriptionModal">
        Add Subscription
    </button>
</div>
                <div class="text-end mb-4">
                    <input type="text" oninput="search_user(this.value)" class="form-control shadow-none w-25 ms-auto" placeholder="Search...">
                </div>

<div class="modal fade" id="addSubscriptionModal" tabindex="-1" role="dialog" aria-labelledby="addSubscriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="invoices.php" onsubmit="return validateForm();">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSubscriptionModalLabel">Add Subscription</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email (optional)</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" value="35" readonly>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount Paid</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required oninput="calculateChange()">
                    </div>
                    <div class="form-group" id="changeDisplay" style="display:none;">
                        <label for="change">Change</label>
                        <input type="text" class="form-control" id="change" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="add_subscription">Add Subscription</button>
                </div>
            </form>
        </div>
    </div>
</div>
                <div class="table-responsive-md" style="height: 450px; overflow-y: scroll;">
                    <table class="table table-hover border" id="gymTable">
                        <thead class="sticky-top">
                            <tr class="bg-dark text-light">
                                <th scope="col">#</th>
                                <th scope="col">Gym-goer</th>
                                <th scope="col">Email</th>
                                <th scope="col">Plan</th>
                                <th scope="col">Price</th>
                                <th scope="col">StartDate</th>
                                <th scope="col">EndDate</th>
                                <th scope="col">Status</th> <!-- New Status Column -->
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
    <?php
    mysqli_data_seek($result, 0); // Rewind the result set for table display

    // Check if records exist again for table display
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Determine if the subscription is active or expired
            $end_date = strtotime($row['end_date']);
            $current_date = time();
            $status = ($end_date > $current_date) ? 'Active' : 'Expired';
            $status_color = ($status === 'Active') ? 'green' : 'red'; // Set status color
    ?>
    <tr>
        <th scope="row"><?= htmlspecialchars($row["id"]) ?></th>
        <td><?= htmlspecialchars($row["name"]) ?></td>
        <td><?= htmlspecialchars($row["email"]) ?></td>
        <td><?= htmlspecialchars($row["plan"]) ?></td>
        <td>₱<?= htmlspecialchars($row["price"]) ?></td>
        <td><?= htmlspecialchars($row["created_at"]) ?></td>
        <td><?= htmlspecialchars($row["end_date"]) ?></td>
        <td style="color: <?= $status_color ?>; font-weight: bold;"><?= $status ?></td> <!-- Status Column -->
        <td>
            <div class="action-button">
                <!-- Delete button is always enabled, regardless of status -->
                <button class="btn btn-danger delete-button" 
                        onclick="deleteSubscription(<?= htmlspecialchars($row['id']) ?>)">
                    Delete
                </button>
            </div>
        </td>
    </tr>
    <?php
        }
    } else {
        echo "<tr><td colspan='8' class='text-center'>No records found</td></tr>";
    }
    ?>
</tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    <script>
        // Function to delete subscription
        function deleteSubscription(id) {
            if (confirm('Are you sure you want to delete this subscription?')) {
                window.location.href = 'invoices.php?delete=' + id; // Redirect with delete parameter
            }
        }
        function calculateChange() {
        const price = parseFloat(document.getElementById('price').value);
        const amount = parseFloat(document.getElementById('amount').value);
        const changeField = document.getElementById('changeDisplay');
        const changeInput = document.getElementById('change');

        if (!isNaN(amount)) {
            if (amount >= price) {
                const change = amount - price;
                changeField.style.display = 'block'; // Show the change field
                changeInput.value = change.toFixed(2); // Display change
            } else {
                changeField.style.display = 'none'; // Hide change field if no extra amount
                changeInput.value = ''; // Clear change field
            }
        }
    }

    function validateForm() {
        const price = parseFloat(document.getElementById('price').value);
        const amount = parseFloat(document.getElementById('amount').value);

        if (amount < price) {
            alert("The amount paid is less than the price. Please enter at least the exact amount.");
            return false;
        }

        return true; // Proceed with form submission
    }

    function renderAnalyticsChart() {
    const plans = <?= json_encode($plans) ?>; // Get plans data from PHP
    const planNames = Object.keys(plans);
    const planCounts = Object.values(plans);

    const ctx = document.getElementById('analyticsChart').getContext('2d');
    const analyticsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: planNames,
            datasets: [{
                label: 'Number of Subscriptions',
                data: planCounts,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                // Add labels on top of the bars
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    formatter: function(value, context) {
                        return context.chart.data.datasets[0].data[context.dataIndex]; // Display count
                    },
                    color: 'black',
                    font: {
                        weight: 'bold'
                    }
                }
            }
        }
    });
}

function search_user(searchTerm) {
    const table = document.getElementById('gymTable');
    const rows = table.getElementsByTagName('tr');

    // Loop through each row in the table
    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let matchFound = false;

        // Loop through each cell in the row
        for (let j = 0; j < cells.length; j++) {
            const cellValue = cells[j].textContent.toLowerCase();
            if (cellValue.includes(searchTerm.toLowerCase())) {
                matchFound = true;
                break;
            }
        }

        // Show or hide the row based on the match
        rows[i].style.display = matchFound ? '' : 'none';
    }
}

// Add event listener to the search input field
const searchInput = document.querySelector('input[oninput="search_user(this.value)"]');
searchInput.addEventListener('input', function() {
    search_user(this.value);
});

// Call the render function after the page loads
window.onload = renderAnalyticsChart;

    </script>

    <?php require('inc/scripts.php'); ?>
</body>
</html>
