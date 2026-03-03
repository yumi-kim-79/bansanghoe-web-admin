<?php
require_once './_common.php';

if($sign_id == ''){
    alert('잘못된 접근입니다.');
}

$sign_row = sql_fetch("SELECT COUNT(*) as cnt FROM a_sign_off WHERE sign_id = '{$sign_id}'");

if($sign_row['cnt'] == 0) alert('잘못된 접근입니다.');

$update_del = "UPDATE a_sign_off SET is_del = 1 WHERE sign_id = '{$sign_id}'";
sql_query($update_del);

goto_url('./approval_list.php?'.$qstr);
?>