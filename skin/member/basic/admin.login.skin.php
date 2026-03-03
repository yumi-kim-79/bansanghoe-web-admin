<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>
<style>
    body {max-width: inherit;}
</style>
<!-- 로그인 시작 { -->
<div id="admin_login_wrap">
    <div class="admin_login_left">
        <img src="/images/bansang_logos.svg" alt="">
    </div>
    <div class="admin_login_right">
        <div id="mb_login" class="mbskin">
            <div class="mbskin_box">
                <h1><?php echo $g5['title'] ?></h1>
                <form name="flogin" action="<?php echo $login_action_url ?>" onsubmit="return flogin_submit(this);" method="post">
                <input type="hidden" name="url" value="<?php echo $login_url ?>" autocomplete="off">
                <div class="admin_login_label">
                    ADMIN LOGIN
                </div>
                <fieldset id="login_fs" class="login_fs2">
                    <legend>회원로그인</legend>
                    <label for="login_id" class="sound_only">회원아이디<strong class="sound_only"> 필수</strong></label>
                    <input type="text" name="mb_id" id="mb_id" required class="bansang_ipt ver2"  placeholder="관리자 아이디" >
                    <label for="login_pw" class="sound_only">비밀번호<strong class="sound_only"> 필수</strong></label>
                    <input type="password" name="mb_password" id="login_pw" required class="bansang_ipt mgt10 ver2" size="20" maxLength="20" placeholder="비밀번호">
                    <button type="submit" class="btn_submit mgt10">로그인</button>
                    
                    <!-- <div id="login_info" style="margin-top:10px;">
                        <div class="login_if_auto chk_box" style="align-items:center;">
                            <input type="checkbox" name="auto_login" id="login_auto_login" class="selec_chk">
                            <label for="login_auto_login"><span></span> 자동로그인</label>  
                        </div>
                    </div> -->
                </fieldset> 
                </form>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(function($){
    $("#login_auto_login").click(function(){
        if (this.checked) {
            this.checked = confirm("자동로그인을 사용하시면 다음부터 회원아이디와 비밀번호를 입력하실 필요가 없습니다.\n\n공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?");
        }
    });
});

function oninputPhone(target) {
    target.value = target.value
    .replace(/[^0-9]/g, '')
    .replace(/(^02.{0}|^01.{1}|[0-9]{3,4})([0-9]{3,4})([0-9]{4})/g, "$1-$2-$3");
}

function flogin_submit(f)
{
    if( $( document.body ).triggerHandler( 'login_sumit', [f, 'flogin'] ) !== false ){
        return true;
    }
    return false;
}
</script>
<!-- } 로그인 끝 -->

