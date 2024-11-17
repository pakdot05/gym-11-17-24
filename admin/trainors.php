<?php
    require('inc/essentials.php');
    require('inc/db_config.php');
    adminLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainors</title>
    

    <?php require('inc/links.php');?>

    <style>

        th{
            background-color: #40534C !important;
        }

    </style>

</head>
<body class="bg-light">
    
    <?php require('inc/header.php');?>

    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-10 ms-auto p-4 overflow-hidden">
              <h3 class="mb-4">Trainor</h3>

                <!-- Trainor -->

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">

                        <div class="text-end mb-4">
                            <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#add-trainor">
                                <i class="bi bi-plus-square"></i> Add
                            </button>
                        </div>

                        <div class="table-responsive-lg" style="height: 450px; overflow-y: scroll;">
                            <table class="table table-hover border">
                                <thead>
                                    <tr class="bg-dark text-light">
                                    <th scope="col">#</th>
                                    <th scope="col" width="5%">Trainor Name</th>
                                    <th scope="col" class="text-start" width="15%">Address</th>
                                    <th scope="col" class="text-start" width="5%">Contact No.</th>
                                    <th scope="col" class="text-start" width="7%">Email</th>
                                    <th scope="col" width="30%" class="text-start">Description</th>
                                    <th scope="col" class="text-center" width="5%">Status</th>
                                    <th scope="col" width="20%">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="trainor-data">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- trainor MODAL -->

  <div class="modal fade" id="add-trainor" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="add_trainor_form">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">Add Trainor</div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Name</label>
                            <input type="text" min="1" name="name" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Address</label>
                            <input type="text" min="1" name="address" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Contact No.</label>
                            <input type="text" min="1" name="contact_no" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" min="1" name="email" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="info" min="1" required class="form-control shadow-none" rows="3" style="resize: none;"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Specialty</label>
                            <div class="row">                            
                                <?php 
                                    $res = selectAll('specialty');
                                    $count = 0;
                                    while($opt = mysqli_fetch_assoc($res)){
                                        if ($count % 2 === 0) {
                                            echo '<div class="row">';
                                        }
                                        echo "
                                            <div class='col-md-6 mb-1'>
                                                <label>

                                                    <input type='checkbox' name='specialty' value='$opt[specialty_id]' class='form-check-input shadow-none'>
                                                    $opt[name]
                                                </label>
                                            </div>
                                        ";
                                        if ($count % 2 === 1) {
                                            echo '</div>';
                                        }
                                        $count++;
                                    }
                                    if ($count % 2 !== 0) {
                                        echo '</div>'; // Close the row div tag if there are remaining checkboxes
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn text-secondary shadow-none" data-bs-dismiss="modal">CANCEL</button>
                    <button type="submit" class="btn custom-bg text-white shadow-none">SUBMIT</button>
                </div>
            </div>
        </form>
    </div>
</div>
   <!-- MANAGE trainor IMAGES MODAL -->
   <div class="modal fade" id="trainor-images" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Trainor Name</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="image-alert"></div>
                    <div class="border-bottom border-3 pb-3 mb-3">
                        <form id="add_image_form">
                            <label class="form-label fw-bold">Add Image</label>
                            <input type="file" name="image" accept=".jpg, .png, .webp, .jpeg" class="form-control shadow-none mb-3" required>
                            <button type="submit" class="btn custom-bg text-white shadow-none">ADD</button>
                            <input type="hidden" name="trainor_id">
                        </form>
                    </div>
                    <div class="table-responsive-lg" style="height: 350px; overflow-y: scroll;">
                        <table class="table table-hover border">
                            <thead>
                                <tr class="bg-dark text-light sticky-top">
                                    <th scope="col" width="60%">Image</th>
                                    <th scope="col">Thumbs</th>
                                    <th scope="col">Delete</th>
                                </tr>
                            </thead>
                            <tbody id="trainor-image-data"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- EDIT MODAL -->
    <div class="modal fade" id="edit-trainor" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="edit_trainor_form">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">Edit Trainor</div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Name</label>
                            <input type="text" min="1" name="name" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" min="1" name="email" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Contact No.</label>
                            <input type="text" min="1" name="contact_no" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Address</label>
                            <input type="text" min="1" name="address" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="info" min="1" required class="form-control shadow-none" rows="3" style="resize: none;"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Specialty</label>
                            <div class="row">
                                <?php 
                                    $res = selectAll('specialty');
                                    $count = 0;
                                    while($opt = mysqli_fetch_assoc($res)){
                                        if ($count % 2 === 0) {
                                            echo '<div class="row">';
                                        }
                                        echo "
                                            <div class='col-md-6 mb-1'>
                                                <label>
                                                    <input type='checkbox' name='specialty' value='$opt[specialty_id]' class='form-check-input shadow-none'>
                                                    $opt[name]
                                                </label>
                                            </div>
                                        ";
                                        if ($count % 2 === 1) {
                                            echo '</div>';
                                        }
                                        $count++;
                                    }
                                    if ($count % 2 !== 0) {
                                        echo '</div>'; // Close the row div tag if there are remaining checkboxes
                                    }
                                ?>
                            </div>
                        </div>
                        <input type="hidden" name="trainor_id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn text-secondary shadow-none" data-bs-dismiss="modal">CANCEL</button>
                    <button type="submit" class="btn custom-bg text-white shadow-none">SUBMIT</button>
                </div>
            </div>
        </form>
    </div>
</div>


    <?php require('inc/scripts.php');?>

    <script src="scripts/trainor.js"></script>

</body>
</html>