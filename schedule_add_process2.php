<?php
require_once "./_common.php";

if($cal_idx == "") die(result_data(false, "잘못된 접근입니다.", []));
if($cal_date == "") die(result_data(false, "잘못된 접근입니다.", []));
if($mb_id == "") die(result_data(false, "로그인 후 이용해 주세요.", []));

$today = date("Y-m-d H:i:s");

$process_check = sql_fetch("SELECT COUNT(*) as cnt FROM a_calendar_process WHERE cal_idx = '{$cal_idx}' and process_date = '{$cal_date}'");

if($process_check['cnt'] > 0){
    die(result_data(false, "이미 처리된 일정입니다.", []));
}

//처리하기
$process_insert = "INSERT INTO a_calendar_process SET
                    cal_idx = '{$cal_idx}',
                    process_date = '{$cal_date}',
                    process_id = '{$mb_id}',
                    created_at = '{$today}'";
                    
if($_SERVER['REMOTE_ADDR'] != ADMIN_IP) sql_query($process_insert);

echo result_data(true, $cal_date." 날짜의 일정이 처리완료 되었습니다.", []);