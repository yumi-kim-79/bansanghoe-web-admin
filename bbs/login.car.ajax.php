<?php
include_once('./_common.php');

$sql = "SELECT * FROM a_member WHERE mb_hp = '{$mb_id}'";
$row = sql_fetch($sql);


$car = "SELECT COUNT(*) as cnt FROM a_building_car WHERE mb_id = '{$row['mb_id']}' and is_del = 0 ORDER BY car_id asc limit 0, 1";
$car_row = sql_fetch($car);

echo result_data(true, '회원정보', $car_row);