<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if($complain_idx == "") die(result_data(false, "잘못된 접근입니다.", []));

//민원 정보
$online_complain_info = sql_fetch("SELECT * FROM a_online_complain WHERE complain_idx = '{$complain_idx}'");

if($online_complain_info['mng_id'] == $mng_id) die(result_data(false, "동일한 담당자입니다.", []));

if($mng_change_memo == "") die(result_data(false, "변경이유를 입력해주세요.", []));

//담당자 변경
$complain_update = "UPDATE a_online_complain SET
                    mng_department = '{$mng_department}',
                    mng_id = '{$mng_id}',
                    mng_change_memo = '{$mng_change_memo}'
                    WHERE complain_idx = '{$complain_idx}'";
sql_query($complain_update);

if($mng_id != ""){
    //sm 매니저 전직원에게 푸시 발송
    $mng_sql = "SELECT * FROM g5_member WHERE mb_id = '{$mng_id}'";
    $mng_row = sql_fetch($mng_sql);

    $push_title = '[담당자 변경] 민원 담당자로 변경되었습니다.';
    $push_content = '민원 담당자로 변경되었습니다. 민원 확인 후 처리 부탁드립니다.';

    if($mng_row['mb_token'] != "" && $mng_row['noti6']){ //토큰이 있는경우 푸시 발송 민원알림이 켜져있는경우
           
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

echo result_data(true, '담당자 변경이 완료되었습니다.', []);