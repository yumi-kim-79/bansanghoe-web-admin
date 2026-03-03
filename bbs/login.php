<?php
include_once('./_common.php');

if( function_exists('social_check_login_before') ){
    $social_login_html = social_check_login_before();
}

$g5['title'] = '로그인';
include_once('./_head.sub.php');


$od_id = isset($_POST['od_id']) ? safe_replace_regex($_POST['od_id'], 'od_id') : '';

//로그인 상태가 아닌데 자동로그인 체크 된 경우
//동일한 토큰 값을 가지고 있다면 로그인 처리
if(!$is_users){
    
    //앱에서 접속했을 때
    if($chk_app == 'Y' && $app_token){

        $sql2 = " select count(*) cnt from a_member where mb_token = '{$app_token}' and is_del = 0 and mb_auto = 1 ";
        $row2 = sql_fetch($sql2);

        if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
            $sql2 = " select count(*) cnt from a_member where mb_token = '{$app_token}' and is_del = 0 and mb_auto = 1 ";
            echo $sql2.'<br>';
            // exit;
        }
        
        //로그인 기록이 있는 경우 다시 로그인
        if($row2['cnt'] > 0){
            $sql = " select * from a_member where mb_token = '{$app_token}' ";
            $user_mb = sql_fetch($sql);

            $sql_ho_hd = "SELECT * FROM a_building_ho WHERE ho_status = 'Y' and ho_tenant_hp = '{$user_mb['mb_hp']}' ORDER BY ho_id asc ";
            $row_ho_hd = sql_fetch($sql_ho_hd);
            
            session_start_samesite();

            $_SESSION['users']['id'] = $user_mb['mb_id'];
            $_SESSION['users']['ho_id'] = $row_ho_hd['ho_id'];

            goto_url('/');
        }
    }
    
}


// url 체크
check_url_host($url);
//echo "!23;";
// 이미 로그인 중이라면
if ($is_users) {
    if ($url)
        goto_url($url);
    else
        goto_url(G5_URL);
}

$login_url        = login_url($url);
$login_action_url = G5_HTTPS_BBS_URL."/login_check.php";

// 로그인 스킨이 없는 경우 관리자 페이지 접속이 안되는 것을 막기 위하여 기본 스킨으로 대체
$login_file = $member_skin_path.'/login.skin.php';
if (!file_exists($login_file))
    $member_skin_path   = G5_SKIN_PATH.'/member/basic';

$login_type = "user";
include_once($member_skin_path.'/login.skin.php');

run_event('member_login_tail', $login_url, $login_action_url, $member_skin_path, $url);

include_once('./_tail.sub.php');