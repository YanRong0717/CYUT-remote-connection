<?php
# 將e620's user_id and power , user's status 歸零
header('Access-Control-Allow-Origin: *');

// echo $weekarray[date("w", time())];

#########################################################################
//
include "dbconn.php";
#連結資料庫
$conn_str = $database . ":host=" . $host . ";dbname=" . $dbname . ";charset=utf8";
$conn = new PDO($conn_str, $username, $password); #初始化一個PDO物件
#設定sql語句，變數前面加上冒號(:)表示它是placeholder，我們會在之後execute的時候再來填入它

$sql1 = "UPDATE `e620` SET `e620`.`user_id` = 0";
$statement1 = $conn->prepare($sql1);
$statement1->execute();
// $result = $statement->fetch(PDO::FETCH_ASSOC);
$count1 = $statement1->rowCount();
#########################################################################

include "runBat2.php"; // 先執行一次搜尋

$find_mac = "c4-12-f5-67-5e-fb"; # 隨便給一個MAC 讓程式能順利執行

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

#########################################################################

$sql2 = "UPDATE `e620` SET `e620`.`power` = 0";
$statement2 = $conn->prepare($sql2);
$statement2->execute();
// $result = $statement->fetch(PDO::FETCH_ASSOC);
$count2 = $statement2->rowCount();

for ($k = 0; $k < count($ip_array); $k++) {
    // print_r($k . "\r\n");
    // print_r($count . "\r\n");

    $sql = "UPDATE `user`  SET `ip` = :ip  WHERE `user`.`mac` = :mac ";
    $statement = $conn->prepare($sql);
    $statement->execute(array(':ip' => $ip_array[$k], ':mac' => $mac_array[$k]));

    $sql6 = "UPDATE `e620` SET  `e620`.`ip` = :ip , `e620`.`power` = 1 WHERE `e620`.`mac` = :mac";
    $statement6 = $conn->prepare($sql6);
    $statement6->execute(array(':ip' => $ip_array[$k], ':mac' => $mac_array[$k]));
    // $result = $statement->fetch(PDO::FETCH_ASSOC);
    // $count1 = $statement->rowCount();
}
// echo "1";

$sql3 = "UPDATE `user` SET `user`.`status` = 0";
$statement3 = $conn->prepare($sql3);
$statement3->execute();

$sql4 = "UPDATE `user` SET `user`.`ip` = ''";
$statement4 = $conn->prepare($sql4);
$statement4->execute();

$sql5 = "UPDATE `user` SET `user`.`mac` = ''";
$statement5 = $conn->prepare($sql5);
$statement5->execute();

$conn = null;
