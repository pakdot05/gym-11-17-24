<?php
require_once '../admin/inc/db_config.php';
require_once '../admin/inc/essentials.php';

if (isset($_POST['register'])) {
    $data = filteration($_POST);

    // Check password length
    if (!preg_match('/[A-Z]/', $data['pass']) || 
    !preg_match('/[a-z]/', $data['pass']) || 
    !preg_match('/[0-9]/', $data['pass']) || 
    !preg_match('/[!@#\$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $data['pass']) || 
    strlen($data['pass']) < 8 || strlen($data['pass']) > 16) {
    echo 'pass_invalid';
    exit;
}

    // Check phone number format
    if (strlen($data['phonenum']) !== 12 || substr($data['phonenum'], 0, 2) !== '63') {
        echo 'num_req';
        exit;
    }

    // Check age based on date of birth
    $dob = new DateTime($data['dob']);
    $today = new DateTime();
    $age = $today->diff($dob)->y;

    if ($age < 16) {
        echo 'age_below_16';
        exit;
    }

    // Match passwords
    if ($data['pass'] != $data['cpass']) {
        echo 'pass_mismatch';
        exit;
    }

    // Check if user exists
    $u_exist = select("SELECT * FROM `user_cred` WHERE `email` = ? OR `phonenum` = ? LIMIT 1", [$data['email'], $data['phonenum']], "ss");

    if (mysqli_num_rows($u_exist) != 0) {
        $u_exist_fetch = mysqli_fetch_assoc($u_exist);
        echo ($u_exist_fetch['email'] == $data['email']) ? 'email_already' : 'phone_already';
        exit;
    }

    // Upload image
    $img = uploadUserImage($_FILES['profile']);

    if ($img == 'inv_img') {
        error_log('Invalid image format');
        echo 'inv_img';
        exit;
    } else if ($img == 'upd_failed') {
        error_log('Image upload failed');
        echo 'upd_failed';
        exit;
    }

    // Hash password
    $enc_pass = password_hash($data['pass'], PASSWORD_BCRYPT);

    // Insert user into database
    $query = "INSERT INTO `user_cred`(`name`, `email`, `dob`, `phonenum`, `address`, `password`, `profile`) VALUES (?,?,?,?,?,?,?)";
    $values = [$data['name'], $data['email'], $data['dob'], $data['phonenum'], $data['address'], $enc_pass, $img];

    if (insert($query, $values, 'sssssss')) {
        echo 1; // Registration successful

        // Send SMS notification
        sendSmsNotification($data['phonenum']);

    } else {
        echo 'ins_failed'; // Registration failed
        error_log('Database insert failed: ' . mysqli_error($conn)); // Log SQL error
    }
}

function sendSmsNotification($phoneNumber) {
    $apiUrl = 'https://ggnm6r.api.infobip.com/sms/2/text/advanced';
    $apiKey = 'dc2e4d4babfbed09beaa00a43563bdcc-4c0c2e1c-c416-4ab0-aed4-9377562b755f'; // Replace with your actual API key

    // SMS data
    $smsData = array(
        'messages' => array(
            array(
                'destinations' => array(
                    array('to' => $phoneNumber)
                ),
                'from' => 'GEA FITNESS GYM', // Adjust as needed
                'text' => 'Congratulations! You\'re now a member of Gea Fitness Gym.'
            )
        )
    );

    // Initialize cURL session
    $curl = curl_init();

    // Set cURL options
    curl_setopt_array($curl, array(
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($smsData),
        CURLOPT_HTTPHEADER => array(
            'Authorization: App ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ),
    ));

    // Execute cURL request and fetch the response
    $response = curl_exec($curl);

    // Check for cURL errors
    if (curl_errno($curl)) {
        error_log('cURL Error: ' . curl_error($curl));
        echo 'cURL Error: ' . curl_error($curl);
    } else {
        // Display response from Infobip
        echo $response;
    }

    // Close cURL session
    curl_close($curl);
}

if (isset($_POST['login'])) {
    $data = filteration($_POST);

    $u_exist = select("SELECT * FROM `user_cred` WHERE `email` = ? OR `phonenum` = ? LIMIT 1", [$data['email_mob'], $data['email_mob']], "ss");

    if (mysqli_num_rows($u_exist) == 0) {
        echo 'inv_email_mob';
    } else {
        $u_fetch = mysqli_fetch_assoc($u_exist);
        if ($u_fetch['status'] == 0) {
            echo 'inactive';
        } else {
            if (password_verify($data['pass'], $u_fetch['password'])) {
               echo 'inactive';
  

                echo 1;
            } else {
               echo 'invalid_pass';
            }
        }
    }
}
?>
