<?php
$sub_menu = "910100";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '등록';
}

$g5['title'] .= '배너 ' . $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;


$sql = "SELECT * FROM a_banner
        WHERE banner_id = {$banner_id}";
$row = sql_fetch($sql);

//파일 확인
$file = "SELECT * FROM g5_board_file WHERE bo_table = 'banner' and wr_id = '{$banner_id}'";
$file_row = sql_fetch($file);

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
        <h2 class="h2_frm ver2">배너 정보</h2>
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
                    <th>위치</th>
                    <td colspan="3">
                        <select name="banner_area" id="banner_area" class="bansang_sel" required>
                            <option value="">선택</option>
                            <option value="main" <?php echo get_selected($row['banner_area'], "main"); ?>>메인</option>
                            <option value="company" <?php echo get_selected($row['banner_area'], "company"); ?>>용역업체</option>
                        </select>
                    </td>
                </tr>
               <tr>
                    <th>배너명</th>
                    <td>
                        <input type="text" name="banner_name" class="bansang_ipt ver2" size="100" value="<?php echo $row['banner_name']; ?>" required>
                    </td>
                    <th>노출기간 설정</th>
                    <td>
                        <div class="ipt_box flex_ver">
                            <input type="text" name="banner_sdate" class="bansang_ipt ver2 ipt_date" value="<?php echo $row['banner_sdate']; ?>"> ~
                            <input type="text" name="banner_edate" class="bansang_ipt ver2 ipt_date" value="<?php echo $row['banner_edate']; ?>">
                        </div>
                    </td>
               </tr>
               <tr>
                    <th>우선순위</th>
                    <td>
                        <?php echo help("숫자가 낮을수록 먼저 노출됩니다.");?>
                        <input type="number" name="is_prior" class="bansang_ipt ver2" value="<?php echo $row['is_prior']; ?>">
                    </td>
                    <th>노출여부</th>
                    <td>
                        <select name="is_view" id="is_view" class="bansang_sel">
                            <option value="1" <?php echo get_selected($row['is_view'], "1"); ?>>노출</option>
                            <option value="0" <?php echo get_selected($row['is_view'], "0"); ?>>미노출</option>
                        </select>
                    </td>
               </tr>
               <tr>
                    <th>URL</th>
                    <td>
                        <?php echo help("https://로 시작하는 URL을 입력해주세요. https://로 시작하는 URL 아닌경우 오류가 발생 될 수 있습니다."); ?>
                        <div class="url_use_box">
                            <input type="checkbox" name="burl_use" id="burl_use" value="1" <?php echo $row['burl_use'] ? 'checked' : ''; ?>>
                            <label for="burl_use">사용</label>
                        </div>
                        <script>
                            $("#burl_use").click(function () {
                                //console.log($("#burl_use").is(":checked"));
                                if($("#burl_use").is(":checked")){
                                    $("#burl").attr('disabled', false);
                                    $("#burl").addClass('ver2');
                                }else{
                                    $("#burl").attr('disabled', true);
                                    $("#burl").removeClass('ver2');
                                }
                            });
                        </script>
                        <div class="url_ipt_box mgt5">
                            <input type="text" name="burl" id="burl" class="bansang_ipt <?php echo $row['burl_use'] ? 'ver2' : ''; ?>" size="100" placeholder="URL 입력해주세요. ex) https://www.naver.com" value="<?php echo $row['burl']; ?>" <?php echo $row['burl_use'] ? '' : 'disabled'; ?>>
                        </div>
                    </td>
               </tr>
               <tr>
                    <th>광고배너 이미지</th>
                    <td colspan="3">
                        <?php echo help("배너 이미지 권장 사이즈");?>
                        <div class="bn_file_wrap">
                            <div class="ipt_box flex_ver">
                                <div class="file_box">
                                    <input type="file" name="banner_file[]" id="banner_file" class="bf_file" accept="image/*" <?php echo $w == '' ? 'required' : '';?>>
                                    <label for="banner_file">
                                        <div class="file_contents_box file_contents_box">
                                            <?php if($file_row && $w == 'u'){?>
                                                <?php echo $file_row['bf_source']; ?>
                                            <?php }?>
                                        </div>
                                        <div class="label_box">파일첨부</div>
                                    </label>
                                </div>
                                <?php if($file_row && $w == 'u'){?>
                                <button class="btn btn_03" type="button" onclick="bigSize('/data/file/banner/<?php echo $file_row['bf_file']; ?>')">파일확인</button>
                                <?php }?>
                            </div>
                        </div>
                    </td>
               </tr>
               <tr>
                    <th>비고</th>
                    <td colspan="3">
                        <textarea name="banner_content" id="banner_content" class="bansang_ipt ver2 full ta"><?php echo $row['banner_content']; ?></textarea>
                    </td>
               </tr>
            </tbody>
        </table>
    </div>
    <div class="btn_fixed_top">
        <a href="./banner_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
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
$("#banner_file").change(function() {
    //readURL(this);
    $(".file_contents_box").text(this.files[0].name);
    console.log(this.files[0].name);
});

$(function(){
    //minDate:"-365d" 
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
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


//날짜형식
function checkValidDate(value) {
	var result = true;
	try {
	    var date = value.split("-");
	    var y = parseInt(date[0], 10),
	        m = parseInt(date[1], 10),
	        d = parseInt(date[2], 10);
	    
	    var dateRegex = /^(?=\d)(?:(?:31(?!.(?:0?[2469]|11))|(?:30|29)(?!.0?2)|29(?=.0?2.(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(?:\x20|$))|(?:2[0-8]|1\d|0?[1-9]))([-.\/])(?:1[012]|0?[1-9])\1(?:1[6-9]|[2-9]\d)?\d\d(?:(?=\x20\d)\x20|$))?(((0?[1-9]|1[012])(:[0-5]\d){0,2}(\x20[AP]M))|([01]\d|2[0-3])(:[0-5]\d){1,2})?$/;
	    result = dateRegex.test(d+'-'+m+'-'+y);
	} catch (err) {
		result = false;
	}    
    return result;
}


function fbanner_submit(f) {

    if(f.banner_sdate.value != "" && f.banner_edate.value == ""){
        alert("배너 시작일을 설정하신 경우 배너 종료일도 설정해주세요.");
        f.banner_edate.focus();
        return false;
    }

    if(f.banner_sdate.value == "" && f.banner_edate.value != ""){
        alert("배너 종료일을 설정하신 경우 배너 시작일도 설정해주세요.");
        f.banner_sdate.focus();
        return false;
    }
    
    if(f.banner_sdate.value != "" && f.banner_edate.value != ""){
        if(!checkValidDate(f.banner_sdate.value)){
            alert("배너 시작일을 날짜 형식에 맞게 입력해주세요.");
            f.banner_sdate.focus();
            return false;
        }

        if(!checkValidDate(f.banner_edate.value)){
            alert("배너 종료일을 날짜 형식에 맞게 입력해주세요.");
            f.banner_edate.focus();
            return false;
        }

        if(f.banner_sdate.value > f.banner_edate.value){
            alert("배너 시작일이 종료일보다 이후일 수 없습니다.");
            f.banner_sdate.focus();
            return false;
        }
    }

    if(f.banner_sdate.value == "" && f.banner_edate.value == ""){
        if (!confirm("배너를 기한 없이 상시노출 하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

