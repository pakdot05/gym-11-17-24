<?php

require('inc/essentials.php');
require('inc/db_config.php');

adminLogin();

if(isset($_POST['add_product'])){
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit']; // Get the unit from the form
 
    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../images/'.$image;
 
    // Check if product already exists
    $select_products = mysqli_query($con, "SELECT * FROM `products` WHERE name = '$name'");
 
    if(mysqli_num_rows($select_products) > 0){
       $message[] = 'Product name already exists!';
    }else{
       if($image_size > 2000000){
          $message[] = 'Image size is too large';
       }else{
          move_uploaded_file($image_tmp_name, $image_folder);
          $insert_product = mysqli_prepare($con, "INSERT INTO `products`(name, quantity, price, unit, image) VALUES(?, ?, ?, ?, ?)");
          mysqli_stmt_bind_param($insert_product, "sdiis", $name, $quantity, $price, $unit, $image); // 's' = string, 'i' = integer, 'd' = double
          
          if(mysqli_stmt_execute($insert_product)){
             $message[] = 'NEW PRODUCT ADDED!!';
          } else {
             $message[] = 'Failed to add product!';
          }
       }
    }
 }
 
 if(isset($_GET['delete'])){
    $delete_id = $_GET['delete'];
    $select_product_image = mysqli_query($con, "SELECT * FROM `products` WHERE id = '$delete_id'");
    $fetch_delete_image = mysqli_fetch_assoc($select_product_image);
    
    if($fetch_delete_image){
       unlink('../images/'.$fetch_delete_image['image']);
    }
 
    $delete_product = mysqli_prepare($con, "DELETE FROM `products` WHERE id = ?");
    mysqli_stmt_bind_param($delete_product, "i", $delete_id);
    
    if(mysqli_stmt_execute($delete_product)){
       header('location:products.php');
    } else {
       die("Error deleting product.");
    }
 }
 
 if(isset($_GET['update'])){
    $update_id = $_GET['update'];
    $select_product = mysqli_query($con, "SELECT * FROM `products` WHERE id = '$update_id'");
    $product = mysqli_fetch_assoc($select_product);
}

if(isset($_POST['update_product'])){
    $update_id = $_POST['update_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit']; 
    $image = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../images/'.$image;

    if(!empty($image)){
        move_uploaded_file($image_tmp_name, $image_folder);
        $update_product = mysqli_prepare($con, "UPDATE `products` SET name=?, quantity=?, price=?, image=?, unit=? WHERE id=?");
        mysqli_stmt_bind_param($update_product, "sdiisi", $name, $quantity, $price, $image, $unit, $update_id);
    } else {
        $update_product = mysqli_prepare($con, "UPDATE `products` SET name=?, quantity=?, price=?, unit=? WHERE id=?");
        mysqli_stmt_bind_param($update_product, "sdiis", $name, $quantity, $price, $unit, $update_id); 
    }

    if(mysqli_stmt_execute($update_product)){
        header('location:products.php');
    } else {
        die("Error updating product.");
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Manage Product</title>
   <?php require('inc/links.php');?>
</head>
<body class="bg-light">
<?php require('inc/header.php');?>


<div class="container-fluid" id="main-content">
    <div class="row">
        <div class="col-lg-10 ms-auto p-4">
            <h3 class="mb-4">Manage Products</h3>

   
<section class="add-products" >
<div class="text-end mb-4">
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addProductModal">
        Add Product
    </button>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">
                        <span id="modalTitle">Add Product</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST" enctype="multipart/form-data" id="addProductForm">
                        <div class="mb-3">
                            <label for="nameInput" class="form-label">Product Name:</label>
                            <input type="text" required placeholder="Product name" name="name" maxlength="100" class="form-control" id="nameInput" value="">
                        </div>
                        <div class="mb-3">
                            <label for="priceInput" class="form-label">Product Price:</label>
                            <input type="number" min="0" max="9999999999" required placeholder="Product Price ₱" name="price" class="form-control" id="priceInput" value="">
                        </div>
                        <div class="mb-3">
                            <label for="quantityInput" class="form-label">Quantity:</label>
                            <input type="number" min="1" required placeholder="Quantity" name="quantity" class="form-control" id="quantityInput" value="">
                        </div>
                        <div class="mb-3">
                            <label for="unitInput" class="form-label">Unit:</label>
                            <input type="text" required placeholder="Unit (e.g., 100mg, 100g)" name="unit" class="form-control" id="unitInput" value="">
                        </div>
                        <div class="mb-3">
                        <label for="imageInput" class="form-label">Image:</label>
                        <input type="file" name="image" class="form-control" accept="image/jpg, image/jpeg, image/png" id="imageInput" onchange="previewAddImage(event)">
                    </div>
                    <div class="mb-3">
                        <label for="imagePreview" class="form-label">Image Preview:</label>
                        <img id="imagePreview" src="#" alt="Image Preview" style="max-width: 200px; max-height: 200px; display: none;">
                    </div>
                        <input type="submit" value="Add Product" class="btn btn-dark" id="submitBtn" name="add_product">
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="updateProductModal" tabindex="-1" aria-labelledby="updateProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateProductModalLabel">
                    <span id="modalTitle">Update Product</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" enctype="multipart/form-data" id="updateProductForm">
                    <?php if (isset($_GET['update'])): ?>
                        <input type="hidden" name="update_id" value="<?= $product['id']; ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="updateNameInput" class="form-label">Product Name:</label>
                        <input type="text" required placeholder="Product name" name="name" maxlength="100" class="form-control" id="updateNameInput" value="<?= isset($_GET['update']) ? $product['name'] : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="updatePriceInput" class="form-label">Product Price:</label>
                        <input type="number" min="0" max="9999999999" required placeholder="Product Price ₱" name="price" class="form-control" id="updatePriceInput" value="<?= isset($_GET['update']) ? $product['price'] : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="updateQuantityInput" class="form-label">Quantity:</label>
                        <input type="number" min="1" required placeholder="Quantity" name="quantity" class="form-control" id="updateQuantityInput" value="<?= isset($_GET['update']) ? $product['quantity'] : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="updateUnitInput" class="form-label">Unit:</label>
                        <input type="text" required placeholder="Unit (e.g., 100mg, 100g)" name="unit" class="form-control" id="updateUnitInput" value="<?= isset($_GET['update']) ? $product['unit'] : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="updateImageInput" class="form-label">Image:</label>
                        <input type="file" name="image" class="form-control" accept="image/jpg, image/jpeg, image/png" id="updateImageInput" onchange="previewImage(event)">
                    </div>
                    <div class="mb-3">
                        <label for="updateImagePreview" class="form-label"></label>
                        <img id="updateImagePreview" src="<?= isset($_GET['update']) ? '../images/' . $product['image'] : ''; ?>" alt="Image Preview" style="max-width: 200px; max-height: 200px;">
                    </div>
                    <input type="submit" value="<?= isset($_GET['update']) ? 'Update Product' : '' ?>" name="<?= isset($_GET['update']) ? 'update_product' : '' ?>" class="btn btn-dark">
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
            <section class="show-products">
               <div class="table-responsive">
                  <table class="table table-hover">
                     <thead class="bg-dark text-white">
                        <tr>
                           <th scope="col">#</th>
                           <th scope="col"> Product Name</th>
                           <th scope="col">Quantity</th>
                           <th scope="col">Price</th>
                           <th scope="col">Unit</th>
                           <th scope="col">Action</th>
                        </tr>
                     </thead>
                     <tbody>
                     <?php
if (isset($_GET['search'])) {
   $search = $_GET['search'];
   // Escape the search term to prevent SQL injection
   $search = mysqli_real_escape_string($con, $search);
   
   // Query to search for products with the name like the search term
   $select_products = mysqli_query($con, "SELECT * FROM `products` WHERE `name` LIKE '%$search%'");
} else {
   // Query to fetch all products if no search term is provided
   $select_products = mysqli_query($con, "SELECT * FROM `products`");
}

// Check if any products were found
$productsCount = mysqli_num_rows($select_products);

if ($productsCount > 0) {
   // Loop through all fetched products
   while ($fetch_products = mysqli_fetch_assoc($select_products)) {
?>
<tr>
   <td><?= $fetch_products['id']; ?></td>
   <td><?= $fetch_products['name']; ?></td>
   <td><?= $fetch_products['quantity']; ?></td>
   <td>₱<?= number_format($fetch_products['price'], 2); ?></td> <!-- Formatting the price -->
   <td><?= $fetch_products['unit']; ?></td> 
   <td>
      <a href="products.php?update=<?= $fetch_products['id']; ?>" class="btn btn-warning">Update</a>
      <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="btn btn-danger" 
      onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
   </td>
</tr>
<?php
   }
} else {
   // Display a message if no products are found
   echo '<tr><td colspan="6" class="text-center">No Products Found!</td></tr>';
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

searchInput.addEventListener('input', searchProducts);

function searchProducts() {
  const filter = searchInput.value.toUpperCase();
  const boxes = boxContainer.getElementsByTagName('tr');

  let productsFound = false;

  for (let i = 1; i < boxes.length; i++) {
    const name = boxes[i].getElementsByTagName('td')[1].textContent.toUpperCase();
    const price = boxes[i].getElementsByTagName('td')[3].textContent.toUpperCase();

    if (name.includes(filter) || price.includes(filter)) {
      boxes[i].style.display = '';
      productsFound = true;
    } else {
      boxes[i].style.display = 'none';
    }
  }

  if (productsFound) {
    emptyMessage.style.display = 'none';
  } else {
    emptyMessage.style.display = 'block';
  }
}
document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has("update")) {
            const updateProductModal = new bootstrap.Modal(document.getElementById("updateProductModal"));
            console.log("Opening modal:", updateProductModal);
            updateProductModal.show();
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
const addProductModal = document.getElementById('addProductModal');
const modalTitle = document.getElementById('modalTitle');
const nameInput = document.getElementById('nameInput');
const priceInput = document.getElementById('priceInput');
const quantityInput = document.getElementById('quantityInput');
const unitInput = document.getElementById('unitInput'); // Fixed duplicate variable
const imageInput = document.getElementById('imageInput');
const submitBtn = document.getElementById('submitBtn');
const saveBtn = document.getElementById('saveBtn');

// Handle Add Button Click
document.getElementById('addFoodBtn').addEventListener('click', () => {
  modalTitle.innerText = 'Add Product';
  nameInput.value = '';
  priceInput.value = '';
  quantityInput.value = '';
  unitInput.value = '';
  imageInput.value = ''; // Clear image input
  submitBtn.value = 'Add Product';
  saveBtn.innerText = 'Save Changes';

  addProductModal.show();
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
const addProductForm = document.getElementById('addProductForm');

addProductForm.addEventListener('submit', (event) => {
  event.preventDefault();

  const name = nameInput.value;
  const price = priceInput.value;
  const quantity = quantityInput.value;
  const unit = unitInput.value;
  const image = imageInput.files[0]; // Handle image file

  let formData = new FormData();
  formData.append('name', name);
  formData.append('price', price);
  formData.append('quantity', quantity);
  formData.append('unit', unit);
  if (image) {
    formData.append('image', image); // Append image only if selected
  }
  formData.append('action', 'add_product');

  $.ajax({
    url: 'products.php', // URL of your PHP file for handling the request
    type: 'POST',
    data: formData,
    contentType: false, // Don't set contentType
    processData: false, // Don't process the data
    success: function (response) {
      console.log(response); // Log server response for debugging
      window.location.href = 'products.php'; // Redirect after success
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