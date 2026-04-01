<?php
$sub_menu = "800100";
require_once './_common.php';


$sql = "SELECT * FROM a_sign_off
        WHERE sign_id = {$sign_id}";
$row = sql_fetch($sql);

switch($row['sign_status']){
    case "N":
        $status = "승인대기";
        break;
    case "P":
        $status = "승인중";
        break;
    case "E":
        $status = "승인완료";
        break;
    case "R":
        $status = "반려";
        break;
}

$html_title = '';
if($w == 'u'){
    $html_title = $status;
}else{
    $html_title = '등록';
}

$g5['title'] .= '결재 ' . $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;

$mb_ids = $member['mb_id'];
$mng_infos = get_manger($mb_ids);



if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql;

    //print_r2($row);
}
?>

<form name="fapproval" id="fapproval" action="./approval_form_update.php" onsubmit="return fapproval_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="sign_id" value="<?php echo $sign_id; ?>">
    <input type="hidden" name="mem_type" value="admin">

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">결재관리 정보</h2>
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_4">
                <col>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <?php if($w == 'u'){?>
                    <th>상태</th>
                    <td colspan='3'>
                        <?php
                        echo $status;
                        ?>
                    </td>
                <?php }?>
                <tr>
                    <th>결재 종류</th>
                    <td colspan="3">
                        <?php
                        $approval_category = "SELECT * FROM a_sign_off_category WHERE is_del = 0 and is_use = 1 ORDER BY is_prior asc, sign_cate_id asc";

                        $approval_res = sql_query($approval_category);
                        ?>
                        <select name="sign_off_category" id="sign_off_category" class="bansang_sel arr93 <?php echo $w == "u" ? "hide_ct" : ""; ?>" required onchange="approvalHandler();">>
                            <option value="">선택</option>
                            <?php while($approval_row = sql_fetch_array($approval_res)){?>
                            <option value="<?php echo $approval_row['sign_cate_code']; ?>" <?php echo get_selected($approval_row['sign_cate_code'], $row['sign_off_category']); ?>><?php echo $approval_row['sign_cate_name']; ?></option>
                            <?php }?>
                        </select>
                        <?php echo $w == "u" ? approval_category_name($row['sign_off_category']) : ""; ?>
                        <script>
                            let w = "<?php echo $w; ?>";

                            if(w == "u"){
                                approvalHandler();
                            }
                            //결재 종류에 따라 폼 변경
                            function approvalHandler(){
                                var categorySelect = document.getElementById("sign_off_category");
                            
                                var selectValue = categorySelect.options[categorySelect.selectedIndex].value;

                                if(selectValue == ""){
                                    $(".approval_request_form_box").html("");
                                    $(".btn_fixed_top").html("");
                                    return false;
                                }else{
                                    let page = "";
                                    switch(selectValue){
                                        case "paid_holiday":
                                            page = "./approval_form_ajax1.php";
                                        break;
                                        case "holiday":
                                            page = "./approval_form_ajax2.php";
                                        break;
                                        case "daily_paid":
                                        case "onsite_expenses":
                                        case "personal_signoff":
                                        case "expenditure_plan":
                                        case "builder_statement":
                                        case "building_account":
                                        case "mng_adjustment":
                                        case "bill_payment":
                                        case "household_refund":
                                            page = "./approval_form_ajax3.php";
                                        break;
                                        case "duty_report":
                                            page = "./approval_form_ajax4.php";
                                        break;
                                        case "overtime_work_request":
                                        case "overtime_work_report":
                                            page = "./approval_form_ajax5.php";
                                        break;
                                    }

                                    let sign_status = "<?php echo $row['sign_status']; ?>";
                                    let mng_id = "<?php echo $w == 'u' ? $row['mng_id'] :  $member['mb_id']; ?>";
                                    $.ajax({

                                    url : page,
                                    type : "POST",
                                    data: {"selectValue":selectValue, "w":w, "sign_id":"<?php echo $sign_id; ?>", "mng_id":mng_id},
                                    success: function(msg){

                                        console.log('msg::',msg);
                                        $(".approval_request_form_box").html(msg);
                                    }

                                    });

                                    $.ajax({

                                    url : "./approval_form_button.php",
                                    type : "POST",
                                    data: {"selectValue":selectValue, "mng_id":mng_id, "w":w, "sign_id":"<?php echo $sign_id; ?>", "sign_status":sign_status, "mb_id":"<?php echo $member['mb_id']; ?>", "mng_certi":"<?php echo $mng_infos['mng_certi']; ?>"},
                                    success: function(msg){

                                        console.log('msg::',msg);
                                        $(".btn_fixed_top").html(msg);
                                    }

                                    });
                                }

                                
                            }
                        </script>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="approval_request_form_box"></div>
    <div class="btn_fixed_top">
        <a href="./approval_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <input type="submit" value="저장" class="btn_submit btn" accesskey='s'>
    </div>
</form>

<div class="cm_pop" id="sign_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <p class="cm_pop_title">전자서명</p>
        <div class="cm_pop_desc">
            <canvas id="signatureCanvas" width="600" height="150"></canvas>
        </div>
		<div class="cm_pop_btn_box flex_ver flex_ver2">
			<button type="button" class="cm_pop_btn" onClick="popClose('sign_pop');">취소</button>
			<button type="button" class="cm_pop_btn ver3" onClick="clearSign();">다시입력</button>
			<button type="button" class="cm_pop_btn ver2" onClick="saveSign();">서명</button>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<script>
function singCheck(){
    let mb_id = "<?php echo $member['mb_id']; ?>";
    let sign_id = "<?php echo $sign_id; ?>";
    let approval_signature = $("#approval_signature").val();
    let approval_cont = $("#approval_cont").val();

    if(approval_cont == "" && approval_cont == ""){
        alert('서명을 입력해주세요.');
        return false;
    }

    let sendData = {'mb_id': mb_id, "sign_id":sign_id, "signdata":approval_signature, "data":approval_cont};

    $.ajax({
        type: "POST",
        url: "./approval_form_check.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                alert(data.msg);
                return false;
            }else{

               alert(data.msg);

               setTimeout(() => {
                    window.location.reload();
                }, 700);
            }
        }
    });
}

let ele = "";
let approval_cont = "";
function signHandler(a, datas = ''){
    ele = a;
    approval_cont = datas;

    console.log('datas:::',datas);

    popOpen('sign_pop');
    resizeCanvas();
}

const canvas = document.getElementById('signatureCanvas');
const signaturePad = new SignaturePad(canvas);

function clearSign(){
    signaturePad.clear();
}

function saveSign(){
    if (signaturePad.isEmpty()) {
        alert('서명을 입력하세요.');
        return false;
    } else {
        const dataURL = signaturePad.toDataURL("image/png");

        console.log(dataURL);

        const part = dataURL.split(';base64,');

        console.log('part0', part[0]);
        console.log('part1', part[1]);

        $("#approval_signature").val(dataURL);

        if(approval_cont != ""){
            $("#approval_cont").val(approval_cont);
        }

        let imgs = `<img src='${dataURL}' />`;
        $("." + ele).html(imgs);

        popClose('sign_pop');
    }
}

const ratio =  Math.max(window.devicePixelRatio || 1, 1);
canvas.width = canvas.offsetWidth * ratio;
canvas.height = canvas.offsetHeight * ratio;
canvas.getContext("2d").scale(ratio, ratio);

function resizeCanvas() {
    const ratio =  Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
}

window.addEventListener("resize", resizeCanvas);
resizeCanvas();

$(document).on("click", ".paid_holiday_request_add", function(){

    let html = `<div class="paid_holiday_request_wrapper">
                    <div class="paid_holiday_request_wrap">
                        <div class="paid_holiday_request_box">
                            <div class="paid_holiday_info_box_wrap flex_ver3">
                                <div class="paid_holiday_info_box">
                                    <div class="paid_holiday_info_label">이름</div>
                                    <div class="paid_holiday_info_ipt">
                                        <input type="text" name="hp_name[]" class="bansang_ipt ver2" required>
                                    </div>
                                </div>
                                <div class="paid_holiday_info_box">
                                    <div class="paid_holiday_info_label">사용일수</div>
                                    <div class="paid_holiday_info_ipt">
                                        <select name="hp_day[]" class="bansang_sel">
                                            <option value="1">1일</option>
                                            <option value="2">2일</option>
                                            <option value="3">3일</option>
                                            <option value="4">4일</option>
                                            <option value="5">5일</option>
                                            <option value="am_half">오전반차</option>
                                            <option value="pm_half">오후반차</option>
                                            <option value="halfhalf">반반차</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="paid_holiday_info_box">
                                    <div class="paid_holiday_info_label">사용일자</div>
                                    <div class="paid_holiday_info_ipt">
                                        <input type="text" name="hp_date[]" class="bansang_ipt ver2 ipt_date" required>
                                    </div>
                                </div>
                            </div>
                            <div class="paid_holiday_info_box_wrap mgt15">
                                <div class="paid_holiday_info_box21">
                                    <div class="paid_holiday_info_label ver2">비고</div>
                                    <div class="paid_holiday_info_ipt ver2 mgt10">
                                        <textarea name="hp_memo[]" class="bansang_ipt ver2 full ta"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <button type="button" onclick="paid_holiday_remove(this)" class="btn btn_01">삭제</button>
                </div>`;

    $(".paid_holiday_request_wrappers").append(html);

    $(document).find(".ipt_date").removeClass('hasDatepicker').datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
});

function paid_holiday_remove(ele){
    ele.closest(".paid_holiday_request_wrapper").remove();
}

function signOffDel(){
    if (!confirm("선택한 결재를 정말 삭제하시겠습니까?")) {
        return false;
    }

    let sign_id = "<?php echo $sign_id; ?>";

    window.location.href = './approval_del.php?sign_id=' + sign_id;
}

function isValidTime(value) {
  const regex = /^([01]\d|2[0-3]):([0-5]\d)$/;
  return regex.test(value);
}

function fapproval_submit(f) {
   
    if(f.sign_off_category.value == "duty_report"){
        if(f.duty_sdate.value > f.duty_edate.value){
            alert("당직 근무 시작일이 종료일보다 이후일 수 없습니다.");
            f.duty_sdate.focus();
            return false;
        }
    }

    if(f.sign_off_category.value == "overtime_work_request" || f.sign_off_category.value == "overtime_work_report"){
        if(!isValidTime(f.extension_stime.value)){
            alert("연장 근무 시작시간을 형식에 맞게 입력하세요. ex.12:00");
            f.extension_stime.focus();
            return false;
        }

        if(!isValidTime(f.extension_etime.value)){
            alert("연장 근무 종료시간을 형식에 맞게 입력하세요. ex.12:00");
            f.extension_stime.focus();
            return false;
        }

        if(f.extension_stime.value > f.extension_etime.value){
            alert("연장 근무 시작시간이 종료시간보다 이후일 수 없습니다.");
            f.extension_stime.focus();
            return false;
        }
        
    }
    
    if(f.approval_signature.value == "" && f.w.value == ''){
        alert("서명을 입력해주세요.");
        return false;
    }

    return true;
}

function approval_submit(){

    let signStatus = "<?php echo $row['sign_status']; ?>";
    let w_status = "<?php echo $w; ?>";
    let sign_off_category = $("#sign_off_category option:selected").val();
    let wdate = $("#wdate").val();
    let mng_department = $("#mng_department").val();
    let mng_grade = $("#mng_grade").val();

    let sign_off_mng_id1;
    let sign_off_mng_id2;
    let sign_off_mng_id3;

    if(signStatus != 'N'){
        sign_off_mng_id1 = $("#sign_off_mng_id1").val();
        sign_off_mng_id2 = $("#sign_off_mng_id2").val();
        sign_off_mng_id3 = $("#sign_off_mng_id3").val();
    }else{
        sign_off_mng_id1 = $("#sign_off_mng_id1 option:selected").val();
        sign_off_mng_id2 = $("#sign_off_mng_id2 option:selected").val();
        sign_off_mng_id3 = $("#sign_off_mng_id3 option:selected").val();
    }
  
    let sign_off_memo = $("#sign_off_memo").val();
    let mng_id = "<?php echo $member['mb_id']; ?>";
    let mng_name = "<?php echo $member['mb_name']; ?>";
    let approval_signature = $("#approval_signature").val();
   

    if(sign_off_mng_id1 == ""){
        alert("1차 결재자를 선택해주세요.");
        $("#sign_off_mng_id1").focus();
        return false;
    }

    if(sign_off_mng_id2 == ""){
        alert("2차 결재자를 선택해주세요.");
        $("#sign_off_mng_id2").focus();
        return false;
    }

    if(sign_off_mng_id3 == ""){
        alert("3차 결재자를 선택해주세요.");
        $("#sign_off_mng_id3").focus();
        return false;
    }

    if(approval_signature == ""){
        alert("서명을 입력해주세요.");
        return false;
    }

    var formData = new FormData();
    formData.append('w', w_status);
    formData.append('sign_off_category', sign_off_category);
    formData.append('wdate', wdate);
    formData.append('mng_department', mng_department);
    formData.append('mng_grade', mng_grade);
    formData.append('sign_off_mng_id1', sign_off_mng_id1);
    formData.append('sign_off_mng_id2', sign_off_mng_id2);
    formData.append('sign_off_mng_id3', sign_off_mng_id3);
    formData.append('sign_off_memo', sign_off_memo);
    formData.append('wid', mng_id);
    formData.append('wname', mng_name);
    formData.append('sign_id', "<?php echo $sign_id; ?>");
    formData.append('approval_signature', approval_signature);

    for (var i = 0; i < filesArr.length; i++) {
        formData.append("approval_file[]", filesArr[i]);
    }

    $("input[name^=file_del]").each(function() {
        if($(this).is(":checked") == true){
            formData.append("file_del[]", '1');
        }else{
              formData.append("file_del[]", '0');
        }
    });

    $.ajax({
        type: "POST",
        url: "./approval_form_update2.php",
        data: formData,
        cache: false,
        async: false,
        dataType: "json",
        contentType: false,
        processData: false,
        success: function(data) {
            console.log('data:::', data);
            if(data.result == false) { 
                alert(data.msg);
                return false;
            }else{
                alert(data.msg);

                setTimeout(() => {
                    // ✅ holiday_request_sample.php에서 문서 캡처 후 approval_info.php로 이동
                    location.replace("/holiday_request_sample.php?sign_id=" + data.data + '&mem_type=admin');
                }, 1000);
            }
        },
        error:function(e){
            alert('오류가 발생했습니다.');
            console.log('e', e);
        }
    });

}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';
