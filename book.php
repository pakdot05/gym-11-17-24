<?php 
session_start();
?>
<?php

require('inc/links.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
if (!isset($_SESSION['uId'])) {
    die("User not logged in");
}


$user_id = $_SESSION['uId'];
$statusSql = "SELECT appointment_status FROM user_cred WHERE user_id = ?";
$statusResult = select($statusSql, [$user_id], 'i');
$userStatus = $statusResult->fetch_assoc();

$trainer_contact = '';
$trainer_id = isset($_SESSION['trainor']['trainor_id']) ? $_SESSION['trainor']['trainor_id'] : null;
$trainer_email = '';

if ($trainer_id) {
    // Retrieve trainer's contact number and email
    $trainerSql = "SELECT contact_no, email FROM trainor WHERE trainor_id = ?";
    $trainerResult = select($trainerSql, [$trainer_id], 'i');
    if ($trainerResult->num_rows > 0) {
        $trainerData = $trainerResult->fetch_assoc();
        $trainer_contact = $trainerData['contact_no'];
        $trainer_email = $trainerData['email'];
    }
}

// Get current time in Philippine Timezone
date_default_timezone_set('Asia/Manila');
$currentTime = new DateTime();

$duration = 60;
$cleanup = 0;
$start = "07:00";
$end = "20:00";
$date = isset($_GET['date']) ? date('Y-m-d', strtotime($_GET['date'])) : null;
function timeslots($duration, $cleanup, $start, $end, $selectedDate) {
    global $currentTime;
    
    $start = new DateTime($start);
    $end = new DateTime($end);
    $interval = new DateInterval("PT" . $duration . "M");
    $cleanupInterval = new DateInterval("PT" . $cleanup . "M");
    $slots = [];

    // Check if selected date is today
    $today = (new DateTime())->format('Y-m-d');
    $isToday = ($selectedDate === $today);

    // Adjust start time for today; otherwise, use 7:00 AM
    if ($isToday && $start < $currentTime) {
        $start = clone $currentTime;
    } else {
        $start->setTime(7, 0); // Start at 7:00 AM for future dates
    }

    // Ensure `start` is aligned to the nearest 30-minute mark
    $start->setTime($start->format('H'), round($start->format('i') / 60) * 60);
    $endPeriod = clone $start;
    $endPeriod->add($interval);

    while ($endPeriod <= $end) {
        $endPeriod = clone $start;
        $endPeriod->add($interval);

        $isPast = ($isToday && $start < $currentTime);

        // Only add slots that are in the future for today or all slots for future dates
        if (!$isPast || !$isToday) {
            $slots[] = [
                'slot' => $start->format("H:iA") . "-" . $endPeriod->format("H:iA"),
                'isPast' => $isPast
            ];
        }

        // Move to the next slot time
        $start->add($interval)->add($cleanupInterval);
    }

    return $slots;
}


// Call the function with arguments and store the result
$timeslots = timeslots($duration, $cleanup, $start, $end, $date);

if (isset($_GET['date']) && $trainer_id) {
    $date = $_GET['date'];
    $date = date('Y-m-d', strtotime($date));

    // Retrieve bookings only for the selected date and trainer
    $bookingsSql = "SELECT timeslot FROM bookings WHERE date = ? AND trainor_id = ?";
    $bookingsResult = select($bookingsSql, [$date, $trainer_id], 'si');
    $bookings = array();
    
    if ($bookingsResult->num_rows > 0) {
        while ($row = $bookingsResult->fetch_assoc()) {
            $bookings[] = $row['timeslot'];
        }
    }
}

if (isset($_POST['submit']) && $userStatus['appointment_status'] != 'pending' && $userStatus['appointment_status'] != 'approved') {
    $user_id = $_SESSION['uId'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phonenum = $_POST['phonenum'];
    $note = $_POST['note'];
    $trainor_name = $_POST['trainor_name'];
    $timeslot = $_POST['timeslot'];

    // Check if the timeslot is already booked for this trainer
    $checkBookingSql = "SELECT * FROM bookings WHERE date = ? AND timeslot = ? AND trainor_id = ?";
    $checkBookingResult = select($checkBookingSql, [$date, $timeslot, $trainer_id], 'ssi');
    if ($checkBookingResult->num_rows > 0) {
        // Slot is already booked
    } else {
        // Insert the new booking
        $insertBookingSql = "INSERT INTO bookings (user_id, name, timeslot, email, phonenum, note, date, trainor_name, trainor_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertBookingResult = insert($insertBookingSql, [$user_id, $name, $timeslot, $email, $phonenum, $note, $date, $trainor_name, $trainer_id], 'isssssssi');

        // Update appointment status in user_cred table to pending
        $updateStatusSql = "UPDATE user_cred SET appointment_status = 'pending' WHERE user_id = ?";
        $updateStatusResult = update($updateStatusSql, [$user_id], 'i');

        // Display success message
        $msg = "<div class='alert alert-success'>Appointment Submitted!</div>";
        $bookings[] = $timeslot; 

        // Send email to both user and trainer
        require_once './phpmailer/src/Exception.php';
        require_once './phpmailer/src/PHPMailer.php';
        require_once './phpmailer/src/SMTP.php';

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'lorem.ipsum.sample.email@gmail.com';
            $mail->Password   = 'tetmxtzkfgkwgpsc';
            $mail->SMTPSecure = 'ssl';
            $mail->Port       = 465;

            // Send email to user
            $mail->setFrom('lorem.ipsum.sample.email@gmail.com', 'Geafitnessgym');
            $mail->addAddress($_POST["email"]);
            $mail->addReplyTo('lorem.ipsum.sample.email@gmail.com', 'Geafitnessgym');

            // Content for user email
            $mail->isHTML(true);
            $mail->Subject = 'Appointment Request Confirmation - Geafitnessgym';
            $mail->Body = "
                <p>Dear {$name},</p>

                <p>Your appointment request has been submitted successfully.</p>

                <p><strong>Details:</strong></p>

                <ul>
                    <li><strong>Date:</strong> {$date}</li>
                    <li><strong>Timeslot:</strong> {$timeslot}</li>
                    <li><strong>Trainor:</strong> {$trainor_name}</li>
                    <li><strong>Trainor's Contact:</strong> {$trainer_contact}</li>
                    <li><strong>Email:</strong> {$trainer_email}</li>
                    <li><strong>Phone:</strong> {$phonenum}</li>
                    <li><strong>Note:</strong> {$note}</li>
                </ul>

                <p>You will receive another email once your appointment is confirmed or declined.</p>
                <p>Thank you,</p>

                <p>Gymko Team</p>
            ";

            $mail->send();

            // Send email to trainer
            $mail->clearAddresses();
            $mail->addAddress($trainer_email);
            $mail->Subject = 'New Appointment Request at Geafitnessgym';
            $mail->Body = "
                <p>Dear {$trainor_name},</p>

                <p>You have a new appointment request from {$name}.</p>

                <p><strong>Details:</strong></p>

                <ul>
                    <li><strong>Date:</strong> {$date}</li>
                    <li><strong>Timeslot:</strong> {$timeslot}</li>
                    <li><strong>Email:</strong> {$email}</li>
                    <li><strong>Phone:</strong> {$phonenum}</li>
                    <li><strong>Note:</strong> {$note}</li>
                </ul>

                <p>Please review the request and confirm or decline the appointment.</p>
                <p>Thank you,</p>

                <p>Gymko Team</p>
            ";

            $mail->send();

            echo "
                <script>
                document.location.href = 'book.php';
                </script>
            ";
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        $stmt->close();
    }
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Appointment</title>
    <style>
        .btn{
            background-color:  #323232 !important;
            color: white !important;
        }
        .btn:hover{
            background-color: #096066 !important;
        }
        .btn1{
            border: 1px solid #09858d !important;
            color: #09858d !important;
            text-decoration: none !important;
        }
        .btn1:hover{
            background-color: #096066 !important;
            color: white !important;
        }
    </style>
</head>
<body class="bg-light">
    <?php require('inc/header.php') ?>

    <div class="container">
        <div class="row d-flex justify-content-center bg-light shadow mt-5 rounded mb-3 p-2">
            <div class="mt-4">
                <h2 class="text-center">Appointment for Date: 
                    <?php
                        if (isset($date)) {
                            echo date('m/d/Y', strtotime($date)); 
                        } else {
                            echo "Please select a date"; 
                        }
                    ?>
                </h2>
            </div>
            <div class="col-md-12">
                <?php echo isset($msg) ? $msg : ""; ?>
            </div>
            <hr style="width: 98%;">

            <?php if ($userStatus['appointment_status'] == 'pending') { ?>
                <div class="alert alert-warning">You already have a pending appointment. Please complete or cancel your current appointment before booking a new one.</div>
            <?php } elseif ($userStatus['appointment_status'] == 'approved') { ?>
                <div class="alert alert-warning">You already have an appointment. Please complete or cancel your current appointment before booking a new one.</div>
            <?php } else { ?>
                <div class="col-md-12">
                    <?php
                        if (isset($date) && isset($timeslots)) {
                            // Generate timeslot buttons
                            foreach ($timeslots as $ts) {
                                $timeslot = explode(' (', $ts['slot'])[0];

                                if (in_array($timeslot, $bookings)) {
                                    echo '<button class="btn m-1 p-2 appoint unavailable" style="width: 170px; background-color: #dc3545 !important;" disabled>' . $timeslot . '</button>';
                                } elseif ($ts['isPast']) {
                                    continue;
                                } else {
                                    echo '<button class="btn m-1 p-2 appoint" style="width: 170px;" data-bs-toggle="modal" data-bs-target="#myModal" data-timeslot="' . $timeslot . '">' . $timeslot . '</button>';
                                }
                            }
                        } else {
                            echo "Please select a date.";
                        }
                    ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php
    // Generate modals dynamically
    if (isset($date) && isset($timeslots)) {
        foreach ($timeslots as $ts) {
            $timeslot = explode(' (', $ts['slot'])[0];
            if (in_array($timeslot, $bookings)) {
                continue; // Skip if timeslot is already booked
            }
        }
    }
            ?>
    <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Appointment: <span id="slot"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                    <div class="form-group mb-3">
                                    <label for="timeslot">Timeslot</label>
                                    <input required type="text" name="timeslot" id="timeslot" class="form-control" value="">
                                </div>
                        <div class="form-group mb-3">
                            <label for="trainor_name">Trainor</label>
                            <input required type="text" readonly name="trainor_name" id="trainor_name" class="form-control" value="<?php echo isset($_SESSION['trainor']['name']) ? $_SESSION['trainor']['name'] : ''; ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label for="trainer_contact">Trainer's Contact</label>
                            <input required type="text" readonly name="trainer_contact" id="trainer_contact" class="form-control" value="<?php echo $trainer_contact; ?>">
                        </div>
                       <div class="form-group mb-3">
                            <label for="username">Name</label>
                            <input required type="text" name="name" id="username" class="form-control" value="<?php echo isset($_SESSION['uName']) ? $_SESSION['uName'] : ''; ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label for="email">Email</label>
                            <input required type="email" name="email" id="email" class="form-control" value="<?php echo isset($_SESSION['uEmail']) ? $_SESSION['uEmail'] : ''; ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label for="phonenum">Phone no.</label>
                            <input required type="number" name="phonenum" id="phonenum" class="form-control" value="<?php echo isset($_SESSION['uPhone']) ? $_SESSION['uPhone'] : ''; ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label for="note">Note (optional)</label>
                            <textarea name="note" id="note" class="form-control shadow-none" rows="3"></textarea>
                        </div>
                        <div class="form-group text-end">
                            <button type="submit" class="btn custom-bg text-white" name="submit">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".appoint").click(function() {
                var timeslot = $(this).data('timeslot');
                $("#slot").html(timeslot);
                $("#timeslot").val(timeslot);
                $("#myModal").modal("show");
          
        
           $('#appointmentForm').submit();
    });
});
    </script>

    <script>
   function goBack() {
            window.history.back();
        }
    </script>
<br>
<br>
<br>
<br>
<br>
<br>
    <?php require('inc/footer.php') ?>
</body>;
</html> 
