<?php
require_once './_common.php';

if($ex_id == "") die(result_data(false, "잘못된 접근입니다.", []));

$today = date("Y-m-d H:i:s");

$del_update = "UPDATE a_expense_report SET
                is_del = 1,
                deleted_at = '{$today}'
                WHERE ex_id = '{$ex_id}'";
sql_query($del_update);

echo result_data(true, "품의서가 삭제되었습니다.", []);