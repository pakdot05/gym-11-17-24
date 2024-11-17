<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
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
                window.location.href = "product.php"; // Replace with the actual product page URL
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

<?php 
  
   session_start();

   
   // If session data was saved during the payment process
   if (isset($_SESSION['sessionData'])) {
       // Restore session data
       $_SESSION = $_SESSION['sessionData']; 
       unset($_SESSION['sessionData']); // Remove the temporary storage

       // Get the payment ID from the URL (assuming it's passed by the payment gateway)
       $paymentId = isset($_GET['paymentId']) ? $_GET['paymentId'] : '';

       // Update the payment status in the database
       if (!empty($paymentId)) {
           $updateQuery = "UPDATE `orders` SET payment_status = 'success' WHERE payment_id = ?";
           $updateStmt = $con->prepare($updateQuery);
           $updateStmt->bind_param("s", $paymentId);
           $updateStmt->execute();
           $updateStmt->close();
       }
   }
?>

<div class="container">
    <h2>Payment Successful!</h2>
    <p>Your product has been successfully paid. Thank you for your purchase!</p>
    <p>We appreciate your business and hope you enjoy your item!</p>
    <p>You will be redirected to the product page in <span class="countdown" id="countdown">3</span> seconds.</p>
</div>

</body>
</html>
