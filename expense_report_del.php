<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

$del_query = "UPDATE a_expense_report SET 
                is_del = 1,
                deleted_at = '{$today}'
                WHERE ex_id = '{$ex_id}'";

sql_query($del_query);

echo result_data(true, "품의서가 삭제되었습니다.", []);
//die(result_data(false, $del_query, []));