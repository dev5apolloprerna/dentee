<?php

date_default_timezone_set("Asia/Calcutta");
error_reporting(E_ALL);
$dbhost = "localhost";
$dbuser = "vrajdahj";
$dbpass = "Vraj@Apollo@786";
$dbname = "vrajdahj_vgdc";
$dbconn = mysqli_connect("$dbhost", "$dbuser", "$dbpass",$dbname) or die('Could not connect: ' . mysqli_connect_error($dbconn));
$key="029ca92e908590bf49c8470d99844b84";

$date = date('Y-m-d');
$iCounter = 0;

$branchs = mysqli_query($dbconn,"SELECT * FROM `branches` where deleted_at is null and branch_id in (16,15,2,4,7,17,19,20)");
// $MsgQuotation = "*New Case Quotation Added Amount  :*
// ";
$MsgQuotation = "*New Bill Generated Amount :*
";
$MsgPatient = "
*New Case Arrived Today Number :*
";
    
$MsgDailyCollection = "
*Daily Collection :*
";
$billArr = [];
$patientArr = [];
$collectionArr = [];

if (mysqli_num_rows($branchs) > 0) {
    $iCounter = 1;
    while($branch = mysqli_fetch_assoc($branchs)){
        //$quotation = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT sum(amount) as Amount FROM `quotation` where branch_id='".$branch['branch_id']."' and created_at like '".$date."%'"));
        $quotation = mysqli_fetch_assoc(mysqli_query($dbconn,"select sum(net_amount) as Amount from order_master where branch_id='".$branch['branch_id']."' and istatus=0 and created_at like '".$date."%'"));
        $Amount = isset($quotation['Amount']) ? $quotation['Amount'] : 0;
$MsgQuotation .=
$iCounter.") ". $branch['branch_name'] ." : " .$Amount."
";
        
        $patient = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT count(*) as count FROM `patients` where branch_id='".$branch['branch_id']."' and created_at like '".$date."%'"));
        $PCount = isset($patient['count']) ? $patient['count'] : 0;
$MsgPatient .=
$iCounter.") ". $branch['branch_name'] ." : " .$PCount."
";
        
        $DailyCollection = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT sum(order_payment_detail.amount) as amount from order_payment_detail inner join order_master on order_master.order_master_id=order_payment_detail.order_id
        where order_payment_detail.istatus=0 and order_master.is_paid!=0 and order_payment_detail.clinic_id=1 and order_master.istatus=0  and order_payment_detail.branch_id='".$branch['branch_id']."' and order_payment_detail.payment_date like '".$date."%'"));
        $DailyCollectionAmount = isset($DailyCollection['amount']) ? $DailyCollection['amount'] : 0;
$MsgDailyCollection .=
$iCounter.") ". $branch['branch_name'] ." : " .$DailyCollectionAmount."
";
        $DailyCollectionAmount = isset($DailyCollection['amount']) ? $DailyCollection['amount'] : 0;

        // store per branch
        $billArr[] = $Amount;
        $patientArr[] = $PCount;
        $collectionArr[] = $DailyCollectionAmount;
    
        $iCounter++;
    }
}
echo "<pre/>";
// print_r($billArr);
// print_r($patientArr);
// print_r($collectionArr);
echo $msg = $MsgQuotation ." " . $MsgPatient . " " .$MsgDailyCollection;
// exit;
// $data = "https://newweb.technomantraa.com/api/send?number=917046673769&type=text&message=".urlencode($msg)."&instance_id=690EDD8CCD25E&access_token=65c486860588c";
// $ret = file_get_contents($data);
// $result = json_decode($ret);

// $data = "https://newweb.technomantraa.com/api/send?number=919904500629&type=text&message=".urlencode($msg)."&instance_id=690EDD8CCD25E&access_token=65c486860588c";
// $ret = file_get_contents($data);
// $result = json_decode($ret);

/*$data = "https://newweb.technomantraa.com/api/send?number=918401442448&type=text&message=".urlencode($msg)."&instance_id=690EDD8CCD25E&access_token=65c486860588c";
$ret = file_get_contents($data);
$result = json_decode($ret);


$data = "https://newweb.technomantraa.com/api/send?number=919724630450&type=text&message=".urlencode($msg)."&instance_id=690EDD8CCD25E&access_token=65c486860588c";
$ret = file_get_contents($data);
$result = json_decode($ret);*/

// 1) VGDC : RAOPURA :  9110.00 
// 2) VGDC: sama - Savli :  10000.00 
// 3) VGDC : HARNI :  39800.00 
// 4) VGDC : GOTRI BRANCH :  1700.00 
// 5) VGDC : VASNA BHAYALI :  7250.00 
// 6) VGDC : AJWA BRANCH :  450.00 
// 7) VGDC: Manjalpur :  1600.00 
// 8) VGDC: SUN PHARMA :  7950.00

// *New Case Arrived Today Number :*
// 1) VGDC : RAOPURA :  0 
// 2) VGDC: sama - Savli :  7 
// 3) VGDC : HARNI :  1 
// 4) VGDC : GOTRI BRANCH :  8 
// 5) VGDC : VASNA BHAYALI :  6 
// 6) VGDC : AJWA BRANCH :  4 
// 7) VGDC: Manjalpur :  3 
// 8) VGDC: SUN PHARMA :  11590.00
 
// *Daily Collection :*
// 1) VGDC : RAOPURA :  2 
// 2) VGDC: sama - Savli :  0 
// 3) VGDC : HARNI :  15410.00 
// 4) VGDC : GOTRI BRANCH :  10000.00 
// 5) VGDC : VASNA BHAYALI :  6300.00 
// 6) VGDC : AJWA BRANCH :  1700.00 
// 7) VGDC: Manjalpur :  10250.00 
// 8) VGDC: SUN PHARMA :  0

// $bodyValues = [
//     "1" => $billArr[0],
//     "2" => $billArr[1],
//     "3" => $billArr[2],
//     "4" => $billArr[3],
//     "5" => $billArr[4],
//     "6" => $billArr[5],
//     "7" => $billArr[6],
    
//     "8" => $patientArr[0],
//     "9" => $patientArr[1],
//     "10" => $patientArr[2],
//     "11" => $patientArr[3],
//     "12" => $patientArr[4],
//     "13" => $patientArr[5],
//     "14" => $patientArr[6],
    
//     "15" => $collectionArr[0],
//     "16" => $collectionArr[1],
//     "17" => $collectionArr[2],
//     "18" => $collectionArr[3],
//     "19" => $collectionArr[4],
//     "20" => $collectionArr[5],
//     "21" => $collectionArr[6],
    
//     "22" => $billArr[7],
//     "23" => $patientArr[7],
//     "24" => $collectionArr[7],
// ];

$bodyValues = [
    "1" => $billArr[0],
    "2" => $billArr[1],
    "3" => $billArr[2],
    "4" => $billArr[3],
    "5" => $billArr[4],
    "6" => $billArr[5],
    "7" => $billArr[6],
    "8" => $billArr[7],
    
    "9" => $patientArr[0],
    "10" => $patientArr[1],
    "11" => $patientArr[2],
    "12" => $patientArr[3],
    "13" => $patientArr[4],
    "14" => $patientArr[5],
    "15" => $patientArr[6],
    "16" => $patientArr[7],
    
    "17" => $collectionArr[0],
    "18" => $collectionArr[1],
    "19" => $collectionArr[2],
    "20" => $collectionArr[3],
    "21" => $collectionArr[4],
    "22" => $collectionArr[5],
    "23" => $collectionArr[6],
    "24" => $collectionArr[7],
];

$bodyValuesShreya = [
    "1" => $billArr[0],
    "2" => $billArr[7],
    "3" => $patientArr[0],
    "4" => $patientArr[7],
    "5" => $collectionArr[0],
    "6" => $collectionArr[7],
];
$bodyValuesSwati = [
    "1" => $billArr[3],
    "2" => $billArr[4],
    "3" => $patientArr[3],
    "4" => $patientArr[4],
    "5" => $collectionArr[3],
    "6" => $collectionArr[4],
];
// $response = sendWhatsAppAuthkey("7046673769", "30506", $bodyValuesShreya);
// $response = sendWhatsAppAuthkey("7046673769", "30505", $bodyValuesSwati);
// $response = sendWhatsAppAuthkey("7046673769", "30521", $bodyValues);
// echo "<pre/>";
// print_r($bodyValues);exit;

$response = sendWhatsAppAuthkey("8401442448", "30521", $bodyValues);
$response = sendWhatsAppAuthkey("9724630450", "30521", $bodyValues);

$response = sendWhatsAppAuthkey("7600030911", "30506", $bodyValuesShreya);
$response = sendWhatsAppAuthkey("9879640827", "30505", $bodyValuesSwati);

// $response = sendWhatsAppAuthkey("7046673769", "30521", $bodyValues);
// $response = sendWhatsAppAuthkey("9904500629", "30521", $bodyValues);

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

?>