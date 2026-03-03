<?php
require_once './_common.php';

if($industry_name_add == "") die(result_data(false, "추가하실 업종명을 입력해주세요.", []));

$industry_name_add = trim($industry_name_add);

$industry_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM a_industry_list WHERE industry_name = '{$industry_name_add}'");

if($industry_confirm['cnt'] > 0) die(result_data(false, "이미 등록된 업종명입니다.", []));

echo result_data(true, "사용할 수 있는 업종입니다.", []);
?>