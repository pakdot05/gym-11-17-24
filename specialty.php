<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYM - Specialties</title> 
    <link rel="stylesheet"href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

    <!-- LINKS -->

    <?php require('inc/links.php') ?> 

    <style>

        .box{

            border-top-color: var(--blue) !important;

        }

        .box:hover{

            border-top-color: #096066 !important;
            transform: scale(1.03);
            transition: all 0.3s;

        }

    </style>

</head>
<body class="bg-light">

    <!-- HEADER/NAVBAR --> 
    <?php require('inc/header.php') ?> 
    <!-- HEADER/NAVBAR --> 

    <div class="my-5 px-4">
        <h2 class="fw-bold text-center">OUR SERVICES</h2>
        <div class="h-line bg-dark"></div>
    </div>
    
    <?php 
    
    $contact_q = "SELECT * FROM `specialty` WHERE `specialty_id`=?";
    $values = [1];
    $contact_r = mysqli_fetch_assoc(select($contact_q,$values,'i'));
    
    
    ?>

<div class="container mt-5">
    <div class="row">
        <?php
        $about_r = selectAll('specialty');
        while ($row = mysqli_fetch_assoc($about_r)) {
            echo <<<data
    <div class="col-lg-4 col-md-6 mb-5 px-4">
        <div class="bg-white rounded shadow p-4 border-top border-4 box" style="height: 250px;">
            <div class="d-flex">
                <h5 class="mb-3" style="margin :0 auto;">$row[name]</h5>
            </div>
            <p style="overflow-y: auto; max-height: 150px;">$row[description]</p>
        </div>
    </div>
data;

        }
        ?>
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