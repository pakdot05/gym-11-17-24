<?php
    require('../inc/db_config.php');
    require('../inc/essentials.php');
    adminLogin();

    if(isset($_POST['get_users']))
    {
        // Get today's date
        $today = date('Y-m-d');
        $res = select("SELECT * FROM `bookings` WHERE `date` = ? AND `status` = 1", [$today], 's');
        $i=1;

        $data = "";

        while($row = mysqli_fetch_assoc($res))
        {
            $del_btn = "<button onclick='remove_user($row[id],0)' class='btn btn-danger shadow-none btn-sm'><i class='bi bi-trash'></i>Delete</button>";

            $status = "<button onclick='toggle_status($row[id],0)' class='btn btn-dark btn-sm shadow-none' disabled>Approved</button>";

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

    if(isset($_POST['toggle_status']))
    {
        $frm_data = filteration($_POST);

        $q = "UPDATE `bookings` SET `status`=? WHERE `id`=?";
        $v = [$frm_data['value'],$frm_data['toggle_status']];

        if(update($q,$v,'ii')){
            echo 1;
        }
        else{
            echo 0;
        }
    }

    if(isset($_POST['remove_user'])) {

        $frm_data = filteration($_POST);
    
        $res = delete("DELETE FROM `bookings` WHERE `id`=?",[$frm_data['id']],'i');

        if($res){
            echo 1;
        }
        else{
            echo 0;
        }
    }

    if(isset($_POST['search_user']))
    {
        $frm_data = filteration($_POST);

        $query = "SELECT * FROM `bookings` WHERE `name` LIKE ? AND `status` = 1"; // Filter for approved status

        $res = select($query,["%$frm_data[name]%"],'s');
        $i=1;

        $data = "";

        while($row = mysqli_fetch_assoc($res))
        {
            $del_btn = "<button onclick='remove_user($row[id],0)' class='btn btn-danger shadow-none btn-sm'><i class='bi bi-trash'></i></button>";
            $edit_btn = "<button onclick='remove_user($row[id],0)' class='btn btn-primary shadow-none btn-sm'><i class='bi bi-pencil-square'></button>";

            $status = "<button onclick='toggle_status($row[id],0)' class='btn btn-dark btn-sm shadow-none' disabled>Approved</button>";

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
