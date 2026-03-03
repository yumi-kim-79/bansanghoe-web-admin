<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if($types == 'sm'){
    if($mb_id == "") die(result_data(false, "아이디를 입력해주세요.", "mb_id"));
}
if($mb_hp == "") die(result_data(false, "휴대폰번호를 입력해주세요.", "mb_hp"));
if($certi_number == "") die(result_data(false, "인증번호를 입력해주세요.", "certi_number"));

if($types == 'sm'){
    $sql_sms = "SELECT COUNT(*) as cnt FROM a_member_sms WHERE mb_id = '{$mb_id}' and mb_hp = '{$mb_hp}' and ms_number = '{$certi_number}' and ms_status = 1";
}else{
    $sql_sms = "SELECT COUNT(*) as cnt FROM a_member_sms WHERE mb_hp = '{$mb_hp}' and ms_number = '{$certi_number}' and ms_status = 1";


    $mb_info = get_user_hp($mb_hp);
    $mb_id = $mb_info['mb_id'];
}
$row_sms = sql_fetch($sql_sms);

if($row_sms['cnt'] > 0){
    echo result_data(true, "인증이 완료되었습니다. 비밀번호를 변경해주세요.", $mb_id);
}else{
    die(result_data(false, "인증을 완료해주세요.".$sql_sms, []));
}