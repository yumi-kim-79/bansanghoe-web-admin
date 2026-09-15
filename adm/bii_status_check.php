<?php
require_once './_common.php';

// [고지서 2026-09] 수정화면에서 단지를 바꿀 때도 이 검사를 탄다.
//  현재 편집 중인 고지서 자신은 중복에서 빼야 한다. (bill_id 를 넘겨받는다)
$sql_self = "";
if (isset($bill_id) && (int)$bill_id > 0) {
    $sql_self = " and bill_id != '".(int)$bill_id."' ";
}

$bill_check = "SELECT COUNT(*) as cnt FROM a_bill
               WHERE building_id = '{$building_id}' and bill_year = '{$year}' and bill_month = '{$month}'
                 and is_del = 0 {$sql_self}";
$bill_check_row = sql_fetch($bill_check);

if($bill_check_row['cnt'] > 0){
    die(result_data(false, "이미 고지서가 발행되었습니다.", "bill_check"));
}else{
    echo result_data(true, "고지서 발행이 가능합니다.", []);
}
