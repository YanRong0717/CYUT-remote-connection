<?php
header('Access-Control-Allow-Origin: *');
ini_set('date.timezone', 'Asia/Taipei');
date_default_timezone_set('Asia/Taipei');

$weekarray = ["7", "1", "2", "3", "4", "5", "6"];

$today = $weekarray[date("w", time())];
$tomorrow = $today + 1;

include "dbconn.php";

#變數設定
#連結資料庫
$conn_str = $database . ":host=" . $host . ";dbname=" . $dbname . ";charset=utf8";
$conn = new PDO($conn_str, $username, $password); #初始化一個PDO物件
#設定sql語句，變數前面加上冒號(:)表示它是placeholder，我們會在之後execute的時候再來填入它

// not logged in
// $sql = "SELECT * FROM `setting` WHERE `day_of_week` = :today OR `day_of_week` = :tomorrow";
$sql = "SELECT * FROM `setting`";
$statement = $conn->prepare($sql);
// $statement->execute(array(':today' => $today, ':tomorrow' => $tomorrow));
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result);
#關閉連線
$conn = null;
