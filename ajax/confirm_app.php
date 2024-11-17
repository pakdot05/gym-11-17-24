<?php

    require('../admin/inc/db_config.php');
    require('../admin/inc/essentials.php');

    // if(isset($_POST['check_availability']))
    // {
    //     $frm_data = filteration($_POST);
    //     $status = "";
    //     $result = "";

    //     // checkin checkout validation

    //     $today_date = new DateTime(date("Y-m-d"));
    //     $checkin_date = new DateTime($frm_data['check_in']);
    //     $checkout_date = new DateTime($frm_data['check_out']);

    //     if($checkin_date == $checkout_date){
    //         $status = 'check_in_out_equal';
    //         $result = json_encode(["status"=>$status]);
    //     }
    //     else if($checkout_date < $checkin_date){
    //         $status = 'check_out_earlier';
    //         $result = json_encode(["status"=>$status]);
    //     }
    //     else if($checkin_date < $today_date){
    //         $status = 'check_out_earlier';
    //         $result = json_encode(["status"=>$status]);
    //     }

    //     // check booking availability if status is blank else return the error

    //     if($status!=''){
    //         echo $result;
    //     }
    //     else{
    //         session_start();
    //         $_SESSION['room'];

    //         // run query to check room is available or not
    //         $count_days = date_diff($checkin_date,$checkout_date)->days;
    //         $payment = $_SESSION['room']['price'] * $count_days;

    //         $_SESSION['room']['payment'] = $payment;
    //         $_SESSION['room']['available'] = true;
            
    //         $result = json_encode(["status"=>'available', "days"=>$count_days, "payment"=> $payment]);
    //         echo $result;
    //     }
    // }

    if(isset($_POST['check_availability']))
        {
            $frm_data = filteration($_POST);
            $status = "";
            $result = "";


            // Get the selected date and time
            $selected_date = $frm_data['date'];
            $selected_time = $frm_data['time'];

            // Check if the selected date and time are available
            // You need to implement your logic here to check the availability in your database
            // For example, you can query your database to check if there are any appointments already scheduled for the selected date and time

            // Assuming $available is a boolean variable indicating availability
            $available = true; // Example, replace this with your actual logic

            if($available) {
                session_start();
                $status = 'available';
            } else {
                $status = 'unavailable';
            }

            // Encode the result as JSON and echo it
            $result = json_encode(["status" => $status]);
            echo $result;
            
        }
    
?>