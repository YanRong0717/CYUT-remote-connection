<?php

# 將e620's user_id and power , user's status 歸零

echo $weekarray[date("w", time())];

$sql = "SELECT * FROM `setting` WHERE CURRENT_TIME() between `start` and `end` and `day_of_week` = 6";
