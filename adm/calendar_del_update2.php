<?php
require_once './_common.php';

if($cal_idx == "") die(result_data(false, "잘못된 접근입니다.", []));

$today = date("Y-m-d H:i:s");

$cal_check = "SELECT *, COUNT(*) as cnt FROM a_calendar WHERE cal_idx = '{$cal_idx}' and cal_date = '{$cal_date}'";
$cal_check_row = sql_fetch($cal_check);

// die(result_data(false, $cal_check, $cal_check_row));

if($cal_check_row['cnt'] > 0){

    //삭제처리
    $del_update = "UPDATE a_calendar SET
                    is_del = 1,
                    deleted_at = '{$today}'
                    WHERE cal_idx = '{$cal_idx}'";
    sql_query($del_update);

}else{
  
    $cal_edate = date('Y-m-t',strtotime($cal_date."-1 month")); // -1달

    //반복일정 마감일 설정
    $del_update = "UPDATE a_calendar SET
                        cal_edate = '{$cal_edate}'
                        WHERE cal_idx = '{$cal_idx}'";
    sql_query($del_update);

    //새로운 반복일정 설정을 위해 기존 일정 정보 조회
    $cal_check2 = "SELECT * FROM a_calendar WHERE cal_idx = '{$cal_idx}'";
    $cal_check_row2 = sql_fetch($cal_check2);

    //새로운 반복일정 설정
    $insert_query = "INSERT a_calendar SET
                        cal_code = '{$cal_check_row2['cal_code']}',
                        post_id = '{$cal_check_row2['post_id']}',
                        building_id = '{$cal_check_row2['building_id']}',
                        mng_department = '{$cal_check_row2['mng_department']}',
                        mng_id = '{$cal_check_row2['mng_id']}',
                        exception_idx = '{$cal_idx}',
                        cal_date = '{$cal_date}',
                        noti_repeat = '{$cal_check_row2['noti_repeat']}',
                        cal_title = '{$cal_check_row2['cal_title']}',
                        cal_content = '{$cal_check_row2['cal_content']}',
                        wid = '{$cal_check_row2['wid']}',
                        created_at = '{$cal_check_row2['created_at']}'";
    sql_query($insert_query);

}

// die(result_data(false, $insert_query, []));

echo result_data(true, "일정이 삭제되었습니다.", []);