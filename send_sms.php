<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if($mb_id == "") die(result_data(false, "아이디를 입력해주세요.", "mb_id"));
if($mb_hp == "") die(result_data(false, "휴대폰번호를 입력해주세요.", "mb_hp"));

if($type == "u"){
    if($types == "sm"){
        //sm 매니저
        $confirm_hp = sql_fetch("SELECT COUNT(*) as cnt FROM g5_member WHERE mb_hp = '{$mb_hp}' and mb_leave_date = ''");
    }else{
        //일반
        $confirm_hp = sql_fetch("SELECT COUNT(*) as cnt FROM a_member WHERE mb_hp = '{$mb_hp}' and is_del = 0");
    }
    

    if($confirm_hp['cnt'] > 0) 
        die(result_data(false, "사용 중인 휴대폰번호입니다", []));
}else{
    $confirm_hp = sql_fetch("SELECT COUNT(*) as cnt FROM a_member WHERE mb_id = '{$mb_id}' and mb_hp = '{$mb_hp}' and is_del = 0");

    if($confirm_hp['cnt'] == 0) 
        die(result_data(false, "등록된 휴대폰번호가 아닙니다.", ["SELECT COUNT(*) as cnt FROM a_member WHERE mb_id = '{$mb_id}' and mb_hp = '{$mb_hp}' and is_del = 0"]));
}


$hp_arr = [
    "010-1111-1111",
    "010-2222-2222",
    "010-3333-3333",
    "010-4444-4444",
    "010-5555-5555",
    "010-6666-6666",
    "010-7777-7777",
    "010-8888-8888",
    "010-9999-9999",
    "010-9131-4910",
];

if(in_array($mb_hp, $hp_arr)){
    $rand_number = '111111';
}else{
    $rand_number = rand(111111,999999);
    
    $messageText = $config['cf_title']." 인증번호 [".$rand_number."] 입니다.";
    aligo_sms($mb_hp, $messageText);
}
// $rand_number = '111111';

//인증내역 업데이트
$insert_sms = "INSERT INTO a_member_sms SET
                mb_id = '{$mb_id}',
                mb_hp = '{$mb_hp}',
                ms_number = '{$rand_number}',
                created_at = '{$today}'";
sql_query($insert_sms);

// 111111로 인증하세요.
echo result_data(true, '인증번호가 발송되었습니다.', []);