<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Management System - Trainors</title> 
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
            border: 1px solid #09858d !important;
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

    <?php 
        if(!isset($_GET['trainor_id'])){
            redirect('trainors.php');
        }

        $data = filteration($_GET);

        $trainor_res = select("SELECT * FROM `trainor` WHERE `trainor_id`=? AND `status`=? AND `removed`=?",[$data['trainor_id'],1,0],'iii');

        if(mysqli_num_rows($trainor_res)==0){
            redirect('trainors.php');
        }

        $trainor_data = mysqli_fetch_assoc($trainor_res);
          $trainor_thumb=TRAINORS_IMG_PATH."IMG_11728.jpg";
                        $thumb_q = mysqli_query($con, "SELECT * FROM `trainor_images` WHERE `trainor_id`='$trainor_data[trainor_id]' AND `thumb`='1'");
                        if (mysqli_num_rows($thumb_q) > 0) {
                            $thumb_res = mysqli_fetch_assoc($thumb_q);
                            $trainor_thumb = TRAINORS_IMG_PATH . $thumb_res['image'];
                        } else {
                            $trainor_thumb = TRAINORS_IMG_PATH . 'default.jpg'; // Provide a default image path if no thumbnail is found
                        }

    ?>

    

    <div class="container">
        <div class="row">

            <div class="col-12 my-5 mb-4 px-4">
                <h2 class="fw-bold"><?php echo $trainor_data['name'] ?></h2>
                <div style="font-size: 14px;">
                    <a href="index.php" class="text-secondary text-decoration-none">HOME</a>
                    <span class="text-secondary"> > </span>
                    <a href="trainors.php" class="text-secondary text-decoration-none">TRAINORS</a>
                </div>
            </div>

            <div class="col-lg-5 col-md-12 px-4">
                <div style="display:flex; overflow: hidden; max-height: 500px; background-size: cover; background-position: center;">
                <img src="<?php echo $trainor_thumb; ?>" class="rounded" style="min-width: 100%; min-height: 100%;">
                </div>
            </div>

            <div class="col-lg-7 col-md-12 px-4">
                <div class="card mb-4 border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <?php 

                         

                            $app_btn = "";

                            if(!$settings_r['shutdown']){
                                $login=0;
                                if(isset($_SESSION['login']) && $_SESSION['login']==true){
                                    $login=1;
                                }
                                $app_btn = "<button onclick='checkLoginToApp($login,$trainor_data[trainor_id])' class='btn btn-sm w-100 text-white custom-bg shadow-none'>Confirm</button>";
                            }

                            $spec_q = mysqli_query($con,"SELECT s.name FROM `specialty` s 
                            INNER JOIN `trainor_specialty` dspec ON s.specialty_id = dspec.specialty_id 
                                WHERE dspec.doc_id = '$trainor_data[trainor_id]'");

                            $specialty_data = "";
                            while($spec_row = mysqli_fetch_assoc($spec_q)){
                                $specialty_data .="<span class='badge rounded-pill bg-light text-dark text-wrap'>
                                    $spec_row[name]
                                </span>";
                            }
                            echo<<<data
                                <div style="height: 200px; position: relative;">
                                    <div>
                                        <h5 class="mb-3">Background</h5>
                                        <p>$trainor_data[info]</p>
                                    </div>
                                    <h5 class="mb-3">Specialty</h5>
                                    $specialty_data
                                    
                                </div>
                                
                            data;

                            echo<<<data
                                <div class="m-0">
                                    $app_btn
                                </div>
                            data;
                            

                           
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-9 col-md-12 px-4">

            
                
            </div>

        </div>
    </div>

    

    <!-- FOOTER -->
    <?php require('inc/footer.php') ?>
    <!-- FOOTER -->
    
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

</body>
</html>