<?php
header('Content-Type: application/json; charset=utf-8');
@ob_clean();
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if($mv_idx == '') die(result_data(false, "잘못된 접근입니다.", []));

$update = "UPDATE a_move_request SET 
            is_del = 1,
            deleted_at = '{$today}'
            WHERE mv_idx = '{$mv_idx}'";
sql_query($update);

echo result_data(true, "이사(전출) 신청이 취소되었습니다.", []);
exit;
