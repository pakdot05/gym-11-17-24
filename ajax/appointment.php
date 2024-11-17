<?php
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');

session_start();

if (isset($_POST['get_users'])) {
    // Check if user is logged in
    if (isset($_SESSION['uId'])) {
        $user_id = $_SESSION['uId']; // Retrieve user_id from session data

        // Modify the SQL query to fetch all bookings associated with the logged-in user
        $query = "SELECT * FROM `bookings` WHERE `user_id` = ?";
        $res = select($query, [$user_id], 'i');

        $i = 1;
        $data = "";

        while ($row = mysqli_fetch_assoc($res)) {
            $del_btn = "";
            $del_btn = "";
            if ($row['status'] != 3) {
                // Only enable the cancel button if the status is not approved or completed
                if ($row['status'] == 0) {
                    $del_btn = "<button onclick='remove_user($row[id],0)' class='btn btn-danger shadow-none btn-sm'><i>Cancel</i></button>";
                } else {
                    $del_btn = "<button onclick='remove_user($row[id],0)' class='btn btn-danger shadow-none btn-sm' disabled><i>Cancel</i></button>";
                }
            }
            $edit_btn = "<button onclick='edit_user($row[id])' class='btn btn-primary shadow-none btn-sm'><i class='bi bi-pencil-square'></i></button>";

            $status = "";
            if ($row['status'] == 1) {
                $status = "<button class='btn btn-dark btn-sm shadow-none' disabled>Approved</button>";
            } elseif ($row['status'] == 0) {
                $status = "<button class='btn btn-danger btn-sm shadow-none' disabled>Pending</button>";
            } elseif ($row['status'] == 2) { // Add this elseif condition
                $status = "<button class='btn btn-success btn-sm shadow-none' disabled>Completed</button>";
            } elseif ($row['status'] == 3) {
                $status = "<button class='btn btn-success btn-sm shadow-none' disabled>Completed</button>";
            }

            $data .= "
                <tr>
                    <td>$i</td>
                    <td>$row[name]</td>
                    <td>$row[email]</td>
                    <td>$row[phonenum]</td>
                    <td>$row[note]</td>
                    <td>$row[trainor_name]</td>
                    <td>$row[date]</td>
                    <td>$row[timeslot]</td>
                    <td>$status</td>
                    <td>$del_btn</td>
                </tr>
            ";
            $i++;
        }

        // Echo the generated HTML back to the AJAX request
        echo $data;
    } else {
        // User is not logged in
        echo "not_logged_in";
    }
}

if (isset($_POST['toggle_status'])) {
    $frm_data = filteration($_POST);

    // Update the status in the bookings table
    $q = "UPDATE `bookings` SET `status`=? WHERE `id`=?";
    $v = [$frm_data['value'], $frm_data['toggle_status']];

    if (update($q, $v, 'ii')) {
        // If the status is changed to approved, update the user's appointment_status in user_cred table
        if ($frm_data['value'] == 1) {
            $booking_id = $frm_data['toggle_status'];
            // Fetch the user_id associated with this booking
            $userQuery = "SELECT user_id FROM bookings WHERE id=?";
            $userResult = select($userQuery, [$booking_id], 'i');
            $userRow = mysqli_fetch_assoc($userResult);

            if ($userRow) {
                $user_id = $userRow['user_id'];
                // Update the appointment_status in user_cred table
                $updateUserCred = "UPDATE user_cred SET appointment_status = 'approved' WHERE user_id = ?";
                update($updateUserCred, [$user_id], 'i');
            }
        }

        echo 1;
    } else {
        echo 0;
    }
}

if (isset($_POST['remove_user'])) {
    $frm_data = filteration($_POST);

    // Update the user_cred's appointment_status to 'cancelled'
    $userQuery = "SELECT user_id FROM bookings WHERE id=?";
    $userResult = select($userQuery, [$frm_data['id']], 'i');
    $userRow = mysqli_fetch_assoc($userResult);

    if ($userRow) {
        $user_id = $userRow['user_id'];
        $updateUserCred = "UPDATE user_cred SET appointment_status = 'cancelled' WHERE user_id = ?";
        update($updateUserCred, [$user_id], 'i');
    }

    $res = delete("DELETE FROM `bookings` WHERE `id`=?", [$frm_data['id']], 'i');

    if ($res) {
        echo 1;
    } else {
        echo 0;
    }
}

if (isset($_POST['search_user'])) {
    $frm_data = filteration($_POST);

    // Modify the SQL query to search for bookings by name and user_id
    $query = "SELECT * FROM `bookings` WHERE `user_id` = ?";
    $res = select($query, [$user_id], 'i');

    $i = 1;
    $data = "";

    while ($row = mysqli_fetch_assoc($res)) {
        $del_btn = "";
        $status = "";
        if ($row['status'] == 1) {
            $status = "<button class='btn btn-dark btn-sm shadow-none' disabled>Approved</button>";
        } elseif ($row['status'] == 0) {
            $status = "<button class='btn btn-danger btn-sm shadow-none' disabled>Pending</button>";
        } elseif ($row['status'] == 3) {
            $status = "<button class='btn btn-success btn-sm shadow-none' disabled>Completed</button>";
        }
        

        $data .= "
            <tr>
                <td>$i</td>
                <td>$row[name]</td>
                <td>$row[email]</td>
                <td>$row[phonenum]</td>
                <td>$row[note]</td>
                <td>$row[trainor_name]</td>
                <td>$row[date]</td>
                <td>$row[timeslot]</td>
                <td>$status</td>
                <td>$del_btn</td>
            </tr>
        ";
        $i++;
    }

    // Echo the generated HTML back to the AJAX request
    echo $data;
}
?>
