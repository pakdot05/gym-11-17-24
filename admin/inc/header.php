<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        :root {
            --blue: #40534C;
            --blue-hover: #D6BD98;
        }

        .nav a {
            display: block;
            padding: 10px;
            margin: 3px;
            text-decoration: none;
            transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out;
            border-radius: 5px;
        }

        .nav a:hover {
            background-color: var(--blue-hover);
            color: white !important;
        }

        .nav a.active {
            background-color: var(--blue-hover);
            color: white !important;
        }

        .logo {
            height: 50px;
            width: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Dropdown styles */
        .dropdown-menu {
            width: 100%;
            position: relative;
            top: 100%;
            left: 0;
            background-color: var(--blue);
            z-index: 1000;
            display: none;
            max-height: calc(80vh - 100px); /* Dynamically adjust height for visibility */
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-menu li {
            width: 100%;
            padding: 1px;
            box-sizing: border-box;
        }

        .dropdown-menu a {
            color: white;
            display: block;
        }

        .dropdown-menu a:hover {
            background-color: var(--blue-hover);
        }

        .dropdown-menu .active {
            background-color: var(--blue-hover);
            color: white !important;
        }

        .dropdown-toggle::after {
            margin-left: 5px;
        }
        #dashboard-menu {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            background-color: var(--blue);
        }

        /* Show full menu on desktop, collapse on mobile */
        @media (max-width: 991px) {
            #dashboard-menu {
                display: none;
            }
        }

        @media (min-width: 992px) {
            #dashboard-menu {
                display: block !important;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid ambot text-light p-3 d-flex align-items-center justify-content-between sticky-top">
    <div class="d-flex align-items-center">
        <a href="https://r.search.yahoo.com/_ylt=AwrKFyeq.mxmw14SbaazRwx.;_ylu=Y29sbwNzZzMEcG9zAzEEdnRpZAMEc2VjA3Ny/RV=2/RE=1718446891/RO=10/RU=https%3a%2f%2fweb.facebook.com%2fMinglanillaDentalClinic%2f/RK=2/RS=7yvecSnF0Y43YkmiECpXqcOj7po-">
            <img src="images/logo.jpg" alt="Logo" class="logo">
        </a>
        <a class="navbar-brand" style="font-size: 1.2rem; font-weight: bold; text-align: center; color: white;"> Gym Samurai</a>
    </div>
    <a href="logout.php" class="btn btn-light btn-sm">LOGOUT</a>
</div>

<div class="col-lg-2 ambot border-top border-3" id="dashboard-menu">
    <nav class="navbar navbar-expand-lg ambot">
        <div class="container-fluid flex-lg-column align-items-stretch">
            <h4 class="mt-2 text-white">ADMIN PANEL</h4>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminDropdown" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse flex-column align-items-stretch mt-2" id="adminDropdown">
            <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a id="dashboard-link" class="link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a id="patient-link" class="link" href="users.php">Members</a>
                    </li>
                    <li class="nav-item">
                        <a id="product-link" class="link" href="products.php">Product</a>
                    </li>
                    <li class="nav-item">
                        <a id="trainors-link" class="link" href="trainors.php">Trainor</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button">Invoices</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item link" href="all.php">Monthly Sales</a></li>
                            <li><a class="dropdown-item link" href="invoices.php">Subscription</a></li>
                            <li><a class="dropdown-item link" href="product_sales.php">Sold Product</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a id="specialty-link" class="link" href="specialty.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a id="equipment-link" class="link" href="equipment.php">Equipment</a>
                    </li>
                    <li class="nav-item">
                        <a id="carousel-link" class="link" href="carousel.php">Announcement</a>
                    </li>
                    <li class="nav-item">
                        <a id="settings-link" class="link" href="settings.php">Settings</a>
                    </li>
                   
                </ul>
            </div>
        </div>
    </nav>
</div>
<script>
     document.addEventListener('DOMContentLoaded', function () {
        const navLinks = document.querySelectorAll('.link');
        const dropdownItems = document.querySelectorAll('.dropdown-item');
        const dropdownToggle = document.querySelector('.dropdown-toggle');
        const dropdownMenu = document.querySelector('.dropdown-menu');
        const dropdown = document.querySelector('.nav-item.dropdown');

        // Set active link based on current URL
        function setActiveLink() {
            const currentPath = window.location.pathname.split('/').pop();

            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });

            dropdownItems.forEach(item => {
                if (item.getAttribute('href') === currentPath) {
                    item.classList.add('active');
                    dropdownMenu.classList.add('show'); // Keep dropdown open if an item is active
                } else {
                    item.classList.remove('active');
                }
            });
        }

        // Toggle dropdown visibility on click
        dropdownToggle.addEventListener('click', function (e) {
            e.preventDefault();
            dropdownMenu.classList.toggle('show');
        });

        // Prevent closing dropdown if inside click
        dropdownMenu.addEventListener('click', function (e) {
            e.stopPropagation();
        });

        // Close dropdown if clicked outside
        document.addEventListener('click', function (e) {
            if (!dropdown.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });

        setActiveLink();
    });
</script>


</body>
</html>
