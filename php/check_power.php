<?php
header('Access-Control-Allow-Origin: *');
ini_set('date.timezone', 'Asia/Taipei');
date_default_timezone_set('Asia/Taipei');
// session 的存放路徑 預設為 C:\xampp\tmp
session_start();

include "dbconn.php";
$mac = $_SESSION['remote_mac'];

#變數設定
#連結資料庫
$conn_str = $database . ":host=" . $host . ";dbname=" . $dbname . ";charset=utf8";
$conn = new PDO($conn_str, $username, $password); #初始化一個PDO物件
#設定sql語句，變數前面加上冒號(:)表示它是placeholder，我們會在之後execute的時候再來填入它

// not logged in
$sql = "SELECT * FROM `e620` WHERE `power` = 1 and `mac` = :mac";
$statement = $conn->prepare($sql);
$statement->execute(array(':mac' => $mac));
$result = $statement->fetch(PDO::FETCH_ASSOC);
$count = $statement->rowCount();

if ($count) {
    echo "1"; // success;
} else {
    echo "0"; // failed;
}
#關閉連線
$conn = null;
