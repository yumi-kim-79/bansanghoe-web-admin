<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);

//print_r2($_SESSION);
//echo $login_type;
?>

<!-- 로그인 시작 { -->
<form name="flogin" id="flogin" method="post" autocomplete="off">
    <!-- <input type="hidden" name="url" value="<?php echo $login_url ?>"> -->
    <div class="login_view">
        <div class="login_view_top">
            <p class="login_logo"><img src="/images/bansang_logos.svg" alt="이맥스 로고"></p>
            <ul class="login_list">
                <li>
                    <div class="ipt_box">
                        <?php if($login_type == "sm"){?>
                        <input type="text" name="mb_id" id="mb_id" class="bansang_ipt" placeholder="아이디" oninput="idOnInput(this)">
                        <?php }else{ ?>
                        <input type="tel" name="mb_id" id="mb_id" class="bansang_ipt phone" placeholder="아이디(휴대폰번호)">
                        <?php }?>
                    </div>
                </li>
                <li>
                    <div class="ipt_box">
                        <input type="password" name="mb_password" id="mb_password" class="bansang_ipt" placeholder="비밀번호">
                    </div>
                </li>
            </ul>
            <ul class="find_list">
                <li><a href="<?php echo G5_URL?>/find_id.php?types=<?php echo $login_type; ?>">아이디 찾기</a></li>
                <li><a href="<?php echo G5_URL?>/find_pw.php?types=<?php echo $login_type; ?>">비밀번호 찾기</a></li>
            </ul>
        </div>
        <div class="login_view_bot">
            <!-- 회원가입 노출 상태 cf_4 Y -->
            <?php if($config['cf_4'] == "Y" && $login_type == "user"){?>
            <div class="fix_btn_box2">
                <a href="<?php echo G5_URL?>/register.php" class="fix_btn ver2">회원가입</a>
            </div>
            <?php }?>
            <div class="fix_btn_box2">
                <button type="button" class="fix_btn on" onClick="login_submit();">로그인</button>
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
<!-- style="<?=$_SERVER['REMOTE_ADDR'] == ADMIN_IP ? 'display:block' : '';?>" -->
<div class="cm_pop" id="first_login_pop" > 
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
       
        <form action="">
            <div class="cm_pop_title ver2">신반상회</div>
            <p class="cm_pop_desc3">최초 로그인 시 약관 동의 절차입니다.</p>
            <p class="cm_pop_desc3">비밀번호 변경 및 차량정보는 마이페이지에서도 수정 가능합니다.</p>
            <ul class="regi_list">
                <li>
                    <p class="regi_list_title">비밀번호 <span>*</span></p>
                    <div class="ipt_box">
                        <input type="password" name="mb_passwords" id="mb_passwords" class="bansang_ipt ver2" placeholder="영문,숫자 6자리 이상 16자리 미만" maxLength="16">
                    </div>
                    <div class="ipt_box">
                        <input type="password" name="mb_passwords_re" id="mb_passwords_re" class="bansang_ipt ver2" placeholder="비밀번호 확인" maxLength="16">
                    </div>
                </li>
                <?php
                
                ?>
                <li class="car_li">
                    <p class="regi_list_title">차종</p>
                    <div class="ipt_box">
                        <input type="text" name="car_type" id="car_type" class="bansang_ipt ver2" placeholder="차종을 입력해 주세요." >
                    </div>
                </li>
                <li class="car_li">
                    <p class="regi_list_title">차량번호</p>
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
                <button type="button" class="cm_pop_btn ver2" onClick="first_login_submit()">확인</button>
            </div>
        </form>
	</div>
</div>

<!-- 로딩 중 -->
<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>내 정보를 저장 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
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

//연락처 하이픈
$(".phone").keyup(function () {
  // 숫자 이외의 모든 문자 제거
  var value = this.value.replace(/[^0-9]/g, "");

  // 길이에 따라 하이픈 삽입
  if (value.length <= 3) {
    // 3자리까지는 아무것도 하지 않음
    this.value = value;
  } else if (value.length <= 7) {
    // 4자리까지는 '010-XXXX' 형태
    this.value = value.replace(/(\d{3})(\d{0,4})/, "$1-$2");
  } else if (value.length <= 11) {
    // 11자리까지는 '010-XXXX-YYYY' 형태
    this.value = value.replace(/(\d{3})(\d{4})(\d{0,4})/, "$1-$2-$3");
  } else {
    // 11자리를 초과하는 경우는 잘라서 처리
    this.value = value
      .substring(0, 11)
      .replace(/(\d{3})(\d{4})(\d{0,4})/, "$1-$2-$3");
  }
});

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
    var mb_id = $("#mb_id").val();
    var sendData = {'mb_id':mb_id}

    $.ajax({
        type: "POST",
        url: "/bbs/login.car.ajax.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data 1231231:::', data);

            if(data.data.cnt > 0) { 
                //popClose("member_chk_pop");
                //location.replace('/');
                $(".car_li").hide();
            }else{
                $(".car_li").show();
            }
           
        },
    });

    popOpen("first_login_pop");
}

function first_login_submit(){

    var mb_id = $("#mb_id").val();
    var mb_password = $("#mb_password").val();

    var mb_passwords = $("#mb_passwords").val();
    var mb_passwords_re = $("#mb_passwords_re").val();

    var car_type = $("#car_type").val();
    var car_number = $("#car_number").val();

    var chk1 = $("#chk1").prop("checked") ? "1" : "0"; 
    var chk2 = $("#chk2").prop("checked") ? "1" : "0"; 

    var sendData = {'mb_id':mb_id, 'mb_password':mb_password, 'mb_passwords':mb_passwords, 'mb_passwords_re':mb_passwords_re, 'car_type':car_type, 'car_number':car_number, 'chk1':chk1, 'chk2':chk2};


    popOpen('building_info_pop');

    setTimeout(() => {
        $.ajax({
            type: "POST",
            url: "./user_first_login.php",
            data: sendData,
            cache: false,
            async: false,
            dataType: "json",
            success: function(data) {
                console.log('data:::', data);

                if(data.result == false) { 
                    showToast(data.msg);
                    //$(".btn_submit").attr('disabled', false);
                    if(data.data != ""){
                        $("#" + data.data).focus();
                    }

                    popClose('building_info_pop');
                    
                    return false;
                }else{
                    showToast(data.msg);
                
                    setTimeout(() => {
                        location.replace('/');
                    }, 700);
                }
            },
        });
    }, 100);
    //console.log(sendData);

    
}


function login_submit(){
    //popOpen('success_pop');
    let mb_id = $("#mb_id").val();

    var formData = $("#flogin").serialize();

    let urls = "";
    let login_type = "<?php echo $login_type; ?>";
    

    if(login_type == "sm"){
        urls = "./login_check_sm.php";
    }else{
        urls = "./login_check_mb.php";
    }

    $.ajax({
        type: "POST",
        url: urls,
        data: formData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                showToast(data.msg);
                //$(".btn_submit").attr('disabled', false);
                if(data.data != ""){

                    if(data.data == "privacy_agree"){
                        if(login_type == "sm"){
                            location.replace('/sm_login_agree.php?id=' + mb_id);
                        }else{
                            //location.replace('/');
                            checkLogin();
                        }
                    }else{
                        $("#" + data.data).focus();
                    }
                }
                
                return false;
            }else{
                showToast(data.msg);
               
                setTimeout(() => {
                    if(login_type == "sm"){
                        location.replace('/sm_index.php');
                    }else{
                        location.replace('/');
                    }
                }, 700);
            }
        },
    });
}
</script>
<!-- } 로그인 끝 -->

<?php
	include_once(G5_PATH.'/tail.php');
?>