<?php 
session_start();
?>
<?php

require('inc/links.php'); // Include your database connection and other necessary files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$paymentId = isset($_GET['paymentId']) ? $_GET['paymentId'] : '';

// Check if the payment ID is valid
if (empty($paymentId)) {
    // Handle the case where payment ID is missing or invalid
    die("Invalid payment ID.");
}

// Fetch the user information from the database (using the payment ID)
$query = "SELECT user_id, name, email, plan, price, `interval`, end_date FROM subscriptions WHERE payment_id = ?";
if ($stmt = $con->prepare($query)) {
    $stmt->bind_param("s", $paymentId);
    $stmt->execute();
    $stmt->bind_result($userId, $userName, $userEmail, $plan, $amount, $interval, $endDate);
    
    // Check if fetch was successful
    if ($stmt->fetch()) {
        // Data successfully fetched
    } else {
        die("No data found for this payment ID.");
    }
    
    $stmt->close();
} else {
    die("Error fetching subscription details.");
}


// Update the payment status in the database
$updateQuery = "UPDATE `subscriptions` SET payment_status = 'success' WHERE payment_id = ?";
$updateStmt = $con->prepare($updateQuery);
$updateStmt->bind_param("s", $paymentId);
$updateStmt->execute();
$updateStmt->close();
// Send the confirmation email
require_once './phpmailer/src/Exception.php';
require_once './phpmailer/src/PHPMailer.php';
require_once './phpmailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'lorem.ipsum.sample.email@gmail.com'; // Replace with your Gmail username
    $mail->Password   = 'tetmxtzkfgkwgpsc'; // Replace with your Gmail password
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    // Recipients
    $mail->setFrom('lorem.ipsum.sample.email@gmail.com', 'Geafitnessgym');
    $mail->addAddress($userEmail); // Use the email fetched from the database
    $mail->addReplyTo('lorem.ipsum.sample.email@gmail.com', 'Geafitnessgym');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Geafitnessgym Subscription Confirmation';
    $mail->Body = "
          <p>Dear {$userName},</p>

        <p>Thank you for subscribing to the {$plan} plan at Geafitnessgym!</p>

        <p><strong>Your subscription details:</strong></p>

        <ul>
            <li><strong>Plan:</strong> {$plan}</li>
            <li><strong>Interval:</strong> {$interval}</li>
            <li><strong>Amount:</strong> ₱{$amount}</li>
            <li><strong>End Date:</strong> {$endDate}</li>
        </ul>

        <p>You can now enjoy all the benefits of your subscription!</p>

        <p>Thank you,</p>

        <p>Gymko Team</p>
    ";

    $mail->send();


} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Successful</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding-top: 50px;
            text-align: center;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 3rem 2rem;
        }

        h2 {
            color: #0070f3;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }

        p {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .countdown {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0070f3;
        }

        .btn-home {
            background-color: black;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            text-transform: uppercase;
            border-radius: 5px;
            transition: background-color 0.3s;
            text-decoration: none;
        }

        .btn-home:hover {
            background-color: #333;
        }
    </style>
    <script>
        let countdown = 3; // Countdown start
        function updateCountdown() {
            document.getElementById('countdown').innerText = countdown;
            countdown--;

            if (countdown < 0) {
                // Redirect after countdown reaches zero
                window.location.href = "pricing.php"; // Replace "account.php" with the actual account page URL
            } else {
                // Update the countdown every second
                setTimeout(updateCountdown, 1000);
            }
        }

        // Start the countdown when the page loads
        window.onload = function() {
            updateCountdown();
        }
    </script>
</head>
<body>

<div class="container">
    <h2>Subscription Successful!</h2>
    <p>Your subscription has been successfully processed. You can now enjoy all the benefits of your membership.</p>
    <p>Thank you for choosing our gym!</p>
    <p>You will be redirected to your account in <span class="countdown" id="countdown">3</span> seconds.</p>
</div>

</body>
</html>
