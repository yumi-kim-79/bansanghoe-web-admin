<?php
require_once './_common.php';

$today = date('Y-m-d H:i:s');
$ct_history_row = sql_fetch("SELECT * FROM a_contract_list_history WHERE ct_hidx = '{$ct_hidx}'");

if($ct_history_row['ct_sdate'] < date('Y-m-d')) die(result_data(false, "계약시작일이 지난 계약건은 삭제할 수 없습니다.", []));

//계약 히스토리 삭제
$del_ct_history = "UPDATE a_contract_list_history SET
                    is_del = 1,
                    deleted_at = '{$today}'
                    WHERE ct_hidx = '{$ct_hidx}'";
sql_query($del_ct_history);

//비용 내역에서도 삭제
$del_ct_price = "UPDATE a_contract_list_price_history SET
                 is_del = 1,
                 deleted_at = '{$today}'
                 WHERE ct_hidx = '{$ct_hidx}'";
sql_query($del_ct_price);


echo result_data(true, "계약내역 삭제가 완료되었습니다.", []);