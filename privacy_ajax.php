<?php
require_once "./_common.php";

$privacy_sql = "SELECT * FROM g5_content WHERE co_id = '{$co_id}'";
$privacy_row = sql_fetch($privacy_sql);

echo $privacy_row['co_content'];
?>