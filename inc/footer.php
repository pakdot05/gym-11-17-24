<!-- FOOTER -->

<?php 
    $contact_q = "SELECT * FROM `contact_details` WHERE `contact_id`=?";
    $values = [1];
    $contact_r = mysqli_fetch_assoc(select($contact_q,$values,'i'));
    
    $contact_q = "SELECT * FROM `settings` WHERE `settings_id`=?";
    $values = [1];
    $contact_r = mysqli_fetch_assoc(select($contact_q,$values,'i'));
?>

<div class="container-fluid mt-5" style="background-color: #393E46; color: white;">
    <div class="row">
        <div class="col-lg-4 p-4 fw-light" style="width: 50vw;">
            <h3 class="h-font fw-bold fs-3 mb-2 fw-light"><?php echo $contact_r['site_title'] ?></h3>
            <p class="fw-light"><?php echo $contact_r['site_about'] ?></p>
        </div>
        <div class="iconlinks col-lg-4 p-4" style="width: 30vw;">
            <h5 class="mb-3">Links</h5>
            <a href="index.php" class="d-inline-block mb-2 text-white fw-light text-decoration-none">Home</a> <br>
            <a href="about.php" class="d-inline-block mb-2 text-white fw-light text-decoration-none">About</a>
            <a href="trainors.php" class="d-inline-block mb-2 text-white fw-light text-decoration-none">Trainors</a> <br>
            <a href="index.php" class="d-inline-block mb-2 text-white fw-light text-decoration-none">Appointment</a> <br>
            <a href="contact.php" class="d-inline-block mb-2 text-white fw-light text-decoration-none">Contact Us</a> <br>
            <a href="pricing.php" class="d-inline-block mb-2 text-white fw-light text-decoration-none">Plan</a><br>
            <a href="product.php" class="d-inline-block mb-2 text-white fw-light text-decoration-none">Product</a>
        </div>
        <div class="iconlinks col-lg-4 p-4" style="width: 20%;">
            <h5 class="mb-3">Follow us</h5>
            
            <?php 
                $contact_q = "SELECT * FROM `contact_details` WHERE `contact_id`=?";
                $values = [1];
                $contact_r = mysqli_fetch_assoc(select($contact_q,$values,'i')); 
            ?>

            <?php
                if($contact_r['twt']!=''){
                    echo<<<data
                        <a href="$contact_r[twt]" class="d-inline-block text-white fw-light text-decoration-none mb-2">
                            <i class="bi bi-twitter-x me-1"></i> Twitter
                        </a> <br>
                    data;
                }
            ?>
        
            <a href="<?php echo $contact_r['ig'] ?>" class="d-inline-block text-white fw-light text-decoration-none mb-2">
                <i class="bi bi-instagram me-1"></i> Instagram
            </a> <br>
            <a href="<?php echo $contact_r['fb'] ?>" class="d-inline-block text-white fw-light text-decoration-none">
                <i class="bi bi-facebook me-1"></i> Facebook
            </a>

        </div>
    </div>
</div>

<h6 class="text-center text-white p-3 m-0" style="background-color: #40534C;">Designed and Developed by Team Cyber Samurai</h6>
<style>
    @media (max-width: 576px) {
        .container-fluid h3, .container-fluid h5 {
            font-size: 1.2rem;
        }
        .container-fluid p {
            font-size: 0.9rem;
        }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<script>

    function alert(type,msg,position='body')
    {
        let bs_class = (type == 'success') ? 'alert-success' : 'alert-danger';
        let element = document.createElement('div');
        element.innerHTML = `
            <div class="alert ${bs_class} alert-dismissible fade show custom-alert text-center" role="alert">
                <strong class="me-3">${msg}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        if(position=='body'){
            document.body.append(element);
            element.classList.add('custom-alert');
        }
        else{
            document.getElementById(position).appendChild(element);
        }
        setTimeout(remAlert, 2000);
    }

    function remAlert()
    {
        document.getElementsByClassName('alert')[0].remove();
    }

    function setActive() {
    let navbar = document.getElementById('nav-bar');
    let a_tags = navbar.getElementsByTagName('a'); // Correct method to get all <a> tags

    for (let i = 0; i < a_tags.length; i++) {
        let file = a_tags[i].href.split('/').pop();
        let file_name = file.split('.')[0];

        if (document.location.href.indexOf(file_name) >= 0) {
            a_tags[i].classList.add('active');
        }
    }
}

    let register_form = document.getElementById('register-form');

    register_form.addEventListener('submit', (e)=>{
        e.preventDefault();

        let data = new FormData();

        data.append('name', register_form.elements['name'].value);
        data.append('email', register_form.elements['email'].value);
        data.append('dob', register_form.elements['dob'].value);
        data.append('phonenum', register_form.elements['phonenum'].value);
        data.append('address', register_form.elements['address'].value);
        data.append('pass', register_form.elements['pass'].value);
        data.append('cpass', register_form.elements['cpass'].value);
        data.append('profile', register_form.elements['profile'].files[0]);
        data.append('register','');

        var myModal = document.getElementById('registerModal');
        var modal = bootstrap.Modal.getInstance(myModal);
        modal.hide();

        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/login_register.php",true);

        xhr.onload = function(){
            if(this.responseText == 'pass_mismatch'){
                alert('error',"Password Mismatch!");
            }
            else if(this.responseText == 'pass_invalid'){
        alert('error', "Password must include an uppercase letter, a lowercase letter, a number, a special character, and be between 8-16 characters.");
    }
            else if(this.responseText == 'email_already'){
                alert('error',"Email is already used!");
            }
       
            else if(this.responseText == 'num_req'){
                alert('error',"Phone number should be 12 digits and Start with 63!");
            }
            else if(this.responseText == 'age_below_16'){
                alert('error',"Should be 16 above to register!");
            }
            else if(this.responseText == 'phone_already'){
                alert('error',"Phone number is already used!");
            }
            else if(this.responseText == 'inv_img'){
                alert('error',"Only JPG, WEBP & PNG images are allowed!");
            }
            else if(this.responseText == 'upd_failed'){
                alert('error',"Image upload failed!");
            }
            else if(this.responseText == 'ins_failed'){
                alert('error',"Registration failed! Server down!");
            }
            else{
                alert('success',"Registration successful!")
                register_form.reset();
            }
        }

        xhr.send(data);
    });

    let login_form = document.getElementById('login-form');

    login_form.addEventListener('submit', (e)=>{
        e.preventDefault();

        let data = new FormData();

        data.append('email_mob', login_form.elements['email_mob'].value);
        data.append('pass', login_form.elements['pass'].value);
        data.append('login','');

        var myModal = document.getElementById('loginModal');
        var modal = bootstrap.Modal.getInstance(myModal);
        modal.hide();

        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/login_register.php",true);

        xhr.onload = function(){
           
            if(this.responseText == 'inv_email_mob'){
                alert('error',"Invalid Email/Mobile number!");
            }
            else if(this.responseText == 'inactive'){
                alert('error',"Account Suspended!");
            }
            else if(this.responseText == 'invalid_pass'){
                alert('error',"Incorrect password!");
            }
            else{
                let fileurl = window.location.href.split('/').pop().split('?').shift();
                if(fileurl == 'trainor_details.php'){
                    window.location = window.location.href;
                }
                else{
                    // window.location = window.location.pathname;
                }             
            }
        }

        xhr.send(data);
    });

    function checkLoginToApp(status,trainor_id)
    {
        if(status){
            window.location.href='confirm_app.php?trainor_id='+trainor_id;
        }
        else{
            alert('error','Please login to make a Booking!');
        }
    }

    setActive();
    function validateCheckout() {
        // Assuming a session variable or similar is used to determine if the user is logged in
        let isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

        if (isLoggedIn) {
            window.location.href = 'product.php';
        } else {
            // Show an alert if not logged in
            alert('error', 'Please log in to proceed to checkout!');
        }
    }
    
</script>
