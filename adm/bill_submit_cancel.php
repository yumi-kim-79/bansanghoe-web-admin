<?php
require_once './_common.php';

if($bill_id == '') die(result_data(false, '잘못된 접근입니다.', []));

$today = date("Y-m-d H:i:s");

$update = "UPDATE a_bill SET 
            is_submit = 'N',
            submited_at = NULL,
            r_submited = '',
            r_submited_at = NULL
            WHERE bill_id = '{$bill_id}'";
sql_query($update);

echo result_data(true, '고지서가 발행이 취소되었습니다.', []);

?>