<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>

    <?php require('inc/links.php');?>

    <style>

        th{
            background-color: #40534C !important;
            color: white !important;
        }

        .tablez{
            background-color: red;
            width: 100%;
        }

        .modal-dialog {
            max-width: 100%;
            margin: 0 auto;
        }

        .modal-content {
            width: 100%;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
        }


    </style>

</head>
<body class="bg-light">

    <?php require('inc/header.php');?>

    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-10 ms-auto p-4 overflow-hidden">
              <h3 class="mb-4">Gym-goer</h3>

                <!-- USERS -->

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">

                        <div class="text-end mb-4">
                            <input type="text" oninput="search_user(this.value)" class="form-control shadow-none w-25 ms-auto" placeholder="Search...">
                        </div>


                        <div class="table-responsive" style="height: 450px; overflow-y: scroll;">
                            <table class="table table-hover border text-center">
                                <thead class='sticky-top'>
                                    <tr class="bg-dark text-light">
                                        <th scope="col">#</th>
                                        <th scope="col" width="10%">Gym-goer</th>
                                        <th scope="col" width="15%">Email</th>
                                        <th scope="col">Phone#</th>
                                        <th scope="col">Address</th>
                                        <th scope="col">Bdate</th>
                                        <th scope="col" width="10%">Status</th>
                                        <th scope="col" width="10%">Date</th>
                                        <th scope="col" width="10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="users-data">
                                    <!-- User data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
            <div class="modal-header position-relative">
    <h5 class="modal-title w-100 text-center" id="userDetailsModalLabel">Gym Goer Details</h5>
    <button type="button" class="btn-close position-absolute end-0" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

                <div class="modal-body" id="modal-user-details">
                <div id="successMessage" class="alert alert-success d-none" role="alert">
    Appointment deleted successfully!
</div>
<div id="errorMessage" class="alert alert-danger d-none" role="alert">
    Failed to delete the appointment. Please try again.
</div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <?php require('inc/scripts.php');?>

    <script src="scripts/users.js"></script>

</body>
</html>