<?php
// 放在每台被遠端的電腦中，每次遠端中斷連線時，執行
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

include "dbconn.php";

$g = $_GET['data'];
$data = explode(",", $g);
$ipAddress = $data[0];
$macAddr = $data[1];

$macAddr = str_replace(":", "-", $macAddr);

echo "==========" . "<br>";
echo "ip: " . $ipAddress . "<br>";
echo "mac: " . $macAddr . "<br>";

// die();
#變數設定

#連結資料庫
$conn_str = $database . ":host=" . $host . ";dbname=" . $dbname . ";charset=utf8";
$conn = new PDO($conn_str, $username, $password); #初始化一個PDO物件
#設定sql語句，變數前面加上冒號(:)表示它是placeholder，我們會在之後execute的時候再來填入它

$sql = "UPDATE `user` SET `status` = :statuss , `ip` = :ip , `mac` = :mac  WHERE `mac` = :pc_mac";
$statement = $conn->prepare($sql);
$statement->execute(array(':statuss' => 0, ':ip' => "", ':mac' => "", ':pc_mac' => $macAddr));
$result = $statement->fetch(PDO::FETCH_ASSOC);
$count = $statement->rowCount();

$sql2 = "UPDATE `e620` SET `user_id` = :userid , `power` = :powerr WHERE `mac` = :mac";
$statement2 = $conn->prepare($sql2);
$statement2->execute(array(':userid' => 0, ':powerr' => 0, ':mac' => $macAddr));
$result2 = $statement2->fetch(PDO::FETCH_ASSOC);
$count2 = $statement2->rowCount();

echo "==========" . "<br>";
print("update e620 count :" . $count . "<br>");
print("update user count :" . $count2 . "<br>");
echo "==========" . "<br>";

if ($count > 0 && $count2 > 0) {
    echo "success"; // success;
    file_put_contents("../borrow&return_record.txt", "x" . "\t" . "return" . "\t\t" . $ip . "\t\t" . $ipAddress . "\t\t" . date("Y-m-d h:i:sa") . "\r\n", FILE_APPEND | LOCK_EX);
} else {
    echo "failed"; // failed
}

#關閉連線
$conn = null;
