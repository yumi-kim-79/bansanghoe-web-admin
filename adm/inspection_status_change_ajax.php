<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if($inspection_idx == '') die(result_data(false, "잘못된 접근입니다.", []));
if($inspection_status == '') die(result_data(false, "잘못된 접근입니다.", []));


$approval_sql = "";
if($inspection_status == 'Y'){
    $approval_sql = " ,approval_at = '{$today}'";
}

//상태값 변경
$update_inspection = "UPDATE a_inspection SET
                        inspection_status = '{$inspection_status}'
                        {$approval_sql}
                        WHERE inspection_idx = '{$inspection_idx}'";
sql_query($update_inspection);

//상태값에 따라 메시지
switch($inspection_status){
    case "R":
        $status_msg = "재요청 상태로 변경되었습니다.";
        break;
    case "H":
        $status_msg = "보류 상태로 변경되었습니다.";
        break;
    case "Y":
        $status_msg = "승인 상태로 변경되었습니다.";
        break;
}

//승인일때 푸시발송
if($inspection_status == 'Y'){

    $inspection_info = sql_fetch("SELECT * FROM a_inspection WHERE inspection_idx = '{$inspection_idx}'");

    $builidng_info = get_builiding_info($inspection_info['building_id']);
    $building_name = $building_info['building_name'];

     // 단지내 세대에게 푸시발송
    $ho_sql = "SELECT ho.*, mem.mb_id, mem.mb_token FROM a_building_ho as ho
               LEFT JOIN a_member as mem ON ho.ho_tenant_hp = mem.mb_hp
               WHERE ho.building_id = '{$inspection_info['building_id']}' and ho.ho_status = 'Y' and ho.is_del = 0 ORDER BY ho.ho_id asc";
    $ho_res = sql_query($ho_sql);

    while($ho_row = sql_fetch_array($ho_res)){
        
        $push_title = '[점검일지] '.$building_name.' 점검일지가 등록되었습니다.';
        $push_content = $inspection_info['inspection_year'].'년 '.$inspection_info['inspection_month'].'월 '.$building_name.' 점검일지입니다.';

        if($ho_row['mb_token'] != ""){ //토큰이 있는경우 푸시 발송
            
            fcm_send($ho_row['mb_token'], $push_title, $push_content, 'inspection_y', "{$inspection_idx}", "/inspection_info.php?inspection_idx=");
        }

        $insert_push = "INSERT INTO a_push SET
                    recv_id_type = 'user',
                    recv_id = '{$ho_row['mb_id']}',
                    push_title = '{$push_title}',
                    push_content = '{$push_content}',
                    wid = '{$inspection_info['inspection_name']}',
                    push_type = 'inspection_y',
                    push_idx = '{$inspection_idx}',
                    created_at = '{$today}'";
        sql_query($insert_push);
    }
}

echo result_data(true, $status_msg, []);