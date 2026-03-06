<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$sql = "SELECT * FROM a_move_request WHERE mv_idx = '{$mv_idx}'";
//echo $sql;
$row = sql_fetch($sql);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <ul class="regi_list">
                <li>
                    <p class="regi_list_title">이사 예정 날짜<?php if(!$w){?> <span>*</span><?php }?></p>
                    <div class="ipt_box">
                        <input type="text" name="mv_date" id="mv_date" class="bansang_ipt ipt_date ver2" value="<?php echo $row['mv_date']; ?>" readonly>
                    </div>
                </li>
                <li>
                    <p class="regi_list_title">이사 시작 시간<?php if(!$w){?> <span>*</span><?php }?></p>
                    <div class="ipt_box ipt_flex ipt_box_ver2">
                        <select name="move_time" id="move_time" class="bansang_sel">
                            <option value="">시 선택</option>
                            <?php for($i=1;$i<24;$i++){
                                $time = $i < 10 ? '0'.$i : $i;
                                ?>
                                <option value="<?php echo $time; ?>" <?php echo $row['move_time'] == $time ? 'selected' : ''?>><?php echo $time.'시'; ?></option>
                            <?php }?>
                        </select>
                        <span>:</span>
                        <select name="move_min" id="move_min" class="bansang_sel">
                            <option value="">분 선택</option>
                            <?php for($i=0;$i<60;$i+=5){
                                $min = $i < 10 ? '0'.$i : $i;
                                ?>
                                <option value="<?php echo $min; ?>" <?php echo $row['move_min'] == $min ? 'selected' : ''?>><?php echo $min.'분'; ?></option>
                            <?php }?>
                        </select>
                    </div>
                </li>
                <li>
                    <p class="regi_list_title">부동산 <?php if(!$w){?> <span>*</span><?php }?></p>
                    <div class="ipt_box">
                        <input type="text" name="mv_estate_name" id="mv_estate_name" class="bansang_ipt ver2" placeholder="부동산 이름을 입력해 주세요." value="<?php echo $row['mv_estate_name']; ?>">
                    </div>
                </li>
                <li>
                    <p class="regi_list_title">부동산 연락처 <?php if(!$w){?> <span>*</span><?php }?></p>
                    <div class="ipt_box">
                        <input type="tel" name="mv_estate_number" id="mv_estate_number" class="bansang_ipt ver2" placeholder="부동산 연락처를 입력해 주세요." value="<?php echo $row['mv_estate_number']; ?>">
                    </div>
                </li>
                <li>
                    <p class="regi_list_title">기타 접수사항</p>
                    <div class="ipt_box">
                        <textarea type="tel" name="mv_memo" id="mv_memo" class="bansang_ipt ta ver2" placeholder="기타 메모할 것을 입력해 주세요."><?php echo $row['mv_memo']; ?></textarea>
                    </div>
                </li>
            </ul>
            <!-- <div class="fix_btn_wrap">
                <a href="<?php echo G5_URL?>/find_pw.php" class="fix_btn ver4"><img src="/images/update_icons.svg" alt="">수정하기</a>
            </div> -->
        </div>
        <div class="fix_btn_back_box"></div>
        <div class="fix_btn_box flex_ver ver3">
            <?php if($w == 'u'){?>
            <button type="button" onclick="popOpen('cancel_pop');" class="fix_btn" id="fix_btn">취소</button>
            <?php }?>
            <?php if($w == 'u'){?>
                <button type="button" class="fix_btn on" id="fix_btn" onClick="request_handler();"><?php echo $w == "u" ? "수정하기" : "신청하기";?></button>
            <?php }else{ ?>
                <button type="button" class="fix_btn on" id="fix_btn" onClick="request_check();"><?php echo $w == "u" ? "수정하기" : "신청하기";?></button>
            <?php }?>
        </div>
    </div>
</div>
<div class="cm_pop" id="confirm_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2">이사(전출) 신청을 하시겠습니까?</p>
		<div class="cm_pop_btn_box flex_ver">
			<button type="button" class="cm_pop_btn" onClick="popClose('confirm_pop');">취소</button>
			<button type="button" class="cm_pop_btn ver2" onClick="request_handler();">확인</button>
		</div>
	</div>
</div>
<div class="cm_pop" id="cancel_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2">이사(전출) 신청을 취소 하시겠습니까?</p>
		<div class="cm_pop_btn_box flex_ver">
			<button type="button" class="cm_pop_btn" onClick="popClose('cancel_pop');">취소</button>
			<button type="button" class="cm_pop_btn ver2" onClick="request_cancel();">확인</button>
		</div>
	</div>
</div>

<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>이사/전출 신청 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>
<script>
$(function(){
    $("#mv_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
});

//신청
function request_check(){
    let mv_date = $("#mv_date").val();
    let move_time = $("#move_time option:selected").val();
    let move_min = $("#move_min option:selected").val();
    let mv_estate_name = $("#mv_estate_name").val();
    let mv_estate_number = $("#mv_estate_number").val();

    if(mv_date == ''){
        showToast('이사 예정 날짜를 선택해주세요.');
        return false;
    }

    if(move_time == ''){
        showToast('이사 시작 시간을 선택해주세요.');
        return false;
    }

    if(move_min == ''){
        showToast('이사 시작 분을 선택해주세요.');
        return false;
    }

    if(mv_estate_name == ''){
        showToast('부동산을 입력해주세요.');
        return false;
    }

    if(mv_estate_number == ''){
        showToast('부동산 연락처를 입력해주세요.');
        return false;
    }

    popOpen('confirm_pop');
}

//취소
function request_cancel(){

    let sendData = {'mv_idx':"<?php echo $mv_idx; ?>"};

    $.ajax({
        type: "POST",
        url: "/move_request_cancel.php",
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
                    
                    // window.location.href = '/index.php';
                    location.replace("/");
                    
                }, 500);
            }
           
        },
    });
}

function request_handler(){

    $("#building_info_pop").show();

    let w = "<?php echo $w; ?>";
    let mv_idx = "<?php echo $mv_idx; ?>";
    let building_id = "<?php echo $user_building['building_id']; ?>";
    let dong_id = "<?php echo $user_building['dong_id']; ?>";
    let ho_id = "<?php echo $user_building['ho_id']; ?>";
    let mb_id = "<?php echo $user_info['mb_id']; ?>";
    let mv_date = $("#mv_date").val();
    let move_time = $("#move_time option:selected").val();
    let move_min = $("#move_min option:selected").val();
    let mv_estate_name = $("#mv_estate_name").val();
    let mv_estate_number = $("#mv_estate_number").val();
    let mv_memo = $("#mv_memo").val();

    let sendData = {"w":w, "mv_idx":mv_idx, "building_id":building_id, "dong_id":dong_id, "ho_id":ho_id, "mb_id":mb_id, "mv_date":mv_date, "move_time":move_time, "move_min":move_min, "mv_estate_name":mv_estate_name, "mv_estate_number":mv_estate_number, "mv_memo":mv_memo};

    setTimeout(() => {
        $.ajax({
            type: "POST",
            url: "/move_request_update.php",
            data: sendData,
            cache: false,
            async: false,
            dataType: "json",
            success: function(data) {
                console.log('data:::', data);

                if(data.result == false) { 
                    showToast(data.msg);
                    $("#building_info_pop").hide();
                    if(data.data != ""){
                        $("#" + data.data).focus();
                    }
                    return false;
                }else{
                    showToast(data.msg);

                    $("#building_info_pop").hide();

                    setTimeout(() => {
                        
                        location.replace("/");
                        
                    }, 500);
                }
            
            },
        });
    }, 50);
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>
