<?php
require('inc/db_config.php');

// Retrieve the search query from the request
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base SQL query with aliases for uniform column names
$sql = "SELECT 
            o.order_id AS id,
            o.user_name AS customer_name,
            o.user_email AS email,
            o.product_name AS product_plan,
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
            s.id AS id,
            s.name AS customer_name,
            s.email AS email,
            s.plan AS product_plan,
            s.price,
            CASE
                WHEN s.payment_id LIKE '%Walk-in%' THEN 'Walk-in'
                ELSE 'E-Wallet'
            END AS payment_method,
            'Paid' AS payment_status,
            s.created_at
        FROM 
            subscriptions s";

// Add search filter if a query is provided
if (!empty($search)) {
    $sql = "SELECT * FROM ($sql) AS combined_data WHERE 
            customer_name LIKE '%$search%' OR
            email LIKE '%$search%' OR
            product_plan LIKE '%$search%'";
}

$sql .= " ORDER BY customer_name ASC";

$result = mysqli_query($con, $sql);

// Check if there are results
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['customer_name']}</td>
            <td>{$row['email']}</td>
            <td>{$row['product_plan']}</td>
            <td>{$row['price']}</td>
            <td>{$row['payment_method']}</td>
            <td>
                <span class='badge bg-success'>{$row['payment_status']}</span>
            </td>
            <td class='text-center'>{$row['created_at']}</td>
            <td class='text-center'>
                <a href='#' class='btn btn-sm btn-danger delete-btn' data-bs-toggle='modal' data-bs-target='#deleteModal'
                   data-id='{$row['id']}'
                   data-name='{$row['customer_name']}'>
                    <i class='bi bi-trash'></i>
                </a>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='9' class='text-center'>No transactions found.</td></tr>";
}
?>
