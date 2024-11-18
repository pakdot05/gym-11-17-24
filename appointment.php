<?php 
session_start();
?>
<?php

    require('inc/links.php');
    // Check if user is logged in and session variables are set
    if (!isset($_SESSION['uId'])) {
        die("User not logged in");
    }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYM- About Us</title> 
    <link rel="stylesheet"href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

    <!-- LINKS -->
    <style>

        .box{

            border-top-color: var(--blue) !important;

        }

        th{
            background-color: #323232 !important;
        }

        #time-remaining {
            font-size: 18px;
            margin-bottom: 20px;
        }

    </style>

</head>
<body class="bg-light">

    <!-- HEADER/NAVBAR --> 
    <?php require('inc/header.php') ?> 
    <!-- HEADER/NAVBAR --> 

    <div class="my-5 px-4">
        <h2 class="fw-bold text-center">APPOINTMENT</h2>
        <div class="h-line bg-dark"></div>
    </div>

    <div class="container" style="height: 100vh;">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="text-end mb-4">
                    <input type="text" oninput="search_user(this.value)" class="form-control shadow-none w-25 ms-auto" placeholder="Search...">
                </div>

                <div class="table-responsive-md" style="height: 600px; ">
                    <table class="table table-hover border">
                        <thead>
                            <tr class="bg-dark text-light">
                                <th scope="col">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone no.</th>
                                <th scope="col" width="20%">Note</th>
                                <th scope="col">Trainor</th>
                                <th scope="col">Date</th>
                                <th scope="col">Time</th>
                                <th scope="col">Status</th>   
                                <th scope="col" width="8%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="users-data">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <?php require('inc/footer.php') ?>
    <!-- FOOTER -->

    <script>
    function get_users() {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/appointment.php", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function () {
    console.log(this.responseText); // Add this line
    document.getElementById('users-data').innerHTML = this.responseText;
    updateTimeRemaining();
}
        xhr.send('get_users');
    }

    function remove_user(id) {
        if (confirm("Are you sure you want to remove this appointment?")) {
            let data = new FormData();
            data.append('id', id);
            data.append('remove_user', '');

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/appointment.php", true);

            xhr.onload = function () {
                if (this.responseText == 1) {
                    alert('success', 'Appointment removed!');
                    get_users();
                } else {
                    alert('error', 'Appointment removal failed!');
                }
            }
            xhr.send(data);
        }
    }

    function toggle_status(id, val) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/appointment.php", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function () {
            if (this.responseText == 1) {
                alert('success', 'Status toggled!');
                get_users();
            } else {
                alert('error', 'Server down!');
            }
        }

        xhr.send('toggle_status=' + id + '&value=' + val);
    }

    function search_user(username) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/appointment.php", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function () {
            document.getElementById('users-data').innerHTML = this.responseText;
            updateTimeRemaining();
        }

        xhr.send('search_user&name=' + username);
    }

    window.onload = function () {
        get_users();
    }

    function updateTimeRemaining() {
        const rows = document.querySelectorAll('#users-data tr');
        let timeRemainingText = 'No upcoming appointments.';
        let closestTimeDiff = Number.MAX_SAFE_INTEGER;

        const now = new Date();

        rows.forEach(row => {
            const status = row.children[7].innerText.trim();
            if (status === 'Approved') {
                const date = row.children[5].innerText.trim();
                const timeslot = row.children[6].innerText.trim();
                const appointmentDateTimeString = `${date} ${timeslot.split('-')[0]}`;
                const appointmentDateTime = new Date(appointmentDateTimeString);

                if (appointmentDateTime > now) {
                    const timeDiff = appointmentDateTime - now;
                    if (timeDiff < closestTimeDiff) {
                        closestTimeDiff = timeDiff;
                        const hours = Math.floor(timeDiff / (1000 * 60 * 60));
                        const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);
                        timeRemainingText = `Time remaining for the next appointment: ${hours}h ${minutes}m ${seconds}s`;
                    }
                }
            }
        });

        document.getElementById('time-remaining').innerText = timeRemainingText;
    }

    setInterval(updateTimeRemaining, 1000);
</script>



</body>
</html>
