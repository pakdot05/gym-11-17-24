<?php
    // Include necessary files and configurations
    require('../admin/inc/db_config.php');
    require('../admin/inc/essentials.php');

    // Check if the form data is submitted
    if(isset($_POST['pay_now'])) {
        // Sanitize and validate form data
        $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
        $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);

        // Retrieve user's session data
        session_start();
        $userId = $_SESSION['uId'];

        // Fetch user details from the users table
        $user_query = "SELECT name, address, phonenum FROM users WHERE user_id = ?";
        $stmt_user = $conn->prepare($user_query);
        $stmt_user->bind_param("i", $userId);
        $stmt_user->execute();
        $stmt_user->bind_result($name, $address, $phonenum);
        $stmt_user->fetch();
        $stmt_user->close();

        // Insert appointment data into the database
        $insert_query = "INSERT INTO appointments (user_id, appointment_date, appointment_time) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iss", $userId, $date, $time);

        // Execute the statement
        if($stmt->execute()) {
            // Appointment data inserted successfully
            // Redirect to a confirmation page or display a success message
            header("Location: confirmation.php");
            exit();
        } else {
            // Error occurred while inserting data
            // Display an error message or redirect to an error page
            echo "Error: Unable to process your request.";
        }

        // Close the statement and database connection
        $stmt->close();
        $conn->close();
    } else {
        // Redirect to the appointment page if accessed directly without form submission
        header("Location: appointment.php");
        exit();
    }
?>
