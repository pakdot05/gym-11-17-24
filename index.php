<?php 
$_SESSION['login'] = true;
echo $_SESSION['login'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYM</title> 
    <link rel="stylesheet"href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

    <!-- LINKS -->

    <?php 
        require('inc/links.php')
    ?> 

    <style>

            .availability-form{
            margin-top: -50px;
            z-index: 2;
            position: relative;
            }

            @media screen and (max-width: 575px) {
            .availability-form{
            margin-top: 25px;
            padding: 0 35px;
            }
            }

            .navbar{

            background-color: #335272;
            }

            nav .navbut{

            color: white !important;

            }

            nav a:hover{

            color: #21364b !important;

            }

            .d-flex button{

            background-color: white;
            color: #335272;
            border: 1px solid #335272;
            font-weight: 500;


            }

            .d-flex button:hover{

            background-color: #335272;
            color: white;
            border: 1px solid #57718b; 

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
            color: #000; /* Changed to black */
            }

            .pricing .plan .price {
            font-size: 3rem;
            font-weight: bold;
            color: #0070f3; /* Changed to blue */
            margin: 1rem 0;
            }

            .pricing .plan .price span {
            font-size: 1.5rem;
            color: #777; /* Retained as is for currency symbol */
            }

            .pricing .plan .list p {
            font-size: 1.2rem;
            color: #000; /* Changed to black */
            }

            .pricing .plan .list p i {
            color: #9CD02F; /* Retained as is for icons */
            }


            .pricing .plan .btn {
            background-color: #335272;
            color: white;
            border: 1px solid #335272;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            text-transform: uppercase;
            font-weight: bold;
            }

            .pricing .plan .btn:hover {
            background-color: #9CD02F;
            color: white;
            border-color: #9CD02F;
            }

            /* Responsive */
            @media screen and (max-width: 768px) {
            .pricing {
            grid-template-columns: 1fr;
            }
            }



            .bg_container{

            background: linear-gradient( rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5) ), url('images/carousel/2nd.jpg');
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            backdrop-filter: brightness(50%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;

            }

            .bgi{
            position: relative;
            width: auto;


            }

            .bgi h1{
            text-align: center;
            color: white;
            font-size: 80px;
            }

            .caro {
            height: 500px; /* Ensure this height is set to match your design */
            display: flex;
            justify-content: center;
            align-items: center;
            }

            .caro img {
            width: 100%;          /* Make the image take the full width */
            height: 100%;         /* Make the image take the full height */
            object-fit: cover;    /* Maintain aspect ratio while covering the area */
            }


            .btn{
            background-color: #393E46 !important;
            border: 1px solid #393E46 !important;
            }

            .btn:hover{
            background-color: #096066 !important;
            }

            .btn1{
            background-color: #393E46 !important;
            border: 1px solid #09858d !important;
            color: #09858d !important;
            text-decoration: none !important;
            }

            .btn1:hover{
            background-color: #096066 !important;
            color: white !important;
            }
/* Set a fixed height for the entire swiper container */
.swiper-container {
    height: 100vh; /* Adjust height as needed */
}

.swiper-slide {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%; /* Ensures swiper slides fill the container */
}

.carousel-img {
    width: 100%;
    height: 100%; /* Ensure images take up full height of container */
    object-fit: cover; /* Maintain aspect ratio and cover area */
    object-position: center; /* Center the image within the container */
}

    </style>

</head>
<body class="bg-white">

    <!-- HEADER/NAVBAR -->
    
    <?php require('inc/header.php') ?> 

    <!-- TEMPORARY SHIZ -->

    <div class="bg_container px-lg-0 pb-5 shadow">
        <div class="bgi">
            <h1 class="text-center fw-bold">GEA FITNESS GYM</h1>
            <p class="text-center text-white">Rise above the ordinary, achieve the extraordinary.</p>
        </div>
    </div>
    <h2 class="mt-5 pt-4 mb-4 text-center fw-bold">Plan</h2>
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
                    'Weekly' => ['price' => '245', 'interval' => 'week', 'description' => 'Unlock a week of unlimited access to our gym, with additional perks like diet plans and group sessions.'],
                    'Monthly' => ['price' => '999', 'interval' => 'mo', 'description' => 'Commit to a month of full access, including personalized training, diet planning, and progress tracking.'],
                    'Yearly' => ['price' => '10000', 'interval' => 'yr', 'description' => 'Enjoy a full year of unlimited access with additional benefits, including exclusive workshops and premium support.']
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

    <div class="container-fluid px-lg-4 mt-4">

        <div class="swiper swiper-container">
            <div class="swiper-wrapper">

                <?php 
                    $res = selectAll('carousel');

                    while($row = mysqli_fetch_assoc($res))
                    {
                        $path = CAROUSEL_IMG_PATH;
                        echo <<<data
                            <div class="caro swiper-slide">
                                <img src="$path$row[image]" class="w-100 d-block">
                            </div>
                        data;
                    }
                ?>

            </div>
        </div>
    </div>

    <h2 class="mt-5 pt-4 mb-4 text-center fw-bold">Trainor</h2>

    <div class="container px-4">
        <div class="swiper mySwiper">
            <div class="swiper-wrapper mb-5">

                <?php
                    $trainor_res = select("SELECT * FROM `trainor` WHERE `status`=? AND `removed`=?",[1,0],'ii');

                    while($trainor_data = mysqli_fetch_assoc($trainor_res))
                    {
                        $spec_q = mysqli_query($con,"SELECT s.name FROM `specialty` s 
                            INNER JOIN `trainor_specialty` dspec ON s.specialty_id = dspec.specialty_id 
                            WHERE dspec.doc_id = '$trainor_data[trainor_id]'");

                        $specialty_data = "";
                        while($spec_row = mysqli_fetch_assoc($spec_q)){
                            $specialty_data .="<span class='badge rounded-pill bg-light text-dark text-wrap'>
                                $spec_row[name]
                            </span>";
                        }

                        $app_btn = "";

                        if(!$settings_r['shutdown']){
                            $login=0;
                            if(isset($_SESSION['login']) && $_SESSION['login']==true){
                                $login=1;
                            }
                            $app_btn = "<button onclick='checkLoginToApp($login,$trainor_data[trainor_id])' class='btn btn-sm text-white shadow-none'>Make a Book</button>";
                        }

                        $trainor_thumb=TRAINORS_IMG_PATH."IMG_11728.jpg";
                        $thumb_q = mysqli_query($con, "SELECT * FROM `trainor_images` WHERE `trainor_id`='$trainor_data[trainor_id]' AND `thumb`='1'");
                        if (mysqli_num_rows($thumb_q) > 0) {
                            $thumb_res = mysqli_fetch_assoc($thumb_q);
                            $trainor_thumb = TRAINORS_IMG_PATH . $thumb_res['image'];
                        } else {
                            $trainor_thumb = TRAINORS_IMG_PATH . 'default.jpg'; // Provide a default image path if no thumbnail is found
                        }

                        echo<<<data
                        <div class="swiper-slide bg-white overflow-hidden rounded shadow">
                            <div class="row">
                                <div class="col-lg-12 col-md-6">
                                    <div class="card border-0 shadow" style="max-width: 350rem; margin: auto; height: 600px; display: flex; flex-direction: column;">
                                        <img src="$trainor_thumb" class="card-img-top" style="height: 350px; background-size: cover;"> <!-- PHOTO OF TRAINOR -->
                                        <div class="card-body d-flex flex-column justify-content-between bg-white">
                                            <div>
                                                <h3>$trainor_data[name]</h3>
                                                <div class="features mb-4 "> <!-- Set max-height and overflow hidden -->
                                                    <h5 class="mb-1 mt-3">Services</h5>
                                                    $specialty_data
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-evenly mb-2">
                                                $app_btn
                                                <a href="trainor_details.php?trainor_id=$trainor_data[trainor_id]" class="btn1 btn-sm custom-out shadow-none">View Trainor</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    
                        data;
                    }
                ?>

            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>



    <!-- REACH US -->

    <h2 class="mt-5 pt-4 mb-4 text-center fw-bold">REACH US</h2>
    
    <div class="container">

        <?php 
            $contact_q = "SELECT * FROM `contact_details` WHERE `contact_id`=?";
            $values = [1];
            $contact_r = mysqli_fetch_assoc(select($contact_q,$values,'i')); 
        ?>
        
        <div class="row">
            <div class="col-lg-8 col-md-8 p-4 mb-lg-0 mb-3 bg-white rounded">
                <iframe class="w-100 rounded" height="320" src="<?php echo $contact_r['iframe'] ?>" loading="lazy"></iframe>
            </div>
            <div class="col-lg-4 col-md-4">
                <div class="bg-white p-4 rounded mb-4">
                    <h5>Call us</h5>
                    
                    <a href="tel: +<?php echo $contact_r['pn1'] ?>" class="d-inline-block mb-2 text-decoration-none text-dark">
                        <i class="bi bi-telephone-fill"></i> +<?php echo $contact_r['pn1'] ?>
                    </a>
                    <br>

                    <?php
                        if($contact_r['pn2']!=''){
                            echo<<<data
                                <a href="tel: +$contact_r[pn2]" class="d-inline-block mb-2 text-decoration-none text-dark">
                                    <i class="bi bi-telephone-fill"></i> +$contact_r[pn2]
                                </a>
                            data;
                        }
                    ?>
                    
                </div>
                    <div class="bg-white p-4 rounded mb-4">
                        <h5>Follow us</h5>

                        <?php
                            if($contact_r['twt']!=''){
                                echo<<<data
                                    <a href="$contact_r[twt]" class="d-inline-block mb-2">
                                        <span class="badge bg-light text-dark fs-6 p-2">
                                            <i class="bi bi-twitter-x me-1"></i> Twitter
                                        </span>
                                    </a>
                                    <br>
                                data;
                            }
                        ?>

                        
                        <a href="<?php echo $contact_r['ig'] ?>" class="d-inline-block mb-2">
                            <span class="badge bg-light text-dark fs-6 p-2">
                                <i class="bi bi-instagram me-1"></i> Instagram
                            </span>
                        </a>
                        <br>
                        <a href="<?php echo $contact_r['fb'] ?>" class="d-inline-block mb-2">
                            <span class="badge bg-light text-dark fs-6 p-2">
                                <i class="bi bi-facebook me-1"></i> Facebook
                            </span>
                        </a>
                </div>
            </div>
            
        </div>
    </div>

    <br><br><br>

    <!-- HEADER/NAVBAR -->
    
    <?php require('inc/footer.php') ?> 
    
    
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        var swiper = new Swiper(".swiper-container", {
        spaceBetween: 30,
        effect: "fade",
        loop: true,
        autoplay: {
            delay: 3500,
            disableOnInteraction: false,
        }
        });

        var swiper = new Swiper(".swiper-feedbacks", {
            effect: "coverflow",
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: "auto",
            slidesPerView: "3",
            loop: true,
            coverflowEffect: {
            rotate: 50,
            stretch: 0,
            depth: 100,
            modifier: 1,
            slideShadows: false,
            },
            pagination: {
                el: ".swiper-pagination",
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                },
                640: {
                    slidesPerView: 1,
                },
                768: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                },
            }
        });
        
    $('.login-subscribe').click(function () {
        var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
    });



    </script>

<script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 3,
            spaceBetween: 40,
            loop: true,
            pagination: {
                el: ".swiper-pagination",
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                },
                640: {
                    slidesPerView: 1,
                },
                768: {
                    slidesPerView: 3,
                },
                1024: {
                    slidesPerView: 3,
                },
            }
        });
      
        function handleSubscribe() {
            // Assuming you have a modal with the ID 'loginModal' for login
            var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            loginModal.show();
        }
    </script>

</body>
</html>
