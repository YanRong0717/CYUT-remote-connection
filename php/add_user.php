<?php
header('Access-Control-Allow-Origin: *');
ini_set('date.timezone', 'Asia/Taipei');
date_default_timezone_set('Asia/Taipei');
// session 的存放路徑 預設為 C:\xampp\tmp

include "dbconn.php";

#變數設定
$user = $_POST['user'];
$name = $_POST['name'];

// $start = date('hh:mm:ss', strtotime($start));
// $end = date('hh:mm:ss', strtotime($end));
// echo $start;
// echo $end;

// die();
#連結資料庫
$conn_str = $database . ":host=" . $host . ";dbname=" . $dbname . ";charset=utf8";
$conn = new PDO($conn_str, $username, $password); #初始化一個PDO物件
#設定sql語句，變數前面加上冒號(:)表示它是placeholder，我們會在之後execute的時候再來填入它

$sql2 = "SELECT * FROM `user` WHERE `user` = :user";
$statement2 = $conn->prepare($sql2);
$statement2->execute(array(':user' => $user));
$result2 = $statement2->fetch(PDO::FETCH_ASSOC);
$count2 = $statement2->rowCount();

// echo $count2;
// die();
if ($count2 < 1) {
    $sql = "INSERT INTO `user` (`id`, `user`, `pass`, `name`, `status`, `ip`, `mac`, `login_time`, `logout_time`, `borrow_time`) VALUES (NULL, :user, :pass, :namee, '0', '', '', '2022-01-01 00:00:00', '2022-01-01 00:00:00', '2022-01-01 00:00:00');";

    $statement = $conn->prepare($sql);
    $statement->execute(array(':user' => $user, ':pass' => $user, ':namee' => $name));
    $result = $statement->fetch(PDO::FETCH_ASSOC);
    $count = $statement->rowCount();

    if ($count < 1) {
        echo "0"; // failed;
    } else {
        echo "1"; // success;
    }
} else {
    echo "2"; // 有重複ID
}

#關閉連線
$conn = null;
