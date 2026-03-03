<?php
include_once("_common.php");
include_once(G5_PATH."/_head.php");
?>
<form name="regi_frm" id="regi_frm" method="post" autocomplete="off">
    <input type="hidden" name="w" id="w" value="<?php echo $w?>">
    <div class="register_view">
		<div class="regi_v sub_box">
			<div class="inner">
                <?php if(!$w){?>
				<p class="regi_title">회원가입</p>
				<?php }?>
                <ul class="regi_list">
					<li>
						<p class="regi_list_title">아이디<?php if(!$w){?> <span>*</span><?php }?></p>
						<div class="ipt_box <?php if(!$w){?>ipt_flex<?php }?>">
                            <input type="hidden" name="id_chk" id="id_chk" value="">
							<input type="text" name="mb_id" id="mb_id" class="bansang_ipt ver3" placeholder="5~20자의 영문 소문자, 숫자" oninput="idOnInput(this)" maxLength="20">
							<button type="button" class="regi_box_btn" onClick="idValidCheck()">중복확인</button>
                            <script>
                            $("#mb_id").on("change keyup paste", function() {
                                var currentVal = $(this).val();
                                console.log(currentVal);

                                $("#mb_id").removeClass('actives');
                                $("#id_chk").val("");
                            });
                            </script>
						
						</div>
					</li>
                    <li>
                        <p class="regi_list_title">비밀번호<?php if(!$w){?> <span>*</span><?php }?></p>
                        <div class="ipt_box">
                            <input type="password" name="mb_password" id="mb_password" class="bansang_ipt" placeholder="영문,숫자 6자리 이상 16자리 미만" maxLength="16">
                        </div>
                        <div class="ipt_box">
                            <input type="password" name="mb_password_re" id="mb_password_re" class="bansang_ipt" placeholder="비밀번호 확인" maxLength="16">
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">이름<?php if(!$w){?> <span>*</span><?php }?></p>
                        <div class="ipt_box">
                            <input type="text" name="mb_name" id="mb_name" class="bansang_ipt" placeholder="이름을 입력해 주세요.">
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">휴대폰 번호<?php if(!$w){?> <span>*</span><?php }?></p>
                        <div class="ipt_box">
                            <input type="tel" name="mb_hp" id="mb_hp" class="bansang_ipt phone" placeholder="- 없이 숫자만 입력해 주세요." maxlength="13">
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">아파트 정보<?php if(!$w){?> <span>*</span><?php }?></p>
                        <?php 
                        $building_sql = "SELECT * FROM a_building WHERE is_del = 0 ORDER BY building_id desc";
                        $building_res = sql_query($building_sql);
                        ?>
                        <div class="ipt_box">
                            <select name="building_id" id="building_id" onchange="building_change();" class="bansang_sel" required>
                                <option value="">아파트 선택</option>
                                <?php for($i=0;$building_row = sql_fetch_array($building_res);$i++){?>
                                    <option value="<?php echo $building_row['building_id']; ?>"><?php echo $building_row['building_name']; ?></option>
                                <?php }?>
                            </select>
                            <script>
                                function building_change(){
                                    var buildingSelect = document.getElementById("building_id");
                                    var buildingValue = buildingSelect.options[buildingSelect.selectedIndex].value;

                                    //console.log('buildingValue', buildingValue);

                                    $.ajax({

                                    url : "/building_dong_ajax.php", //ajax 통신할 파일
                                    type : "POST", // 형식
                                    data: { "building_id":buildingValue}, //파라미터 값
                                    success: function(msg){ //성공시 이벤트

                                        //console.log(msg);
                                        $("#dong_id").html(msg);
                                    }

                                    });
                                }
                            </script>
                        </div>
                        <div class="ipt_box ipt_flex">
                            <p class="regi_list_title ver2">단지/동<?php if(!$w){?> <span>*</span><?php }?></p>
                            <!-- <input type="text" name="danji_name" id="danji_name" class="bansang_ipt ver3" placeholder="단지/동을 입력하세요."> -->
                            <select name="dong_id" id="dong_id" class="bansang_sel ver3" onchange="dong_change();" required>
                                <option value="">단지/동을 선택하세요.</option>
                            </select>
                            <script>
                                function dong_change(){
                                    var dongSelect = document.getElementById("dong_id");
                                    var dongValue = dongSelect.options[dongSelect.selectedIndex].value;

                                    //console.log('buildingValue', buildingValue);

                                    $.ajax({

                                    url : "/building_ho_ajax2.php", //ajax 통신할 파일
                                    type : "POST", // 형식
                                    data: { "dong_id":dongValue}, //파라미터 값
                                    success: function(msg){ //성공시 이벤트

                                        //console.log(msg);
                                        $("#ho_id").html(msg);
                                    }

                                    });
                                }
                            </script>
                        </div>
                        <div class="ipt_box ipt_flex">
                            <p class="regi_list_title ver2">호수<?php if(!$w){?> <span>*</span><?php }?></p>
                            <div class="ipt_box ver_regi">
                            <input type="text" name="ho_name" id="ho_name" class="bansang_ipt ver2" required placeholder="호수를 입력하세요. (숫자와 -만 입력)" oninput="validateInput(this);">
                            </div>
                            <!-- <select name="ho_id" id="ho_id" class="bansang_sel ver3" required>
                                <option value="">호수를 선택하세요.</option>
                            </select> -->
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">차종</p>
                        <div class="ipt_box">
                            <input type="text" name="car_type" id="car_type" class="bansang_ipt ver2" placeholder="차종을 입력해 주세요." >
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">차량번호</p>
                        <div class="ipt_box">
                            <input type="text" name="car_name" id="car_name" class="bansang_ipt ver2" placeholder="차량번호를 입력해 주세요." >
                        </div>
                    </li>
                </ul>
                <div class="bar"></div>
               
            </div>
            <div class="agree_wraps">
                <div class="inner">
                    <p class="prv_all">
                        <input type="checkbox" id="chk_all">
                        <label for="chk_all">약관 전체 동의</label>
                    </p>
                    <ul class="prv_list">
                        <li>
                            <p class="regi_prv">
                                <input type="checkbox" name="chk1" id="chk1" class="chk_box" value="1">
                                <label for="chk1">[필수] 신반상회 서비스 이용약관</label>
                            </p>
                            <button type="button" onClick="prvPopOn('provision');"></button>
                        </li>
                        <li>
                            <p class="regi_prv">
                                <input type="checkbox" name="chk2" id="chk2" class="chk_box" value="1">
                                <label for="chk2">[필수] 개인정보 수집 및 이용동의</label>
                            </p>
                            <button type="button" onClick="prvPopOn('privacy');"></button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="fix_btn_back_box"></div>
	<div class="fix_btn_box">
		<button type="button" class="fix_btn on" id="fix_btn" onClick="register();"><?php if($w=="u"){ echo "수정하기"; }else{ echo "가입하기"; }?></button>
	</div>
</form>

<div class="cm_pop" id="privacy_pop" >
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont cm_pop_cont2">
		<p class="cm_pop_title cm_pop_title_privacy">개인정보처리방침</p>
        <div class="close_box">
            <div class="close_box_line close_box_line1"></div>
            <div class="close_box_line close_box_line2"></div>
        </div>
		<div class="cm_pop_desc cm_pop_desc_pop black ver2 mgt20">
      
		</div>
		<div class="cm_pop_btn_box">
			<button type="button" class="cm_pop_btn ver2" onClick="popClose('privacy_pop');">닫기</button>
		</div>
	</div>
</div>

<div class="cm_pop" id="success_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_title">회원가입 완료!</p>
		<p class="cm_pop_desc2 ver2">
			<span>로그인은 관리자 승인 후 할 수 있어요.<br>최대 1~2일 소요됩니다.</span>
		</p>
		<div class="cm_pop_btn_box">
			<a href="<?php echo G5_BBS_URL?>/login.php" class="cm_pop_btn ver2">확인</a>
		</div>
	</div>
</div>

<script>
function idOnInput(e){
    //e.value = e.value.replace(/[^a-z0-9]/gi, '')
    e.value = e.value.replace(/[^a-z0-9_]/g, '');

}

//호수 입력시 숫자와 -만 입력
function validateInput(input) {
    // 숫자와 하이픈만 허용 (나머지는 제거)
    input.value = input.value.replace(/[^0-9\-]/g, '');
}

function prvPopOn(privacy){
    
    if(privacy == "qr_privacy"){
        $(".cm_pop_title_privacy").text("QR 체커 서비스 이용약관");
    }else{
        $(".cm_pop_title_privacy").text("개인정보 수집 및 이용약관");
    }

    $.ajax({

        url : "/privacy_ajax.php", //ajax 통신할 파일
        type : "POST", // 형식
        data: { "co_id":privacy}, //파라미터 값
        success: function(msg){ //성공시 이벤트
            console.log(msg);
            $(".cm_pop_desc").html(msg); 
            popOpen('privacy_pop');

            
        }

    });
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

//중복확인
function idValidCheck(){

    let id = $("#mb_id").val();

    if(id == ""){
        showToast("아이디를 입력해주세요.");
        $("#mb_id").focus();
        return false;
    } 

    let sendData = {'mb_id': id};

    $.ajax({
        type: "POST",
        url: "./user.id.check.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                showToast(data.msg);
                //$(".btn_submit").attr('disabled', false);
                $("#mb_id").removeClass('actives');
                $("#id_chk").val("");
                $("#mb_id").focus();
                return false;
            }else{
                showToast(data.msg);
                $("#mb_id").addClass('actives');
                $("#id_chk").val(1);
               
            }
        },
    });
}


//회원가입
function register(){
    //popOpen('success_pop');
    var formData = $("#regi_frm").serialize();

    $.ajax({
        type: "POST",
        url: "/register.update.php",
        data: formData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                showToast(data.msg);
                //$(".btn_submit").attr('disabled', false);
                $("#" + data.data).focus();
                return false;
            }else{
                showToast(data.msg);
                popOpen('success_pop');
                //$("#id_chk").val(1);
            }
        },
    });
}
</script>
<?php
	include_once(G5_PATH."/_tail.php");
?>