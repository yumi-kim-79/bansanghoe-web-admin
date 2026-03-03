<?php
require_once "./_common.php";

$company_confirm = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_manage_company WHERE company_idx = '{$cidx}'");

if($company_confirm['cnt'] == 0) die(result_data(false, "존재하지 않는 업체입니다. 확인 후 다시 시도하세요", []));
if($company_confirm['transaction_status'] == 'Y') die(result_data(false, "이미 거래 활성화가 되어있는 업체입니다.", []));


$update_transaction = "UPDATE a_manage_company SET
                        transaction_status = 'Y'
                        WHERE company_idx = '{$cidx}'";
sql_query($update_transaction);

echo result_data(true, $cname." 업체를 거래활성화 상태로 변경하였습니다.", []);