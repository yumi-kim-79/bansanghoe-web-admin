<?php
require_once "./_common.php";

//die(result_data(false, $_POST, []));
$today = date("Y-m-d H:i:s");
$dates = date("Y-m-d");
$ip_info = $_SERVER['REMOTE_ADDR'];

function isValidPhoneNumber($phone) {
    return preg_match('/^010-\d{4}-\d{4}$/', $phone);
}

if($visit_car_name == "") die(result_data(false, "방문 차량 차종을 입력해주세요.", "visit_car_name"));
if($visit_car_number == "") die(result_data(false, "방문 차량 번호를 입력해주세요.", "visit_car_number"));
if($visit_hp == "") die(result_data(false, "방문자 연락처를 입력해주세요.", "visit_hp"));

if (!isValidPhoneNumber($visit_hp)) die(result_data(false, "연락처를 올바른 형식으로 입력해주세요. ex)010-1111-1111", "visit_hp"));

if($w == "u"){

    $update_query = "UPDATE a_building_visit_car SET
                        visit_car_name = '{$visit_car_name}',
                        visit_car_number = '{$visit_car_number}',
                        visit_hp = '{$visit_hp}'
                        WHERE car_id = '{$car_id}'";
    
    sql_query($update_query);

    echo result_data(true, "방문차량 정보가 수정되었습니다.", []);
    //die(result_data(false, $update_query, []));

}else{

    if($visit_date == "") die(result_data(false, "방문 날짜를 입력해주세요.", "visit_date"));
    if($visit_day == "") die(result_data(false, "방문 기간을 선택해주세요.", "visit_day"));
    if($agree1 == "") die(result_data(false, "첫번째 동의 내역에 체크해주세요", "agree1"));
    if($agree2 == "") die(result_data(false, "두번째 동의 내역에 체크해주세요.", "agree2"));
    if($agree3 == "") die(result_data(false, "세번째 동의 내역에 체크해주세요.", "agree3"));

    $visit_car_submit_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_visit_car WHERE mb_id = '{$mb_id}' and visit_date = '{$visit_date}' ");

    if($visit_car_submit_confirm['cnt'] >= 5){
        die(result_data(false, "방문 차량은 날짜당 최대 5대까지 등록 가능합니다.", []));
    }

    $insert_query = "INSERT INTO a_building_visit_car SET
                        building_id = '{$building_id}',
                        dong_id = '{$dong_id}',
                        ho_id = '{$ho_id}',
                        mb_id = '{$mb_id}',
                        visit_car_name = '{$visit_car_name}',
                        visit_car_number = '{$visit_car_number}',
                        visit_hp = '{$visit_hp}',
                        visit_date = '{$visit_date}',
                        visit_day = '{$visit_day}',
                        agree1 = '{$agree1}',
                        agree2 = '{$agree2}',
                        agree3 = '{$agree3}',
                        ip_info = '{$ip_info}',
                        created_at = '{$today}'";
    sql_query($insert_query);

    echo result_data(true, "방문차량이 등록되었습니다.", []);
}