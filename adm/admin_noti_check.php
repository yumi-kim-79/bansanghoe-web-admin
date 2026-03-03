<?php
require_once './_common.php';

$push_sql = "SELECT COUNT(*) as cnt FROM a_push WHERE recv_id_type = 'sm' and recv_id = '{$mb_id}' and is_view = 0 ORDER BY push_id desc";
$push_row = sql_fetch($push_sql);

if($push_row['cnt'] > 0){
    echo result_data(true, '확인하지 않은 알림이 있습니다.', $push_row['cnt']);
}else{
    die(result_data(false, '확인하지 않은 알림이 없습니다.', '0'));
}