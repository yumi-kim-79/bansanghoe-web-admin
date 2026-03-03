<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if($mb_id == "") die(result_data(false, "잘못된 접근입니다.", []));

//유효성 검증
if($mb_password == "") die(result_data(false, "비밀번호를 입력해주세요.", "mb_password"));
if(mb_strlen($mb_password) < 6) die(result_data(false, "비밀번호는 6자 이상 입력하세요.", "mb_password"));
if($mb_password_re == "") die(result_data(false, "비밀번호를 한번 더 입력해주세요.", "mb_password_re"));
if(mb_strlen($mb_password_re) < 6) die(result_data(false, "비밀번호는 6자 이상 입력하세요.", "mb_password_re"));
if($mb_password_re != $mb_password) die(result_data(false, "비밀번호를 동일하게 입력해주세요.", "mb_password"));
if(!validatePassword($mb_password_re)) die(result_data(false, "비밀번호는 양식에 맞게 입력해 주세요.", "mb_password"));

$pws = get_encrypt_string($mb_password);

if($types == 'sm'){
    $update_member = "UPDATE g5_member SET
                    mb_password = '{$pws}'
                    WHERE mb_id = '{$mb_id}'";
    sql_query($update_member);
}else{
    $update_member = "UPDATE a_member SET
                    mb_password = '{$pws}'
                    WHERE mb_id = '{$mb_id}'";
    sql_query($update_member);
}

echo result_data(true, "비밀번호가 변경되었습니다.", []);