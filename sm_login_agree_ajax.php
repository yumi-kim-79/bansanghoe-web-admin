<?php
require_once "./_common.php";

if($agree1 == "0") die(result_data(false, "서비스 이용약관에 동의해주세요.", []));
if($agree2 == "0") die(result_data(false, "개인정보 수집 및 이용에 동의해주세요.", []));

$update_query = "UPDATE g5_member SET
                    mb_agree1 = '{$agree1}',
                    mb_agree2 = '{$agree2}'
                    WHERE mb_id = '{$mb_id}'";
sql_query($update_query);

echo result_data(true, "약관 동의가 완료되었습니다.", []);