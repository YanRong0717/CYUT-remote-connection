<?php
header('Access-Control-Allow-Origin: *');
ini_set('date.timezone', 'Asia/Taipei');
date_default_timezone_set('Asia/Taipei');
// session 的存放路徑 預設為 C:\xampp\tmp
session_start();

include "dbconn.php";

#連結資料庫
$conn_str = $database . ":host=" . $host . ";dbname=" . $dbname . ";charset=utf8";
$conn = new PDO($conn_str, $username, $password); #初始化一個PDO物件
#設定sql語句，變數前面加上冒號(:)表示它是placeholder，我們會在之後execute的時候再來填入它

$sql = "SELECT * FROM `user` WHERE `id` = :id";
$statement = $conn->prepare($sql);
$statement->execute(array(':id' => $_SESSION['remote_userid']));
$result = $statement->fetch(PDO::FETCH_ASSOC);
$count = $statement->rowCount();

$_SESSION['remote_mac'] = $result['mac'];
$_SESSION['remote_ip'] = $result['ip'];

// print_r($_SESSION);
$data = array();

$data['id'] = (isset($_SESSION['remote_userid'])) ? $_SESSION['remote_userid'] : "";
$data['user'] = (isset($_SESSION['remote_user'])) ? $_SESSION['remote_user'] : "";
$data['name'] = (isset($_SESSION['remote_name'])) ? $_SESSION['remote_name'] : "";
$data['status'] = (isset($_SESSION['remote_status'])) ? $_SESSION['remote_status'] : "";
$data['ip'] = (isset($_SESSION['remote_ip'])) ? $_SESSION['remote_ip'] : "";
$data['mac'] = (isset($_SESSION['remote_mac'])) ? $_SESSION['remote_mac'] : "";

echo json_encode($data);
