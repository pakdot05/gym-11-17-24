
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment</title>

    <?php require('inc/links.php'); ?>

</head>
<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-10 ms-auto p-4 overflow-hidden">
                <h3 class="mb-4">APPOINTMENTS</h3>

                <!--Trainor -->

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">

                        <a href="dashboard.php" class="text-secondary text-decoration-none"> < BACK</a>

                        <div class="d-flex mb-4">
                            <input type="text" oninput="search_user(this.value)" class="form-control shadow-none w-25 ms-auto" placeholder="Search...">
                        </div>

                        <div class="table-responsive-md" style="height: auto; overflow-y: scroll;">
                            <table class="table table-hover border">
                                <thead>
                                    <tr class="text-light ambot">
                                        <th scope="col">#</th>
                                        <th scope="col">GymGoer</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Phone no.</th>
                                        <th scope="col" width="20%">Note</th>
                                        <th scope="col">Trainor</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Time</th>
                                        <th scope="col" class="text-center">Status</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="users-data">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require('inc/scripts.php'); ?>

    <script src="scripts/booking.js"></script>

</body>
</html>