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
include "wake_on_lan.php";

#變數設定
#連結資料庫
$conn_str = $database . ":host=" . $host . ";dbname=" . $dbname . ";charset=utf8";
$conn = new PDO($conn_str, $username, $password); #初始化一個PDO物件
#設定sql語句，變數前面加上冒號(:)表示它是placeholder，我們會在之後execute的時候再來填入它

$user_id = $_POST['user_id']; //由id 去獲取、修改資料

// not logged in
$sql = "SELECT * FROM `user` WHERE `id` = :userid";
$statement = $conn->prepare($sql);
$statement->execute(array(':userid' => $user_id));
$result = $statement->fetch(PDO::FETCH_ASSOC);
$count = $statement->rowCount();

// 資料庫的user table 的 status 0: 未租用， 1 : 已租用

if ($count < 1) {
    echo "0"; // failed;
} else {

    $weekarray = ["7", "1", "2", "3", "4", "5", "6"];
    $day_of_week = $weekarray[date("w", time())];
    $sql5 = "SELECT * FROM `setting` WHERE CURRENT_TIME() between `start` and `end` and `day_of_week` = :day_of_week"; // 查看當前時間是否在 設定的租用時間

    $statement5 = $conn->prepare($sql5);
    $statement5->execute(array(':day_of_week' => $day_of_week));
    $count5 = $statement5->rowCount();

    if ($count5) { //如果有搜尋到 代表可以租借
        if ($result['status'] == "0") { // 未租用
            $sql2 = "SELECT * FROM `e620` WHERE `user_id` = 0  and `power` = 0 ORDER BY RAND() LIMIT 1"; // 隨機取一台未被使用的電腦
            $statement2 = $conn->prepare($sql2);
            $statement2->execute();
            $result2 = $statement2->fetch(PDO::FETCH_ASSOC);
            $count2 = $statement2->rowCount();
            if ($count2 < 1) { // 無可以租借的電腦
                echo "1";
                die();
            }

            $sql3 = "UPDATE `user` SET `status` = :remote_status  , `ip` = :ip ,`mac` = :mac , `borrow_time` = :borrow_time WHERE `id` = :id"; // 這個 id 是 user id
            $statement3 = $conn->prepare($sql3);
            $statement3->execute(array(':remote_status' => "1", ':ip' => $result2['ip'], ':mac' => $result2['mac'], ':id' => $user_id, ':borrow_time' => date("Y-m-d H:i:s")));
            $count3 = $statement3->rowCount(); // 若更新成功則會回傳更新行數 這邊只更新一行 所以只有1

            $sql4 = "UPDATE `e620` SET `user_id` = :userid , `last_time` = :last_time WHERE `e620`.`id` = :id"; // 這個 id 是 e620 id
            $statement4 = $conn->prepare($sql4);
            $statement4->execute(array(':userid' => $user_id, 'last_time' => date("Y-m-d H:i:s"), ':id' => $result2['id']));
            $count4 = $statement4->rowCount(); // 若更新成功則會回傳更新行數 這邊只更新一行 所以只有1

            // print_r(array($count3, $count4));

            if ($count3 && $count4) {
                $_SESSION['remote_status'] = "2";
                $_SESSION['remote_ip'] = $result2['ip'];
                $_SESSION['remote_mac'] = $result2['mac'];
                file_put_contents("../borrow&return_record.txt", $user_id . "\t" . "borrow" . "\t\t" . $ip . "\t\t" . $result2['ip'] . "\t\t" . date("Y-m-d h:i:sa") . "\r\n", FILE_APPEND | LOCK_EX);
                echo "2"; // 2, success , 變為已租用狀態
            }

            // echo "1"; // 成功租用一台電腦

        }
    } else {
        echo "3";
    }

}

#關閉連線
$conn = null;
