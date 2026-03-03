<?php
include_once('./_common.php');

$today = date("Y-m-d H:i:s");
$ip_info = $_SERVER['REMOTE_ADDR'];

$mb_id       = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
$mb_password = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';

if($mb_id == "") die(result_data(false, "아이디를 입력해주세요.", "mb_id"));
if($mb_password == "") die(result_data(false, "비밀번호를 입력해주세요.", "mb_password"));

$mem_info = sql_fetch("SELECT * FROM a_member WHERE mb_hp = '{$mb_id}' and is_del = 0");

//die(result_data(false, $mem_info, []));

if(!$mem_info['mb_id']) die(result_data(false, "가입된 회원이 아닙니다.", []));

if (!$is_need_not_password && (! (isset($mem_info['mb_id']) && $mem_info['mb_id']) || !login_password_check2($mem_info, $mb_password, $mem_info['mb_password'])) ) {
    
    
    if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
        die(result_data(false, "비밀번호가 틀립니다. 비밀번호는 대소문자를 구분합니다.", []));
    }

    // die(result_data(false, "비밀번호가 틀립니다. 비밀번호는 대소문자를 구분합니다.", []));
}

if($mem_info['deleted_at'] && $mem_info['deleted_at'] <= date("Ymd", G5_SERVER_TIME)){
    $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mem_info['deleted_at']);
    //alert('탈퇴한 아이디이므로 접근하실 수 없습니다.\n탈퇴일 : '.$date);

    die(result_data(false, "탈퇴한 회원이므로 접근하실 수 없습니다.\n탈퇴일 : ".$date, []));
}

if($mem_info['mb_type'] == 'IN' && ($mem_info['mb_agree1'] == '0' || $mem_info['mb_agree2'] == '0')){
    if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
        die(result_data(false, "로그인을 위해 약관 동의가 필요합니다.", 'privacy_agree'));
       
    }
}

if($mem_info['mb_type'] == 'IN'){
    $ho_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_ho as ho
                         LEFT JOIN a_building as building on ho.building_id = building.building_id
                         WHERE ho_status = 'Y' and ho_tenant_id = '{$mem_info['mb_id']}' and building.is_use = 1 ORDER BY ho_id asc");

    if($ho_cnt['cnt'] == 0){
        die(result_data(false, "등록된 단지가 없습니다.", 'my_building'));
    }
    

    //내 빌딩 첫번째 가져오기
    $sql_ho = "SELECT ho.*, building.building_name, building.is_use, dong.dong_name FROM a_building_ho as ho
    LEFT JOIN a_building as building on building.building_id = ho.building_id
    LEFT JOIN a_building_dong as dong on dong.dong_id = ho.dong_id
    WHERE ho.ho_tenant_id = '{$mem_info['mb_id']}' and building.is_use = 1 and ho.ho_status = 'Y' ORDER BY ho.ho_name asc limit 0, 1";

    $row_ho = sql_fetch($sql_ho);

    //die(result_data(false, $sql_ho, []));

    //세션 생성 맟 자동로그인 체크 로그 남기기
    session_start_samesite();

    $_SESSION['users']['id'] = $mem_info['mb_id'];
    $_SESSION['users']['ho_id'] = $row_ho['ho_id'];

    $sql= " update a_member set mb_auto = '1', mb_ip = '{$ip_info}' where mb_id = '{$mem_info['mb_id']}' and is_del = 0 ";
    sql_query($sql);
}else{

    $mng_team_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM a_mng_team WHERE mb_id = '{$mem_info['mb_id']}' and is_del = 0 ORDER BY mt_id desc");

    if($mng_team_cnt['cnt'] == 0){
        die(result_data(false, "관리단으로 등록된 단지가 없습니다.", 'my_building'));
    }

    //외부인의 경우 관리단으로 등록된 첫번째 단지정보를 가져옵니다.
    $mng_team_sql = "SELECT * FROM a_mng_team WHERE mb_id = '{$mem_info['mb_id']}' and is_del = 0 ORDER BY mt_id desc limit 0, 1";
    $mng_team_row = sql_fetch($mng_team_sql);

    session_start_samesite();

    $_SESSION['users']['id'] = $mem_info['mb_id'];
    $_SESSION['users']['mng_building'] = $mng_team_row['mt_id'];

    $sql= " update a_member set mb_auto = '1', mb_ip = '{$ip_info}' where mb_id = '{$mem_info['mb_id']}' and is_del = 0 ";
    sql_query($sql);
}

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