<?php
require_once './_common.php';

if($cal_idx == "") die(result_data(false, "잘못된 접근입니다.", []));

$today = date("Y-m-d H:i:s");

$del_update = "UPDATE a_calendar SET
                is_del = 1,
                deleted_at = '{$today}'
                WHERE cal_idx = '{$cal_idx}'";
sql_query($del_update);

echo result_data(true, "일정이 삭제되었습니다.", []);