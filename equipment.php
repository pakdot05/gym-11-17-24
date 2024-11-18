<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYM - Equipment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <?php require('inc/links.php'); ?>

    <style>
        /* General Styles */
        body {
            background-color: #f8f9fa;
        }

        .h-line {
            width: 100px;
            height: 5px;
            background-color: #007BFF;
            margin: 10px auto;
        }

        .title {
            font-size: 28px;
            margin-bottom: 20px;
            color: #343a40;
        }

        /* Equipment Box Styling */
        .box-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }

        .box {
            background-color: white;
            width: calc(25% - 20px);
            margin: 10px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .equipment-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #343a40;
        }

        .description {
            font-size: 14px;
            margin-bottom: 10px;
            color: #6c757d;
        }

        .flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Empty Equipment Message */
        .empty {
            text-align: center;
            font-size: 18px;
            color: #888;
            margin-top: 20px;
        }

        /* Checkout Button Style */
        .checkout-button {
            background-color: black;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
            width: 100%;
            text-align: center;
        }

        .checkout-button:hover {
            background-color: #096066;
        }

        .checkout-button[disabled] {
            background-color: #cccccc;
            cursor: not-allowed;
            color: #ffffff;
        }

        /* Responsive Styling */
        @media (max-width: 1200px) {
            .box {
                width: calc(33.333% - 20px);
            }
        }

        @media (max-width: 768px) {
            .box {
                width: calc(50% - 20px);
            }

            .search-form input[type="text"] {
                width: 250px;
            }
        }

        @media (max-width: 576px) {
            .box {
                width: 100%;
                margin: 10px 0;
            }

            .search-form input[type="text"] {
                width: 100%;
                margin-bottom: 10px;
            }

            .search-form {
                flex-direction: column;
            }
        }
    </style>

</head>
<body class="bg-light">

    <!-- HEADER/NAVBAR --> 
    <?php require('inc/header.php'); ?>
    <!-- HEADER/NAVBAR --> 

    <div class="my-5 px-4">
    <h2 class="fw-bold text-center">Equipment</h2>
    <div class="h-line bg-dark"></div>
    <div id="equipmentContainer" class="box-container">
        <?php
        $search = isset($_GET['search']) ? $_GET['search'] : ''; 
            // SQL query to fetch equipment
            $query = "SELECT * FROM `equipment` WHERE `name` LIKE ? OR `description` LIKE ?";

            if ($stmt = $con->prepare($query)) {
                // Bind the search parameter
                $search_param = "%$search%";
                $stmt->bind_param('ss', $search_param, $search_param);

                // Execute the query
                $stmt->execute();

                // Get the result
                $result = $stmt->get_result();

                // Check if there are any matching equipment
                if ($result->num_rows > 0) {
                    while ($fetch_equipment = $result->fetch_assoc()) {
                        ?>

                         <form action="equipment.php" class="box checkout-form">
                   

                    <img src="images/<?=$fetch_equipment['image'];?>" alt="<?=$fetch_equipment['name'];?>" class="equipment-image">
                    <div class="name text-center"><?=$fetch_equipment['name'];?></div>
                    <div class="description text-center"><?=$fetch_equipment['description'];?></div>
                </form>
   
                <?php
            }
        }
    }

        ?>

     
    </div>
</div>


    <!-- FOOTER -->
    <?php require('inc/footer.php'); ?>
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
                320: { slidesPerView: 1 },
                640: { slidesPerView: 1 },
                768: { slidesPerView: 3 },
                1024: { slidesPerView: 3 },
            }
        });

      

    </script>

</body>
</html>
