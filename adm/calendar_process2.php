<?php
require_once './_common.php';

if($cal_idx == "") die(result_data(false, "잘못된 접근입니다.", []));
if($cal_date == "") die(result_data(false, "잘못된 접근입니다.", []));
if($mb_id == "") die(result_data(false, "로그인 후 이용해 주세요.", []));

$today = date("Y-m-d H:i:s");

$process_check = sql_fetch("SELECT COUNT(*) as cnt FROM a_calendar_process WHERE cal_idx = '{$cal_idx}' and process_date = '{$cal_date}'");

if($process_check['cnt'] > 0){
    die(result_data(false, "이미 처리된 일정입니다.", []));
}

// 폼에서 변경된 담당자가 있으면 일정에도 반영
if($mng_id != ""){
    $cal_info = sql_fetch("SELECT * FROM a_calendar WHERE cal_idx = '{$cal_idx}'");

    if($cal_info && $mng_id != $cal_info['mng_id']){
        // 반복일정에서 특정 날짜의 담당자 변경
        $cal_check = sql_fetch("SELECT COUNT(*) as cnt FROM a_calendar WHERE cal_idx = '{$cal_idx}' and cal_date = '{$cal_date}'");

        if($cal_check['cnt'] > 0){
            // 원본 날짜와 일치: 직접 UPDATE
            sql_query("UPDATE a_calendar SET mng_id = '{$mng_id}', updated_at = '{$today}' WHERE cal_idx = '{$cal_idx}'");
        } else if($cal_info['noti_repeat'] != 'N'){
            // 반복일정 특정 날짜: 예외 레코드가 있으면 UPDATE, 없으면 INSERT
            $exception_check = sql_fetch("SELECT cal_idx, COUNT(*) as cnt FROM a_calendar WHERE exception_idx = '{$cal_idx}' and cal_date = '{$cal_date}' and is_del = 0");

            if($exception_check['cnt'] > 0){
                sql_query("UPDATE a_calendar SET mng_id = '{$mng_id}', updated_at = '{$today}' WHERE cal_idx = '{$exception_check['cal_idx']}'");
            } else {
                // 예외 레코드 생성 (담당자만 변경, noti_repeat='N'으로 중복 방지)
                sql_query("INSERT INTO a_calendar SET
                    cal_code = '{$cal_info['cal_code']}',
                    post_id = '{$cal_info['post_id']}',
                    building_id = '{$cal_info['building_id']}',
                    mng_department = '{$cal_info['mng_department']}',
                    mng_id = '{$mng_id}',
                    exception_idx = '{$cal_idx}',
                    cal_date = '{$cal_date}',
                    noti_repeat = 'N',
                    cal_title = '{$cal_info['cal_title']}',
                    cal_content = '{$cal_info['cal_content']}',
                    wid = '{$cal_info['wid']}',
                    created_at = '{$today}'");
            }
        }
    }
}

//처리완료 기록
$process_insert = "INSERT INTO a_calendar_process SET
                    cal_idx = '{$cal_idx}',
                    process_date = '{$cal_date}',
                    process_id = '{$mb_id}',
                    created_at = '{$today}'";
sql_query($process_insert);

echo result_data(true, $cal_date." 날짜의 일정이 처리완료 되었습니다.", []);
