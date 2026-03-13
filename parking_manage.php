<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

//print_r2($user_info);
//내 차량 리스트
$sql_my_car = "SELECT * FROM a_building_car WHERE is_del = 0 and ho_id = '{$user_building['ho_id']}' and mb_id = '{$user_info['mb_id']}' ORDER BY car_id asc";
$my_car_res = sql_query($sql_my_car);
$my_car_total = sql_num_rows($my_car_res);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $sql_my_car.'<br>';
}
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
                <p class="parking_building">
                    <?php echo $user_building['building_name'];?><br>
                    <?php echo $user_building['dong_name'];?>동 <?php echo $user_building['ho_name'];?>호 <?php echo $user_info['mb_name']; ?>
                </p>
                <div class="parking_info <?php echo $my_car_total > 0 ? "align" : ""; ?>">
                    <div class="parking_info_label">차량정보</div>
                    <?php if($my_car_total == 0){?>
                     
                    <div class="parking_info_text">
                        <a href="/my_info.php">
                            <img src="/images/parking_update.svg" alt="">
                            <div class="parking_info_car">등록된 차량이 없습니다.</div>
                        </a>
                    </div>
                    <?php }else{?>
                    <div class="parking_info_text">
                        <div class="parking_info_car_list">
                            <?php for($i=0;$my_car_row = sql_fetch_array($my_car_res);$i++){?>
                                <div class="parking_info_car on"><?php echo $my_car_row['car_type'].' '; ?><?php echo $my_car_row['car_name']; ?></div>
                            <?php }?>
                        </div>
                    </div>
                    <?php }?>
                </div>
            </div>
        </div>
        <div class="parking_sc parking_sc2">
            <div class="inner">
                <div class="bbs_vote_notice">
                    <div class="bbs_vote_notice_inner">
                    원활한 주차관리를 위해 방문차량을 등록해주세요
                    </div>
                </div>
                <div class="car_visit_btn_box">
                    <button onclick="popOpen('visit_car_info');" class="car_visit_btn">
                        방문차량 등록
                    </button>
                </div>
                <ul class="tab_lnb ver2">
                    <li class="tab01 on" onclick="tab_handler('1', 'in')">입주민차량</li>
                    <li class="tab02" onclick="tab_handler('2', 'visit')">방문차량</li>
                    <li class="tab03" onclick="tab_handler('3', 'my_visit')">내가 등록한 방문차량</li>
                </ul>
                <div class="sch_box_wrap mgt15">
                    <div class="ipt_box ipt_flex ipt_box_ver2">
                        <input type="text" name="sch_text" id="sch_text" class="bansang_ipt ver4" placeholder="차량정보 (차종 또는 차량번호)를 입력하세요.">
                        <button type="button" onclick="schHandler();" class="sch_button">
                            <img src="/images/sch_icons.svg" alt="">
                        </button>
                    </div>
                </div>

                <!-- ★ 주차 안내박스 -->
                <div class="parking_guide_box" id="parking_guide_box">
                    <ul>
                        <li>차량을 조회해야 입주자 차량인지 방문차량인지 확인 가능합니다.</li>
                        <li>원활한 주차를 위해 "이동주차호출" 기능을 통해 차주에게 알림을 발송할 수 있습니다.</li>
                        <li>주민께서 이동주차 알림을 받으신 세대주님께서는 이동주차를 해주십시오.</li>
                    </ul>
                </div>

                <input type="hidden" name="out_car_id" id="out_car_id" value="">
                <div class="car_list_wrap mgt20">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 내차정보입력 팝업 -->
<div class="cm_pop" id="mycar_info">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_title">차량정보</div>
        <ul class="regi_list">
            <li>
                <p class="regi_list_title">차종</p>
                <div class="ipt_box">
                    <input type="text" name="my_car_name" id="my_car_name" class="bansang_ipt ver2" placeholder="차종을 입력해주세요.">
                </div>
            </li>
            <li>
                <p class="regi_list_title">차량번호</p>
                <div class="ipt_box">
                    <input type="text" name="my_car_number" id="my_car_number" class="bansang_ipt ver2" placeholder="차량번호를 입력해주세요.">
                </div>
            </li>
        </ul>
        <div class="cm_pop_btn_box flex_ver">
            <button type="button" class="cm_pop_btn" onClick="popClose('mycar_info');">취소</button>
			<button type="button" class="cm_pop_btn ver2" onClick="popClose('mycar_info');">확인</button>
		</div>
    </div>
</div>

<!-- 방문차량 등록 팝업 -->
<div class="cm_pop" id="visit_car_info">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_title">방문 차량 등록</div>
        <ul class="regi_list">
            <li>
                <p class="regi_list_title">방문자 차량 차종 <span>*</span></p>
                <div class="ipt_box">
                    <input type="text" name="visit_car_name" id="visit_car_name" class="bansang_ipt ver2" placeholder="차종을 입력해주세요.">
                </div>
            </li>
            <li>
                <p class="regi_list_title">방문자 차량 번호 <span>*</span></p>
                <div class="ipt_box">
                    <input type="text" name="visit_car_number" id="visit_car_number" class="bansang_ipt ver2" placeholder="차량번호를 입력해주세요.">
                </div>
            </li>
            <li>
                <p class="regi_list_title">방문자 연락처 <span>*</span></p>
                <div class="ipt_box">
                    <input type="tel" name="visit_hp" id="visit_hp" class="bansang_ipt ver2 phone" placeholder="연락처를 입력해주세요." maxLength="13">
                </div>
            </li>
            <li>
                <p class="regi_list_title">방문 날짜 <span>*</span></p>
                <div class="ipt_box">
                    <input type="tel" name="visit_date" id="visit_date" class="bansang_ipt ver2 ipt_date" readonly>
                </div>
            </li>
            <li>
                <p class="regi_list_title">방문 기간 <span>*</span></p>
                <p class="regi_list_sub_title">*방문 차량은 날짜당 최대 5개 등록 가능합니다.</p>
                <div class="ipt_box ipt_flex ver2">
                   <div class="ipt_radio">
                     <input type="radio" name="visit_day" id="visit_gigan1" class="visit_day" value="1">
                     <label for="visit_gigan1">1일(당일)</label>
                   </div>
                   <div class="ipt_radio">
                     <input type="radio" name="visit_day" id="visit_gigan2" class="visit_day" value="2">
                     <label for="visit_gigan2">2일</label>
                   </div>
                   <div class="ipt_radio">
                     <input type="radio" name="visit_day" id="visit_gigan3" class="visit_day" value="3">
                     <label for="visit_gigan3">3일</label>
                   </div>
                </div>
            </li>
        </ul>
        <div class="agree_wrap">
            <p class="prv_all">
                <input type="checkbox" id="chk_all" class="ver2">
                <label for="chk_all">전체 동의</label>
            </p>
            <ul class="prv_list">
                <li>
                    <p class="regi_prv ver2">
                        <input type="checkbox" name="chk1" id="chk1" class="chk_box" value="1">
                        <label for="chk1">방문차량 등록 절차에 입력된 정보는 주차관련 목적으로 활용될 수 있음에 동의합니다. <span>(필수)</span></label>
                    </p>
                </li>
                <li>
                    <p class="regi_prv ver2">
                        <input type="checkbox" name="chk2" id="chk2" class="chk_box" value="1">
                        <label for="chk2">방문 종료일은 방문 시작일로부터 최대 3일까지 선택할 수 있습니다. <span>(필수)</span></label>
                    </p>
                </li>
                <li>
                    <p class="regi_prv ver2">
                        <input type="checkbox" name="chk3" id="chk3" class="chk_box" value="1">
                        <label for="chk3" class="chk_icon_mv">
                            견인 및 주차 금지 스티커 부착 대상 <span>(필수)</span>
                            <span class="regi_prv_txt regi_prv_txt1">방문차량으로 등록되지 않은 차량</span>
                            <span class="regi_prv_txt regi_prv_txt2">차량 이동 등이 필요하여 연락했으나, 통화연결 되지 않은 경우</span>
                            <span class="regi_prv_txt regi_prv_txt3">건물 내 주차관리에 지장을 초래한 경우</span>
                            <span class="regi_prv_txt regi_prv_txt4">방문차량 등록 시간을 초과한 경우</span>
                            <span class="regi_prv_txt regi_prv_txt5">정상적인 주차에 불편을 초래한 경우</span>
                            <span class="regi_prv_txt regi_prv_txt6">기타 - 조치가 필요한 차량으로 판단된 경우</span>
                        </label>
                    </p>
                </li>
            </ul>

        </div>
        <div class="cm_pop_btn_box flex_ver">
            <button type="button" class="cm_pop_btn" onClick="popClose('visit_car_info');">취소</button>
            <button type="button" class="cm_pop_btn ver2" onClick="visit_car_handler('');">확인</button>
        </div>
    </div>
</div>

<!-- 방문차량 수정 팝업 -->
<div class="cm_pop" id="visit_car_update">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_title">차량정보</div>
        <ul class="regi_list regi_list_vs_car_info">
            <li>
                <input type="hidden" name="car_id" id="car_id" value="">
                <p class="regi_list_title">방문자 차량 차종 <span>*</span></p>
                <div class="ipt_box">
                    <input type="text" name="visit_car_name" id="visit_car_name2" class="bansang_ipt ver2" placeholder="차종을 입력해주세요.">
                </div>
            </li>
            <li>
                <p class="regi_list_title">방문자 차량 번호 <span>*</span></p>
                <div class="ipt_box">
                    <input type="text" name="visit_car_number" id="visit_car_number2" class="bansang_ipt ver2" placeholder="차량번호를 입력해주세요.">
                </div>
            </li>
            <li>
                <p class="regi_list_title">방문자 연락처 <span>*</span></p>
                <div class="ipt_box">
                    <input type="tel" name="visit_hp" id="visit_hp2" class="bansang_ipt ver2 phone" placeholder="연락처를 입력해주세요." maxLength="13">
                </div>
            </li>
        </ul>
        <div class="cm_pop_btn_box flex_ver">
            <button type="button" class="cm_pop_btn" onClick="popClose('visit_car_update');">취소</button>
			<button type="button" class="cm_pop_btn ver2" onClick="visit_car_handler('u');">확인</button>
		</div>
    </div>
</div>

<!-- 출차처리 팝업 -->
<div class="cm_pop" id="out_car_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2">출차 처리 하시겠습니까?<br />출차 처리 후 수정 불가합니다.</p>
		<div class="cm_pop_btn_box flex_ver">
			<button type="button" class="cm_pop_btn" onClick="visit_car_out_pop_cancel();">취소</button>
            <button type="button" class="cm_pop_btn ver2" onClick="visit_car_out_handler();">확인</button>
		</div>
	</div>
</div>

<style>
/* ★ 주차 안내박스 */
.parking_guide_box {
    background-color: #EAF4FF;
    border: 1px solid #1A87D0;
    border-radius: 8px;
    padding: 14px 16px;
    margin-top: 15px;
}
.parking_guide_box ul {
    margin: 0;
    padding: 0;
    list-style: none;
}
.parking_guide_box ul li {
    font-size: 13px;
    color: #1A87D0;
    line-height: 1.7;
    padding-left: 14px;
    position: relative;
}
.parking_guide_box ul li::before {
    content: "•";
    position: absolute;
    left: 0;
}
</style>

<script>
let tabIdx = "<?php echo $tabIdx ?? '1'; ?>";
let tabCode = "<?php echo $tabCode ?? 'in'; ?>"; //검색을 위해 코드명 저장
tab_handler(tabIdx, tabCode);

//탭변경
function tab_handler(index, code){
    tabCode = code;

    $("#sch_text").val("");
    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");

    let mb_id = "<?php echo $user_info['mb_id']; ?>";
    let building_id = "<?php echo $user_building['building_id']; ?>";
    let ho_tenant_at_de = "<?php echo $user_building['ho_tenant_at']; ?>";

    $.ajax({
        url : "/parking_manage_list_ajax.php",
        type : "POST",
        data: { "mb_id":mb_id, "code":code, "building_id":building_id, "ho_tenant_at_de":ho_tenant_at_de},
        success: function(msg){
            $(".car_list_wrap").html(msg);
            $("#parking_guide_box").show(); // ★ 탭 변경 시 안내박스 다시 표시
        }
    });
}

//검색
function schHandler(){
    let schText = $("#sch_text").val();

    let mb_id = "<?php echo $user_info['mb_id']; ?>";
    let building_id = "<?php echo $user_building['building_id']; ?>";
    let ho_tenant_at_de = "<?php echo $user_building['ho_tenant_at']; ?>";

    $.ajax({
        url : "/parking_manage_list_ajax.php",
        type : "POST",
        data: { "schText":schText, "mb_id":mb_id, "code":tabCode, "building_id":building_id, "ho_tenant_at_de":ho_tenant_at_de},
        success: function(msg){
            $(".car_list_wrap").html(msg);
            $("#parking_guide_box").hide(); // ★ 검색하면 안내박스 숨김
        }
    });
}

//방문차량등록
function visit_car_handler(w){
    let visit_car_name;
    let visit_car_number;
    let visit_hp;
    let building_id = "<?php echo $user_building['building_id']; ?>";
    let dong_id = "<?php echo $user_building['dong_id']; ?>";
    let ho_id = "<?php echo $user_building['ho_id']; ?>";
    let mb_id = "<?php echo $user_info['mb_id']; ?>";
    let car_id = $("#car_id").val();

    if(w == ''){
        visit_car_name = $("#visit_car_name").val();
        visit_car_number = $("#visit_car_number").val();
        visit_hp = $("#visit_hp").val();
    }else{
        visit_car_name = $("#visit_car_name2").val();
        visit_car_number = $("#visit_car_number2").val();
        visit_hp = $("#visit_hp2").val();
    }

    let visit_date = $("#visit_date").val();
    let visit_day = $('input[name=visit_day]:checked').val() ?? "";
    let agree1 = $('input[name=chk1]:checked').val() ?? "";
    let agree2 = $('input[name=chk2]:checked').val() ?? "";
    let agree3 = $('input[name=chk3]:checked').val() ?? "";

    let sendData = {'w':w, 'car_id':car_id, 'building_id':building_id, 'dong_id':dong_id, 'ho_id':ho_id, 'mb_id':mb_id, 'visit_car_name': visit_car_name, "visit_car_number":visit_car_number, "visit_hp":visit_hp, "visit_date":visit_date, "visit_day":visit_day, "agree1":agree1, "agree2":agree2, "agree3":agree3};

    $.ajax({
        type: "POST",
        url: "/parking_manage_vs_car_ajax.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) {
                showToast(data.msg);
                if(data.data != ""){
                    $("#" + data.data).focus();
                }
                return false;
            }else{
                showToast(data.msg);
                popClose('visit_car_info');
                setTimeout(() => {
                    location.replace("/parking_manage.php?tabIdx=3&tabCode=my_visit");
                }, 700);
            }
        },
    });
}

//방문차량 수정
function visit_car_update_pop(idx){
    $.ajax({
        url : "/parking_manage_vs_car_info.php",
        type : "POST",
        data: { "car_id":idx},
        success: function(msg){
            console.log(msg);
            $(".regi_list_vs_car_info").html(msg);
            popOpen('visit_car_update');
        }
    });
}

function visit_car_out_pop(idx){
    $("#out_car_id").val(idx);
    popOpen('out_car_pop');
}

function visit_car_out_pop_cancel(){
    $("#out_car_id").val("");
    popClose('out_car_pop');
}

function visit_car_out_handler(){
    let out_car_id = $("#out_car_id").val();

    $.ajax({
        url : "/parking_manage_out_car.php",
        type : "POST",
        data: { "car_id":out_car_id},
        success: function(msg){
            console.log(msg);
            showToast("출차 처리가 완료되었습니다.");
            setTimeout(() => {
                window.location.reload();
            }, 700);
        }
    });
}

//연락처 하이픈
$(".phone").keyup(function () {
  var value = this.value.replace(/[^0-9]/g, "");
  if (value.length <= 3) {
    this.value = value;
  } else if (value.length <= 7) {
    this.value = value.replace(/(\d{3})(\d{0,4})/, "$1-$2");
  } else if (value.length <= 11) {
    this.value = value.replace(/(\d{3})(\d{4})(\d{0,4})/, "$1-$2-$3");
  } else {
    this.value = value.substring(0, 11).replace(/(\d{3})(\d{4})(\d{0,4})/, "$1-$2-$3");
  }
});

$(function(){
    $("#visit_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
});

//전체 선택
$("#chk_all").click(function () {
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

function visit_car_submit(){
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>