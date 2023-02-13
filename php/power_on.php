<?php
header('Access-Control-Allow-Origin: *');
ini_set('date.timezone', 'Asia/Taipei');
date_default_timezone_set('Asia/Taipei');
// session 的存放路徑 預設為 C:\xampp\tmp
// session_start();
include "wake_on_lan.php";
include "dbconn.php";
// $ip = "163.17.12.255";
// $ip = $_SESSION['remote_ip'];
// $mac = $_SESSION['remote_mac'];
$mac = $_POST['mac'];

#連結資料庫
$conn_str = $database . ":host=" . $host . ";dbname=" . $dbname . ";charset=utf8";
$conn = new PDO($conn_str, $username, $password); #初始化一個PDO物件
#設定sql語句，變數前面加上冒號(:)表示它是placeholder，我們會在之後execute的時候再來填入它

// not logged in
$sql = "SELECT * FROM `e620` WHERE `mac` = :mac";
$statement = $conn->prepare($sql);
$statement->execute(array(':mac' => $mac));
$result = $statement->fetch(PDO::FETCH_ASSOC);
$count = $statement->rowCount();

// 資料庫的user table 的 status 0: 未租用， 1 : 已租用
$ip = "120.110.14.255"; //廣播位置

if ($ip == "") {
    echo "0";
    // die();
} else {
    echo "1";
    $_SESSION['remote_ip'] = $ip;
}
#關閉連線
$conn = null;

// $ip = '120.110.14.58';
// $mac = '7c-10-c9-52-27-cc';
// $mac = '7c-10-c9-52-27-dc'; # 9
// $mac = '7c-10-c9-52-28-87'; #7
// $mac = '94-c6-91-6b-8d-32'; #1

// $mac = "94-C6-91-6B-8D-32"; #1
// $mac = "94-C6-91-6B-8E-46"; #6

// $mac1 = "74-D0-2B-99-49-27"; // 72
// $mac2 = "48-0F-CF-54-BD-AD"; // 79
// $mac3 = "40-A8-F0-3E-CE-24"; // 72

$port = 7;

try {
    // print_r(array($ip, $mac, $port));
    $WOL = new WakeOnLan($ip, $mac, $port);
    $status = $WOL->wake_on_wan();
    echo $status;
} catch (Exception $ex) {
    var_dump($ex->getMessage());
}

// $WOL = new WakeOnLan();

// try {
//     // 使用 port 7
//     $res = $WOL->wake($ip, $mac, $port);
//     // 也可使用 port 9
//     //$res = $WOL->wake('120.110.14.51', "94-C6-91-6B-8E-22", 9);
//     var_dump($res);
// } catch (Exception $ex) {
//     var_dump($ex->getMessage());
// }
