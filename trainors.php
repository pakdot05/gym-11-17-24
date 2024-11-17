<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Mananagement - Trainors</title> 
    <link rel="stylesheet"href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

    <!-- LINKS -->

    <?php require('inc/links.php') ?> 

    <style>

        .btn{
            background-color: #393E46 !important;
            border: 1px solid #393E46 !important;
        }

        .btn:hover{
            background-color: #096066 !important;
        }

        .btn1{
            border: 1px solid #393E46 !important;
            color: #09858d !important;
            text-decoration: none !important;
        }

        .btn1:hover{
            background-color: #096066 !important;
            color: white !important;
        }

    </style>



</head>
<body class="bg-light">

    <!-- HEADER/NAVBAR --> 
    <?php require('inc/header.php') ?> 
    <!-- HEADER/NAVBAR --> 

    <div class="my-5 px-4">
        <h2 class="fw-bold text-center">TRAINORS</h2>
        <div class="h-line bg-dark"></div>
    </div>

    <div class="container-fluid">
        <div class="row">



            <div class="col-lg-12 col-md-12 px-4">

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
                             $app_btn = "<a href='trainor_details.php?trainor_id=$trainor_data[trainor_id]' class='btn btn-sm w-100 text-white custom-out shadow-none'>Appoint now</a>";
                        }
                        $trainor_thumb=TRAINORS_IMG_PATH."IMG_11728.jpg";
                        $thumb_q = mysqli_query($con, "SELECT * FROM `trainor_images` WHERE `trainor_id`='$trainor_data[trainor_id]' AND `thumb`='1'");
                        if (mysqli_num_rows($thumb_q) > 0) {
                            $thumb_res = mysqli_fetch_assoc($thumb_q);
                            $trainor_thumb = TRAINORS_IMG_PATH . $thumb_res['image'];
                        } else {
                            $trainor_thumb = TRAINORS_IMG_PATH . 'default.jpg'; // Provide a default image path if no thumbnail is found
                        }
                        
                    echo <<<data
                    <div class="card mb-4 border-0 shadow">
                        <div class="row g-0 p-3">
                            <div class="col-md-4 mb-lg-0 mb-md-0 mb-3" style="display:flex; justify-content:center; align-items:center; overflow: hidden; max-height: 400px; height: 400px; background-size: cover; background-position: center;">
                                <img src="$trainor_thumb" class="rounded" style="min-width: 100%; min-height: 100%;">
                            </div>
                            <div class="col-md-8 px-lg-3 px-md-3 px-0 align-items-center" style="position: relative;">
                                <h3 class="mb-3">$trainor_data[name]</h3>
                                <p class="mt-3">$trainor_data[info]</p>
                                
                                <div class="mb-3">
                                    <p class="mb-1"><b>Address:</b> $trainor_data[address]</p>
                                    <p class="mb-1"><b>Contact:</b> $trainor_data[contact_no]</p>
                                    <p class="mb-1"><b>Email:</b> $trainor_data[email]</p>
                                </div>
                                <div class="features mb-3">
                                    <h5 class="mb-3">Services</h5>
                                    $specialty_data
                                </div>
                                <div class="col-md-8 px-lg-3 px-md-3 px-0 align-items-center" style="position: absolute; bottom: 1px; width: 100%; left:0; right:0;">
                                    $app_btn
                                </div>                                                                           
                            </div>   
                        </div>
                    </div>
                data;
                }                

                ?>
                
            </div>

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