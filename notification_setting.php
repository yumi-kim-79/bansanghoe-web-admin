<?php
include_once('./_common.php');
if($types == "sm") include_once(G5_PATH.'/head_sm.php');
else include_once(G5_PATH.'/head.php');

$all_chks = '';
$noti1 = "";
$noti2 = "";
$noti3 = "";
$noti4 = "";
$noti5 = "";
$noti6 = "";
$noti7 = "";

if($types == "sm"){
    // noti_all 은 독립 컬럼 (개별 noti와 별개). 값이 '0'이 아니면 ON 으로 표시 (기본 ON)
    $all_chks = ($member['noti_all'] === '0' || $member['noti_all'] === 0) ? '' : 'checked';
    if($member['noti1'] == "1") $noti1 = "checked";
    if($member['noti2'] == "1") $noti2 = "checked";
    if($member['noti3'] == "1") $noti3 = "checked";
    if($member['noti4'] == "1") $noti4 = "checked";
    if($member['noti5'] == "1") $noti5 = "checked";
    if($member['noti6'] == "1") $noti6 = "checked";
}else{
    $all_chks = ($user_info['noti_all'] === '0' || $user_info['noti_all'] === 0) ? '' : 'checked';
    if($user_info['noti1'] == "1") $noti1 = "checked";
    if($user_info['noti2'] == "1") $noti2 = "checked";
    if($user_info['noti3'] == "1") $noti3 = "checked";
    if($user_info['noti4'] == "1") $noti4 = "checked";
    if($user_info['noti5'] == "1") $noti5 = "checked";
    if($user_info['noti6'] == "1") $noti6 = "checked";
    if($user_info['noti7'] == "1") $noti7 = "checked";
}
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <ul class="noti_setting_list ver2">
                <li>
                    <p>전체 알림</p>
                    <div class="noti_setting_swich">
                        <div class="noti_setting_swich_box">
                            <input type="checkbox" name="noti_all" id="switch_all" class="switch_chk" <?php echo $all_chks; ?>>
                            <label for="switch_all" class="switch_label">
                                <span class="onf_btn"></span>
                            </label>
                        </div>
                    </div>
                </li>
            </ul>
            <ul class="noti_setting_list">
                <?php if($user_info['mb_type'] == "IN" || $is_member){?>
                <li>
                    <p><?php echo $types == "sm" ? "사내결재" : "고지서 알림";?></p>
                    <div class="noti_setting_swich">
                        <div class="noti_setting_swich_box">
                            <input type="checkbox" name="noti1" id="switch1" class="switch_chk switch_chk2" value="1" <?php echo $noti1; ?>>
                            <label for="switch1" class="switch_label">
                                <span class="onf_btn"></span>
                            </label>
                        </div>
                    </div>
                </li>
                <?php }?>
                <li>
                    <p><?php echo $types == "sm" ? "게시판" : "공문 알림";?></p>
                    <div class="noti_setting_swich">
                        <div class="noti_setting_swich_box">
                            <input type="checkbox" name="noti2" id="switch2" class="switch_chk switch_chk2" value="1" <?php echo $noti2; ?>>
                            <label for="switch2" class="switch_label">
                                <span class="onf_btn"></span>
                            </label>
                        </div>
                    </div>
                </li>
                <?php if($user_info['mb_type'] == "IN" || $is_member){?>
                <li>
                    <p><?php echo $types == "sm" ? "캘린더 일정" : "민원처리 알림";?></p>
                    <div class="noti_setting_swich">
                        <div class="noti_setting_swich_box">
                            <input type="checkbox" name="noti3" id="switch3" class="switch_chk switch_chk2" <?php echo $noti3; ?>>
                            <label for="switch3" class="switch_label">
                                <span class="onf_btn"></span>
                            </label>
                        </div>
                    </div>
                </li>
                <?php }?>
                <li>
                    <p><?php echo $types == "sm" ? "전출 신청" : "점검일지 알림";?></p>
                    <div class="noti_setting_swich">
                        <div class="noti_setting_swich_box">
                            <input type="checkbox" name="noti4" id="switch4" class="switch_chk switch_chk2" value="1" <?php echo $noti4; ?>>
                            <label for="switch4" class="switch_label">
                                <span class="onf_btn"></span>
                            </label>
                        </div>
                    </div>
                </li>
                <li>
                    <p>품의서</p>
                    <div class="noti_setting_swich">
                        <div class="noti_setting_swich_box">
                            <input type="checkbox" name="noti6" id="switch6" class="switch_chk switch_chk2" value="1" <?php echo $noti6; ?>>
                            <label for="switch6" class="switch_label">
                                <span class="onf_btn"></span>
                            </label>
                        </div>
                    </div>
                </li>
                <?php if($user_info['mb_type'] == "IN" || $is_member){?>
                <li>
                    <p><?php echo $types == "sm" ? "민원" : "온라인 투표 알림";?></p>
                    <div class="noti_setting_swich">
                        <div class="noti_setting_swich_box">
                            <input type="checkbox" name="noti5" id="switch5" class="switch_chk switch_chk2" value="1" <?php echo $noti5; ?>>
                            <label for="switch5" class="switch_label">
                                <span class="onf_btn"></span>
                            </label>
                        </div>
                    </div>
                </li>
                <?php }?>
                <?php if($types != "sm"){?>
                <li>
                    <p>반상회 전달사항 (긴급사항 포함)</p>
                    <div class="noti_setting_swich">
                        <div class="noti_setting_swich_box">
                            <input type="checkbox" name="noti7" id="switch7" class="switch_chk switch_chk2" value="1" <?php echo $noti7; ?>>
                            <label for="switch7" class="switch_label">
                                <span class="onf_btn"></span>
                            </label>
                        </div>
                    </div>
                </li>
                <?php }?>
            </ul>
        </div>
    </div>
</div>

<!-- 기존 noti7 팝업 -->
<div class="cm_pop" id="noification_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2">
        신반상회 전달사항의 알림을 비활성화하실 경우,<br>
        <span>건물에 발생한 긴급상황</span> 안내를 받지 못합니다.<br><br>
        그래도 비활성화 하시겠습니까?
        </p>
		<div class="cm_pop_btn_box flex_ver">
			<button type="button" class="cm_pop_btn" onClick="switchCancle();">취소</button>
			<button type="button" class="cm_pop_btn ver2" onClick="popClose('noification_pop');">비활성화</button>
		</div>
	</div>
</div>

<!-- 전체 알림 끄기 확인 팝업 (신규 추가) -->
<div class="cm_pop" id="noti_all_off_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2">
        전체 알림을 비활성화하면<br>
        <span>모든 푸시 알림</span>을 받지 못합니다.<br><br>
        그래도 비활성화 하시겠습니까?
        </p>
		<div class="cm_pop_btn_box flex_ver">
			<button type="button" class="cm_pop_btn" onClick="cancelAllOff();">취소</button>
			<button type="button" class="cm_pop_btn ver2" onClick="confirmAllOff();">비활성화</button>
		</div>
	</div>
</div>

<script>
// 알림 설정 저장 (공통)
function saveNotiSetting(name, isChecked){
    var mb_id = "<?php echo $types == "sm" ? $member['mb_id'] : $user_info['mb_id']; ?>";
    var sendData = {'mb_id':mb_id, 'noti':name, 'noti_status':isChecked, 'types':"<?php echo $types; ?>"};
    $.ajax({
        type: "POST",
        url: "/notification_setting_ajax.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            if(data.result == false) {
            }else{
                showToast(data.msg);
            }
        }
    });
}

// 전체 알림 토글 (noti_all 만 저장, 개별 noti 값 건드리지 않음)
$("#switch_all").click(function () {
    if ($("#switch_all").is(":checked")) {
        // 전체 켜기 - noti_all만 1로 저장
        saveNotiSetting('noti_all', true);
    } else {
        // 전체 끄기 - 확인 팝업 먼저
        $("#switch_all").prop("checked", true); // 일단 다시 켜놓고
        popOpen('noti_all_off_pop');
    }
});

function cancelAllOff(){
    // 취소 - 전체 알림 켜진 상태 유지
    popClose('noti_all_off_pop');
}

function confirmAllOff(){
    // 확인 - noti_all 만 0으로 저장 (개별 noti 값 유지)
    popClose('noti_all_off_pop');
    $("#switch_all").prop("checked", false);
    saveNotiSetting('noti_all', false);
}

function switchCancle(){
    $("#switch7").prop('checked', true);
    popClose('noification_pop');
}

$(document).ready(function () {
    // 개별 noti 토글만 처리 (switch_all 은 별도 click 핸들러에서 처리)
    $(".switch_chk2").change(function () {
        let isChecked = $(this).prop("checked");
        let name = $(this).attr("name");

        if(name == 'noti7' && !isChecked){
            popOpen('noification_pop');
        }

        saveNotiSetting(name, isChecked);
    });
});
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>
