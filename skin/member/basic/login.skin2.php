<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 로그인 시작 { -->

<form name="flogin" id="flogin" method="post" autocomplete="off">
    <input type="hidden" name="url" value="<?php echo $login_url ?>">
    <div class="login_view">
        <div class="login_view_top">
            <p class="login_logo"><img src="/images/sm_logo.svg" alt="로고"></p>
            <ul class="login_list">
                <li>
                    <div class="ipt_box">
                        <input type="text" name="st_id" id="login_id" class="bansang_ipt" placeholder="아이디" oninput="idOnInput(this)">
                    </div>
                </li>
                <li>
                    <div class="ipt_box">
                        <input type="password" name="st_password" id="login_pw" class="bansang_ipt" placeholder="비밀번호">
                    </div>
                </li>
            </ul>
            <ul class="find_list">
                <li><a href="<?php echo G5_URL?>/find_id.php?type=sm">아이디 찾기</a></li>
                <li><a href="<?php echo G5_URL?>/find_pw.php?type=sm">비밀번호 찾기</a></li>
            </ul>
        </div>
        <div class="login_view_bot">
            <div class="fix_btn_box2">
                <button type="button" class="fix_btn on" onClick="checkLogin();">로그인</button>
            </div>
        </div>
    </div>
</form>

<div class="cm_pop" id="member_chk_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2">입주민인지 확인하고 있어요.<br />최대 1~2일 소요됩니다.</p>
		<!-- <p class="cm_pop_desc2">학생이 휴원상태인 경우 관리자에게 문의해주세요:)</p> -->
		<div class="cm_pop_btn_box">
			<button type="button" class="cm_pop_btn ver2" onClick="popClose('member_chk_pop');">확인</button>
		</div>
	</div>
</div>

<div class="cm_pop" id="first_login_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_title ver2">신반상회</div>
		<p class="cm_pop_desc3">최초 로그인 시 약관 동의 절차입니다.</p>
		<p class="cm_pop_desc3">비밀번호 변경 및 차량정보는 마이페이지에서도 수정 가능합니다.</p>
        <ul class="regi_list">
            <li>
                <p class="regi_list_title">비밀번호 <span>*</span></p>
                <div class="ipt_box">
                    <input type="password" name="us_password" id="us_password" class="bansang_ipt ver2" placeholder="영문,숫자 6자리 이상 16자리 미만" maxLength="16">
                </div>
                <div class="ipt_box">
                    <input type="password" name="us_password_re" id="us_password_re" class="bansang_ipt ver2" placeholder="비밀번호 확인" maxLength="16">
                </div>
            </li>
            <li>
                <p class="regi_list_title">차종 <span>*</span></p>
                <div class="ipt_box">
                    <input type="text" name="car_type" id="car_type" class="bansang_ipt ver2" placeholder="차종을 입력해 주세요." >
                </div>
            </li>
            <li>
                <p class="regi_list_title">차량번호 <span>*</span></p>
                <div class="ipt_box">
                    <input type="text" name="car_number" id="car_number" class="bansang_ipt ver2" placeholder="차량번호를 입력해 주세요." >
                </div>
            </li>
        </ul>
        <div class="agree_wrap">
            <p class="prv_all">
                <input type="checkbox" id="chk_all" class="ver2">
                <label for="chk_all">약관 전체 동의</label>
            </p>
            <ul class="prv_list">
                <li>
                    <p class="regi_prv">
                        <input type="checkbox" name="chk1" id="chk1" class="chk_box" value="1">
                        <label for="chk1">[필수] QR체커 서비스 이용약관</label>
                    </p>
                    <button type="button" onClick="prvPopOn('cctvfilming');"></button>
                </li>
                <li>
                    <p class="regi_prv">
                        <input type="checkbox" name="chk2" id="chk2" class="chk_box" value="1">
                        <label for="chk2">[필수] 개인정보 수집 및 이용동의</label>
                    </p>
                    <button type="button" onClick="prvPopOn('provision');"></button>
                </li>
            </ul>
        </div>
		<div class="cm_pop_btn_box flex_ver">
            <button type="button" class="cm_pop_btn" onClick="popClose('first_login_pop');">취소</button>
			<button type="button" class="cm_pop_btn ver2" onClick="popClose('first_login_pop');">확인</button>
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

function idOnInput(e){
    //e.value = e.value.replace(/[^a-z0-9]/gi, '')
    e.value = e.value.replace(/[^a-z0-9_]/g, '');

}

//전체 선택
$("#chk_all").click(function () {
  console.log($("#chk_all").is(":checked"));
  if ($("#chk_all").is(":checked")) {
    $(".chk_box").prop("checked", true);
  } else {
    $(".chk_box").prop("checked", false);
  }
  $(".chk_box").change();
});
$(".chk_box").click(function () {
  var total = $(".chk_box").length;
  var checked = $(".chk_box:checked").length;

  if (total != checked) $("#chk_all").prop("checked", false);
  else $("#chk_all").prop("checked", true);
});

function checkLogin(){
    //popOpen("member_chk_pop");
    popOpen("first_login_pop");
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

<?php
	include_once(G5_PATH.'/tail.php');
?>