<?php
require_once "./_common.php";

$cal_info = sql_fetch("SELECT * FROM a_calendar WHERE cal_idx = '{$cal_idx}'");

$today = date("Y-m-d H:i:s");

$cal_process = "UPDATE a_calendar SET
                    is_process = 1,
                    process_id = '{$mb_id}',
                    processed_at = '{$today}'
                    WHERE cal_idx = '{$cal_idx}'";
//die(result_data(false, $cal_process, []));
sql_query($cal_process);

echo result_data(true, "일정이 처리완료 되었습니다.", []);