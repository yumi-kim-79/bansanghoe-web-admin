<?php
require_once './_common.php';

$bill_check = "SELECT COUNT(*) as cnt FROM a_bill WHERE building_id = '{$building_id}' and bill_year = '{$year}' and bill_month = '{$month}' and is_del = 0";
$bill_check_row = sql_fetch($bill_check);

if($bill_check_row['cnt'] > 0){
    die(result_data(false, "이미 고지서가 발행되었습니다.", "bill_check"));
}else{
    echo result_data(true, "고지서 발행이 가능합니다.".$bill_check, []);
}