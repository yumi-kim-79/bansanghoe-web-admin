<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$building_sql = "SELECT building.*, post.post_name FROM a_building as building
                 LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
                 WHERE building.building_id = '{$building_id}'";
$building_row = sql_fetch($building_sql);
?>
<style>
    /* .bansang_ipt.ta {resize:vertical} */
    .regi_list.ver3 textarea {height: 160px;}
</style>
<form name="building_frm" id="building_frm" method="post" autocomplete="off">
<input type="hidden" name="building_id" value="<?php echo $building_id; ?>">
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="build_box_form">
                <div class="build_box build_flex ver2">
                    <div class="build_label">건물명</div>
                    <div class="build_cts"><?php echo $building_row['building_name']; ?></div>
                </div>
                <div class="build_box build_flex ver2">
                    <div class="build_label">주소</div>
                    <div class="build_cts"><?php echo $building_row['building_addr'].' '.$building_row['building_addr2']; ?></div>
                </div>
                <ul class="regi_list ver3">
					<li>
						<p class="regi_list_title">1층 현관 비밀번호</p>
						<div class="ipt_box">
							<textarea name="open_password" id="open_password" class="bansang_ipt ta ver2" placeholder="1층 현관 비밀번호 정보를 입력하세요."><?php echo $building_row['open_password']; ?></textarea>
						</div>
					</li>
                    <li>
						<p class="regi_list_title">CCTV 비밀번호</p>
						<div class="ipt_box">
							<textarea name="cctv_password" id="cctv_password" class="bansang_ipt ta ver2" placeholder="CCTV 비밀번호 정보를 입력하세요."><?php echo $building_row['cctv_password']; ?></textarea>
						</div>
					</li>
                    <li>
						<p class="regi_list_title">비고</p>
						<div class="ipt_box">
							<textarea name="building_bigo" id="building_bigo" class="bansang_ipt ta ver2" placeholder="내용을 입력하세요."><?php echo $building_row['building_bigo']; ?></textarea>
						</div>
					</li>
                    <li>
						<p class="regi_list_title">건축주</p>
						<div class="ipt_box">
							<input type="text" name="building_owner" class="bansang_ipt ver2" placeholder="건축주명을 입력하세요." value="<?php echo $building_row['building_owner']; ?>">
						</div>
					</li>
                    <li>
						<p class="regi_list_title">분양 사무실</p>
						<div class="ipt_box">
							<input type="text" name="building_estate" class="bansang_ipt ver2" placeholder="분양 사무실을 입력하세요." value="<?php echo $building_row['building_estate']; ?>">
						</div>
					</li>
                    <li>
						<p class="regi_list_title">시공사</p>
						<div class="ipt_box">
							<input type="text" name="building_company" class="bansang_ipt ver2" placeholder="시공사를 입력하세요." value="<?php echo $building_row['building_company']; ?>">
						</div>
					</li>
                </ul>
            </div>
        </div>
        <div class="fix_btn_back_box"></div>
        <div class="fix_btn_box flex_ver ver3">
            <button type="button" onclick="historyBack();" class="fix_btn" id="fix_btn" onClick="register();">취소</button>
            <button type="button" onclick="building_update();" class="fix_btn on" id="fix_btn">확인</button>
        </div>
    </div>
</div>
</form>

<script>
// function building_history(){

//     var building_id = "<?php echo $building_id; ?>";
//     location.replace('/building_info.php?building_id=' + building_id);
// }

//단지 정보 수정
function building_update(){
    var formData = $("#building_frm").serialize();

    $.ajax({
        type: "POST",
        url: "/building_info_form_update.php",
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
                // popOpen('success_pop');
                //$("#id_chk").val(1);
                setTimeout(() => {
                    location.replace('/building_info.php?building_id=' + data.data);
                    //window.location.reload();
                }, 700);
                
            }
        },
    });
}

</script>
<?php
include_once(G5_PATH.'/tail.php');
?>