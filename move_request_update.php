<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if($mv_date == "") die(result_data(false, "이사 예정 날짜를 선택해주세요.", "mv_date"));
if($move_time == "") die(result_data(false, "이사 시작 시간을 선택해주세요.", "move_time"));
if($move_min == "") die(result_data(false, "이사 시작 분을 선택해주세요.", "move_min"));
if($mv_estate_name == "") die(result_data(false, "부동산을 입력해주세요.", "mv_estate_name"));
if($mv_estate_number == "") die(result_data(false, "부동산 연락처를 입력해주세요.", "mv_estate_number"));

if($w == "u"){

    $update_query = "UPDATE a_move_request SET
                        mv_date = '{$mv_date}',
                        move_time = '{$move_time}',
                        move_min = '{$move_min}',
                        mv_estate_name = '{$mv_estate_name}',
                        mv_estate_number = '{$mv_estate_number}',
                        mv_memo = '{$mv_memo}'
                        WHERE mv_idx = '{$mv_idx}'";
    sql_query($update_query);
    
    echo result_data(true, "이사(전출) 신청이 수정되었습니다.", []);

}else{

    $insert_query = "INSERT INTO a_move_request SET
                        building_id = '{$building_id}',
                        dong_id = '{$dong_id}',
                        ho_id = '{$ho_id}',
                        mb_id = '{$mb_id}',
                        mv_date = '{$mv_date}',
                        move_time = '{$move_time}',
                        move_min = '{$move_min}',
                        mv_estate_name = '{$mv_estate_name}',
                        mv_estate_number = '{$mv_estate_number}',
                        mv_memo = '{$mv_memo}',
                        created_at = '{$today}'";

    sql_query($insert_query);
    $mv_idx = sql_insert_id(); //팝업 idx

    //작성자 정보 조회
    $users = get_user($mb_id); 

    //담당 매니저들에게 푸시발송
    $mng_building_sql = "SELECT mng_b.*, mem.mb_token, mem.mb_leave_date, mem.noti4, building.is_use FROM a_mng_building as mng_b
                         LEFT JOIN g5_member as mem ON mng_b.mb_id = mem.mb_id
                         LEFT JOIN a_building as building ON mng_b.building_id = building.building_id
                         WHERE building.is_use = 1 and mem.mb_leave_date = '' and mng_b.building_id = '{$building_id}'";
    //die(result_data(false, $mng_building_sql, []));                     
    $mng_building_res = sql_query($mng_building_sql);

    while($mng_building_row = sql_fetch_array($mng_building_res)){
        
        $push_title = "[이사(전출)신청] 민원신청이 있습니다.";
        $push_content = $users['mb_name']."님의 이사(전출)신청이 있습니다.";

        if($mng_building_row['mb_token'] != "" && $mng_building_row['noti4']){ //토큰이 있는경우 푸시 발송
           
            fcm_send($mng_building_row['mb_token'], $push_title, $push_content, 'move', "{$mv_idx}", "/sm_move.php?mv_idx=");
        }

        $insert_push = "INSERT INTO a_push SET
                        recv_id_type = 'sm',
                        recv_id = '{$mng_building_row['mb_id']}',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$mb_id}',
                        push_type = 'move',
                        push_idx = '{$mv_idx}',
                        created_at = '{$today}'";
        sql_query($insert_push);
    }

    echo result_data(true, "이사(전출) 신청이 완료되었습니다.", []);
    //die(result_data(false, $insert_query, []));
}

