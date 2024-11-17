<?php
require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();
function hasActivity($user_id) {
    global $conn; // Access the global database connection

    $hasBooking = mysqli_num_rows(select("SELECT 1 FROM `bookings` WHERE `user_id` = ?", [$user_id], 'i')) > 0;
    $hasOrder = mysqli_num_rows(select("SELECT 1 FROM `orders` WHERE `user_id` = ?", [$user_id], 'i')) > 0;
    $hasSubscription = mysqli_num_rows(select("SELECT 1 FROM `subscriptions` WHERE `user_id` = ?", [$user_id], 'i')) > 0;

    return $hasBooking || $hasOrder || $hasSubscription;
}

if (isset($_POST['get_users'])) {
    $res = selectAll('user_cred');
    $i = 1;
    $data = "";

    while ($row = mysqli_fetch_assoc($res)) {
        // Ensure that `status` is either 1 or 0, defaulting to 0 if not set
        $userStatus = isset($row['status']) ? (int)$row['status'] : 0;

        // Check if the user has any activity
        $hasActivity = hasActivity($row['user_id']);

        // Status toggle button HTML
        if ($hasActivity) {
            // User has activity, keep status as is
            $status = $userStatus === 1 ? "<button onclick='toggleUserAccountStatus($row[user_id],0)' class='btn btn-success btn-sm shadow-none'>Active</button>" : "<button onclick='toggleUserAccountStatus($row[user_id],1)' class='btn btn-danger btn-sm shadow-none'>Inactive</button>";
        } else {
            // User has no activity, check last activity date
            $lastActivityDate = strtotime($row['datentime']); // Get last activity timestamp
            $sevenDaysAgo = strtotime('-7 days'); // Calculate timestamp for 7 days ago

            if ($lastActivityDate < $sevenDaysAgo) {
                // User was inactive for 7 days, set status to inactive
                $userStatus = 0; // Set status to inactive in the database
                $status = "<button onclick='toggleUserAccountStatus($row[user_id],1)' class='btn btn-danger btn-sm shadow-none'>Inactive</button>";
            } else {
                // User was inactive less than 7 days, keep status as is
                $status = $userStatus === 1 ? "<button onclick='toggleUserAccountStatus($row[user_id],0)' class='btn btn-success btn-sm shadow-none'>Active</button>" : "<button onclick='toggleUserAccountStatus($row[user_id],1)' class='btn btn-danger btn-sm shadow-none'>Inactive</button>";
            }
        }

        // Format the date
        $date = date("d-m-Y", strtotime($row['datentime'] ?? 'now'));
        // Append row data to $data
        $data .= "
            <tr data-user-id='$row[user_id]'>
    <td>$i</td>
    <td>$row[name]</td>
    <td>$row[email]</td>
    <td>$row[phonenum]</td>
    <td>$row[address]</td>
    <td>$row[dob]</td>
    <td>$status</td>
    <td>$date</td>
   <td>
            <button class='btn btn-primary btn-sm shadow-none' onclick='openUserDetailsModal($row[user_id])'>View</button>
            <button onclick='remove_user($row[user_id],0)' class='btn btn-danger shadow-none btn-sm'><i class='bi bi-trash'></i></button>
        </td>
</tr> 
        ";
        $i++;
    }

    echo $data;
}

if (isset($_POST['fetch_user_details'])) {
    $user_id = filter_var($_POST['fetch_user_details'], FILTER_SANITIZE_NUMBER_INT);

    // Fetch user details
    $query_user = "SELECT * FROM `user_cred` WHERE `user_id` = ?";
    $res_user = select($query_user, [$user_id], 'i');
    $user_details = mysqli_fetch_assoc($res_user);

    $query_bookings = "SELECT b.*, t.name as trainer_name, t.email as trainer_email, t.contact_no as trainer_phone, t.info as trainer_info
    FROM `bookings` b 
    JOIN `trainor` t ON b.trainor_id = t.trainor_id 
    WHERE b.user_id = ?";
$res_bookings = select($query_bookings, [$user_id], 'i');

    $bookings_data = "";
    $i = 1;
    while ($booking = mysqli_fetch_assoc($res_bookings)) {
        $date = date("d-m-Y", strtotime($booking['date']));
        $time = $booking['timeslot']; 
        $names = $booking['name'];
    
        // Add the action buttons for each booking
        $status = "<button data-booking-id='$booking[id]' data-status='$booking[status]' class='btn btn-secondary btn-sm shadow-none'>Pending</button>";
        if ($booking['status'] == 1) {
            $status = "<button data-booking-id='$booking[id]' data-status='$booking[status]' class='btn btn-dark btn-sm shadow-none'>Approved</button>";
        } else if ($booking['status'] == 2) {
            $status = "<button data-booking-id='$booking[id]' data-status='$booking[status]' class='btn btn-success btn-sm shadow-none' disabled>Completed</button>";
        } 
        // Remove the else block, as it's causing the "Unknown" status
    
        $delete_btn = "<button onclick='remove_booking($booking[id])' class='btn btn-danger shadow-none btn-sm'><i class='bi bi-trash'></i> Delete</button>";
        $bookings_data .= "
            <tr>
                <td>{$i}</td>
                <td>{$booking['trainer_name']}</td>
                <td>{$booking['trainer_email']}</td>
                <td>{$booking['trainer_phone']}</td>
                <td>{$booking['note']}</td>
                <td>{$date}</td>
                <td>{$time}</td>
                <td>{$status}</td>
                <td>{$delete_btn}</td>
            </tr>
        ";
        $i++;
    }
    // Ensure that the image path is correct
    $user_profile_image = "../images/users/{$user_details['profile']}"; // Adjust path as per your directory structure

    $del_btn = "<button onclick='remove_user($user_details[user_id],0)' class='btn btn-danger shadow-none btn-sm'><i class='bi bi-trash'></i> Delete</button>";
    $status = "<button onclick='toggle_status($user_details[user_id],0)' class='btn btn-success btn-sm shadow-none'>" . ($user_details['status'] ? 'active' : 'inactive') . "</button>";
    if (!$user_details['status']) {
        $status = "<button onclick='toggle_status($user_details[user_id],1)' class='btn btn-danger btn-sm shadow-none'>inactive</button>";
    }

    echo "

<div class='container my-5'>
    <div class='card mx-auto text-center shadow-sm' style='max-width: 800px; padding: 20px;'>
        <!-- Profile Picture -->
        <div class='photo mx-auto' style='height: 120px; width: 120px; border-radius: 50%; overflow: hidden;'>
            <img src='$user_profile_image' alt='Profile Image' style='height: 100%; width: 100%; object-fit: cover;'>
        </div>

        <!-- Name and Bio -->
        <div class='mt-3'>
            <h4 class='font-weight-bold'>Profile</h4>
        </div>

        <!-- Profile Options -->
        <div class='list-group'>
            <a href='#' class='list-group-item list-group-item-action d-flex justify-content-between align-items-center'>
                <span>Name:</span>
                <span>{$user_details['name']}</span>
            </a>
            <a href='#' class='list-group-item list-group-item-action d-flex justify-content-between align-items-center'>
                <span>Email:</span>
                <span>{$user_details['email']}</span>
            </a>
            <a href='#' class='list-group-item list-group-item-action d-flex justify-content-between align-items-center'>
                <span>Phone no.</span>
                <span>{$user_details['phonenum']}</span>
            </a>
            <a href='#' class='list-group-item list-group-item-action d-flex justify-content-between align-items-center'>
                <span>Address:</span>
                <span>{$user_details['address']}</span>
            </a>
            <a href='#' class='list-group-item list-group-item-action d-flex justify-content-between align-items-center'>
                <span>Birthdate :</span>
                <span>{$user_details['dob']}</span>
            </a>
            <a href='#' class='list-group-item list-group-item-action d-flex justify-content-between align-items-center'>
                <span>Registration Date :</span>
                <span>" . date("d-m-Y", strtotime($user_details['datentime'])) . "</span>
            </a>
        </div>
    </div>
</div>
        <hr>   
    <h4 class='w-100 text-center'>Appointment</h4>
        <div class='table-responsive-md' style='height: 300px; overflow-y: scroll;'>
            <table class='table table-hover border'>
                <thead class='sticky-top'>
                    <tr class='text-light ambot'>
                        <th scope='col'>#</th>
                        <th scope='col'>Trainor Name</th>
                        <th scope='col'>Email</th>
                        <th scope='col'>Phone no.</th>
                        <th scope='col' width='20%'>Note</th>
                        <th scope='col'>Date</th>
                        <th scope='col'>Time</th>
                        <th scope='col'>Status</th>
                        <th scope='col'>Action</th>
                    </tr>
                </thead>
                <tbody id='users-data'>
                    $bookings_data
                </tbody>
            </table>
        </div><br>
           <div>$del_btn</div>
    ";
}
if (isset($_POST['toggle_booking_status'])) {
    $frm_data = filteration($_POST);
    $booking_id = $frm_data['toggle_booking_status'];
    $new_status = $frm_data['value'];

    $q = "UPDATE `bookings` SET `status`=? WHERE `id`=?";
    $v = [$new_status, $booking_id];
    if (update($q, $v, 'ii')) {
        echo "Successfully Updated";
    } else {
        echo "Failed to Update";
    }
}

if (isset($_POST['toggle_user_status'])) { 
    $frm_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    $user_id = $frm_data['user_id'];
    $new_status = $frm_data['value']; // 0 for active, 1 for inactive

    $q = "UPDATE `user_cred` SET `status`=? WHERE `user_id`=?";
    $v = [$new_status, $user_id];
    
    if (update($q, $v, 'ii')) {
        echo "1";  // Success response
    } else {
        echo "0";  // Failure response
    }
}

if (isset($_POST['remove_booking'])) {
    $frm_data = filteration($_POST);
    $id = $frm_data['booking_id']; // Get booking_id

    $res = delete("DELETE FROM `bookings` WHERE `id`=?", [$id], 'i'); // Delete by booking_id
    if ($res) {
        echo 1; 
    } else {
        echo 0;
    }
}

if (isset($_POST['toggle_status'])) {
    $frm_data = filteration($_POST);
    $q = "UPDATE `user_cred` SET `status`=? WHERE `user_id`=?";
    $v = [$frm_data['value'], $frm_data['toggle_status']];
    if (update($q, $v, 'ii')) {
        echo 1;
    } else {
        echo 0;
    }
}


if (isset($_POST['remove_user'])) {
    $frm_data = filteration($_POST);
    $user_id = $frm_data['user_id'];

    // Delete bookings for the user
    $res = delete("DELETE FROM `bookings` WHERE `user_id`=?", [$user_id], 'i');

    // Delete the user
    $res = delete("DELETE FROM `user_cred` WHERE `user_id`=?", [$user_id], 'i');

    if ($res) {
        echo 1;
    } else {
        echo 0;
    }
}
if (isset($_POST['search_user'])) {
    $frm_data = filteration($_POST);
    $query = "SELECT * FROM `user_cred` WHERE `name` LIKE ?";
    $res = select($query, ["%$frm_data[name]%"], 's');
    $i = 1;
    $data = "";

    while ($row = mysqli_fetch_assoc($res)) {
        $del_btn = "<button onclick='remove_user($row[user_id],0)' class='btn btn-danger shadow-none btn-sm'><i class='bi bi-trash'></i> Delete</button>";
        $status = "<button onclick='toggle_status($row[user_id],0)' class='btn btn-dark btn-sm shadow-none'>active</button>";
        if (!$row['status']) {
            $status = "<button onclick='toggle_status($row[user_id],1)' class='btn btn-danger btn-sm shadow-none'>inactive</button>";
        }
        $date = date("d-m-Y", strtotime($row['datentime']));
        $data .= "
            <tr data-user-id='$row[user_id]'>
                <td>$i</td>
                <td>$row[name]</td>
                <td>$row[email]</td>
                <td>$row[phonenum]</td>
                <td>$row[address]</td>
                <td>$row[dob]</td>
                <td>$status</td>
                <td>$date</td>
                <td><button class='btn btn-primary btn-sm shadow-none' onclick='openUserDetailsModal($row[user_id])'>View Details</button></td>
            </tr>
        ";
        $i++;
    }

    echo $data;
}
?>
