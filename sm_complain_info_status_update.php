<?php
require_once "./_common.php";

//die(result_data(false, $_POST, []));
$today = date("Y-m-d H:i:s");

if($mng_department == "") die(result_data(false, "부서를 선택해주세요.", []));

if($complain_status == "CA"){
    if($mng_id == "") die(result_data(false, "담당자를 선택해주세요.", []));
}

$sql_up = "";
if($mng_id != ""){

    $sql_up = " mng_id = '{$mng_id}',
                complain_status = 'CC' ";

    $msg = "해당 민원의 담당자 배정이 완료되었습니다.";

}else{
    
    $sql_up = " complain_status = 'CA'";

    $msg = "해당 민원의 부서 배정이 완료되었습니다.";
}

$update_complain = "UPDATE a_online_complain SET
                    mng_department = '{$mng_department}',
                    {$sql_up}
                    WHERE complain_idx = '{$complain_idx}'";
sql_query($update_complain);


if($mng_id != ""){
    //sm 매니저 전직원에게 푸시 발송
    $mng_sql = "SELECT * FROM g5_member WHERE mb_id = '{$mng_id}'";
    $mng_row = sql_fetch($mng_sql);

    $push_title = '[담당자 배정] 민원 담당자로 배정되었습니다.';
    $push_content = '민원 담당자로 배정되었습니다. 민원 확인 후 처리 부탁드립니다.';

    if($mng_row['mb_token'] != "" && $mng_row['noti6']){ //토큰이 있는경우 푸시 발송
           
        fcm_send($mng_row['mb_token'], $push_title, $push_content, 'complain_mng', "{$complain_idx}", "/sm_complain_info.php?complain_status=CC&complain_idx=");
    }

    $insert_push = "INSERT INTO a_push SET
                    recv_id_type = 'sm',
                    recv_id = '{$mng_id}',
                    push_title = '{$push_title}',
                    push_content = '{$push_content}',
                    wid = '{$wid}',
                    push_type = 'complain_mng',
                    push_idx = '{$complain_idx}',
                    created_at = '{$today}'";
    sql_query($insert_push);
}


echo result_data(true, $msg, []);