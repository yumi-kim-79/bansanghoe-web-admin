<?php
require_once "./_common.php";

$cal_info = sql_fetch("SELECT * FROM a_calendar WHERE cal_idx = '{$cal_idx}'");

if($cal_info['is_process']) die(result_data(false, "처리완료가 된 일정은 삭제할 수 없습니다.", []));
if($cal_info['wid'] != $mb_id) die(result_data(false, "본인이 등록한 일정만 삭제할 수 있습니다.", []));

$today = date("Y-m-d H:i:s");

$del_query = "UPDATE a_calendar SET
                is_del = 1,
                deleted_at = '{$today}'
                WHERE cal_idx = '{$cal_idx}'";

sql_query($del_query);

echo result_data(true, "일정이 삭제되었습니다.", []);