<?php
include_once('./_common.php');

$g5['title'] = "로그인 검사";

//die(result_data(false, "smsm", []));
$today = date("Y-m-d H:i:s");
$ip_info = $_SERVER['REMOTE_ADDR'];

$mb_id       = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
$mb_password = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';

run_event('member_login_check_before', $mb_id);

if (!$mb_id || run_replace('check_empty_member_login_password', !$mb_password, $mb_id))
    die(result_data(false, '회원아이디나 비밀번호가 공백이면 안됩니다.', []));

$mb = get_member($mb_id);

//소셜 로그인추가 체크

$is_social_login = false;
$is_social_password_check = false;

// 소셜 로그인이 맞는지 체크하고 해당 값이 맞는지 체크합니다.
if(function_exists('social_is_login_check')){
    $is_social_login = social_is_login_check();

    //패스워드를 체크할건지 결정합니다.
    //소셜로그인일때는 체크하지 않고, 계정을 연결할때는 체크합니다.
    $is_social_password_check = social_is_login_password_check($mb_id);
}

$is_need_not_password = run_replace('login_check_need_not_password', $is_social_password_check, $mb_id, $mb_password, $mb, $is_social_login);

// $is_need_not_password 변수가 true 이면 패스워드를 체크하지 않습니다.
// 가입된 회원이 아니다. 비밀번호가 틀리다. 라는 메세지를 따로 보여주지 않는 이유는
// 회원아이디를 입력해 보고 맞으면 또 비밀번호를 입력해보는 경우를 방지하기 위해서입니다.
// 불법사용자의 경우 회원아이디가 틀린지, 비밀번호가 틀린지를 알기까지는 많은 시간이 소요되기 때문입니다.
if (!$is_need_not_password && (! (isset($mb['mb_id']) && $mb['mb_id']) || !login_password_check($mb, $mb_password, $mb['mb_password'])) ) {

    //run_event('password_is_wrong', 'login', $mb);

  
        die(result_data(false, '가입된 회원아이디가 아니거나 비밀번호가 틀립니다. 비밀번호는 대소문자를 구분합니다.', []));
        if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
    }
    
    //alert('가입된 회원아이디가 아니거나 비밀번호가 틀립니다.\\n비밀번호는 대소문자를 구분합니다.');
}

// 차단된 아이디인가?
if ($mb['mb_intercept_date'] && $mb['mb_intercept_date'] <= date("Ymd", G5_SERVER_TIME)) {
    $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mb['mb_intercept_date']);
    die(result_data(false, '회원님의 아이디는 접근이 금지되어 있습니다.\n처리일 : '.$date, []));
}

// 탈퇴한 아이디인가?
if ($mb['mb_leave_date'] && $mb['mb_leave_date'] <= date("Ymd", G5_SERVER_TIME)) {
    $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mb['mb_leave_date']);
    //alert('탈퇴한 아이디이므로 접근하실 수 없습니다.\n탈퇴일 : '.$date);

    die(result_data(false, '탈퇴한 아이디이므로 접근하실 수 없습니다. - 탈퇴일 : '.$date, []));
}

if($mb['mb_level'] > 9) {

 if($_SERVER['REMOTE_ADDR'] != ADMIN_IP) die(result_data(false, '최고관리자 계정은 접근 불가능합니다. 매니저 계정으로 로그인 하세요.', []));
}

$mng_infos = get_manger($mb_id);

if($mng_infos['mng_status'] == 2){
    die(result_data(false, '퇴사처리된 담당자입니다. 확인 후 다시 로그인하세요.', []));
}


if (! (defined('SKIP_SESSION_REGENERATE_ID') && SKIP_SESSION_REGENERATE_ID)) {
    session_regenerate_id(false);
    if (function_exists('session_start_samesite')) {
        session_start_samesite();
    }
}

// 회원아이디 세션 생성
set_session('ss_mb_id', $mb['mb_id']);
// FLASH XSS 공격에 대응하기 위하여 회원의 고유키를 생성해 놓는다. 관리자에서 검사함
generate_mb_key($mb);

// 회원의 토큰키를 세션에 저장한다. /common.php 에서 해당 회원의 토큰값을 검사한다.
if(function_exists('update_auth_session_token')) update_auth_session_token($mb['mb_datetime']);

$sql= " update g5_member set mb_auto = '1', mb_ip = '{$ip_info}' where mb_id = '{$mb['mb_id']}' and mb_leave_date = '' ";
sql_query($sql);

$log_insert = "INSERT INTO a_login_log SET
                mem_type = 'sm',
                mb_id = '{$mb['mb_id']}',
                mb_ip = '{$ip_info}',
                created_at = '{$today}'";
sql_query($log_insert);

if($mb['mb_agree1'] == '0' || $mb['mb_agree2'] == '0'){
    die(result_data(false, "로그인을 위해 약관 동의가 필요합니다.", 'privacy_agree'));
}

echo result_data(true, '로그인 되었습니다.', []);