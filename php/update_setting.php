<?php
header('Access-Control-Allow-Origin: *');
ini_set('date.timezone', 'Asia/Taipei');
date_default_timezone_set('Asia/Taipei');
// session 的存放路徑 預設為 C:\xampp\tmp

include "dbconn.php";

#變數設定
$db_id = $_POST['db_id'];
$start = $_POST['start'];
$end = $_POST['end'];

// $start = date('hh:mm:ss', strtotime($start));
// $end = date('hh:mm:ss', strtotime($end));
// echo $start;
// echo $end;

// die();
#連結資料庫
$conn_str = $database . ":host=" . $host . ";dbname=" . $dbname . ";charset=utf8";
$conn = new PDO($conn_str, $username, $password); #初始化一個PDO物件
#設定sql語句，變數前面加上冒號(:)表示它是placeholder，我們會在之後execute的時候再來填入它

$sql = "UPDATE `setting` SET `start` = :startt , `end` = :endd WHERE `id` = :id";
$statement = $conn->prepare($sql);
$statement->execute(array(':startt' => $start, ':endd' => $end, ':id' => $db_id));
$result = $statement->fetch(PDO::FETCH_ASSOC);
$count = $statement->rowCount();

if ($count < 1) {
    echo "0"; // failed;
} else {
    echo "1"; // success;
}

#關閉連線
$conn = null;
