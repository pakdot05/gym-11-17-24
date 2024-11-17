<?php
require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();

if (isset($_POST['get_users'])) {
    global $con;

    // Select all bookings for today
    $today = date('Y-m-d');
    $query = "SELECT * FROM `bookings` WHERE `date` = '$today'";
    $res = mysqli_query($con, $query);
    $i = 1;

    $data = "";

    while ($row = mysqli_fetch_assoc($res)) {
        $del_btn = "<button onclick='remove_user(this)' class='btn btn-danger shadow-none btn-sm' data-id='$row[id]'><i class='bi bi-trash'></i> Delete</button>";
        $edit_btn = "<button onclick='edit_user($row[id])' class='btn btn-primary shadow-none btn-sm'><i class='bi bi-pencil-square'></i></button>";

        // Determine the status button based on the booking status
        switch ($row['status']) {
            case 0: // Pending
                $status = "<button onclick='toggle_status($row[id], 1)' class='btn btn-secondary btn-sm shadow-none'>Pending</button>";
                break;
            case 1: // Approved
                $status = "<button onclick='toggle_status($row[id], 2)' class='btn btn-dark btn-sm shadow-none'>Approved</button>";
                break;
            case 2: // Completed
                $status = "<button class='btn btn-success btn-sm shadow-none' disabled>Completed</button>";
                break;
            default:
                $status = "<button class='btn btn-light btn-sm shadow-none' disabled>Unknown</button>";
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

    echo $data;
}

if (isset($_POST['toggle_status'])) {
    $frm_data = filteration($_POST);

    // Update the status in the bookings table
    $q = "UPDATE `bookings` SET `status`=? WHERE `id`=?";

    // Determine the new status based on the current status
    if ($frm_data['value'] == 1) { // If current status is Pending, update to Approved
        $v = [1, $frm_data['toggle_status']];
    } else if ($frm_data['value'] == 2) { // If current status is Approved, update to Completed
        $v = [2, $frm_data['toggle_status']];
    }

    if (update($q, $v, 'ii')) {
        // Update the user's appointment_status in user_cred table based on the new status
        $booking_id = $frm_data['toggle_status'];
        $userQuery = "SELECT user_id FROM bookings WHERE id=?";
        $userResult = select($userQuery, [$booking_id], 'i');
        $userRow = mysqli_fetch_assoc($userResult);

        if ($userRow) {
            $user_id = $userRow['user_id'];
            switch ($frm_data['value']) {
                case 1:
                    $updateUserCred = "UPDATE user_cred SET appointment_status = 'approved' WHERE user_id = ?";
                    break;
                case 2:
                    $updateUserCred = "UPDATE user_cred SET appointment_status = 'completed' WHERE user_id = ?";
                    break;
            }
            if (isset($updateUserCred)) {
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

    // Correctly use the id in the DELETE query
    $res = delete("DELETE FROM `bookings` WHERE `id`=?", [$frm_data['id']], 'i');

    if ($res) {
        echo 1;
    } else {
        echo 0;
    }
}
if (isset($_POST['search_user'])) {
    $frm_data = filteration($_POST);

    $query = "SELECT * FROM `bookings` WHERE `name` LIKE ?";

    $res = select($query, ["%$frm_data[name]%"], 's');
    $i = 1;

    $data = "";

    while ($row = mysqli_fetch_assoc($res)) {
        $del_btn = "<button onclick='remove_user(this)' class='btn btn-danger shadow-none btn-sm' data-id='$row[id]'><i class='bi bi-trash'></i> Delete</button>";
        $edit_btn = "<button onclick='edit_user($row[id])' class='btn btn-primary shadow-none btn-sm'><i class='bi bi-pencil-square'></i> Edit</button>";

        // Determine the status button based on the booking status
        switch ($row['status']) {
            case 0: // Pending
                $status = "<button onclick='toggle_status($row[id], 1)' class='btn btn-secondary btn-sm shadow-none'>Pending</button>";
                break;
            case 1: // Approved
                $status = "<button onclick='toggle_status($row[id], 2)' class='btn btn-dark btn-sm shadow-none'>Approved</button>";
                break;
            case 2: // Completed
                $status = "<button class='btn btn-success btn-sm shadow-none' disabled>Completed</button>";
                break;
            default:
                $status = "<button class='btn btn-light btn-sm shadow-none' disabled>Unknown</button>";
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

    echo $data;
}

?>