<?php
require_once "./_common.php";

if($mb_id == "") die(result_data(false, "잘못된 접근입니다.", []));
if($sign_id == "") die(result_data(false, "잘못된 접근입니다.", []));

$update = "UPDATE a_sign_off SET 
            is_del = 1,
            deleted_at = NOW()
            WHERE sign_id = '{$sign_id}'";
// die(result_data(false, $update, []));
sql_query($update);

echo result_data(true, "삭제가 완료되었습니다.", []);