<?php
include_once('./_common.php');

$today = date("Y-m-d H:i:s");
$ip_info = $_SERVER['REMOTE_ADDR'];

$mb_id       = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
$mb_password = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';

if($mb_id == "") die(result_data(false, "아이디를 입력해주세요.", "mb_id"));
if($mb_password == "") die(result_data(false, "비밀번호를 입력해주세요.", "mb_password"));
if($mb_passwords == "") die(result_data(false, "비밀번호를 입력해주세요.", "mb_passwords"));

if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
    if(mb_strlen($mb_passwords) < 6) die(result_data(false, "비밀번호는 영문 + 숫자의 조합으로 6자리 이상, 16자리 미만으로 입력해주세요.", "mb_passwords"));
    if($mb_passwords_re == "") die(result_data(false, "비밀번호를 한번 더 입력해주세요.", "mb_passwords_re"));
    if($mb_passwords != $mb_passwords_re) die(result_data(false, "비밀번호를 동일하게 입력해주세요.", "mb_passwords"));
    if(!validatePassword($mb_passwords)) die(result_data(false, "비밀번호는 영문 + 숫자의 조합으로 6자리 이상, 16자리 미만으로 입력해주세요.", "mb_passwords"));
}

if($car_type != ""){
    if($car_number == "") die(result_data(false, "차량번호를 입력해주세요.", "car_number"));
}

if($car_number != ""){
    if($car_type == "") die(result_data(false, "차종을 입력해주세요.", "car_type"));
}

if(!$chk1) die(result_data(false, "QR체커 서비스 이용약관에 동의해주세요.", "chk1"));
if(!$chk2) die(result_data(false, "개인정보 수집 및 이용에 동의해주세요.", "chk2"));

$mem_info = sql_fetch("SELECT * FROM a_member WHERE mb_hp = '{$mb_id}' and is_del = 0");

//첫번째 호 정보
$sql_ho = "SELECT ho.*, building.building_name, building.is_use, dong.dong_name FROM a_building_ho as ho
LEFT JOIN a_building as building on building.building_id = ho.building_id
LEFT JOIN a_building_dong as dong on dong.dong_id = ho.dong_id
WHERE ho.ho_tenant_hp = '{$mb_id}' and building.is_use = 1 and ho.ho_status = 'Y' ORDER BY ho.ho_name asc limit 0, 1";

$row_ho = sql_fetch($sql_ho);

$building_info = get_builiding_info($row_ho['building_id']); //단지정보

//비밀번호
$pws = get_encrypt_string($mb_passwords);

$update_mem = "UPDATE a_member SET 
                mb_password = '{$pws}',
                mb_agree1 = '{$chk1}',
                mb_agree2 = '{$chk2}'
                WHERE mb_id = '{$mem_info['mb_id']}'";


sql_query($update_mem);



//차량정보 추가
if($car_type != "" && $car_number != ""){

    // $ho_info = sql_fetch("SELECT * FROM a_building_ho WHERE ho_tenant_hp = '{$mb_id}' and is_del = 0 ORDER BY ho_id desc");

    $insert_car = "INSERT INTO a_building_car SET
                    building_id = '{$row_ho['building_id']}',
                    dong_id = '{$row_ho['dong_id']}',
                    ho_id = '{$row_ho['ho_id']}',
                    mb_id = '{$mem_info['mb_id']}',
                    car_type = '{$car_type}',
                    car_name = '{$car_number}',
                    ip_info = '{$ip_info}',
                    created_at = '{$today}'";
    
    // die(result_data(false, $insert_car, $_POST));

    sql_query($insert_car);


    $building_mng = "SELECT mng_building.*, mb.mb_token FROM a_mng_building as mng_building LEFT JOIN g5_member as mb ON mng_building.mb_id = mb.mb_id WHERE mng_building.building_id = '{$row_ho['building_id']}'";
    $buidling_mng_res = sql_query($building_mng);


    $push_title = "[차량등록] 차량이 등록되었습니다.";
    $push_content = $mem_info['mb_name']."님이 ". $building_info['building_name']." 단지에 차량을 등록하였습니다.";

    while($buidling_mng_row = sql_fetch_array($buidling_mng_res)){
        //푸시발송
        $insert_push = "INSERT INTO a_push SET
                recv_id_type = 'sm',
                recv_id = '{$buidling_mng_row['mb_id']}',
                push_title = '{$push_title}',
                push_content = '{$push_content}',
                wid = '{$mem_info['mb_id']}',
                push_type = 'car',
                push_idx = '{$row_ho['ho_id']}',
                created_at = '{$today}'";
        sql_query($insert_push);

        if($buidling_mng_row['mb_token'] != ""){ //토큰이 있는경우 푸시 발송
            fcm_send($buidling_mng_row['mb_token'], $push_title, $push_content, 'car', "{$row_ho['ho_id']}", "/adm/car_form.php?w=u&ho_id=");
        }
    }
}

// die(result_data(false, $mem_info, []));

//내 빌딩 첫번째 가져오기
$sql_ho = "SELECT * FROM a_building_ho WHERE ho_status = 'Y' and ho_tenant_hp = '{$mb_id}' ORDER BY ho_id asc ";
$row_ho = sql_fetch($sql_ho);

//세션 생성 맟 자동로그인 체크 로그 남기기
session_start_samesite();

$_SESSION['users']['id'] = $mem_info['mb_id'];
$_SESSION['users']['ho_id'] = $row_ho['ho_id'];

$sql= " update a_member set mb_auto = '1', mb_ip = '{$ip_info}' where mb_id = '{$mem_info['mb_id']}' and is_del = 0 ";
sql_query($sql);

$log_insert = "INSERT INTO a_login_log SET
                mem_type = 'user',
                mb_id = '{$mem_info['mb_id']}',
                mb_ip = '{$ip_info}',
                created_at = '{$today}'";
sql_query($log_insert);

// if($row_st['st_status'] == '0'){
//     die(result_data(false, "eMAX 학생인지 확인하고 있어요:)", "st_status_rd"));
// }

echo result_data(true, '로그인 되었습니다.', []);
?>