<?php
header('Access-Control-Allow-Origin: *');
ini_set('date.timezone', 'Asia/Taipei');
date_default_timezone_set('Asia/Taipei');
// session 的存放路徑 預設為 C:\xampp\tmp
session_start();

include "dbconn.php";

ini_set("max_execution_time", "60");
ini_set("memory_limit", "256M");
ini_set("max_execution_time", 600);

$file_ending = "rdp";
$userid = $_GET['user_id'];

header('Access-Control-Allow-Origin: *');
header("Pragma: public");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/rdp");
header("Content-Disposition: attachment; filename=$userid.$file_ending");
header("Content-Transfer-Encoding: binary ");
header("Pragma: no-cache");
header("Expires: 0");
# -----------------------------------------------------------------------------------------

#連結資料庫
$conn_str = $database . ":host=" . $host . ";dbname=" . $dbname . ";charset=utf8";
$conn = new PDO($conn_str, $username, $password); #初始化一個PDO物件
$conn->exec("set names utf8");
#設定sql語句，變數前面加上冒號(:)表示它是placeholder，我們會在之後execute的時候再來填入它

$sql = "SELECT * FROM `user` WHERE `id` = :userid ";
$statement = $conn->prepare($sql);
$statement->execute(array(':userid' => $userid));
$result = $statement->fetch(PDO::FETCH_ASSOC);
// echo "\xEF\xBB\xBF";
echo "full address:s:" . $result['ip'] . "\r\n";
echo "username:s:user";

#關閉連線
$conn = null;

// $WOL = new WakeOnLan();

// try {
//     // 使用 port 7
//     $res = $WOL->wake('163.17.12.79', "48-0F-CF-54-BD-AD", 7);
//     // 也可使用 port 9
//     //$res = $WOL->wake('120.110.14.51', "94-C6-91-6B-8E-22", 9);
//     var_dump($res);
// } catch (Exception $ex) {
//     var_dump($ex->getMessage());
// }
