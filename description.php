

<?php
ini_set('session.gc_maxlifetime', 3600); // Set session timeout to 1 hour
session_set_cookie_params(3600);
require('inc/links.php');
if (!isset($_SESSION['uId'])) {
    die("User not logged in");
}

// Get the logged-in user ID from the session
$userId = $_SESSION['uId'];

// Fetch user information from the database
$query = "SELECT name, email, address FROM user_cred WHERE user_id = ?";
if ($stmt = $con->prepare($query)) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($userName, $userEmail, $userAddress);
    $stmt->fetch();
    $stmt->close();
} else {
    die("Error fetching user information.");
}

// Initialize variables with default values
$plan = isset($_GET['plan']) ? $_GET['plan'] : 'Plan Not Found';
$amount = isset($_GET['price']) ? $_GET['price'] : '0';
$interval = isset($_GET['interval']) ? $_GET['interval'] : '';
$description = isset($_GET['description']) ? $_GET['description'] : 'Description not available';
$paymentId = '';

// Define feature sets for each plan type
$features = [
    'yearly' => [
        'Unlimited access to all gym facilities',
        'Personal training sessions (5 per month)',
        'Access to group fitness classes',
        'Free nutrition consultation',
        'Free guest passes for friends',
        'Priority booking for special events',
    ],
    'monthly' => [
        'Unlimited access to all gym facilities',
        'Access to group fitness classes',
        '1 personal training session per month',
        'Discount on nutrition products',
        'Monthly fitness assessment',
    ],
    'weekly' => [
        'Access to gym facilities for 1 week',
        'Access to select group fitness classes',
        '1 free fitness assessment',
        'Discount on merchandise in the gym',
        'Access to sauna and steam room',
    ],
];

// Choose the right set of features based on the plan
$selected_features = [];
if (strpos(strtolower($plan), 'yearly') !== false) {
    $selected_features = $features['yearly'];
} elseif (strpos(strtolower($plan), 'monthly') !== false) {
    $selected_features = $features['monthly'];
} elseif (strpos(strtolower($plan), 'weekly') !== false) {
    $selected_features = $features['weekly'];
}

// Xendit API credentials
$xendit_public_key = 'xnd_public_development_maQ2jWLOlWIh2xfLW9x6HooWyvmqlM7FeLPhbfg8pm779MW5Z9w3iIe9Pph6Mj'; 
$secret_api_key = 'xnd_development_vsjdbgeYHcsdOX1XlmpPLRHKoPCuLlmhQzKhMwIrG1nQH6FWe4TMI7MDWXqJLuaR';

// Function to generate a unique ID for the payment
function generatePaymentId() {
    return uniqid();
}

function calculateEndDate($plan) {
    $currentDate = new DateTime(); // Get the current date
    switch (strtolower($plan)) {
        case 'weekly':
            $currentDate->modify('+7 days');  // Weekly plan lasts 7 days
            break;
        case 'monthly':
            $currentDate->modify('+30 days'); // Monthly plan lasts 1 month
            break;
        case 'yearly':
            $currentDate->modify('+365 days');  // Yearly plan lasts 1 year
            break;
        default:
            $currentDate->modify('+1 day');   // If interval is unknown, default to 1 day (this can be adjusted)
            break;
    }
    return $currentDate->format('Y-m-d'); // Return formatted date
}

// Function to redirect to Xendit payment page
function redirectToXendit($amount, $plan, $interval, $description, $paymentId) {
    global $secret_api_key, $userName, $userEmail, $con, $userId;

    // Construct the Xendit API endpoint URL
    $endpoint = 'https://api.xendit.co/v2/invoices';

    // Prepare the request data
    $data = [
        'external_id' => $paymentId,
        'amount' => $amount,
        'description' => "Subscription for $plan plan ($interval) - $description",
        'customer_name' => $userName,
        'customer_email' => $userEmail,
        'success_redirect_url' => 'http://v8040gs8o4cgwkok8wo80csk.178.128.125.251.sslip.io/success.php?paymentId=' . $paymentId,
        'failure_redirect_url' => 'https://your-website.com/failure.php',
        'currency' => 'PHP',
    ];

    // Encode the data into JSON format
    $jsonData = json_encode($data);

    // Initialize cURL session
    $curl = curl_init();

    // Set cURL options
    curl_setopt_array($curl, [
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($secret_api_key . ':') 
        ],
    ]);

    // Execute the cURL request
    $response = curl_exec($curl);

    // Check for cURL errors
    if (curl_errno($curl)) {
        echo 'Error: ' . curl_error($curl);
    } else {
        curl_close($curl);

        $responseData = json_decode($response, true);

        // Check for successful response
        if (isset($responseData['id'])) {
            $endDate = calculateEndDate($plan); // Calculate the subscription end date

            // Store subscription details in the database
            $insertQuery = "INSERT INTO `subscriptions` (user_id, name, email, plan, price, `interval`, description, payment_id, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $con->prepare($insertQuery);
            $insertStmt->bind_param("issssssss", $userId, $userName, $userEmail, $plan, $amount, $interval, $description, $paymentId, $endDate); 
            $insertStmt->execute();
            $insertStmt->close();
    
            header('Location: ' . $responseData['invoice_url']);        
            exit;
        } else {
            echo 'Error: ' . $responseData['message'];
        }
    }
}

// Generate a unique payment ID
$paymentId = generatePaymentId();

// Handle form submission for confirming subscription
if (isset($_POST['confirm_subscription'])) {
    $amount = $_POST['price'];
    $plan = $_POST['plan']; 
    $interval = $_POST['interval'];
    $description = $_POST['description'];

    redirectToXendit($amount, $plan, $interval, $description, $paymentId);
}
ob_end_flush();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
    <style>
        /* Add some basic styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding-top: 0px;
        }

        .subscription-details {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 3rem 2rem;
            text-align: center;
        }

        .subscription-details h3 {
            font-size: 2rem;
        margin-bottom: 1rem;
        color: #000;
        font-weight: bold;
        }

        .subscription-details .price {
            font-size: 3rem;
            font-weight: bold;
            color: #0070f3;
            margin: 1rem 0;
        }

        .subscription-details .price span {
            font-size: 1.5rem;
            color: #777;
        }

        .subscription-details p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            color: #333;
        }

        .feature-list {
            text-align: left;
            margin-top: 2rem;
            font-size: 1.2rem;
            padding: 0;
            color: #000;
        }

        .feature-list li {
            list-style: none;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .feature-list li::before {
            content: "✓";
            color: #9CD02F;
            margin-right: 10px;
        }

        .subscription-details .btn {
            background-color: black;
            color: white;
            border: 1px solid black;
            margin-top: 1.5rem;
            padding: 0.75rem 2rem;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
        }

        .subscription-details .btn:hover {
            background-color: #096066;
            color: white;
            border-color: #096066;
        }

        /* Responsive */
        @media screen and (max-width: 768px) {
            .subscription-details {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>

<?php require('inc/header.php') ?>

<div class="subscription-details">
    <h2><?php echo htmlspecialchars($plan); ?> Plan</h2>
    <div class="price"><span>₱</span><?php echo htmlspecialchars($amount); ?><span>/<?php echo htmlspecialchars($interval); ?></span></div>
    <p><?php echo htmlspecialchars($description); ?></p>

    <!-- Features Section -->
    <ul class="feature-list">
        <?php foreach ($selected_features as $feature) : ?>
        <li><?php echo htmlspecialchars($feature); ?></li>
        <?php endforeach; ?>
    </ul>

    <!-- Display End Date -->

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <input type="hidden" name="price" value="<?php echo htmlspecialchars($amount); ?>">
        <input type="hidden" name="plan" value="<?php echo htmlspecialchars($plan); ?>">
        <input type="hidden" name="interval" value="<?php echo htmlspecialchars($interval); ?>">
        <input type="hidden" name="description" value="<?php echo htmlspecialchars($description); ?>">
        <button type="submit" name="confirm_subscription" class="btn">Confirm Subscription</button>
    </form>
</div>


<?php require('inc/footer.php') ?>

</body>
</html>
