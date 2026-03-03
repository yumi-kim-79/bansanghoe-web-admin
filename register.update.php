<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");
$tenant_at = date("Y-m-d");
$ip_info = $_SERVER['REMOTE_ADDR'];

//필수 값 검증
if($mb_id == "") die(result_data(false, "아이디를 입력해주세요.", "mb_id"));
if(!$id_chk) die(result_data(false, "아이디 중복확인을 진행해주세요.", []));
if($mb_password == "") die(result_data(false, "비밀번호를 입력해주세요.", "mb_password"));
if(mb_strlen($mb_password) < 6) die(result_data(false, "비밀번호는 6자 이상 입력하세요.", "mb_password"));
if($mb_password_re == "") die(result_data(false, "비밀번호를 한번 더 입력해주세요.", "mb_password_re"));
if(mb_strlen($mb_password_re) < 6) die(result_data(false, "비밀번호는 6자 이상 입력하세요.", "mb_password_re"));
if($mb_password_re != $mb_password) die(result_data(false, "비밀번호를 동일하게 입력해주세요.", "mb_password"));
if(!validatePassword($mb_password_re)) die(result_data(false, "비밀번호는 양식에 맞게 입력해 주세요.", "mb_password_re"));

if($mb_name == "") die(result_data(false, "이름을 입력해주세요.", "mb_name"));
if($mb_hp == "") die(result_data(false, "휴대폰번호를 입력해주세요.", "mb_hp"));

//휴대폰번호 체크
$confirm_mb = sql_fetch("SELECT COUNT(*) as cnt FROM a_member WHERE mb_hp = '{$mb_hp}' and is_del = 0");
if($confirm_mb['cnt'] > 0) die(result_data(false, "이미 가입된 휴대폰 번호입니다.", "mb_hp"));

//필수값 검증
if($building_id == "") die(result_data(false, "아파트를 선택해주세요.", "building_id"));
if($dong_id == "") die(result_data(false, "동을 선택해주세요.", "dong_id"));
if($ho_name == "") die(result_data(false, "호수를 입력해주세요.", "ho_id"));

//이미 등록된 호수인지
// $ho_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_ho WHERE ho_name = '{$ho_name}' and building_id = '{$building_id}' and dong_id = '{$dong_id}'");
// if($ho_confirm['cnt'] > 0) die(result_data(false, "이미 등록된 호수입니다.", "ho_name"));

if($car_type != ""){
    if($car_name == "") die(result_data(false, "차량번호를 입력해주세요.", "car_name"));
}

if($car_name != ""){
    if($car_type == "") die(result_data(false, "차종을 입력해주세요.", "car_type"));
}

//아파트의 지역코드 가져오기
$post_row = sql_fetch("SELECT post_id FROM a_building WHERE building_id = '{$building_id}'");

if($w == "u"){

}else{
    
    //약관 동의
    if(!$chk1) die(result_data(false, "QR체커 서비스 이용약관에 동의해주세요.", "chk1"));
    if(!$chk2) die(result_data(false, "개인정보 수집 및 이용에 동의해주세요.", "chk2"));


    $ho_name1 = preg_match('/-?\d+/', $ho_name, $matches);
    $ho_name2 = $matches[0];

    $ho_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_ho WHERE ho_name = '{$ho_name2}' and building_id = '{$building_id}' and dong_id = '{$dong_id}' and is_del = 0");

    if($ho_confirm['cnt'] > 0) die(result_data(false, "이미 등록된 호수입니다.", "ho_name"));

    
    //호수에 입주자 추가
    $insert_query = "INSERT INTO a_building_ho SET
                        post_id = '{$post_row['post_id']}',
                        building_id = '{$building_id}',
                        dong_id = '{$dong_id}',
                        ho_name = '{$ho_name}',
                        ho_tenant = '{$mb_name}',
                        ho_tenant_hp = '{$mb_hp}',
                        ho_status = 'Y',
                        ip_info = '{$ip_info}',
                        created_at = '{$today}'";
    sql_query($insert_query);
    $ho_id = sql_insert_id(); //호수 idx


    //비밀번호
    $pws = get_encrypt_string($mb_password);
    
    //일반 유저 추가
    $insert_member = "INSERT INTO a_member SET
                        mb_type = 'IN',
                        mb_id = '{$mb_id}',
                        mb_password = '{$pws}',
                        mb_name = '{$mb_name}',
                        mb_hp = '{$mb_hp}',
                        mb_agree1 = '{$chk1}',
                        mb_agree2 = '{$chk2}',
                        mb_ip = '{$ip_info}',
                        created_at = '{$today}'";
    sql_query($insert_member);

    //차량정보 추가
    if($car_type != "" && $car_name != ""){
        $insert_car = "INSERT INTO a_building_car SET
                        building_id = '{$building_id}',
                        dong_id = '{$dong_id}',
                        ho_id = '{$ho_id}',
                        car_type = '{$car_type}',
                        car_name = '{$car_name}',
                        ip_info = '{$ip_info}',
                        created_at = '{$today}'";
        sql_query($insert_car);
    }
   
  
}

echo result_data(true, "회원가입이 완료되었습니다.", []);