<?php
require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();

if (isset($_POST['get_users'])) {
    $res = selectAll('bookings');
    $i = 1;

    $data = "";

    while ($row = mysqli_fetch_assoc($res)) {
        $del_btn = "<button onclick='remove_user($row[id],0)' class='btn btn-danger shadow-none btn-sm'><i class='bi bi-trash'></i></button>";
        $edit_btn = "<button onclick='edit_user($row[id])' class='btn btn-primary shadow-none btn-sm'><i class='bi bi-pencil-square'></i></button>";

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
                    <td>$row[date]</td>
                    <td>$row[timeslot]</td>
                    <td>$status</td>
                </tr>
            ";
            $i++;
    }

    // Echo the generated HTML back to the AJAX request
    echo $data;
}

if (isset($_POST['toggle_status'])) {
    $frm_data = filteration($_POST);

    $q = "UPDATE `bookings` SET `status`=? WHERE `id`=?";
    $v = [$frm_data['value'], $frm_data['toggle_status']];

    if (update($q, $v, 'ii')) {
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

    $query = "SELECT * FROM `bookings` WHERE `name` LIKE ?";

    $res = select($query, ["%$frm_data[name]%"], 's');
    $i = 1;

    $data = "";

    while ($row = mysqli_fetch_assoc($res)) {
        $del_btn = "<button onclick='remove_user($row[id],0)' class='btn btn-danger shadow-none btn-sm'><i class='bi bi-trash'></i></button>";
        $edit_btn = "<button onclick='edit_user($row[id])' class='btn btn-primary shadow-none btn-sm'><i class='bi bi-pencil-square'></i></button>";

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
                    <td>$row[date]</td>
                    <td>$row[timeslot]</td>
                    <td>$status</td>
                </tr>
            ";
            $i++;
    }

    // Echo the generated HTML back to the AJAX request
    echo $data;
}
?>
