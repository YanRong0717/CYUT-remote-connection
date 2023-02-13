<?php
header('Access-Control-Allow-Origin: *');
ini_set('date.timezone', 'Asia/Taipei');
date_default_timezone_set('Asia/Taipei');
// session 的存放路徑 預設為 C:\xampp\tmp
session_start();

include "dbconn.php";

$a = "1";
#變數設定
#連結資料庫
$conn_str = $database . ":host=" . $host . ";dbname=" . $dbname . ";charset=utf8";
$conn = new PDO($conn_str, $username, $password); #初始化一個PDO物件
#設定sql語句，變數前面加上冒號(:)表示它是placeholder，我們會在之後execute的時候再來填入它

$sql = "UPDATE `user` SET `logout_time` = :logout_time WHERE `id` = :userid";
$statement = $conn->prepare($sql);
$statement->execute(array(':logout_time' => date("Y-m-d H:i:s"), ':userid' => $_SESSION['remote_userid']));
$count = $statement->rowCount();

if ($count == 1) {
    session_destroy();
    echo "1"; // 成功登出 並記錄時間
} else {
    // echo "0";
    // 會出現session userid undefined 的錯誤文字，所以就不echo了
}
// unset($_SESSION['remote_user']);
// unset($_SESSION['remote_userid']);
// unset($_SESSION['remote_name']);
// unset($_SESSION['remote_mac']);
// unset($_SESSION['remote_ip']);
