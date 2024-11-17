<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Pricing</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <?php require('inc/links.php') ?>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .pricing {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin: 2rem 0;
        }
        .pricing .plan {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .pricing .plan h3 {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: #000;
        font-weight: bold;

        }
        .pricing .plan .price {
            font-size: 3rem;
            font-weight: bold;
            color: #0070f3;
            margin: 1rem 0;
        }
        .pricing .plan .price span {
            font-size: 1.5rem;
            color: #777;
        }
        .pricing .plan .list p {
            font-size: 1.2rem;
            color: #000;
        }
        .pricing .plan .btn {
            background-color: black;
            color: white;
            border: 1px solid black;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            text-transform: uppercase;
            font-weight: bold;
            cursor: pointer;
        }
        .pricing .plan .btn:hover {
            background-color: #096066;
            color: white;
            border-color: #096066;
        }
        .pricing .plan .btn.disabled {
            background-color: #ccc;
            color: #666;
            border-color: #ccc;
            cursor: not-allowed;
        }
        .custom-modal {
    background-color: #ff4d4d !important; /* Red background */
    color: white !important; /* White text */
    border-radius: 10px !important;
    border: 2px solid #cc0000 !important; /* Darker red border */
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.4) !important;
}

.custom-modal .modal-body {
    font-size: 1.2rem !important;
    font-weight: 600 !important;
    text-align: center !important;
    padding: 30px 20px !important;
    color: white !important; /* Ensures all text is white */
}


        @media screen and (max-width: 768px) {
            .pricing {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <?php require('inc/header.php') ?>

    <div class="container">
        <div class="my-5 px-4">
            <h2 class="fw-bold text-center">Gym Pricing Plans</h2>
            <div class="h-line bg-dark"></div>
        </div>

        <section class="pricing" id="pricing">
            <?php
            // Check if the site is in shutdown mode
            if ($settings_r['shutdown']) {
                echo "<p class='text-center text-danger'>The gym is currently shut down. Subscription is not available.</p>";
            } else {
                // Check if the user is logged in
                $login = false;
                $activePlans = []; // Array to hold the user's active plans

                if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
                    $login = true;

                    // Fetch user subscription status from the database
                    $userId = $_SESSION['uId']; // Assuming user ID is stored in session
                    $query = "SELECT plan FROM subscriptions WHERE user_id = ? AND end_date > NOW()"; // No LIMIT, fetch all active subscriptions
                    if ($stmt = $con->prepare($query)) {
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $stmt->bind_result($plan);
                        while ($stmt->fetch()) {
                            $activePlans[] = $plan; // Store active plans in the array
                        }
                        $stmt->close();
                    }
                }

                // Debugging: Output active plans
                // Uncomment the following line to see the active plans in your HTML output for debugging
                // echo "<p>Active Plans: " . implode(", ", $activePlans) . "</p>";

                // Define plans with their prices and descriptions
                $plans = [
                    'Weekly' => ['price' => '175', 'interval' => 'week', 'description' => 'Unlock a week of unlimited access to our gym, with additional perks like diet plans and group sessions.'],
                    'Monthly' => ['price' => '500', 'interval' => 'mo', 'description' => 'Commit to a month of full access, including personalized training, diet planning, and progress tracking.'],
                    'Yearly' => ['price' => '9000', 'interval' => 'yr', 'description' => 'Enjoy a full year of unlimited access with additional benefits, including exclusive workshops and premium support.']
                ];
                foreach ($plans as $title => $details) {
                    $price = $details['price'];
                    $interval = $details['interval'];
                    $description = $details['description'];
                    $hasActiveSubscription = !empty($activePlans);

                    if ($login) {
                        if ($hasActiveSubscription) {
                            $buttonText = "<button class='btn subscribe-btn' data-plan='$title' data-price='$price' data-interval='$interval' data-description='$description' data-active='true'>Subscribe</button>";
                        } else {
                            $buttonText = "<button class='btn subscribe-btn' data-plan='$title' data-price='$price' data-interval='$interval' data-description='$description' data-active='false'>Subscribe</button>";
                        }
                    } else {
                        $buttonText = "<button class='btn login-subscribe'>Subscribe</button>";
                    }

                    echo <<<HTML
                    <div class="plan">
                        <h3>$title</h3>
                        <div class="price"><span>₱</span>$price<span>/$interval</span></div>
                        <div class="list">
                            <p>$description</p>
                        </div>
                        $buttonText
                    </div>
                    HTML;
                }
            }
            ?>
        </section>
    </div>

    <div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content custom-modal">
            <div class="modal-body" id="modalMessage"></div>
        </div>
    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    
    <script>
       $(document).ready(function () {
    $('.subscribe-btn').click(function () {
        var hasActiveSubscription = $(this).data('active') === true;
        var plan = $(this).data('plan') || 'Unknown Plan';
        var price = $(this).data('price') || 'Unknown Price';
        var interval = $(this).data('interval') || 'Unknown Interval';
        var description = $(this).data('description') || 'No Description Available';

        if (hasActiveSubscription) {
            // Show modal message if there's an active subscription
            $('#modalMessage').text("You already have an active subscription. Please wait until it ends.");
            $('#messageModal').modal('show');
        } else {
            // Redirect immediately if no active subscription
            var redirectUrl = `description.php?plan=${encodeURIComponent(plan)}&price=${encodeURIComponent(price)}&interval=${encodeURIComponent(interval)}&description=${encodeURIComponent(description)}`;
            window.location.href = redirectUrl;
        }
    });

    $('.login-subscribe').click(function () {
        var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
    });
});

    </script>
    
    <?php require('inc/footer.php') ?>
</body>
</html>