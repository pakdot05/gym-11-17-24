<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYM - About Us</title> 
    <link rel="stylesheet"href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

    <!-- LINKS -->

    <?php require('inc/links.php') ?> 

    <style>

        .box{

            border-top-color: var(--blue) !important;

        }

    </style>

</head>
<body class="bg-light">

    <!-- HEADER/NAVBAR --> 
    <?php require('inc/header.php') ?> 
    <!-- HEADER/NAVBAR --> 

    <div class="my-5 px-4">
        <h2 class="fw-bold text-center">ABOUT US</h2>
        <div class="h-line bg-dark"></div>
        <p class="text-center mt-3"><?php echo $contact_s['site_about'] ?></p>
    </div>

    <div class="container">
        <div class="row justify-content-between align-items-center">
            <div class="col-lg-6 col-md-5 mb-4 order-lg-1 order-md-1 order-2">
                <h3 class="mb-3">Elevate Your Fitness Journey</h3>
                <p>Discover the best practices for achieving your fitness goals and transforming your body. Our gym offers a range of activities from intense weightlifting to invigorating cardio sessions. Whether you're a beginner or a seasoned athlete, we provide the tools and guidance needed to help you reach your peak performance. Join us to experience personalized training, motivational support, and a community dedicated to health and wellness.</p>
            </div>
            <div class="col-lg-5 col-md-5 mb-4 order-lg-2 order-md-2 order-1">
                <img src="images/logo.jpg" class="w-100" style="border-radius: 50%;">
            </div>
        </div>
    </div>

    <h3 class="my-5 fw-bold text-center">MANAGEMENT TEAM</h3>

    <div class="container px-4">
        <div class="swiper mySwiper">
            <div class="swiper-wrapper mb-5">

                <?php
                    $about_r = selectAll('team_details');
                    $path = ABOUT_IMG_PATH;

                    while($row = mysqli_fetch_assoc($about_r)){
                        echo<<<data
                            <div class="swiper-slide bg-white text-center overflow-hidden rounded">
                                <img src="$path$row[picture]" class="w-100">
                                <h5 class="mt-2">$row[name]</h5>
                            </div>
                        data;
                    }
                ?>

            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>

    <!-- FOOTER -->
    <?php require('inc/footer.php') ?>
    <!-- FOOTER -->
    
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 4,
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
    </script>

</body>
</html>
