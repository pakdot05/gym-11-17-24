<?php 
session_start();
?>
<?php

require('inc/essentials.php');
require('inc/db_config.php');

adminLogin();

if(isset($_POST['add_equipment'])){
    $name = $_POST['name'];
    $description = $_POST['description'];
    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../images/'.$image;
 
    // Check if equipment already exists
    $select_equipment = mysqli_query($con, "SELECT * FROM `equipment` WHERE name = '$name'");
 
    if(mysqli_num_rows($select_equipment) > 0){
       $message[] = 'Equipment name already exists!';
    }else{
       if($image_size > 2000000){
          $message[] = 'Image size is too large';
       }else{
          move_uploaded_file($image_tmp_name, $image_folder);
          $insert_equipment = mysqli_prepare($con, "INSERT INTO `equipment`(name, description, image) VALUES(?, ?, ?)");
          mysqli_stmt_bind_param($insert_equipment, "sss", $name, $description, $image); // 's' = string
          
          if(mysqli_stmt_execute($insert_equipment)){
             $message[] = 'New equipment added!';
          } else {
             $message[] = 'Failed to add equipment!';
          }
       }
    }
}

if(isset($_GET['delete'])){
    $delete_id = $_GET['delete'];
    $select_equipment_image = mysqli_query($con, "SELECT * FROM `equipment` WHERE equipment_id = '$delete_id'");
    $fetch_delete_image = mysqli_fetch_assoc($select_equipment_image);
    
    if($fetch_delete_image){
       unlink('../images/'.$fetch_delete_image['image']);
    }
 
    $delete_equipment = mysqli_prepare($con, "DELETE FROM `equipment` WHERE equipment_id = ?");
    mysqli_stmt_bind_param($delete_equipment, "i", $delete_id);
    
    if(mysqli_stmt_execute($delete_equipment)){
       header('location:equipment.php');
    } else {
       die("Error deleting equipment.");
    }
}

if(isset($_GET['update'])){
    $update_id = $_GET['update'];
    $select_equipment = mysqli_query($con, "SELECT * FROM `equipment` WHERE equipment_id = '$update_id'");
    $equipment = mysqli_fetch_assoc($select_equipment);
}

if(isset($_POST['update_equipment'])){
    $update_id = $_POST['update_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $image = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../images/'.$image;

    if(!empty($image)){
        move_uploaded_file($image_tmp_name, $image_folder);
        $update_equipment = mysqli_prepare($con, "UPDATE `equipment` SET name=?, description=?, image=? WHERE equipment_id=?");
        mysqli_stmt_bind_param($update_equipment, "sssi", $name, $description, $image, $update_id);
    } else {
        $update_equipment = mysqli_prepare($con, "UPDATE `equipment` SET name=?, description=? WHERE equipment_id=?");
        mysqli_stmt_bind_param($update_equipment, "ssi", $name, $description, $update_id); 
    }

    if(mysqli_stmt_execute($update_equipment)){
        header('location:equipment.php');
    } else {
        die("Error updating equipment.");
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Manage Equipment</title>
   <?php require('inc/links.php');?>
</head>
<body class="bg-light">
<?php require('inc/header.php');?>


<div class="container-fluid" id="main-content">
    <div class="row">
        <div class="col-lg-10 ms-auto p-4">
            <h3 class="mb-4">Manage Equipment</h3>

   
<section class="add-equipment" >
<div class="text-end mb-4">
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
        Add Equipment
    </button>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-labelledby="addEquipmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEquipmentModalLabel">
                        <span id="modalTitle">Add Equipment</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST" enctype="multipart/form-data" id="addEquipmentForm">
                        <div class="mb-3">
                            <label for="nameInput" class="form-label">Equipment Name:</label>
                            <input type="text" required placeholder="Equipment name" name="name" maxlength="100" class="form-control" id="nameInput" value="">
                        </div>
                        <div class="mb-3">
                            <label for="descriptionInput" class="form-label">Description:</label>
                            <textarea required placeholder="Equipment Description" name="description" class="form-control" id="descriptionInput"></textarea>
                        </div>
                        <div class="mb-3">
                        <label for="imageInput" class="form-label">Image:</label>
                        <input type="file" name="image" class="form-control" accept="image/jpg, image/jpeg, image/png" id="imageInput" onchange="previewAddImage(event)">
                    </div>
                    <div class="mb-3">
                        <label for="imagePreview" class="form-label">Image Preview:</label>
                        <img id="imagePreview" src="#" alt="Image Preview" style="max-width: 200px; max-height: 200px; display: none;">
                    </div>
                        <input type="submit" value="Add Equipment" class="btn btn-dark" id="submitBtn" name="add_equipment">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateEquipmentModal" tabindex="-1" aria-labelledby="updateEquipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateEquipmentModalLabel">
                    <span id="modalTitle">Update Equipment</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" enctype="multipart/form-data" id="updateEquipmentForm">
                    <?php if (isset($_GET['update'])): ?>
                        <input type="hidden" name="update_id" value="<?= $equipment['equipment_id']; ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="updateNameInput" class="form-label">Equipment Name:</label>
                        <input type="text" required placeholder="Equipment name" name="name" maxlength="100" class="form-control" id="updateNameInput" value="<?= isset($_GET['update']) ? $equipment['name'] : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="updateDescriptionInput" class="form-label">Description:</label>
                        <textarea required placeholder="Equipment Description" name="description" class="form-control" id="updateDescriptionInput"><?= isset($_GET['update']) ? $equipment['description'] : ''; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="updateImageInput" class="form-label">Image:</label>
                        <input type="file" name="image" class="form-control" accept="image/jpg, image/jpeg, image/png" id="updateImageInput" onchange="previewImage(event)">
                    </div>
                    <div class="mb-3">
                        <label for="updateImagePreview" class="form-label"></label>
                        <img id="updateImagePreview" src="<?= isset($_GET['update']) ? '../images/' . $equipment['image'] : ''; ?>" alt="Image Preview" style="max-width: 200px; max-height: 200px;">
                    </div>
                    <input type="submit" value="<?= isset($_GET['update']) ? 'Update Equipment' : '' ?>" name="<?= isset($_GET['update']) ? 'update_equipment' : '' ?>" class="btn btn-dark">
                </form>
            </div>
        </div>
    </div>
</div>
</section>

            <section class="search-section my-4">
            <form action="" method="get" class="search-form">
            <input type="text" id="searchInput" name="search" placeholder="Search..." class="form-control shadow-none w-25 ms-auto" >
            </form>
            </section>
            <section class="show-equipment">
               <div class="table-responsive">
                  <table class="table table-hover">
                     <thead class="bg-dark text-white">
                        <tr>
                           <th scope="col">#</th>
                           <th scope="col"> Equipment Name</th>
                           <th scope="col">Description</th>
                           <th scope="col">Action</th>
                        </tr>
                     </thead>
                     <tbody>
                     <?php
if (isset($_GET['search'])) {
   $search = $_GET['search'];
   // Escape the search term to prevent SQL injection
   $search = mysqli_real_escape_string($con, $search);
   
   // Query to search for equipment with the name like the search term
   $select_equipment = mysqli_query($con, "SELECT * FROM `equipment` WHERE `name` LIKE '%$search%'");
} else {
   // Query to fetch all equipment if no search term is provided
   $select_equipment = mysqli_query($con, "SELECT * FROM `equipment`");
}

// Check if any equipment were found
$equipmentCount = mysqli_num_rows($select_equipment);

if ($equipmentCount > 0) {
   // Loop through all fetched equipment
   while ($fetch_equipment = mysqli_fetch_assoc($select_equipment)) {
?>
<tr>
   <td><?= $fetch_equipment['equipment_id']; ?></td>
   <td><?= $fetch_equipment['name']; ?></td>
   <td><?= $fetch_equipment['description']; ?></td>
   <td>
      <a href="equipment.php?update=<?= $fetch_equipment['equipment_id']; ?>" class="btn btn-warning">Update</a>
      <a href="equipment.php?delete=<?= $fetch_equipment['equipment_id']; ?>" class="btn btn-danger" 
      onclick="return confirm('Are you sure you want to delete this equipment?');">Delete</a>
   </td>
</tr>
<?php
   }
} else {
   // Display a message if no equipment is found
   echo '<tr><td colspan="4" class="text-center">No Equipment Found!</td></tr>';
}
?>

                     </tbody>
                  </table>
               </div>
            </section>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const searchInput = document.getElementById('searchInput');
const boxContainer = document.querySelector('.table');
const emptyMessage = document.querySelector('.empty');

searchInput.addEventListener('input', searchEquipments);

function searchEquipments() {
  const filter = searchInput.value.toUpperCase();
  const boxes = boxContainer.getElementsByTagName('tr');

  let equipmentFound = false;

  for (let i = 1; i < boxes.length; i++) {
    const name = boxes[i].getElementsByTagName('td')[1].textContent.toUpperCase();
    const description = boxes[i].getElementsByTagName('td')[2].textContent.toUpperCase();

    if (name.includes(filter) || description.includes(filter)) {
      boxes[i].style.display = '';
      equipmentFound = true;
    } else {
      boxes[i].style.display = 'none';
    }
  }

  if (equipmentFound) {
    emptyMessage.style.display = 'none';
  } else {
    emptyMessage.style.display = 'block';
  }
}

document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("update")) {
        const updateEquipmentModal = new bootstrap.Modal(document.getElementById("updateEquipmentModal"));
        console.log("Opening modal:", updateEquipmentModal);
        updateEquipmentModal.show();
    }
});

function previewImage(event) {
  const imagePreview = document.getElementById('updateImagePreview');
  const file = event.target.files[0];

  if (file) {
    // Check if the file is an image
    if (file.type.startsWith('image/')) {
      imagePreview.src = URL.createObjectURL(file);
      imagePreview.style.display = 'block';
    } else {
      imagePreview.src = '#';
      imagePreview.style.display = 'none';
      alert('Please select a valid image file.');
    }
  } else {
    // Reset preview to default image or empty string
    imagePreview.src = 'path/to/default/image.jpg'; // Or imagePreview.src = '';
    imagePreview.style.display = 'none';
  }
}

// Modal Initialization
const addEquipmentModal = document.getElementById('addEquipmentModal');
const modalTitle = document.getElementById('modalTitle');
const nameInput = document.getElementById('nameInput');
const descriptionInput = document.getElementById('descriptionInput'); // Added description input
const imageInput = document.getElementById('imageInput');
const submitBtn = document.getElementById('submitBtn');
const saveBtn = document.getElementById('saveBtn');

// Handle Add Button Click
document.getElementById('addEquipmentBtn').addEventListener('click', () => {
  modalTitle.innerText = 'Add Equipment';
  nameInput.value = '';
  descriptionInput.value = ''; // Clear description input
  imageInput.value = ''; // Clear image input
  submitBtn.value = 'Add Equipment';
  saveBtn.innerText = 'Save Changes';

  addEquipmentModal.show();
});

function previewAddImage(event) {
    const imagePreview = document.getElementById('imagePreview');
    const file = event.target.files[0];

    if (file) {
        // Create a URL for the selected file and set it as the src of the image preview
        imagePreview.src = URL.createObjectURL(file);
        imagePreview.style.display = 'block'; // Show the image preview
    } else {
        // Reset to default if no file is selected (optional)
        imagePreview.src = '#'; 
        imagePreview.style.display = 'none'; // Hide the image preview if no file is selected
    }
}

const addEquipmentForm = document.getElementById('addEquipmentForm');

addEquipmentForm.addEventListener('submit', (event) => {
  event.preventDefault();

  const name = nameInput.value;
  const description = descriptionInput.value;
  const image = imageInput.files[0]; // Handle image file

  let formData = new FormData();
  formData.append('name', name);
  formData.append('description', description); // Added description field
  if (image) {
    formData.append('image', image); // Append image only if selected
  }
  formData.append('action', 'add_equipment');

  $.ajax({
    url: 'equipments.php', // URL of your PHP file for handling the request
    type: 'POST',
    data: formData,
    contentType: false, // Don't set contentType
    processData: false, // Don't process the data
    success: function (response) {
      console.log(response); // Log server response for debugging
      window.location.href = 'equipments.php'; // Redirect after success
    },
    error: function (error) {
      console.error(error); // Handle any errors
      // Display an error message to the user
    }
  });
});
</script>
<?php require('inc/scripts.php'); ?>
</body>
</html>
