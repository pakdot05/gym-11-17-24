<?php
session_start();
    $contact_q = "SELECT * FROM `contact_details` WHERE `contact_id`=?";
    $values = [1];
    $contact_r = mysqli_fetch_assoc(select($contact_q,$values,'i'));
    
    $contact_q = "SELECT * FROM `settings` WHERE `settings_id`=?";
    $values = [1];
    $contact_s = mysqli_fetch_assoc(select($contact_q,$values,'i'));
    
    // Get the current file name
    $current_page = basename($_SERVER['PHP_SELF']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>

        .dropdown-item.active{
            background-color: #09858d !important;
            color: white !important;
        }

        .dropdown-item:active{
            background-color: #09858d !important;
        }

        .dropdown-item:hover{
            background-color: #e0e0e0 !important;
        }

        .btn1{
            background-color: #323232 !important;
            border: 1px solid #323232 !important;
            color: white !important;
        }

        .btn1:hover{
            background-color: #096066 !important;
        }
        .nav-item.active a {
    color: #7ED4AD !important;
    
}


        
    </style>
</head>
<body>


<!-- NAVBAR -->

<nav id="nav-bar" class="navbar navbar-expand-lg navbar-light px-lg-3 py-lg-2 shadow-sm sticky-top">
    <div class="container-fluid">
        <img src="images/logo.jpg" alt="Logo" class="mr-2" style="height: 50px; width: 50px; border-radius: 50%;">
        <a class="navbar-brand" href="index.php" style="font-size: 1.2rem; font-weight: bold; text-align: center; color: white;"> <?php echo $contact_s['site_title'] ?></a>

        <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-lg-0">
    <li class="nav-item text-danger <?= $current_page == 'index.php' ? 'active' : ''; ?>">
        <a class="navbut nav-link m-2" href="index.php">Home</a>
    </li>
    <li class="nav-item <?= $current_page == 'about.php' ? 'active' : ''; ?>">
        <a class="navbut nav-link m-2" href="about.php">About</a>
    </li>
    <li class="nav-item <?= $current_page == 'specialty.php' ? 'active' : ''; ?>">
        <a class="navbut nav-link m-2" href="specialty.php">Services</a>
    </li>
    <li class="nav-item <?= $current_page == 'equipment.php' ? 'active' : ''; ?>">
        <a class="navbut nav-link m-2" href="equipment.php">Equipment</a>
    </li>
    <li class="nav-item <?= $current_page == 'trainors.php' ? 'active' : ''; ?>">
        <a class="navbut nav-link m-2" href="trainors.php">Trainor</a>
    </li>
    <li class="nav-item <?= $current_page == 'pricing.php' ? 'active' : ''; ?>">
        <a class="navbut nav-link m-2" href="pricing.php">Plan</a>
    </li>
    <li class="nav-item <?= $current_page == 'product.php' ? 'active' : ''; ?>">
        <a class="navbut nav-link m-2" href="product.php">Product</a>
    </li>
    <li class="nav-item <?= $current_page == 'contact.php' ? 'active' : ''; ?>">
        <a class="navbut nav-link m-2" href="contact.php">Contact Us</a>
    </li>
</ul>
        </div>
        <div class="drop">
            <?php
                if (isset($_SESSION['login']) && $_SESSION['login']==true)
                { 
                    echo<<<data
                    <div class="btn-group">
                        <button type="button" class="btn shadow-none mr-3 dropdown-toggle" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            $_SESSION[uName]
                        </button>
                        <ul class="dropdown-menu dropdown-menu-lg-end mr-3">
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li><a class="dropdown-item" href="appointment.php">Booking</a></li>
                        <li><a class="dropdown-item" href="subscribed.php">Subscribe</a></li>
                        <li><a class="dropdown-item" href="order.php">Order</a></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                    data;    
                }
                else
                {
                    echo<<<data
                    <button type="button" class="btn shadow-none me-lg-3 me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                    Login
                    </button>
                    <button type="button" class="btn shadow-none" data-bs-toggle="modal" data-bs-target="#registerModal">
                        Register
                    </button>
                    data;
                }
            ?>
    
        </div>
    </div>
</nav>

<!-- LOGIN MODAL -->

<div class="modal fade" id="loginModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="login-form">

                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="bi bi-person-circle fs-3 me-2"></i> Login
                    </h5>
                    <button type="button reset" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Email/Mobile Number</label>
                            <input type="text" name="email_mob" required class="form-control shadow-none">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Password</label>
                            <input type="password" name="pass" required class="form-control shadow-none">
                        </div class=>
                        <div class="d-flex align-items-center mb-4">
                            <a href="javascript: void(0)" class="text-secondary text-decoration-none small"></a>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <button type="submit" class="btn btn1 btn-primary shadow-none me-lg-2 me-3 w-50 my-1">Login</button>
                        </div>
                        <div class="d-flex align-items-center mt-4 justify-content-center">
                            <a class="text-secondary text-decoration-none small">Don't have an account?</a>
                            <a href="javascript: void(0)" class="text-primary text-decoration-none small" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a>
                    </div>                           
                </div>
            </form>               
        </div>
    </div>
</div>


<div class="modal fade" id="registerModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="register-form">

                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="bi bi-person-lines-fill fs-3 me-2"></i>
                        User Registration
                    </h5>
                    <button type="button reset" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                    <div class="modal-body">
                        <span class="badge rounded-pill bg-light text-dark mb-3 text-wrap lh-base">
                            Note: Your details must match your ID.
                        </span>

                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-6 ps-0 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input name="name" type="text" class="form-control shadow-none" required>
                                </div>
                                <div class="col-md-6 p-0 mb-3">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <input name="email" id="email" type="email" class="form-control shadow-none" required>
                                    <div class="input-group-append">
                                        <span id="emailIcon" class="input-group-text">
                                            <i id="emailLoading" class="spinner-border spinner-border-sm" style="display: none;"></i>
                                            <i id="emailValidIcon" class="bi bi-check-circle text-success" style="display: none;"></i>
                                            <i id="emailInvalidIcon" class="bi bi-x-circle text-danger" style="display: none;"></i>
                                        </span>
                                    </div>
                                </div>
                                <div id="emailFeedback" class="invalid-feedback"></div>
                            </div>

                                <div class="col-md-6 ps-0 mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input name="dob" type="date" class="form-control shadow-none" required>
                                </div>
                                <div class="col-md-6 p-0 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input id="phonenum" name="phonenum" value="63" type="number" class="form-control shadow-none" required>
                                <div class="invalid-feedback">
                                Phone number must start with "639" and contain 12 digits.
                                </div>
                                </div>
                                <div class="col-md-12 p-0 mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control shadow-none" rows="3" required></textarea>
                                </div>
                                <div class="col-md-6 ps-0 mb-3">
                                    <label class="form-label">Password</label>
                                    <input id="pass" name="pass" type="password" class="form-control shadow-none" required>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="uppercaseCheck">
                                        <label class="form-check-label" for="uppercaseCheck">Uppercase</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="lowercaseCheck">
                                        <label class="form-check-label" for="lowercaseCheck">Lowercase</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="numberCheck">
                                        <label class="form-check-label" for="numberCheck">Number</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="specialCharCheck">
                                        <label class="form-check-label" for="specialCharCheck">Special Character</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="passLength">
                                        <label class="form-check-label" for="passLength">Password Length 8-16</label>
                                    </div>
                                </div>
                                <div class="col-md-6 p-0 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input name="cpass" id="cpass" type="password" class="form-control shadow-none" required>
                                <div id="cpassFeedback" class="invalid-feedback">Passwords do not match.</div>
                                </div>
                                <div class="col-md-6 ps-0 mb-4">
                                    <label class="form-label">Picture</label>
                                    <input name="profile" type="file" accept=".jpg, .jpeg, .png, .webp" class="form-control shadow-none">
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                        <button type="submit" class="btn btn1 btn-primary shadow-none me-lg-2 me-3 w-50 my-1">Register</button>
                        </div>
                   
                </div>
            </form>               
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    <!-- Data Table -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
<script>
$(document).ready(function() {
    // Function to update password complexity checkboxes based on current password input
    function updatePasswordComplexityChecks() {
        const passInput = $('#pass');
        const hasUppercase = /[A-Z]/.test(passInput.val());
        const hasLowercase = /[a-z]/.test(passInput.val());
        const hasNumber = /[0-9]/.test(passInput.val());
        const hasSpecialChar = /[!@#\$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(passInput.val());

        // Update checkbox states
        $('#uppercaseCheck').prop('checked', hasUppercase);
        $('#lowercaseCheck').prop('checked', hasLowercase);
        $('#numberCheck').prop('checked', hasNumber);
        $('#specialCharCheck').prop('checked', hasSpecialChar);
        $('#passLength').prop('checked', passInput.val().length >= 8 && passInput.val().length <= 16);

        // Add or remove invalid class based on the checks
        if (hasUppercase && hasLowercase && hasNumber && hasSpecialChar && passInput.val().length >= 8 && passInput.val().length <= 16) {
            passInput.removeClass('is-invalid');
            return true;
        } else {
            passInput.addClass('is-invalid');
            return false;
        }
    }

    // Check password complexity on input
    $('#pass').on('input', updatePasswordComplexityChecks);

    // Validate password requirements on form submission
    $('#register-form').on('submit', function(event) {
        if (!updatePasswordComplexityChecks()) {
            event.preventDefault(); // Prevent form submission if requirements are not met
            alert("Your Password Doesn't Meet Our Requirements. Please ensure it has at least one uppercase letter, one lowercase letter, one number, one special character, and is between 8-16 characters.");
            console.log("Password requirements not met."); // Debugging log
            return false; // Explicitly prevent submission
        }
    });
});

$(document).ready(function() {
function validateConfirmPassword() {
        const password = $('#pass').val();
        const confirmPassword = $('#cpass').val();

        if (confirmPassword === password && confirmPassword.length > 0) {
            $('#cpass').removeClass('is-invalid').addClass('is-valid');
            $('#cpassFeedback').hide();
        } else {
            $('#cpass').removeClass('is-valid').addClass('is-invalid');
            $('#cpassFeedback').show();
        }
    }

    // Trigger confirm password validation on input
    $('#cpass').on('input', validateConfirmPassword);
    $('#pass').on('input', validateConfirmPassword); // also trigger on main password change
});
$(document).ready(function() {
    $('#phonenum').on('input', function() {
        const phoneNumber = $(this).val();
        const isValid = /^639\d{9}$/.test(phoneNumber);

        if (isValid) {
            $(this).removeClass('is-invalid');
        } else {
            $(this).addClass('is-invalid');
        }
    });
});

$(document).ready(function() {


let debounceTimer;
const apiKeys = ['d3ecd0a6c0cf49eb9501d93c8a7eae35', '1b40e4e18b8f4d208ec5ff030ca3e8fb', '6ef4250117ba4e7aa1d9ca570d348cea', '02bc4aef1009410ea0695b9e6cfabca5','5675ef8c345b412e94b4097f4512abd6']; // Replace with your actual ZeroBounce API keys
let currentKeyIndex = 0;
    function debounce(func, delay) {
        return function() {
            const context = this, args = arguments;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => func.apply(context, args), delay);
        };
    }

    function showLoading() {
        $('#emailLoading').show();
        $('#emailValidIcon, #emailInvalidIcon').hide();
        $('#emailFeedback').hide();
    }
    function showValid() {
        $('#emailLoading').hide();
        $('#emailValidIcon').show();
        $('#emailInvalidIcon').hide();
        $('#emailFeedback').removeClass('text-danger').addClass('text-success').text('Email is valid.').show();
    }
    function showInvalid(message) {
        $('#emailLoading').hide();
        $('#emailValidIcon').hide();
        $('#emailInvalidIcon').show();
        $('#emailFeedback').removeClass('text-success').addClass('text-danger').text(message).show();
    }

    function checkCreditsAndValidateEmail(email) {
        // Check remaining credits for the current API key
        $.ajax({
            url: 'https://api.zerobounce.net/v2/getcredits',
            type: 'GET',
            data: {
                api_key: apiKeys[currentKeyIndex]
            },
            success: function(response) {
                if (response.Credits && parseInt(response.Credits) > 0) {
                    // If credits are available, validate the email
                    validateEmailWithZeroBounce(email);
                } else {
                    // Switch to the next API key if out of credits
                    currentKeyIndex = (currentKeyIndex + 1) % apiKeys.length;
                    if (currentKeyIndex === 0) {
                        showInvalid('All API keys are out of credits. Please try again later.');
                    } else {
                        // Retry with the next API key
                        checkCreditsAndValidateEmail(email);
                    }
                }
            },
            error: function() {
                showInvalid('Failed to check credits. Please try again later.');
            }
        });
    }

    function validateEmailWithZeroBounce(email) {
        showLoading();
        const apiKey = apiKeys[currentKeyIndex];

        $.ajax({
            url: 'https://api.zerobounce.net/v2/validate',
            type: 'GET',
            data: {
                api_key: apiKey,
                email: email
            },
            success: function(response) {
                if (response.status === 'valid') {
                    showValid();
                } else {
                    const errorMessage = {
                        'invalid': 'Email is invalid. Please enter a different email.',
                        'catch-all': 'Email may be valid but is risky.',
                        'unknown': 'Email validation status unknown. Please double-check.',
                        'spamtrap': 'This email address is flagged as a spam trap.'
                    };
                    showInvalid(errorMessage[response.status] || 'Unexpected status. Please try again later.');
                }
                if (response.did_you_mean) {
                    $('#emailFeedback').append(`<br>Did you mean: <strong>${response.did_you_mean}</strong>?`);
                }
            },
            error: function() {
                // On error, switch to the next API key and try again
                currentKeyIndex = (currentKeyIndex + 1) % apiKeys.length;
                if (currentKeyIndex === 0) {
                    showInvalid('Failed to validate email. All API keys are unavailable. Please try again later.');
                } else {
                    checkCreditsAndValidateEmail(email); // Retry with the next key
                }
            }
        });
    }

    // Attach the debounce function to the email input field
    $('input[name="email"]').on('input', debounce(function() {
        const email = $(this).val();
        const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailPattern.test(email)) {
            showInvalid('Please enter a valid email address.');
            return;
        }
        checkCreditsAndValidateEmail(email);
    }, 500));
});

</script>

</body>
</html>
