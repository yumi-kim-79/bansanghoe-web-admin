<?php
include_once('./_common.php');

if( function_exists('social_check_login_before') ){
    $social_login_html = social_check_login_before();
}

$g5['title'] = '로그인';
include_once('./_head.sub2.php');

   
//로그인 상태가 아닌데 자동로그인 체크 된 경우
//동일한 토큰 값을 가지고 있다면 로그인 처리
if (!$is_member) {
    if($chk_app == 'Y' && $app_token){

        $sql2 = " select count(*) cnt from g5_member as mem 
                    left join a_mng as mng on mem.mb_id = mng.mng_id
                    where mem.mb_token = '{$app_token}' and mem.mb_auto = 1 and mng.mng_status = 1 ";
        $row2 = sql_fetch($sql2);
        
        if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
            // echo $sql2.'<br>';
            // print_r2($row2);
        }
    
        //로그인 기록이 있는 경우 다시 로그인
        if($row2['cnt'] > 0){
    
            $sql = " select * from g5_member where mb_token = '{$app_token}' "; 
            $sm_mb = sql_fetch($sql);
    
            if (! (defined('SKIP_SESSION_REGENERATE_ID') && SKIP_SESSION_REGENERATE_ID)) {
                session_regenerate_id(false);
                if (function_exists('session_start_samesite')) {
                    session_start_samesite();
                }
            }
            
            // 회원아이디 세션 생성
            set_session('ss_mb_id', $sm_mb['mb_id']);
            // FLASH XSS 공격에 대응하기 위하여 회원의 고유키를 생성해 놓는다. 관리자에서 검사함
            generate_mb_key($sm_mb);
            
            // 회원의 토큰키를 세션에 저장한다. /common.php 에서 해당 회원의 토큰값을 검사한다.
            if(function_exists('update_auth_session_token')) update_auth_session_token($sm_mb['mb_datetime']);
         
            goto_url('/sm_index.php');
        }
    }
}

$od_id = isset($_POST['od_id']) ? safe_replace_regex($_POST['od_id'], 'od_id') : '';

// url 체크
check_url_host($url);
//echo "!23;";
// 이미 로그인 중이라면
if ($is_member) {
    if ($url)
        goto_url($url);
    else
        goto_url(G5_URL."/sm_index.php");
}

$login_url        = login_url($url);
$login_action_url = G5_HTTPS_BBS_URL."/login_check.php";

// 로그인 스킨이 없는 경우 관리자 페이지 접속이 안되는 것을 막기 위하여 기본 스킨으로 대체
$login_file = $member_skin_path.'/login.skin.php';
if (!file_exists($login_file))
    $member_skin_path   = G5_SKIN_PATH.'/member/basic';

$login_type = "sm";
include_once($member_skin_path.'/login.skin.php');

run_event('member_login_tail', $login_url, $login_action_url, $member_skin_path, $url);

include_once('./_tail.sub.php');