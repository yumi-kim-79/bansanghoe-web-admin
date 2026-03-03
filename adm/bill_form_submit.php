<?php
require_once './_common.php';

if($bill_id == '') die(result_data(false, '잘못된 접근입니다.', []));

$today = date("Y-m-d H:i:s");

$update = "UPDATE a_bill SET 
            is_submit = 'Y',
            submited_at = '{$today}',
            r_submited = '',
            r_submited_at = NULL
            WHERE bill_id = '{$bill_id}'";
sql_query($update);

//고지서 발행시 푸시발송
$bill_info = sql_fetch("SELECT * FROM a_bill WHERE bill_id = '{$bill_id}'");
$building_id = $bill_info['building_id']; //빌딩 인덱스

$building_info = get_builiding_info($building_id); //단지정보

//입주자
$sql_ho = "SELECT ho.*, mem.mb_token, mem.noti1 FROM a_building_ho as ho
           LEFT JOIN a_member as mem ON ho.ho_tenant_id = mem.mb_id
           WHERE ho.building_id = '{$building_id}' and ho.ho_status = 'Y'
           GROUP BY ho.ho_tenant_id";
$res_ho = sql_query($sql_ho);

while($row_ho = sql_fetch_array($res_ho)){
    $push_title = '[고지서] '.$building_info['building_name']." ".$bill_info['bill_year']."년 ".$bill_info['bill_month']."월 고지서가 발행되었습니다.";
    $push_content = $building_info['building_name']." ".$bill_info['bill_year']."년 ".$bill_info['bill_month']."월 고지서가 발행되었습니다.";


    $insert_push = "INSERT INTO a_push SET
                    recv_id_type = 'user',
                    recv_id = '{$row_ho['ho_tenant_id']}',
                    push_title = '{$push_title}',
                    push_content = '{$push_content}',
                    wid = '{$bill_info['wid']}',
                    push_type = 'bill',
                    push_idx = '{$bill_id}',
                    created_at = '{$today}'";
    sql_query($insert_push);

    if($row_ho['mb_token'] != "" && $row_ho['noti1']){ //토큰이 있는경우 푸시 발송
           
        fcm_send($row_ho['mb_token'], $push_title, $push_content, 'bill', "{$bill_id}", "/bill.php?bill_id=");
    }
}

echo result_data(true, '고지서가 발행되었습니다.', []);