<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if($types == 'sm'){
    if($mb_id == "") die(result_data(false, "아이디를 입력해주세요.", "mb_id"));
}
if($mb_hp == "") die(result_data(false, "휴대폰번호를 입력해주세요.", "mb_hp"));
if($certi_number == "") die(result_data(false, "인증번호를 입력해주세요.", "certi_number"));

if($types == 'sm'){
    $sql_sms = "SELECT * FROM a_member_sms WHERE mb_id = '{$mb_id}' and mb_hp = '{$mb_hp}' ORDER BY ms_idx desc limit 0, 1";
}else{
    $sql_sms = "SELECT * FROM a_member_sms WHERE mb_hp = '{$mb_hp}' ORDER BY ms_idx desc limit 0, 1";
}
$row_sms = sql_fetch($sql_sms);

if($row_sms['ms_number'] != $certi_number) die(result_data(false, "인증번호가 일치하지 않습니다.", []));

if($row_sms['ms_number'] == $certi_number){

    $update_sms = "UPDATE a_member_sms SET
                    ms_status = '1'
                    WHERE ms_idx = '{$row_sms['ms_idx']}'";
    sql_query($update_sms);

    echo result_data(true, "인증이 완료되었습니다.", []);
}