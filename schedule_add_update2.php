<?php
require_once "./_common.php";

if($calcode == "") die(result_data(false, "캘린더 종류를 선택해주세요.", "calcode"));
// if($mng_department == "") die(result_data(false, "담당 부서를 선택해주세요.", "mng_department"));
// if($mng_id == "") die(result_data(false, "담당 매니저를 선택해주세요.", "mng_id"));
if($building_id == "") die(result_data(false, "단지를 선택해주세요.", "building_id"));
if($cal_date == "") die(result_data(false, "날짜를 선택해주세요.", "cal_date"));
if($noti_repeat == "") die(result_data(false, "반복설정 여부를 선택해주세요.", "noti_repeat"));
if($cal_title == "") die(result_data(false, "제목을 입력해주세요.", "cal_title"));
if($cal_content == "") die(result_data(false, "내용을 입력해주세요.", "cal_content"));

$today = date("Y-m-d H:i:s");
$ip_info = $_SERVER['REMOTE_ADDR'];



if($w == "u"){

    $cal_info = "SELECT * FROM a_calendar WHERE cal_idx = '{$cal_idx}'";
    $cal_info_row = sql_fetch($cal_info);

     //반복설정이 되어있을 때 수정하면
    if($cal_info_row['noti_repeat'] != 'N'){

        $cal_check = "SELECT COUNT(*) as cnt FROM a_calendar WHERE cal_date = '{$cal_date}' and cal_idx = '{$cal_idx}'";
        $cal_check_row = sql_fetch($cal_check);

       

        if($cal_check_row['cnt'] > 0){ //기존 일정일 경우(반복일정 중 특정일 수정이 아닌 전체수정)

            $post_row = sql_fetch("SELECT * FROM a_building WHERE building_id = '{$building_id}'");

            //업데이트
            $update_query = "UPDATE a_calendar SET
                            mng_department = '{$mng_department}',
                            mng_id = '{$mng_id}',
                            cal_date = '{$cal_date}',
                            noti_repeat = '{$noti_repeat}',
                            cal_title = '{$cal_title}',
                            cal_content = '{$cal_content}',
                            updated_at = '{$today}'
                            WHERE cal_idx = '{$cal_idx}'";
            // echo $update_query.'33<br>';
            if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
            }else{
                sql_query($update_query);
            }
            

        }else{

            $cal_edate = date('Y-m-t',strtotime($cal_date2."-1 month")); // -1달

            //반복일정 마감일 설정
            $update_query_day = "UPDATE a_calendar SET
                                cal_edate = '{$cal_edate}'
                                WHERE cal_idx = '{$cal_idx}'";
            sql_query($update_query_day);

            $post_row = sql_fetch("SELECT * FROM a_building WHERE building_id = '{$building_id}'");


            //일정추가
            $insert_query = "INSERT a_calendar SET
                                cal_code = '{$calcode}',
                                post_id = '{$post_row['post_id']}',
                                building_id = '{$building_id}',
                                mng_department = '{$mng_department}',
                                mng_id = '{$mng_id}',
                                exception_idx = '{$cal_idx}',
                                cal_date = '{$cal_date}',
                                noti_repeat = '{$noti_repeat}',
                                cal_title = '{$cal_title}',
                                cal_content = '{$cal_content}',
                                wid = '{$mb_id}',
                                created_at = '{$today}'";

            if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
              
                die(result_data(false, $update_query_day, $insert_query));
                
            }else{
                sql_query($insert_query);
            }
        }

        

    }else{

        $post_row_nr = sql_fetch("SELECT * FROM a_building WHERE building_id = '{$building_id}'");

        $update_query = "UPDATE a_calendar SET
                            cal_code = '{$calcode}',
                            post_id = '{$post_row_nr['post_id']}',
                            building_id = '{$building_id}',
                            mng_department = '{$mng_department}',
                            mng_id = '{$mng_id}',
                            cal_date = '{$cal_date}',
                            noti_repeat = '{$noti_repeat}',
                            cal_title = '{$cal_title}',
                            cal_content = '{$cal_content}',
                            updated_at = '{$today}'
                            WHERE cal_idx = '{$cal_idx}'";
        if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        }else{
            sql_query($update_query);
        }

    }
   
    // $cal_info = sql_fetch("SELECT * FROM a_calendar WHERE cal_idx = '{$cal_idx}'");

    //담당자 배정되거나 변경되었을 때
    if($mng_id != $cal_info['mng_id']){
        $mng_sql = "SELECT * FROM g5_member WHERE mb_id = '{$mng_id}'";
        $mng_row = sql_fetch($mng_sql);

        $push_title = '[일정] 일정 담당자로 배정되었습니다.';
        $push_content = '일정 담당자로 배정되었습니다. 일정 확인 후 처리 부탁드립니다.';

        if($mng_row['mb_token'] != "" && $mng_row['noti3']){ //토큰이 있는경우 푸시 발송
           
            if($_SERVER['REMOTE_ADDR'] != ADMIN_IP) fcm_send($mng_row['mb_token'], $push_title, $push_content, 'schedule', "{$cal_idx}", "/schedule_add.php?w=i&cal_idx=");
        }
    
        $insert_push = "INSERT INTO a_push SET
                        recv_id_type = 'sm',
                        recv_id = '{$mng_id}',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$member['mb_id']}',
                        push_type = 'schedule',
                        push_idx = '{$cal_idx}',
                        created_at = '{$today}'";
        sql_query($insert_push);
    }

   

}else{

    $buildings = sql_fetch("SELECT * FROM a_building WHERE building_id = '{$building_id}'");

    $insert_query = "INSERT INTO a_calendar SET
                        cal_code = '{$calcode}',
                        post_id = '{$buildings['post_id']}',
                        building_id = '{$building_id}',
                        mng_department = '{$mng_department}',
                        mng_id = '{$mng_id}',
                        cal_date = '{$cal_date}',
                        noti_repeat = '{$noti_repeat}',
                        cal_title = '{$cal_title}',
                        cal_content = '{$cal_content}',
                        wid = '{$member['mb_id']}',
                        created_at = '{$today}'";

    sql_query($insert_query);
    $cal_idx = sql_insert_id(); //팝업 idx


    //등록시 푸시 발송
    $computation_arr = ['computation', 'move_out_settlement']; //전산팀
    $one_site_arr = ['one_site', 'meter_reading']; //현장팀
    $secretary_arr = ['secretary']; //총무팀
    $all_arr = ['etc1', 'etc2', 'etc3'];

    //전산팀
    if(in_array($calcode, $computation_arr)){
        $sql_wh = " and mng.mng_department = 1 ";
    }

     //현장팀
    if(in_array($calcode, $one_site_arr)){
        $sql_wh = " and mng.mng_department = 2 ";
    }

    //총무팀
    if(in_array($calcode, $one_site_arr)){
        $sql_wh = " and mng.mng_department = 3 ";
    }

    //기타
    if(in_array($calcode, $all_arr)){
        $sql_wh = "";
    }

    $calendar_info = get_calendar_category($calcode);
    $calendar_name = $calendar_info['cal_name'];

    //and mng.mng_id != '{$member['mb_id']}'
    $mng_sql = "SELECT mng.*, mb.mb_token, mb.noti3 FROM a_mng as mng
                LEFT JOIN g5_member as mb ON mng.mng_id = mb.mb_id
                WHERE mng.is_del = 0 {$sql_wh} ORDER BY mng.mng_idx desc";
    $mng_res = sql_query($mng_sql);

    while($mng_row = sql_fetch_array($mng_res)){

        $push_title = '['.$calendar_name.' 캘린더] 일정이 등록되었습니다.';
        $push_content = $calendar_name."캘런더에 일정이 등록되었습니다.";

        if($mng_row['mb_token'] != "" && $mng_row['noti3']){ //토큰이 있는경우 푸시 발송
            
            fcm_send($mng_row['mb_token'], $push_title, $push_content, 'schedule', "{$cal_idx}", "/schedule_add.php?w=i&cal_idx=");
        }

        $insert_push = "INSERT INTO a_push SET
                        recv_id_type = 'sm',
                        recv_id = '{$mng_row['mng_id']}',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$member['mb_id']}',
                        push_type = 'schedule',
                        push_idx = '{$cal_idx}',
                        created_at = '{$today}'";
        sql_query($insert_push);
    }
    
    
    //담당자 배정했을 때
    if($mng_id != ""){
        $mng_sql = "SELECT * FROM g5_member WHERE mb_id = '{$mng_id}'";
        $mng_row = sql_fetch($mng_sql);

        $push_title = '[일정] 일정 담당자로 배정되었습니다.';
        $push_content = '일정 담당자로 배정되었습니다. 일정 확인 후 처리 부탁드립니다.';

        if($mng_row['mb_token'] != "" && $mng_row['noti3']){ //토큰이 있는경우 푸시 발송
           
            fcm_send($mng_row['mb_token'], $push_title, $push_content, 'schedule', "{$cal_idx}", "/schedule_add.php?w=i&cal_idx=");
        }
    
        $insert_push = "INSERT INTO a_push SET
                        recv_id_type = 'sm',
                        recv_id = '{$mng_id}',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$member['mb_id']}',
                        push_type = 'schedule',
                        push_idx = '{$cal_idx}',
                        created_at = '{$today}'";
        sql_query($insert_push);
    }
}

if($w == 'u'){
    echo result_data(true, "일정이 수정되었습니다.", []);
}else{
    echo result_data(true, "일정이 추가되었습니다.", []);
}