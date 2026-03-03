<?php
include_once(__DIR__.'/common.php');

// echo $_SERVER['DOCUMENT_ROOT'].'<br>';
// echo __DIR__.'<br>';
// exit;

$today = date("Y-m-d H:i:s");

$push_send_list = "SELECT * FROM a_push WHERE is_send = 0 ORDER BY created_at desc";
$push_send_res = sql_query($push_send_list);

while($push_send_row = sql_fetch_array($push_send_res)){

    if($push_send_row['recv_id_type'] == 'user'){ //일반 사용자
        $user_infos = get_user($push_send_row['recv_id']);
        $push_token = $user_infos['mb_token'];
    }else{ // 매니저
        $user_infos = get_member($push_send_row['recv_id']);
        $push_token = $user_infos['mb_token'];
    }


    // echo $push_send_row['recv_id'].'<br>';
    // echo $push_token.'<br>';

    if($push_token != ''){

        $app_move_link = "";

        switch($push_send_row['push_type']){
            case "bbs":
                $app_move_link = "/board_info.php?tabCode=all&tabIdx=0&bbs_idx=";
                break;
            case "car":
                $app_move_link = "/sm_car_manage.php?building_id=";
                break;
        }

        fcm_send($push_token, $push_send_row['push_title'], $push_send_row['push_content'], $push_send_row['push_type'], "{$push_send_row['push_idx']}", $app_move_link);
    }


    $push_update = "UPDATE a_push SET
                    is_send = 1
                    WHERE push_id = '{$push_send_row['push_id']}'";
    sql_query($push_update);


    // 크론 처리 로그 남기기
    $insert_log = "INSERT INTO a_cron_log SET
                    cr_type = '{$push_send_row['push_type']}',
                    cr_status = 1,
                    cr_chidx = '{$push_send_row['push_idx']}',
                    created_at = '{$today}'";
    sql_query($insert_log);

}


// echo "푸시발송 완료";