<?php
require_once "./_common.php";

$year = date("Y");
$month = date("n");

$sql = "SELECT * FROM a_building WHERE building_id = '{$building_id}'";
$row = sql_fetch($sql);

//$year."년 ".$month."월 "
echo $row['building_name'].' 검침 엑셀 업로드';