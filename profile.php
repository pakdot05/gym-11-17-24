<?php
require('inc/links.php');
// Check if user is logged in and session variables are set
if (!isset($_SESSION['uId'])) {
    die("User not logged in");
}


// Fetch user profile data from the database
$uId = $_SESSION['uId'];
$query = "SELECT * FROM user_cred WHERE user_id = ?";
$result = select($query, [$uId], 'i');

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $_SESSION['uName'] = $user['name'];
    $_SESSION['uEmail'] = $user['email'];
    $_SESSION['uPhone'] = $user['phonenum'];
    $_SESSION['uDob'] = $user['dob'];
    $_SESSION['uAdd'] = $user['address'];
    $_SESSION['uProfile'] = $user['profile'];
} else {
    die("User not found");
}

// Handle form submission for profile data update
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $phonenum = $_POST['phonenum'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];

    // Update user data in the database excluding email
    $updateQuery = "UPDATE user_cred SET name=?, phonenum=?, dob=?, address=? WHERE user_id=?";
    $updateResult = update($updateQuery, [$name, $phonenum, $dob, $address, $uId], 'ssssi');

    if ($updateResult) {
        // Update session variables with new data
        $_SESSION['uName'] = $user['name']; 
        $_SESSION['uPhone'] = $phonenum;
        $_SESSION['uDob'] = $dob;
        $_SESSION['uAdd'] = $address;

        echo "<script>
        showAlert('Profile updated successfully', 'success');
      </script>";
} else {
echo "<script>
        showAlert('Failed to update profile', 'error');
      </script>";
}
}

// Handle profile picture update
if (isset($_POST['submitPhoto'])) {
    $profileImage = $_FILES['profileImage'];

    // Validate and handle profile image upload
    if ($profileImage['error'] === UPLOAD_ERR_OK) {
        // Validate file type, size, etc. as per your requirements

        // Move uploaded file to a permanent location
        $uploadDir = 'images/users/';
        $uploadFile = $uploadDir . basename($profileImage['name']);

        if (move_uploaded_file($profileImage['tmp_name'], $uploadFile)) {
            // Update profile image path in the database
            $updateQuery = "UPDATE user_cred SET profile=? WHERE user_id=?";
            $newProfileFileName = basename($profileImage['name']);
            $updateResult = update($updateQuery, [$newProfileFileName, $uId], 'si');

            if ($updateResult) {
                $_SESSION['uProfile'] = $newProfileFileName;
                echo "<script>
                showAlert('Profile picture updated successfully', 'success');
              </script>";
    } else {
        echo "<script>
                showAlert('Failed to update profile picture', 'error');
              </script>";
    }
} else {
    echo "<script>
            showAlert('Failed to move uploaded file', 'error');
          </script>";
}
} else {
echo "<script>
        showAlert('Error uploading profile picture', 'error');
      </script>";
}
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYM - Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    
    <style>
        .box {
            border-top-color: var(--blue) !important;
        }

        .btn{
            background-color:  #323232 !important;
            border: 1px solid  #323232 !important;
        }

        .btn:hover{
            background-color: #096066 !important;
        }
        .alert {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 80%;
        max-width: 500px;
        height: 50%;
        padding: 20px;
        box-sizing: border-box;
        z-index: 1050;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    .btn-close {
        background: none;
        border: none;
        font-size: 1.2rem;
    }

    </style>
</head>
<body class="bg-light">

 <?php require('inc/header.php') ?> 


<div class="my-5 px-4">
    <h2 class="fw-bold text-center">PROFILE</h2>
    <div class="h-line bg-dark"></div>
</div>

<div class="container" style="height: 100vh;">
<form method="post" action="" enctype="multipart/form-data">
    <div class="photo bg-dark" style="height: 300px; width: 300px; border-radius: 50%; margin: 0 auto;">
        <?php if (!empty($_SESSION['uProfile'])): ?>
            <?php
            $profilePath = SITE_URL . 'images/users/' . $_SESSION['uProfile'];
            echo "<img src='$profilePath' alt='Profile Image' style='height: 100%; width: 100%; object-fit: cover; border-radius: 50%;'>";
            ?>
        <?php else: ?>
            <p style='color: white; text-align: center; line-height: 300px;'>No profile image set</p>
        <?php endif; ?>
    </div>
    <div class="form-group mt-3">
        <label for="profileImage">Change Profile Picture</label>
        <input type="file" class="form-control" id="profileImage" name="profileImage"required>
    </div>
    <div class="form-group text-center">
        <button type="submit" class="btn custom-bg text-white" name="submitPhoto" >Change Photo</button>
    </div>
</form>

<form method="post" action="">
    <div class="form-group mb-3 mt-5">
        <label for="">Name</label>
        <input required type="text" name="name" id="username" class="form-control" value="<?php echo isset($_SESSION['uName']) ? $_SESSION['uName'] : ''; ?>">
    </div>
    <div class="form-group mb-3">
        <label for="">Email</label>
        <input required type="email" name="email" class="form-control" disabled value="<?php echo isset($_SESSION['uEmail']) ? $_SESSION['uEmail'] : ''; ?>">
    </div>
    <div class="form-group mb-3">
        <label for="">Phone no.</label>
        <input required type="tel" name="phonenum" class="form-control" value="<?php echo isset($_SESSION['uPhone']) ? $_SESSION['uPhone'] : ''; ?>">
    </div>
    <div class="form-group mb-3">
        <label for="dob">Date of Birth</label>
        <input required type="date" name="dob" id="dob" class="form-control" value="<?php echo isset($_SESSION['uDob']) ? $_SESSION['uDob'] : ''; ?>">
    </div>
    <div class="form-group mb-3">
        <label for="address">Address</label>
        <input required type="text" name="address" id="address" class="form-control" value="<?php echo isset($_SESSION['uAdd']) ? $_SESSION['uAdd'] : ''; ?>">
    </div>
    <div class="form-group text-end">
        <button type="submit" class="btn custom-bg text-white" name="submit">Save</button>
    </div>
</form>

    
</div>

<br><br><br>
<br><br><br>
<br><br><br>
<br><br><br>
    <script>
  function showAlert(message, type) {
    // Create the alert container
    var alertDiv = document.createElement('div');
    alertDiv.classList.add('alert');
    
    // Set the classes based on success or error
    if (type === 'success') {
      alertDiv.classList.add('alert-success');
    } else if (type === 'error') {
      alertDiv.classList.add('alert-danger');
    }

    alertDiv.classList.add('alert-dismissible', 'fade', 'show');
    alertDiv.setAttribute('role', 'alert');
    
    // Set the alert message
    alertDiv.innerHTML = `<strong>${type === 'success' ? 'Success' : 'Error'}!</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;

    // Append the alert to the body
    document.body.appendChild(alertDiv);

    // Automatically remove the alert after 3 seconds
    setTimeout(function() {
        alertDiv.classList.add('fade', 'hide');
        setTimeout(function() {
            alertDiv.remove();
        }, 500);
    }, 3000);
  }

  // AJAX form submission using jQuery
  $(document).ready(function () {
    // Form submission for profile update
    $('form#profileForm').on('submit', function (e) {
      e.preventDefault(); // Prevent normal form submission

      var formData = new FormData(this); // Get form data

      $.ajax({
        url: '', // Your PHP script that handles the request
        type: 'POST',
        data: formData,
        contentType: false, // Prevent jQuery from automatically setting the content type
        processData: false, // Prevent jQuery from processing the data
        success: function(response) {
          // Assuming the response is a success or error message
          if (response === 'success') {
            showAlert('Profile updated successfully', 'success');
          } else {
            showAlert('Failed to update profile', 'error');
          }
        },
        error: function() {
          showAlert('Error with the request', 'error');
        }
      });
    });

    // Form submission for profile picture update
    $('form#photoForm').on('submit', function (e) {
      e.preventDefault(); // Prevent normal form submission

      var formData = new FormData(this); // Get form data

      $.ajax({
        url: '', // Your PHP script that handles the request
        type: 'POST',
        data: formData,
        contentType: false, // Prevent jQuery from automatically setting the content type
        processData: false, // Prevent jQuery from processing the data
        success: function(response) {
          if (response === 'success') {
            showAlert('Profile picture updated successfully', 'success');
          } else {
            showAlert('Failed to update profile picture', 'error');
          }
        },
        error: function() {
          showAlert('Error with the request', 'error');
        }
      });
    });
  });
</script>


<!-- FOOTER -->
<?php require('inc/footer.php') ?>
<!-- FOOTER -->

</body>
</html>