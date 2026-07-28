<?php

date_default_timezone_set("Asia/Calcutta");
error_reporting(E_ALL);
$dbhost = "localhost";
$dbuser = "vrajdahj";
$dbpass = "Vraj@Apollo@786";
$dbname = "vrajdahj_vgdc";
$dbconn = mysqli_connect("$dbhost", "$dbuser", "$dbpass",$dbname) or die('Could not connect: ' . mysqli_connect_error($dbconn));
$date = date('Y-m-d');

$sqlOne = "SELECT DISTINCT doctor_id,users.user_name,users.mobile_no,branch_id FROM `appointments` inner join users on appointments.doctor_id=users.user_id where appointment_date='".$date."' and appointments.deleted_at is null";

$resOne = mysqli_query($dbconn, $sqlOne);
if (mysqli_num_rows($resOne) > 0) {
    while ($rowOne = mysqli_fetch_array($resOne)) {
        if(isset($rowOne['doctor_id'])){
            $appointments = mysqli_query($dbconn,"SELECT * FROM `appointments` WHERE doctor_id=".$rowOne['doctor_id']." and appointment_date='".$date."' and branch_id='".$rowOne['branch_id']."' and deleted_at is null order by STR_TO_DATE(appointment_time, '%h:%i %p') asc");
            $branches = mysqli_fetch_array(mysqli_query($dbconn,"SELECT * FROM `branches` where branch_id='".$rowOne['branch_id']."'"));
            
            // $wid = "28844";
            $wid = "30525";
            $bodyValues = [
                "1" => str_replace('Dr. ', '', $rowOne['user_name']),
                "2" => $branches['branch_name'],
            ];
            $key = 3;
            while($appointment = mysqli_fetch_assoc($appointments)){
                $patients = mysqli_fetch_array(mysqli_query($dbconn,"SELECT * FROM `patients` where patient_id='".$appointment['patient_id']."' and branch_id='".$rowOne['branch_id']."'"));
                // $user_name = $patients['name_prefix'] ." " . $patients['name'] ." - ".$appointment['appointment_time']." (".$branches['branch_name'].")";
                $user_name = $patients['name_prefix'] ." " . $patients['name'] ." - ".$appointment['appointment_time'];
                $bodyValues[(string)$key] = $user_name; // ✅ append, not overwrite
                $key++;
            }
            for ($i = $key; $i <= 12; $i++) {
                $bodyValues[(string)$i] = "-";
            }
            // print_r($bodyValues);
            $response = sendWhatsAppAuthkey($rowOne['mobile_no'], $wid, $bodyValues);
            
            // $response = sendWhatsAppAuthkey(7046673769, $wid, $bodyValues);
            // exit;
        }
    }
}


function sendWhatsAppAuthkey($mobile, $wid, $bodyValues = [], $type = "text", $fileUrl = "")
{
    $authkey = "acebd7325b63b99e"; // from Authkey panel
    $url = "https://console.authkey.io/restapi/requestjson.php";

    $postData = [
        "country_code" => "91",
        "mobile" => $mobile,
        "wid" => $wid,
        "type" => $type,
        "bodyValues" => $bodyValues
    ];

    if ($type == "media") {
        $postData["headerValues"] = [
            "headerFileName" => "Document",
            "headerData" => $fileUrl
        ];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Basic " . $authkey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function sendWhatsAppAuthkeyNew($mobile, $wid, $bodyValues = [])
{
    $authkey = "acebd7325b63b99e";
    $url = "https://console.authkey.io/restapi/requestjson.php";

    $postData = [
        "country_code" => "91",
        "mobile" => $mobile,
        "wid" => $wid,
        "type" => "text",
        "bodyValues" => $bodyValues
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Basic " . $authkey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

    $response = curl_exec($ch);

    if(curl_errno($ch)){
        echo 'Curl error: ' . curl_error($ch);
    }

    curl_close($ch);

    return json_decode($response, true);
}


?>