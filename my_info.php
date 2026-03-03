<?php
include_once("_common.php");
if($types == "sm") include_once(G5_PATH.'/head_sm.php');
else include_once(G5_PATH.'/head.php');

// print_r2($user_building);


//빌딩정보
$sql_ho = "SELECT ho.*, building.building_name, dong.dong_name FROM a_building_ho as ho
            LEFT JOIN a_building as building ON ho.building_id = building.building_id
            LEFT JOIN a_building_dong as dong ON ho.dong_id = dong.dong_id
            WHERE ho.ho_tenant_hp = '{$user_info['mb_hp']}' and ho.is_del = 0";
$row_ho = sql_fetch($sql_ho);


//차량정보
$sql_car = "SELECT * FROM a_building_car WHERE mb_id = '{$user_info['mb_id']}' and ho_id = '{$user_building['ho_id']}' and is_del = 0 ORDER BY car_id asc";
$res_car = sql_query($sql_car);
$total_car = sql_num_rows($res_car);

//담당단지
$mng_building_sql = "SELECT mng_b.*, building.building_name FROM a_mng_building as mng_b LEFT JOIN a_building as building on mng_b.building_id = building.building_id WHERE mng_b.is_del = 0 and mng_b.mb_id = '{$member['mb_id']}' ORDER BY building.building_name asc, mng_b.mng_id asc";
//echo $mng_building_sql;
$mng_building_res = sql_query($mng_building_sql);
//echo $sql_car;
// print_r2($user_building);
?>
<form name="regi_up_frm" id="regi_up_frm" method="post" autocomplete="off">
<?php if($types != "sm"){?>
<input type="hidden" name="mb_id" id="mb_id" value="<?php echo $user_info['mb_id']; ?>">
<?php }?>
<input type="hidden" name="types" id="types" value="<?php echo $types; ?>">
<input type="hidden" name="regist_certi" id="regist_certi" value="Y">
<input type="hidden" name="now_hp" id="now_hp" value="<?php echo $types == "sm" ? $member['mb_hp'] : $user_info['mb_hp']; ?>">

<!-- 차량정보 입력용 -->
<input type="hidden" name="building_id" id="building_id" value="<?php echo $user_building['building_id']; ?>">
<input type="hidden" name="dong_id" id="dong_id" value="<?php echo $user_building['dong_id']; ?>">
<input type="hidden" name="ho_id" id="ho_id" value="<?php echo $user_building['ho_id']; ?>">
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <ul class="regi_list">
                <?php if($user_info['mb_type'] == "OUT"){?>
                <li>
                    <p class="regi_list_title">이름</p>
                    <div class="ipt_box">
                        <input type="text" name="mb_name" id="mb_name" class="bansang_ipt" placeholder="이름을 입력해 주세요." value="<?php echo $types == "sm" ? $member['mb_name'] : $user_info['mb_name']; ?>" readonly>
                    </div>
                </li>
                <?php }?>
                <li>
                    <p class="regi_list_title"><?php echo $types == "sm" ? "아이디" : "휴대폰 번호(아이디)";?></p>
                    <?php if($types != "sm"){?>
                    <div class="ipt_box ipt_flex">
                        <input type="tel" name="mb_hp" id="mb_hp" class="bansang_ipt <?php echo $user_info['mb_type'] == "IN" || $is_member ? "ver3" : ""; ?> phone" placeholder="- 없이 숫자만 입력해 주세요." value="<?php echo $user_info['mb_hp']; ?>" maxlength="13" readonly>
                        <?php if($user_info['mb_type'] == "IN" || $is_member){?>
                            <button type="button" class="regi_box_btn regi_box_btn_sms" onClick="hp_certi()">인증번호</button>
                            <button type="button" class="regi_box_btn regi_box_btn_change" onClick="hp_change()">변경하기</button>
                            <script>
                                function hp_change(){
                                    $("#mb_hp").attr("readonly", false);
                                    $("#mb_hp").addClass("ver2");

                                    $(".regi_box_btn_change").hide();
                                    $(".regi_box_btn_sms").show();
                                }
                            </script>
                        <?php }?>
                    </div>
                    <?php if($user_info['mb_type'] == "IN" || $is_member){?>
                        <div class="ipt_box ipt_flex">
                            <div class="ipt_flex_box">
                                <input type="tel" name="certi_number" id="certi_number" class="bansang_ipt" placeholder="인증번호를 입력해 주세요." maxLength="6" readonly>
                                <div class="timer"></div>
                            </div>
                            <button type="button" class="regi_box_btn regi_box_btn_certi" disabled onClick="hp_certi_check()">인증</button>
                        </div>
                    <?php }?>
                    <?php }else{ ?>
                        <div class="ipt_box">
                            <input type="text" name="mb_id" id="mb_id" class="bansang_ipt" placeholder="아이디를 입력해주세요." value="<?php echo $member['mb_id']; ?>" readonly>
                        </div>
                    <?php }?>
                </li>
                <?php if($user_info['mb_type'] == "OUT"){?>
                    <li>
                        <p class="regi_list_title">관리 단지</p>
                        <div class="ipt_box">
                            <input type="text" name="mng_b_name" id="mng_b_name" class="bansang_ipt" value="<?php echo $user_building['building_name']; ?>" readonly>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">직책</p>
                        <div class="ipt_box">
                            <input type="text" name="mng_gr_name" id="mng_gr_name" class="bansang_ipt" value="<?php echo $user_building['gr_name']; ?>" readonly>
                        </div>
                    </li>
                <?php }?>
                <?php if($user_info['mb_type'] == "IN" || $is_member){?>
                    <li>
                        <p class="regi_list_title">비밀번호</p>
                        <div class="ipt_box">
                            <input type="password" name="mb_password" id="mb_password" class="bansang_ipt ver2" placeholder="영문, 숫자 6자리 이상 16자리 미만" maxlength="16">
                        </div>
                        <div class="ipt_box">
                            <input type="password" name="mb_password_re" id="mb_password_re" class="bansang_ipt ver2" placeholder="비밀번호 확인" maxlength="16">
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title"><?php echo $types == "sm" ? "이름" : "세대주";?></p>
                        <div class="ipt_box">
                            <input type="text" name="mb_name" id="mb_name" class="bansang_ipt" placeholder="이름을 입력해 주세요." value="<?php echo $types == "sm" ? $member['mb_name'] : $user_info['mb_name']; ?>" readonly>
                        </div>
                    </li>
                    <?php if($types != "sm" && $user_info['mb_type'] == "IN"){?>
                    <li>
                        <p class="regi_list_title">아파트 정보</p>
                        <div class="ipt_box">
                            <input type="text" name="building_name" id="building_name" class="bansang_ipt" placeholder="아파트 선택" value="<?php echo $user_building['building_name']; ?>" readonly>
                        </div>
                        <div class="ipt_box ipt_flex">
                            <p class="regi_list_title ver2">단지/동</p>
                            <input type="text" name="dong_name" id="dong_name" class="bansang_ipt ver3" placeholder="단지/동을 입력하세요." value="<?php echo $user_building['dong_name'];?>동" readonly>
                        </div>
                        <div class="ipt_box ipt_flex">
                            <p class="regi_list_title ver2">호수</p>
                            <input type="text" name="ho_name" id="ho_name" class="bansang_ipt ver3" placeholder="호수를 입력하세요." value="<?php echo $user_building['ho_name'];?>호" readonly>
                        </div>
                    </li>
                    <?php if($total_car > 0){?>
                        <?php for($i=0;$row_car = sql_fetch_array($res_car);$i++){?>
                        <li>
                            <p class="regi_list_title">차종</p>
                            <div class="ipt_box">
                                <input type="hidden" name="car_id[]" value="<?php echo $row_car['car_id']; ?>">
                                <input type="text" name="car_type[]" class="bansang_ipt <?php echo $i == 0 ? "ver2" : "" ?>" placeholder="차종을 입력해 주세요." value="<?php echo $row_car['car_type']; ?>" <?php echo $i == 0 ? "" : "disabled" ?>>
                            </div>
                        </li>
                        <li>
                            <p class="regi_list_title">차량번호</p>
                            <div class="ipt_box">
                                <input type="text" name="car_name[]" class="bansang_ipt <?php echo $i == 0 ? "ver2" : "" ?>" placeholder="차량번호를 입력해 주세요." value="<?php echo $row_car['car_name']; ?>" <?php echo $i == 0 ? "" : "disabled" ?>>
                            </div>
                        </li>
                        <?php }?>
                        <!-- <?php for($i=0;$row_car = sql_fetch_array($res_car);$i++){?>
                        <li>
                            <p class="regi_list_title">차종</p>
                            <div class="ipt_box">
                                <input type="hidden" name="car_id[]" value="<?php echo $row_car['car_id']; ?>">
                                <input type="text" name="car_type[]" class="bansang_ipt ver2" placeholder="차종을 입력해 주세요." value="<?php echo $row_car['car_type']; ?>">
                            </div>
                        </li>
                        <li>
                            <p class="regi_list_title">차량번호</p>
                            <div class="ipt_box">
                                <input type="text" name="car_name[]" class="bansang_ipt ver2" placeholder="차량번호를 입력해 주세요." value="<?php echo $row_car['car_name']; ?>">
                            </div>
                        </li>
                        <?php }?> -->
                    <?php }else{ ?>
                        <li>
                            <p class="regi_list_title">차종</p>
                            <div class="ipt_box">
                                <input type="hidden" name="car_id[]" value="<?php echo $row_car['car_id']; ?>">
                                <input type="text" name="car_type[]" class="bansang_ipt ver2" placeholder="차종을 입력해 주세요." value="<?php echo $row_car['car_type']; ?>">
                            </div>
                        </li>
                        <li>
                            <p class="regi_list_title">차량번호</p>
                            <div class="ipt_box">
                                <input type="text" name="car_name[]" class="bansang_ipt ver2" placeholder="차량번호를 입력해 주세요." value="<?php echo $row_car['car_name']; ?>">
                            </div>
                        </li>
                    <?php }?>
                    <?php }else{ ?>
                    <li>
                        <p class="regi_list_title">휴대폰 번호</p>
                        <div class="ipt_box ipt_flex">
                            <input type="tel" name="mb_hp" id="mb_hp" class="bansang_ipt ver3 phone" placeholder="- 없이 숫자만 입력해 주세요." value="<?php echo $member['mb_hp']; ?>" maxlength="13" readonly>
                                <button type="button" class="regi_box_btn regi_box_btn_sms" onClick="hp_certi()">인증번호</button>
                                <button type="button" class="regi_box_btn regi_box_btn_change" onClick="hp_change()">변경하기</button>
                                <script>
                                    function hp_change(){
                                        $("#mb_hp").attr("readonly", false);
                                        $("#mb_hp").addClass("ver2");

                                        $(".regi_box_btn_change").hide();
                                        $(".regi_box_btn_sms").show();
                                    }
                                </script>
                        </div>
                        <div class="ipt_box ipt_flex">
                            <div class="ipt_flex_box">
                                <input type="tel" name="certi_number" id="certi_number" class="bansang_ipt" placeholder="인증번호를 입력해 주세요." maxLength="6" readonly>
                                <div class="timer"></div>
                            </div>
                            <button type="button" class="regi_box_btn regi_box_btn_certi" disabled onClick="hp_certi_check()">인증</button>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">부서</p>
                        <div class="ipt_box">
                            <input type="text" name="md_name" id="md_name" class="bansang_ipt" placeholder="부서를 입력하세요." value="<?php echo $mng_info['md_name'];?>" readonly>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">담당단지</p>
                        <?php for($i=0;$mng_building_row = sql_fetch_array($mng_building_res);$i++){?>
                        <div class="ipt_box">
                            <input type="text" name="building_name[]" class="bansang_ipt" placeholder="담당단지 입력하세요." value="<?php echo $mng_building_row['building_name'];?>" readonly>
                        </div>
                        <?php }?>
                    </li>
                    <?php }?>
                <?php }?>
            </ul>
            <?php if($user_info['mb_type'] == "IN"){?>
            <div class="fix_btn_wrap">
                <button type="button" onclick="popOpen('leave_pop');" class="fix_btn ver3">회원탈퇴</button>
            </div>
            <?php }?>
        </div>
        <?php if($user_info['mb_type'] == "IN" || $is_member){?>
        <div class="fix_btn_back_box"></div>
        <div class="fix_btn_box">
            <button type="button" onclick="account_update();" id="save_btn" class="fix_btn on">저장</button>
        </div>
        <?php }?>
    </div>
</div>
<div class="cm_pop" id="leave_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2">정말 회원탈퇴 하시겠습니까?</p>
		<p class="cm_pop_desc2">복구가 불가능합니다</p>
		<div class="cm_pop_btn_box flex_ver">
			<button type="button" class="cm_pop_btn" onClick="popClose('leave_pop');">취소</button>
            <button type="button" class="cm_pop_btn ver2" onclick="leave_handler();">회원탈퇴</button>
		</div>
	</div>
</div>

<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>내 정보를 저장 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>
</form>

<script>

function leave_handler() {
    let types = "<?php echo $types; ?>"; //일반회원, 매니저회원
    let mb_id = $("#mb_id").val();

    let sendData = {'types': types, 'mb_id':mb_id};

    $.ajax({
        type: "POST",
        url: "./my_info_leave.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                showToast(data.msg);
                return false;
            }else{
                showToast(data.msg);

                setTimeout(() => {
                    if(types == 'sm'){
                        location.href = '/bbs/logout.sm.php';
                    }else{
                        location.href = '/bbs/logout.user.php';
                    }
                }, 300);
            }
        },
    });

    //location.href = './my_info_leave.php?types=' + types + '&mb_id=' + mb_id;
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

var intervalId; //타이머
var timerStatus = "N"; //타이머상태

//인증번호 요청
function hp_certi(){
    let mb_id = $("#mb_id").val();
    let now_hp = $("#now_hp").val();
    let mb_hp = $("#mb_hp").val();
    let types = "<?php echo $types; ?>";

    if(now_hp == mb_hp){
        showToast("휴대폰번호를 변경 후 인증번호를 요청해주세요.");
        return false;
    }

    var sendData = {'mb_id':mb_id, 'mb_hp':mb_hp, 'type':'u', 'types':types};

    if(timerStatus == "Y"){
        showToast("이미 발송된 인증번호가 존재합니다. 잠시후에 시도해주세요.");
        return false;
    }

    $.ajax({
        type: "POST",
        url: "/send_sms.php",
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

                $("#save_btn").removeClass("on");
                $("#save_btn").attr({"disabled": true});
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

            $("#save_btn").addClass("on");
            $("#save_btn").attr({"disabled": false});

          
        }
    }, 1000);
}

//인증번호 체크
function hp_certi_check(){

let mb_id = $("#mb_id").val(); //아이디
let mb_hp = $("#mb_hp").val(); //휴대폰번호
let certi_number = $("#certi_number").val();

var sendData = {'mb_id':mb_id, 'mb_hp':mb_hp, 'certi_number':certi_number};

$.ajax({
    type: "POST",
    url: "/send_sms_certi_chk.php",
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
            $(".regi_box_btn_change").text("다시발송");
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

            $("#save_btn").addClass("on");
            $("#save_btn").attr({"disabled": false});
        }
    }
});  
}

function account_update(){
    //popOpen('success_pop');
    var formData = $("#regi_up_frm").serialize();

    console.log('formData', formData);

    popOpen('building_info_pop');

    setTimeout(() => {
        $.ajax({
            type: "POST",
            url: "/my_info_update.php",
            data: formData,
            cache: false,
            async: false,
            dataType: "json",
            success: function(data) {
                console.log('data:::', data);

                if(data.result == false) { 
                    showToast(data.msg);
                    //$(".btn_submit").attr('disabled', false);
                    popClose('building_info_pop');
                    
                    $("#" + data.data).focus();
                    return false;
                }else{
                    showToast(data.msg);
                    //popOpen('success_pop');
                    //$("#id_chk").val(1);
                    setTimeout(() => {
                        window.location.reload();
                    }, 700);

                    popClose('building_info_pop');
                }
            },
            error: function(request, status, error) {
                // console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                popClose('building_info_pop');
                showToast("입력된 내용이 없습니다. 잠시 후 다시 시도해 주세요.");
            }
        });
    }, 50);
}
</script>
<?php
include_once(G5_PATH."/_tail.php");
?>