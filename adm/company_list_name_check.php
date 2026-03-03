<?php
require_once './_common.php';

if($company_name == "") die(result_data(false, "업체명을 입력해주세요.", "company_name"));


$sql_confirm = "SELECT COUNT(*) as cnt FROM a_manage_company WHERE company_name = '{$company_name}'";
$row_confirm = sql_fetch($sql_confirm);

if($row_confirm['cnt'] > 0) die(result_data(false, "이미 등록된 업체명입니다.", []));

echo result_data(true, "등록 가능한 업체명입니다.", []);