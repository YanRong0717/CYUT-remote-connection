<?php header('Access-Control-Allow-Origin: *');

$fieldseparator = ",";
$lineseparator = "\n";
// $csvfile = "test3.csv"; // 測試路徑

$filename = $_FILES["file"]["name"]; // 檔案名稱
$filetmpname = $_FILES["file"]["tmp_name"]; // 上傳檔案後的暫存資料夾位置

// $filename = $_POST["name"]; // 檔案名稱
// $filetmpname = $_POST["tmp"]; // 上傳檔案後的暫存資料夾位置

// echo $filename;
// echo $filetmpname;
// die();
// ---------------------------------------------------
include "dbconn.php";

$sql = "TRUNCATE `remote`.`setting`"; // 清空資料表

include "dbconn.php";

#連結資料庫
$conn_str = $database . ":host=" . $host . ";dbname=" . $dbname . ";charset=utf8";
$conn = new PDO($conn_str, $username, $password); #初始化一個PDO物件
#設定sql語句，變數前面加上冒號(:)表示它是placeholder，我們會在之後execute的時候再來填入它

#執行prepare

$statement = $conn->prepare($sql);
$statement->execute();

// ----------------------------------------------------------------------------------------

$conn1 = new PDO($conn_str, $username, $password, array(
    PDO::MYSQL_ATTR_LOCAL_INFILE => true,
)); #初始化一個PDO物件
# https://stackoverflow.com/questions/7638090/load-data-local-infile-forbidden-in-php

if (!file_exists($filetmpname)) {
    die("找不到檔案，請確認路徑是否正確.");
}

$affectedRows = $conn1->exec(
    "LOAD DATA LOCAL INFILE "
    . $conn1->quote($filetmpname)
    . " INTO TABLE `setting` CHARACTER SET 'UTF8' FIELDS  TERMINATED BY "
    . $conn1->quote($fieldseparator) . "enclosed by '\"' escaped by '\"'"
    . "LINES TERMINATED BY "
    . $conn1->quote($lineseparator)
);

// echo "共新增了 " . $affectedRows . "筆至 setting資料表中.\n";
echo $affectedRows;
