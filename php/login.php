<?php
header('Access-Control-Allow-Origin: *');
ini_set('date.timezone', 'Asia/Taipei');

if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
    $ip = $_SERVER["HTTP_CLIENT_IP"];
} elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
    $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
} else {
    $ip = $_SERVER["REMOTE_ADDR"];
}

date_default_timezone_set('Asia/Taipei');
// session 的存放路徑 預設為 C:\xampp\tmp
session_start();
include "dbconn.php";

#變數設定
#連結資料庫
$conn_str = $database . ":host=" . $host . ";dbname=" . $dbname . ";charset=utf8";
$conn = new PDO($conn_str, $username, $password); #初始化一個PDO物件
#設定sql語句，變數前面加上冒號(:)表示它是placeholder，我們會在之後execute的時候再來填入它

$user = $_POST['user'];
$pass = $_POST['pass'];
// $user = "10815605";
// $pass = "10815605";

// not logged in
$sql = "SELECT * FROM `user` WHERE `user` = :user AND `pass` = :pass;";
$statement = $conn->prepare($sql);
$statement->execute(array(':user' => $user, ':pass' => $pass));
$result = $statement->fetch(PDO::FETCH_ASSOC);
$count = $statement->rowCount();

if ($count < 1) {
    echo "0"; // failed;
    file_put_contents("../login_record.txt", "x" . "\t" . "failed" . "\t\t" . $ip . "\t" . date("Y-m-d h:i:sa") . "\r\n", FILE_APPEND | LOCK_EX);
} else {
    file_put_contents("../login_record.txt", $result['id'] . "\t" . "success" . "\t\t" . $ip . "\t" . date("Y-m-d h:i:sa") . "\r\n", FILE_APPEND | LOCK_EX);

    $sql2 = "UPDATE `user` SET `login_time` = :login_time WHERE `user` = :user AND `pass` = :pass;";
    $statement2 = $conn->prepare($sql2);
    $statement2->execute(array(':login_time' => date("Y-m-d H:i:s"), ':user' => $user, ':pass' => $pass));

    $_SESSION['remote_user'] = $user;
    $_SESSION['remote_userid'] = $result['id'];
    $_SESSION['remote_name'] = $result['name'];
    $_SESSION['remote_mac'] = $result['mac'];
    $_SESSION['remote_ip'] = $result['ip'];

    if ($result['status'] == "0") {
        $_SESSION['remote_status'] = "1";
        echo "1"; // 1, success , 未租用狀態
    } else {
        $_SESSION['remote_status'] = "2";
        echo "2"; // 2, success , 已租用狀態

    }

    // print_r($_SESSION['remote_user']);
}

#關閉連線
$conn = null;
