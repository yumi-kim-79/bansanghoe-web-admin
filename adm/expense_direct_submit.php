<?php
require_once './_common.php';

if($ex_id == "") die(result_data(false, "잘못된 접근입니다.", []));

$today = date("Y-m-d H:i:s");

$update_direct = "UPDATE a_expense_report SET 
                ex_status = 'E',
                ex_status_d = 'Y',
                ex_status_d_at = '{$today}',
                ex_apprval1_chk = 1,
                ex_apprval2_chk = 1,
                ex_apprval3_chk = 1
                WHERE ex_id = '{$ex_id}'";
sql_query($update_direct);

echo result_data(true, '품의서 즉시 승인이 완료되었습니다.', []);