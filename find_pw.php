<?php
include_once("_common.php");
include_once(G5_PATH."/_head.php");
?>
<form name="find_pw_frm" id="find_pw_frm" method="post" autocomplete="off">
    <input type="hidden" name="regist_certi" id="regist_certi" value="">
    <input type="hidden" name="types" id="types" value="<?php echo $types; ?>">
    <div class="find_id_view sub_box">
        <div class="inner">
            <p class="regi_title">비밀번호 찾기</p>
            <ul class="regi_list mgt30">
                <?php if($types == 'sm'){?>
				<li>
					<p class="regi_list_title">아이디 <span>*</span></p>
					<div class="ipt_box">
						<input type="text" name="mb_id" id="mb_id" class="bansang_ipt ver2" placeholder="아이디를 입력하세요." value="<?php echo $id; ?>">
					</div>
				</li>
                <?php }?>
                <li>
                    <p class="regi_list_title">휴대폰 번호 <span>*</span></p>
                    <div class="ipt_box ipt_flex">
                        <input type="tel" name="mb_hp" id="mb_hp" class="bansang_ipt ver2 ver3 phone" placeholder="- 없이 숫자만 입력해 주세요." maxLength="13">
                        <button type="button" class="regi_box_btn" onClick="hp_certi()">인증번호</button>
                    </div>
                    <div class="ipt_box ipt_flex">
                        <div class="ipt_flex_box">
                            <input type="tel" name="certi_number" id="certi_number" class="bansang_ipt" placeholder="인증번호를 입력해 주세요." maxLength="6" readonly>
                            <div class="timer"></div>
                        </div>
                        <button type="button" class="regi_box_btn regi_box_btn_certi" disabled onClick="hp_certi_check()">인증</button>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="fix_btn_back_box"></div>
	<div class="fix_btn_box">
		<button type="button" class="fix_btn" id="fix_btn" disabled onClick="find_info();">확인</button>
	</div>
</form>
<script>
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

var intervalId; //타이머
var timerStatus = "N"; //타이머상태

//인증번호 요청
function hp_certi(){
    let types = "<?php echo $types; ?>";
    let mb_id = "";

    if(types == 'sm'){
        mb_id = $("#mb_id").val();
    }
    
    let mb_hp = $("#mb_hp").val();

    var sendData = {'mb_id':mb_id, 'mb_hp':mb_hp, 'types':'<?php echo $types; ?>'};

    if(timerStatus == "Y"){
        showToast("이미 발송된 인증번호가 존재합니다. 잠시후에 시도해주세요.");
        return false;
    }

    $.ajax({
        type: "POST",
        url: "/send_sms_find.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);
            if(data.result == false) { 
                showToast(data.msg);
                $("#" + data.data).focus();
                return false;
            }else{
                $(".timer").show();
                //startTimer(180);
                startTimer(60)
                showToast(data.msg);

                $("#regist_certi").val("");
                $(".regi_box_btn_certi").text("인증");
                $(".regi_box_btn_certi").attr({'disabled':false});
                $("#certi_number").val("");
                $("#certi_number").addClass("ver2");
                $("#certi_number").attr({"readonly": false});

                $("#fix_btn").removeClass("on");
                $("#fix_btn").attr({"disabled": true});
            }
        }
    });  
}

//타이머
function startTimer(duration) {
    var timer = duration, minutes, seconds;
    timerStatus = "Y";

    intervalId = setInterval(function () {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        //display.textContent = minutes + ":" + seconds;
        $(".timer").text(minutes + ":" + seconds);

        if (--timer < 0) {
            clearInterval(intervalId);
            $(".timer").text("");
            $(".timer").hide();
            timerStatus = "N";
            $(".regi_box_btn_certi").attr({'disabled':true});
            $("#certi_number").removeClass("ver2");
            $("#certi_number").attr({"readonly":true});
        }
    }, 1000);
}

//인증번호 체크
function hp_certi_check(){

    let types = "<?php echo $types; ?>";
    let mb_id = ""; //아이디

    if(types == 'sm'){
        mb_id = $("#mb_id").val();
    }

    let mb_hp = $("#mb_hp").val(); //휴대폰번호
    let certi_number = $("#certi_number").val();

    var sendData = {'mb_id':mb_id, 'mb_hp':mb_hp, 'certi_number':certi_number, 'types':'<?php echo $types; ?>'};

    $.ajax({
        type: "POST",
        url: "/send_sms_find_certi_chk.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);
            if(data.result == false) { 
                showToast(data.msg);
                $("#" + data.data).focus();
                return false;
            }else{
                //$(".timer").show();
                //startTimer(180);
                //startTimer(60)
                showToast(data.msg);

                $("#regist_certi").val("Y");
                $(".regi_box_btn_certi").text("인증완료");
                $(".regi_box_btn_certi").attr({'disabled':true});
                $(".regi_box_btn_certi").addClass("on");
                $("#certi_number").attr({"readonly":true});
                $("#certi_number").removeClass("ver2");
                $("#mb_hp").removeClass("ver2");
                $("#mb_hp").attr({"readonly":true});

                clearInterval(intervalId);

                $(".timer").hide();
                $(".timer").text("");
                timerStatus = "N";

                $("#fix_btn").addClass("on");
                $("#fix_btn").attr({"disabled": false});
            }
        }
    });  
}

$("#mb_hp").on("change keyup paste", function() {
    var currentVal = $(this).val();
    console.log(currentVal);

    $("#mb_id").removeClass('actives');
    $("#id_chk").val("");
});

function find_info(){
    //location.href = "./find_pw_res.php";
    var types = "<?php echo $types; ?>";
    var formData = $("#find_pw_frm").serialize();

    $.ajax({
        type: "POST",
        url: "/find_pw_update.php",
        data: formData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);
            if(data.result == false) { 
                showToast(data.msg);
                $("#" + data.data).focus();
                return false;
            }else{
                //$(".timer").show();
                //startTimer(180);
                //startTimer(60)
                showToast(data.msg);

                location.href = "./find_pw_res.php?id=" + data.data + '&types=' + types;
            }
        }
    });  
}
</script>
<?php
include_once(G5_PATH."/_tail.php");
?>