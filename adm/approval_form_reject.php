<?php
require_once './_common.php';

$today = date("Y-m-d H:i:s");

if($sign_id == '') die(result_data(false, '잘못된 접근입니다.', []));

$row = sql_fetch("SELECT COUNT(*) as cnt FROM a_sign_off WHERE sign_id = '{$sign_id}'");

if($row['cnt'] == 0) die(result_data(false, '잘못된 접근입니다.', []));

$update_sign_status = "UPDATE a_sign_off SET 
                        sign_status = 'R',
                        sign_reject_id = '{$mb_id}',
                        sign_reject_at = '{$today}'
                        WHERE sign_id = '{$sign_id}'";
sql_query($update_sign_status);

//결재정보
$sign_info = sql_fetch("SELECT * FROM a_sign_off WHERE sign_id = '{$sign_id}'");
$approval_name = approval_category_name($sign_info['sign_off_category']);

$mng_info = get_member($sign_info['mng_id']);
$sign_off_id = $mng_info['mb_id']; //결재 신청자 아이디

//결재 반려한 사람 이름
$reject_id_info = get_manger($mb_id);
$reject_id_name = $reject_id_info['mng_name'].' '.$reject_id_info['mg_name'];

//결재반려 메세지
$push_title = '[결재반려] '.$approval_name." 결재 요청이 반려되었습니다.";
$push_content = $approval_name.' 결재 요청을 '.$reject_id_name."님이 반려하였습니다.";

//푸시발송 내역 저장
$insert_push = "INSERT INTO a_push SET
                recv_id = '{$sign_info['mng_id']}',
                recv_id_type = 'sm',
                push_title = '{$push_title}',
                push_content = '{$push_content}',
                wid = '{$mb_id}',
                push_type = 'sign_off',
                push_idx = '{$sign_id}',
                created_at = '{$today}'";
sql_query($insert_push);

//토큰이 있는 경우 푸시발송
if($mng_info['mb_token'] != '' && $mng_info['noti1']){
    fcm_send($mng_info['mb_token'], $push_title, $push_content, "sign_off", "{$sign_id}", "/holiday_reqeust_info.php?mng=Y&sign_id=");
}

echo result_data(true, "결재가 반려되었습니다.", []);