<?php
require_once './_common.php';

if($bill_id == '') die(result_data(false, '잘못된 접근입니다.', []));


$update = "UPDATE a_bill SET 
            is_submit = 'R',
            submited_at = NULL,
            r_submited = 'Y',
            r_submited_at = '{$rv_time}'
            WHERE bill_id = '{$bill_id}'";
sql_query($update);

echo result_data(true, '고지서가 발행이 예약되었습니다.', []);