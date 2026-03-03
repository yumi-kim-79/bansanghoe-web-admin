<?php
$sub_menu = "100100";
require_once './_common.php';

$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '등록';
}

$g5['title'] .= "팝업 ". $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;


$sql = "SELECT * FROM a_popup
        WHERE pop_id = {$pop_id}";
$row = sql_fetch($sql);

//파일 확인
$file = "SELECT * FROM g5_board_file WHERE bo_table = 'popup' and wr_id = '{$pop_id}'";
$file_row = sql_fetch($file);

//print_r2($file_row);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql;

    //print_r2($row);
}
// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js

?>

<form name="fpopup" id="fpopup" action="./popup_form_update.php" onsubmit="return fpopup_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="pop_id" value="<?php echo $row['pop_id']; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">담당자 정보</h2>
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
                    <th>팝업 제목</th>
                    <td colspan="3">
                        <input type="text" name="pop_title" class="bansang_ipt ver2" size="100" value="<?php echo $row['pop_title'];?>" required>
                    </td>
                </tr>
                <tr>
                    <th>앱 선택</th>
                    <td colspan="3">
                        <select name="pop_app" id="pop_app" class="bansang_sel" required>
                            <option value="">선택</option>
                            <option value="user" <?php echo get_selected($row['pop_app'], "user"); ?>>반상회 앱</option>
                            <option value="sm_mng"<?php echo get_selected($row['pop_app'], "sm_mng"); ?> >SM매니저 앱</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>우선순위</th>
                    <td>
                        <?php echo help("숫자가 낮을 수록 먼저 노출됩니다."); ?>
                        <input type="number" name="is_prior" class="bansang_ipt ver2" value="<?php echo $row['is_prior']; ?>">
                    </td>
                    <th>노출여부</th>
                    <td>
                        <select name="is_view" id="is_view" class="bansang_sel" required>
                            <option value="1" <?php echo get_selected($row['is_view'], "1"); ?>>노출</option>
                            <option value="0" <?php echo get_selected($row['is_view'], "0"); ?>>미노출</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>팝업 이미지</th>
                    <td colspan="3">
                        <?php echo help("배너 이미지 권장 사이즈");?>
                        <div class="bn_file_wrap">
                            <div class="ipt_box flex_ver">
                                <div class="file_box">
                                    <input type="file" name="pop_file[]" id="pop_file" class="bf_file" accept="image/*">
                                    <label for="pop_file">
                                        <div class="file_contents_box file_contents_box">
                                            <?php if($file_row && $w == 'u'){?>
                                                <?php echo $file_row['bf_source']; ?>
                                            <?php }?>
                                        </div>
                                        <div class="label_box">파일첨부</div>
                                    </label>
                                </div>
                                <?php if($file_row && $w == 'u'){?>
                                <button class="btn btn_03" type="button" onclick="bigSize('/data/file/popup/<?php echo $file_row['bf_file']; ?>')">파일확인</button>
                                <?php }?>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="btn_fixed_top">
        <a href="./popup_list.php" class="btn btn_02">목록</a>
        <input type="submit" value="저장" class="btn_submit btn btn_02" accesskey='s'>
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
$("#pop_file").change(function() {
    //readURL(this);
    $(".file_contents_box").text(this.files[0].name);
    console.log(this.files[0].name);
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

function fpopup_submit(f) {

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

