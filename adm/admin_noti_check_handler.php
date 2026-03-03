<?php
require_once './_common.php';

$push_sql = "SELECT COUNT(*) as cnt FROM a_push WHERE recv_id_type = 'sm' and recv_id = '{$mb_id}' and is_view = 0 ORDER BY push_id desc";
$push_row = sql_fetch($push_sql);

if($push_row['cnt'] == 0){
    die(result_data(false, '확인하지 않은 알림이 없습니다.', []));
}

$update = "UPDATE a_push SET is_view = 1 WHERE recv_id = '{$mb_id}' and is_view = 0";
sql_query($update);

echo result_data(true, '모든 알림이 읽음 처리되었습니다.', []);