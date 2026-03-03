<?php
require_once './_common.php';

$today = date('Y-m-d H:i:s');
$ct_history_row = sql_fetch("SELECT * FROM a_contract_history WHERE cth_idx = '{$cth_idx}'");

if($ct_history_row['ct_sdate'] < date('Y-m-d')) die(result_data(false, "계약시작일이 지난 계약건은 삭제할 수 없습니다.", []));

//계약 히스토리 삭제
$del_update = "UPDATE a_contract_history SET
                is_del = 1,
                deleted_at = '{$today}'
                WHERE cth_idx = '{$cth_idx}'";
sql_query($del_update);

//비용 히스토리 추가된 부분 삭제
$del_price_update = "UPDATE a_contract_price_history SET
                        is_del = 1,
                        deleted_at = '{$today}'
                        WHERE cth_idx = '{$cth_idx}'";
sql_query($del_price_update);

//금액과 계약기간 이전걸로 변경
$ct_history_row2 = sql_fetch("SELECT * FROM a_contract_history WHERE cth_idx != '{$cth_idx}' ORDER BY cth_idx desc limit 0, 1");

$update_ct = "UPDATE a_contract SET
                ct_sdate = '{$ct_history_row2['ct_sdate']}',
                ct_edate = '{$ct_history_row2['ct_edate']}',
                ct_price = '{$ct_history_row2['ct_price']}'
                WHERE ct_idx = '{$ct_history_row['ct_idx']}'";
sql_query($update_ct);

echo result_data(true, "연장 계약 내역 취소가 완료되었습니다", []);