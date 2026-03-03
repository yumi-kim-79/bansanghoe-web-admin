<?php
include_once('./_common.php');

$sql = "SELECT * FROM a_building WHERE building_id = '{$building_id}'";
$row = sql_fetch($sql);

echo result_data(true, $row['post_id'], []);