<?php
require_once './_common.php';

$member_id = "bansang_mb_7397";
$sql_mem = "SELECT * FROM a_member WHERE mb_id = '{$member_id}'";

echo $sql_mem;
$row_mem = sql_fetch($sql_mem);

fcm_send($row_mem['mb_token'], '1', '2', 'car', "1", "");
?>