<?php
    require('../inc/db_config.php');
    require('../inc/essentials.php');
    adminLogin();

    if(isset($_POST['add_specialty'])) {
        $frm_data = filteration($_POST);

        // Ensure 'description' key is used
        $q = "INSERT INTO `specialty` (`name`, `description`) VALUES (?, ?)";
        $values = [$frm_data['name'], $frm_data['description']]; // Ensure 'description' key is used
        $res = insert($q, $values, 'ss'); // Assuming 'name' and 'description' are strings
        echo $res;
    }

    if(isset($_POST['get_specialty'])) {
        $res = selectAll('specialty');
        $i=1;

        while($row = mysqli_fetch_assoc($res)) {
            echo <<<data
                <tr>
                    <td>$i</td>
                    <td>{$row['name']}</td>
                    <td>{$row['description']}</td>
                    <td>
                        <button type="button" onclick="rem_specialty({$row['specialty_id']})" class="btn btn-danger btn-sm shadow-none">
                            <i class="bi bi-trash"></i> Delete
                        </button> 
                    </td>
                </tr>
            data;
            $i++;
        }
    }

    if(isset($_POST['rem_specialty'])) {
        $frm_data = filteration($_POST);
        $values = [$frm_data['rem_specialty']];

        $check_q = select('SELECT * FROM `trainor_specialty` WHERE `specialty_id`=?', [$frm_data['rem_specialty']], 'i');

        if(mysqli_num_rows($check_q) == 0) {
            $q = "DELETE FROM `specialty` WHERE `specialty_id`=?";
            $res = delete($q, $values, 'i');
            echo $res;
        } else {
            echo 'trainor_added';
        }
    }
?>
