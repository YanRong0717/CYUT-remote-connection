<?php
header('Access-Control-Allow-Origin: *');
ini_set('date.timezone', 'Asia/Taipei');
date_default_timezone_set('Asia/Taipei');
// session 的存放路徑 預設為 C:\xampp\tmp
session_start();

// print_r($_SESSION['remote_user']);
if (isset($_SESSION['remote_user'])) {
    // print_r($_SESSION);
    echo "1"; // already login
} else {
    // print_r($_SESSION);
    echo "0"; // go to login
}
