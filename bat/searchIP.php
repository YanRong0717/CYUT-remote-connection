<?php
header('Access-Control-Allow-Origin: *');
// print_r("start!\n");
// die();
// session_start();
include "runBat2.php"; // 先執行一次搜尋

$find_mac = $_POST['mac'];

// $find_mac = "04-0e-3c-0c-50-15";
// $find_ip = "";

// if ($find_mac == "") {
//     echo "-1"; // 他連 mac都沒有，代表他根本沒有租用，所以不能給他IP
//     die();
// }

$ip_array = array();
$mac_array = array();
$find = "0";
function find_ip($ip_array, $mac_array, $find_mac, $find)
{
    $file_path = __DIR__ . "\ipList.txt";

    $i = 0;
    $count = 0;
    $file = fopen($file_path, "r");
    if (file_exists($file_path)) {
        while (!feof($file)) {
            $qq = fgets($file);
            $count++;
        }
    }
    // print_r($count);
    fclose($file);

    $file = fopen($file_path, "r");
    if (file_exists($file_path)) {
        while (!feof($file)) {
            $get_str = fgets($file);
            if (strpos($get_str, $find_mac) !== false) { // 在該列搜尋中找到對應的mac時，做以下事情
                // echo $get_str;
                $res = explode(" ", $get_str);
                $find = "1"; // 如果有找到該mac就回傳 1 否則 0
            }
            // print_r($get_str);
            if ($i > 0 && $i < $count - 2) { // 第一列不讀取
                // print_r($count);
                try {
                    $res = explode(" ", $get_str);
                    // print_r($res);
                    // print_r($res[12] . "\r\n");
                    // print_r("\r\n");

                    if ($res[11] != "") { // 因為txt 用空格分隔，會因為ip 可能是個位數、十位數、百位數而差一個空白格
                        array_push($ip_array, $res[2]);
                        array_push($mac_array, $res[11]);
                    } else if ($res[12] != "") {
                        array_push($ip_array, $res[2]);
                        array_push($mac_array, $res[12]);
                    }

                } catch (Exception $ex) {
                }
            }
            $i++;
        }
        return array($ip_array, $mac_array, $find);
    }
    fclose($file);
}

$ip = "";
$a = find_ip($ip_array, $mac_array, $find_mac, $find);
$ip_array = $a[0];
$mac_array = $a[1];
$find = $a[2];
// print_r($ip_array);
// print_r($mac_array);

// die();
// print_r("================\r\n");
if ($find == "1") {
    echo "1"; // 有搜尋到該mac對應的ip
} else {
    echo "0";
    include "runBat2.php"; // 沒有搜尋到 ，再執行一次
}
// print_r($a);
// print_r($ip_array);
// print_r($mac_array);
// print($count);
// die();

// echo "ok"; // 有搜尋到
#########################################################################
// 不論有無搜尋到對應的IP ，都將所有有搜尋到的IP 去資料庫更新
include "dbconn.php";

#連結資料庫
$conn_str = $database . ":host=" . $host . ";dbname=" . $dbname . ";charset=utf8";
$conn = new PDO($conn_str, $username, $password); #初始化一個PDO物件
#設定sql語句，變數前面加上冒號(:)表示它是placeholder，我們會在之後execute的時候再來填入它

$sql2 = "UPDATE `e620` SET `e620`.`power` = 0";
$statement2 = $conn->prepare($sql2);
$statement2->execute();
// $result = $statement->fetch(PDO::FETCH_ASSOC);
$count2 = $statement2->rowCount();

for ($k = 0; $k < count($ip_array); $k++) {
    // print_r($k . "\r\n");
    // print_r($count . "\r\n");

    $sql = "UPDATE `user` , `e620` SET `user`.`ip` = :ip , `e620`.`ip` = :ip , `e620`.`power` = 1 WHERE `user`.`mac` = :mac AND `e620`.`mac` = :mac";
    $statement = $conn->prepare($sql);
    $statement->execute(array(':ip' => $ip_array[$k], ':mac' => $mac_array[$k]));
    // $result = $statement->fetch(PDO::FETCH_ASSOC);
    // $count1 = $statement->rowCount();
}
// echo "1";

$conn = null;
