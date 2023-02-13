<?php

$weekarray = ["7", "1", "2", "3", "4", "5", "6"];

echo $weekarray[date("w", time())];

$sql = "SELECT * FROM `setting` WHERE CURRENT_TIME() between `start` and `end` and `day_of_week` = 6";
