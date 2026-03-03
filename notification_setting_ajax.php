<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");
$tenant_at = date("Y-m-d");
$ip_info = $_SERVER['REMOTE_ADDR'];

//$noti_status = $noti_status == true ? 1 : 0;
$noti_status_val;
if($noti_status == 'true'){
    $noti_status_val = 1;
}else{
    $noti_status_val = 0;
}

$tables = "";

if($types != "sm"){
    $tables = "a_member";
}else{
    $tables = "g5_member";
}

//회원의 알림상태 변경
$update_mem = "UPDATE {$tables} SET
                {$noti} = '{$noti_status_val}'
                WHERE mb_id = '{$mb_id}'";

//die(result_data(false, $update_mem, []));
sql_query($update_mem);


//알림상태 변경 히스토리
$insert_noti_history = "INSERT INTO a_notification_history SET
                            id = '{$mb_id}',
                            noti_name = '{$noti}',
                            noti_status = '{$noti_status_val}',
                            created_at = '{$today}'";
sql_query($insert_noti_history);

echo result_data(true, '알림상태가 변경되었습니다.', []);