<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

$cal_info = sql_fetch("SELECT * FROM a_calendar WHERE cal_idx = '{$cal_idx}'");

if(!$cal_info) die(result_data(false, "일정을 찾을 수 없습니다.", []));

// 처리완료 체크: 관리자(mb_level >= 10)는 삭제 가능
if($cal_info['is_process'] && $member['mb_level'] < 10){
    die(result_data(false, "처리완료가 된 일정은 삭제할 수 없습니다.", []));
}

// del_mode: this_only / after_this / all
if(!$del_mode) $del_mode = 'after_this'; // 기본값 (기존 동작 호환)

switch($del_mode){

    // 이 날짜 일정만 삭제
    case 'this_only':
        // 해당 날짜에 직접 매칭되는 레코드가 있으면 soft delete
        $cal_check = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_calendar WHERE cal_idx = '{$cal_idx}' and cal_date = '{$cal_date}'");

        if($cal_check['cnt'] > 0){
            sql_query("UPDATE a_calendar SET is_del = 1, deleted_at = '{$today}' WHERE cal_idx = '{$cal_idx}' and cal_date = '{$cal_date}'");
        } else {
            // 반복일정에서 특정 날짜만 예외 처리: 삭제된 예외 레코드 삽입
            sql_query("INSERT INTO a_calendar SET
                cal_code = '{$cal_info['cal_code']}',
                post_id = '{$cal_info['post_id']}',
                building_id = '{$cal_info['building_id']}',
                mng_department = '{$cal_info['mng_department']}',
                mng_id = '{$cal_info['mng_id']}',
                exception_idx = '{$cal_idx}',
                cal_date = '{$cal_date}',
                noti_repeat = 'N',
                cal_title = '{$cal_info['cal_title']}',
                cal_content = '{$cal_info['cal_content']}',
                wid = '{$cal_info['wid']}',
                is_del = 1,
                deleted_at = '{$today}',
                created_at = '{$cal_info['created_at']}'");
        }
        echo result_data(true, "해당 날짜 일정이 삭제되었습니다.", []);
        break;

    // 이 날짜 이후 반복 일정 전체 삭제
    case 'after_this':
        $cal_edate = date('Y-m-d', strtotime($cal_date . " -1 day"));

        // 반복일정 마감일 설정 (해당 날짜 전월 말로)
        sql_query("UPDATE a_calendar SET cal_edate = '{$cal_edate}' WHERE cal_idx = '{$cal_idx}'");

        // 해당 날짜 이후의 예외 레코드도 삭제
        sql_query("UPDATE a_calendar SET is_del = 1, deleted_at = '{$today}' WHERE exception_idx = '{$cal_idx}' and cal_date >= '{$cal_date}' and is_del = 0");

        echo result_data(true, "해당 날짜 이후 반복 일정이 삭제되었습니다.", []);
        break;

    // 반복 일정 전체 삭제
    case 'all':
        // 원본 레코드 삭제
        sql_query("UPDATE a_calendar SET is_del = 1, deleted_at = '{$today}' WHERE cal_idx = '{$cal_idx}'");

        // 이 일정의 모든 예외 레코드도 삭제
        sql_query("UPDATE a_calendar SET is_del = 1, deleted_at = '{$today}' WHERE exception_idx = '{$cal_idx}' and is_del = 0");

        echo result_data(true, "반복 일정이 전체 삭제되었습니다.", []);
        break;

    default:
        echo result_data(false, "잘못된 삭제 모드입니다.", []);
        break;
}
