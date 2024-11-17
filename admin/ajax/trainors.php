<?php
    require('../inc/db_config.php');
    require('../inc/essentials.php');
    
    adminLogin();

    if(isset($_POST['add_trainor']))
    {
        $specialty = filteration(json_decode($_POST['specialty']));
        $frm_data = filteration($_POST);
        $flag = 0;

        $q1 = "INSERT INTO `trainor` (`name`, `address`, `contact_no`, `email`, `info`) VALUES (?,?,?,?,?)";
        $values = [$frm_data['name'], $frm_data['address'], $frm_data['contact_no'], $frm_data['email'], $frm_data['info']];

        if(insert($q1,$values,'sssss')){
            $flag = 1;
        }

        $trainor_id = mysqli_insert_id($con);

        $q2 = "INSERT INTO `trainor_specialty`(`doc_id`, `specialty_id`) VALUES (?,?)";
        if($stmt = mysqli_prepare($con,$q2))
        {
            foreach($specialty as $f){
                mysqli_stmt_bind_param($stmt,'ii',$trainor_id,$f);
                mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);
        }
        else
        {
            $flag = 0;
            die('query cannot be prepared - insert');
        }

        if($flag)
        {
            echo 1;
        }
        else
        {
            echo 0;
        }

    }

    if(isset($_POST['get_all_trainor']))
    {
        $res = select("SELECT * FROM `trainor` WHERE `removed`=?",[0],'i');
        $i=1;

        $data = "";

        while($row = mysqli_fetch_assoc($res))
        {

            if($row['status']==1){
                $status = "<button onclick='toggle_status($row[trainor_id],0)' class='btn btn-success btn-sm shadow-none'>active</button>";
            }
            else
            {
                $status = "<button onclick='toggle_status($row[trainor_id],1)' class='btn btn-secondary btn-sm shadow-none'>inactive</button>";
            }

            $data .= "
                <tr>
                    <td>$i</td>
                    <td class='text-start'>$row[name]</td>
                    <td class='text-start'>$row[address]</td> 
                    <td class='text-start'>$row[contact_no]</td>
                    <td class='text-start'>$row[email]</td>
                    <td class='text-start'>$row[info]</td>
                    <td>$status</td>
                    <td>
                        <button type='button' onclick='edit_details($row[trainor_id])' class='btn btn-primary shadow-none btn-sm' data-bs-toggle='modal' data-bs-target='#edit-trainor'>
                            <i class='bi bi-pencil-square'></i> Edit
                        </button>
                        <button type='button' onclick=\"trainor_images($row[trainor_id],'$row[name]')\"class='btn btn-primary shadow-none btn-sm'data-bs-toggle='modal' data-bs-target='#trainor-images'>
                            <i class='bi bi-images'></i>
                        </button>
                        <button type='button' onclick='remove_trainor($row[trainor_id])' class='btn btn-danger shadow-none btn-sm'>
                            <i class='bi bi-trash'></i>
                        </button> 
                    </td>
                </tr>   
            ";
            $i++;
        }

        // Echo the generated HTML back to the AJAX request
        echo $data;
    }

    if(isset($_POST['get_trainor']))
    {
        $frm_data = filteration($_POST);

        $res1 = select("SELECT * FROM `trainor` WHERE `trainor_id`=?",[$frm_data['get_trainor']],'i');
        $res2 = select("SELECT * FROM `trainor_specialty` WHERE `doc_id`=?",[$frm_data['get_trainor']],'i');

        $trainordata = mysqli_fetch_assoc($res1);
        $specialty = [];

        if(mysqli_num_rows($res2)>0){
            while($row = mysqli_fetch_assoc($res2)){
                array_push($specialty,$row['specialty_id']);
            }
        }

        $data = ["trainordata" => $trainordata, "specialty" => $specialty];

        $data = json_encode($data);

        echo $data;
    }

    if(isset($_POST['update_trainor']))
    {
        $specialty = filteration(json_decode($_POST['specialty']));
        $frm_data = filteration($_POST);
        $flag = 0;

        $q1 = "UPDATE `trainor` SET `name`=?, `address`=?, `contact_no`=?, `email`=?, `info`=? WHERE `trainor_id`=?";
        $values = [$frm_data['name'], $frm_data['address'], $frm_data['contact_no'], $frm_data['email'], $frm_data['info'], $frm_data['trainor_id']];

        if(update($q1, $values, 'sssssi')){
            $flag = 1;
        }

        $del_specialty = delete("DELETE FROM `trainor_specialty` WHERE `doc_id`=?", [$frm_data['trainor_id']], 'i');

        if(!($del_specialty)){
            $flag = 0;
        }

        $q2 = "INSERT INTO `trainor_specialty`(`doc_id`, `specialty_id`) VALUES (?,?)";
        if($stmt = mysqli_prepare($con,$q2))
        {
            foreach($specialty as $f){
                mysqli_stmt_bind_param($stmt,'ii',$frm_data['trainor_id'],$f);
                mysqli_stmt_execute($stmt);
            }
            $flag = 1;
            mysqli_stmt_close($stmt);
        }
        else
        {
            $flag = 0;
            die('query cannot be prepared - insert');
        }

        if($flag)
        {
            echo 1;
        }
        else
        {
            echo 0;
        }
    }

    if(isset($_POST['toggle_status']))
    {
        $frm_data = filteration($_POST);

        $q = "UPDATE `trainor` SET `status`=? WHERE `trainor_id`=?";
        $v = [$frm_data['value'],$frm_data['toggle_status']];

        if(update($q,$v,'ii')){
            echo 1;
        }
        else{
            echo 0;
        }
    }


    if(isset($_POST['remove_trainor'])) {
        
        $trainor_id = filter_input(INPUT_POST, 'trainor_id', FILTER_VALIDATE_INT);
    
    
        if($trainor_id !== false && $trainor_id !== null) {
            $delete_specialties_query = "DELETE FROM `trainor_specialty` WHERE `doc_id` = ?";
            $delete_specialties_stmt = mysqli_prepare($con, $delete_specialties_query);
    
          
            mysqli_stmt_bind_param($delete_specialties_stmt, 'i', $trainor_id);
    
            
            if(mysqli_stmt_execute($delete_specialties_stmt)) {
                $remove_trainor_query = "UPDATE `trainor` SET `removed` = 1 WHERE `trainor_id` = ?";
                $remove_trainor_stmt = mysqli_prepare($con, $remove_trainor_query);
    
                mysqli_stmt_bind_param($remove_trainor_stmt, 'i', $trainor_id);
    
                // Execute the statement
                if(mysqli_stmt_execute($remove_trainor_stmt)) {
                    echo 1;
                } else {
                    echo 0;
                }
            } else {
                echo 0;
            }
        } else {

            echo 0;
        }
    }
    if(isset($_POST['add_image']))
    {
        $frm_data = filteration($_POST);

        $img_r = uploadImage($_FILES['image'],TRAINORS_FOLDER);

        if($img_r == 'inv_img'){
            echo $img_r;
        }
        else if($img_r == 'inv_size'){
            echo $img_r;
        }
        else if($img_r == 'upd_failed'){
            echo $img_r;
        }
        else{
            $q = "INSERT INTO `trainor_images`(`trainor_id`, `image`) VALUES (?,?)";
            $values = [$frm_data['trainor_id'],$img_r];
            $res = insert($q,$values,'is');
            echo $res;
        }
    }
    if (isset($_POST['get_trainor_images'])) {
        $frm_data = filteration($_POST);
        $res = select("SELECT * FROM `trainor_images` WHERE `trainor_id`=?", [$frm_data['get_trainor_images']], 'i');
        $path = TRAINORS_IMG_PATH;
        
        while($row = mysqli_fetch_assoc($res)) 
        {
        
            if($row['thumb'] == 1)
             {
                $thumb_btn = "<i class='bi bi-check-lg text-light bg-success px-2 py-1 rounded fs-5'></i>";
            }else{
                $thumb_btn = " <button type='button' onclick='thumb_image($row[sr_no], $row[trainor_id])' class='btn btn-secondary btn-sm shadow-none'><i class='bi bi-check-lg'></i></button>" ;
            }
          
            echo <<<DATA
            <tr class='align-middle'>
                <td><img src='$path$row[image]' class='img-fluid'></td>
                <td>$thumb_btn</td>
                <td>
                    <button type='button' onclick='rem_image($row[sr_no], $row[trainor_id])' class='btn btn-danger btn-sm shadow-none'><i class='bi bi-trash'></i></button>
                    
                </td>
            </tr>
            DATA;
        }
    }
    if(isset($_POST['rem_image']))
    {
        $frm_data = filteration($_POST);
        $values = [$frm_data['image_id'], $frm_data['trainor_id']];
    
        // Fetch the image details from the database
        $pre_q = "SELECT * FROM `trainor_images` WHERE `sr_no` = ? AND `trainor_id` = ?";
        $res = select($pre_q, $values, 'ii');
        $img = mysqli_fetch_assoc($res);
    
        // Validate if image exists and delete it from the server
        if ($img) {
            if (deleteImage($img['image'],TRAINORS_FOLDER)) {
                $q = "DELETE FROM `trainor_images` WHERE `sr_no` = ? AND `trainor_id` = ?";
                $res = delete($q, $values, 'ii');
                echo $res;
            } else {
                echo 0; // Error in deleting image from server
            }
        } else {
            echo 0; // Image record not found in database
        }
    }
   if (isset($_POST['thumb_image'])) {
    $frm_data = filteration($_POST);

    // Update previous thumbnail
    $pre_q = "UPDATE `trainor_images` SET `thumb`=? WHERE trainor_id=?";
    $pre_v = [0, $frm_data['trainor_id']];
    $pre_res = update($pre_q, $pre_v, 'ii');

    // Set new thumbnail
    $q = "UPDATE `trainor_images` SET `thumb`=? WHERE sr_no=? AND trainor_id=?";
    $v = [1, $frm_data['image_id'], $frm_data['trainor_id']];
    $res = update($q, $v, 'iii');

    // Echo the result
    echo $res ? 1 : 0; // Ensure echoing 1 for success and 0 for failure
}

?>