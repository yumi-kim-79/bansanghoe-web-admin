<?php
$sub_menu = "300700";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '등록';
}

$g5['title'] .= '입주민 전출 신청 정보';
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;


$sql = "SELECT mv.*, post.post_name, building.building_name, dong.dong_name, ho.ho_name, mem.mb_name, mem.mb_hp FROM a_move_request as mv
        left join a_building as building on mv.building_id = building.building_id
        left join a_post_addr as post on post.post_idx = building.post_id
        left join a_building_dong as dong on mv.dong_id = dong.dong_id
        left join a_building_ho as ho on mv.ho_id = ho.ho_id 
        left join a_member as mem on mv.mb_id = mem.mb_id
        WHERE mv.mv_idx = {$mv_idx}";
$row = sql_fetch($sql);


if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql;

    //print_r2($row);
}
// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js

?>

<form name="fbanner" id="fbanner" action="./banner_form_update.php" onsubmit="return fbanner_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="banner_id" value="<?php echo $row['banner_id']; ?>">
   
    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">입주민 전출 신청 정보</h2>
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_4">
                <col>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th>신청날짜</th>
                    <td colspan="3">
                        <input type="text" name="created_at" id="created_at" class="bansang_ipt" size="50" value="<?php echo date('Y-m-d', strtotime($row['created_at'])); ?>" readonly>
                    </td>
                </tr>
                <tr>
                    <th>지역</th>
                    <td >
                        <input type="text" name="post_name" id="post_name" class="bansang_ipt" value="<?php echo $row['post_name']; ?>" readonly>
                    </td>
                    <th>단지</th>
                    <td >
                        <input type="text" name="building_name" id="building_name" class="bansang_ipt"  value="<?php echo $row['building_name']; ?>" size="50" readonly>
                    </td>
                </tr>
                <tr>
                    <th>동</th>
                    <td >
                        <input type="text" name="dong_name" id="dong_name" class="bansang_ipt" value="<?php echo $row['dong_name']; ?>" readonly>
                    </td>
                    <th>호수</th>
                    <td >
                        <input type="text" name="ho_name" id="ho_name" class="bansang_ipt"  value="<?php echo $row['ho_name']; ?>" size="50" readonly>
                    </td>
                </tr>
                <tr>
                    <th>신청자</th>
                    <td >
                        <input type="text" name="mb_name" id="mb_name" class="bansang_ipt" value="<?php echo $row['mb_name']; ?>" readonly>
                    </td>
                    <th>신청자 전화번호</th>
                    <td >
                        <input type="text" name="mb_hp" id="mb_hp" class="bansang_ipt"  value="<?php echo $row['mb_hp']; ?>" size="50" readonly>
                    </td>
                </tr>
                <tr>
                    <th>이사 예정 날짜</th>
                    <td >
                        <input type="text" name="mv_date" id="mv_date" class="bansang_ipt" value="<?php echo $row['mv_date']; ?>" readonly>
                    </td>
                    <th>이사 예정 시간</th>
                    <td >
                        <input type="text" name="mv_times" id="mv_times" class="bansang_ipt"  value="<?php echo $row['move_time'].':'.$row['move_min']; ?>" size="50" readonly>
                    </td>
                </tr>
                <tr>
                    <th>부동산</th>
                    <td >
                        <input type="text" name="mv_estate_name" id="mv_estate_name" class="bansang_ipt" value="<?php echo $row['mv_estate_name']; ?>" readonly>
                    </td>
                    <th>부동산 연락처</th>
                    <td >
                        <input type="text" name="mv_estate_number" id="mv_estate_number" class="bansang_ipt"  value="<?php echo $row['mv_estate_number']; ?>" size="50" readonly>
                    </td>
                </tr>
                <tr>
                    <th>메모</th>
                    <td colspan='3'>
                        <textarea name="memo" id="memo" class="bansang_ipt ta full" readonly><?php echo $row['mv_memo']; ?></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="btn_fixed_top">
        <a href="./move_request_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <!-- <input type="submit" value="저장" class="btn_submit btn btn_02" accesskey='s'> -->
    </div>
</form>

<div id="big_size_pop">
    <div class="od_cancel_inner"></div>
	<button type="button" class="big_size_pop_x" onclick="bigSizeOff();">
		<span></span>
		<span></span>
	</button>
	<div class="od_cancel_cont">
		<img src="" id="big_img" alt="확대 보기">
	</div>
</div>

<script>
$("#banner_file").change(function() {
    //readURL(this);
    $(".file_contents_box").text(this.files[0].name);
    console.log(this.files[0].name);
});

$(function(){
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
});

function bigSize(url){
	const windowHeight = window.innerHeight;
	$("#big_size_pop .od_cancel_cont").css("height", `${windowHeight}px`);
	$("#big_img").attr("src", url);
	$("#big_size_pop").show();
}

function bigSizeOff(){
	$("#big_size_pop").hide();
	$("#big_img").attr("src", "");
}


function fbanner_submit(f) {
   

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

