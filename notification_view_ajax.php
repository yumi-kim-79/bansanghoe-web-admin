<?php
include_once('./_common.php');

$update = "UPDATE a_push SET is_view = 1 WHERE push_id = '{$push_id}'";
sql_query($update) or die(result_data(false, $update, []));

echo result_data(true, "푸시 확인 완료", []);