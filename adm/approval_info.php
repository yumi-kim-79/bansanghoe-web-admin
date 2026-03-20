<?php
$sub_menu = "800200";
require_once './_common.php';

// ✅ sign_id 안전 처리
$sign_id = isset($sign_id) ? (int)$sign_id : 0;

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

//이미지 파일
$sample_sql = "SELECT * FROM a_sign_off_sample WHERE sign_id = '{$sign_id}'";
// echo $sample_sql;
$sample_row = sql_fetch($sample_sql);

$sign_check = "";

if(!$row['sign_off_status']){
    $sign_check = $row['sign_off_mng_id1'];
}else if(!$row['sign_off_status2']){
    $sign_check = $row['sign_off_mng_id2'];
}else if(!$row['sign_off_status3']){
    $sign_check = $row['sign_off_mng_id3'];
}

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql.'<br>';
    echo $sample_sql.'<br>';
    //print_r2($row);
}
?>
<?php if($sample_row['sample_img']){ ?>
<div class="approval_img_wrap">
    <img src="/data/file/signOffSample/<?php echo $sample_row['sample_img']; ?>" alt="" onclick="bigSize('/data/file/signOffSample/<?php echo $sample_row['sample_img']; ?>')">
</div>
<?php } ?>
<div class="btn_fixed_top">
    <a href="./approval_document_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
</div>
<div class="approval_request_sign_box">
    <?php
    //내 사인 있는지 확인
    $signature_check = "SELECT s.*, t.cnt
                FROM (
                    SELECT *
                    FROM a_signature
                    WHERE mb_id = '{$member['mb_id']}'
                    ORDER BY sg_idx DESC
                    LIMIT 1
                ) s
                JOIN (
                    SELECT COUNT(*) AS cnt
                    FROM a_signature
                    WHERE mb_id = '{$member['mb_id']}'
                ) t ON 1";
    $signature_check_row = sql_fetch($signature_check);
    ?>
    <div class="tbl_frm01 tbl_wrap">
    <div class="h2_frm_wraps">
        <h2 class="h2_frm"><?php echo $sign['sign_cate_name']?> 결재내역</h2>
        <div class="btn_wraps">
            <?php if($row['sign_status'] != 'E' && $row['sign_status'] != 'R'){

            if($sign_check == $member['mb_id']){
            ?>
                <button type="button" onclick="singReject();" class="btn btn_01">반려하기</button>
            <?php }?>
            <?php }?>
            <?php if($row['sign_status'] != 'E' && $row['sign_status'] != 'R'){
            if($sign_check == $member['mb_id']){
            ?>
            <button type="button" onclick="singCheck();" class="btn btn_03">승인하기</button>
            <?php }?>
            <?php }?>
        </div>
    </div>
    <?php if($signature_check_row['cnt'] > 0){?>
    <input type="hidden" name="approval_signature_temp" id="approval_signature_temp" value="<?php echo $signature_check_row['signature_data']; ?>">
    <?php }?>
    <input type="hidden" name="approval_signature" id="approval_signature" value="">
    <input type="hidden" name="approval_cont" id="approval_cont" value="">
    <table>
        <tr>
            <th>
                <?php
                $one_mng = get_manger($row['sign_off_mng_id1']);
                ?>
                1차 결재자 - <?php echo $one_mng['mng_name'].' '.$one_mng['mg_name']; ?>
            </th>
            <td>
                <?php if($row['sign_status'] == 'R' && $row['sign_reject_id'] == $row['sign_off_mng_id1']){?>
                    <p class="red">결재 반려</p>
                <?php }else{?>
                    <?php if($row['sign_off_status']){
                        $sql_sign_off_img = "SELECT soi.*, sig.fil_name FROM a_sign_off_mng_sign as soi
                        LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                        WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}' and sign_mng_data = 'sign_off_mng_id1'";
                        $sign_img_row = sql_fetch($sql_sign_off_img);
                    ?>
                        <button type="button" onclick="signHandler('sign_boxs_imgs1');" disabled class="btn btn_02">서명완료</button>
                    <?php }else{ ?>
                        <?php if(!$row['sign_off_status'] && $row['sign_off_mng_id1'] == $member['mb_id']){?>
                            <?php if($signature_check_row['cnt'] > 0){?>
                                <button type="button" onclick="signHandler('sign_boxs_imgs1', 'sign_off_mng_id1');" class="btn btn_03">다시 서명하기</button>
                                <button type="button" onclick="signLoad('<?php echo $member['mb_id']; ?>', 'sign_boxs_imgs1', 'sign_off_mng_id1')" class="btn btn_03">서명 불러오기</button>
                            <?php }else{?>
                            <button type="button" onclick="signHandler('sign_boxs_imgs1', 'sign_off_mng_id1');" class="btn btn_03">서명하기</button>
                            <?php }?>
                        <?php }?>
                    <?php }?>
                    <div class="sign_boxs_img sign_boxs_imgs1">
                        <?php if($sign_img_row){?>
                        <img src="/data/file/approval/<?php echo $sign_img_row['fil_name']; ?>" alt="">
                        <?php }?>
                    </div>
                <?php }?>
            </td>
        </tr>
        <?php if($row['sign_off_mng_id2'] != ""){?>
        <tr>
            <th>
                <?php
                $two_mng = get_manger($row['sign_off_mng_id2']);
                ?>
                2차 결재자 - <?php echo $two_mng['mng_name'].' '.$two_mng['mg_name']; ?>
            </th>
            <td>
                <?php if($row['sign_status'] == 'R' && $row['sign_reject_id'] == $row['sign_off_mng_id2']){?>
                    <p class="red">결재 반려</p>
                <?php }else{?>

                    <?php if($row['sign_off_status2']){
                        $sql_sign_off_img = "SELECT soi.*, sig.fil_name FROM a_sign_off_mng_sign as soi
                        LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                        WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}' and sign_mng_data = 'sign_off_mng_id2'";
                        $sign_img_row2 = sql_fetch($sql_sign_off_img);
                    ?>
                        <button type="button" onclick="signHandler('sign_boxs_imgs2');" disabled class="btn btn_02">서명완료</button>
                    <?php }else{ ?>
                        <?php if(!$row['sign_off_status2'] && $row['sign_off_status'] && $row['sign_off_mng_id2'] == $member['mb_id']){?>
                            <?php if($signature_check_row['cnt'] > 0){?>
                                <button type="button" onclick="signHandler('sign_boxs_imgs2', 'sign_off_mng_id2');" class="btn btn_03">다시 서명하기</button>
                                <button type="button" onclick="signLoad('<?php echo $member['mb_id']; ?>', 'sign_boxs_imgs2', 'sign_off_mng_id2')" class="btn btn_03">서명 불러오기</button>
                            <?php }else{?>
                                <button type="button" onclick="signHandler('sign_boxs_imgs2', 'sign_off_mng_id2');" class="btn btn_03">서명하기</button>
                            <?php }?>
                        <?php }?>
                    <?php }?>
                    <div class="sign_boxs_img sign_boxs_imgs2">
                        <?php if($sign_img_row2){?>
                        <img src="/data/file/approval/<?php echo $sign_img_row2['fil_name']; ?>" alt="">
                        <?php }?>
                    </div>
                <?php }?>
            </td>
        </tr>
        <?php }?>
        <?php if($row['sign_off_mng_id3'] != ""){?>
        <tr>
            <th>
                <?php
                $three_mng = get_manger($row['sign_off_mng_id3']);
                ?>
                3차 결재자 - <?php echo $three_mng['mng_name'].' '.$three_mng['mg_name']; ?>
            </th>
            <td>
                <?php if($row['sign_status'] == 'R' && $row['sign_reject_id'] == $row['sign_off_mng_id3']){?>
                    <p class="red">결재 반려</p>
                <?php }else{?>
                    <?php if($row['sign_off_status3']){
                        $sql_sign_off_img = "SELECT soi.*, sig.fil_name FROM a_sign_off_mng_sign as soi
                        LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                        WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}' and sign_mng_data = 'sign_off_mng_id3'";
                        $sign_img_row3 = sql_fetch($sql_sign_off_img);
                    ?>
                        <button type="button" onclick="signHandler('sign_boxs_imgs3');" disabled class="btn btn_02">서명완료</button>
                    <?php }else{ ?>
                        <?php if(!$row['sign_off_status3'] && $row['sign_off_status2'] && $row['sign_off_mng_id3'] == $member['mb_id']){?>
                            <?php if($signature_check_row['cnt'] > 0){?>
                                <button type="button" onclick="signHandler('sign_boxs_imgs3', 'sign_off_mng_id3');" class="btn btn_03">다시 서명하기</button>
                                <button type="button" onclick="signLoad('<?php echo $member['mb_id']; ?>', 'sign_boxs_imgs3', 'sign_off_mng_id3')" class="btn btn_03">서명 불러오기</button>
                            <?php }else{?>
                                <button type="button" onclick="signHandler('sign_boxs_imgs3', 'sign_off_mng_id3');" class="btn btn_03">서명하기</button>
                            <?php }?>
                        <?php }?>
                    <?php }?>
                    <div class="sign_boxs_img sign_boxs_imgs3">
                        <?php if($sign_img_row3){?>
                        <img src="/data/file/approval/<?php echo $sign_img_row3['fil_name']; ?>" alt="">
                        <?php }?>
                    </div>
                <?php }?>

            </td>
        </tr>
        <?php }?>
    </table>
    </div>
</div>
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

<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>결재를 반려 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>

<div id="big_size_pop">
    <div class="od_cancel_inner"></div>
	<button type="button" class="big_size_pop_x" onclick="bigSizeOff();">
		<span></span>
		<span></span>
	</button>
	<div class="od_cancel_cont ver2">
		<img src="" id="big_img" alt="확대 보기">
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<script>
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

$(function(){
    $("#wdate, .ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
});

function signLoad(id, ele, approval_cont){
    let approval_signature_temp = $("#approval_signature_temp").val();

    let sendData = {'mb_id': id};

    $.ajax({
        type: "POST",
        url: "/sign_load_ajax.php",
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

                console.log(approval_signature_temp);

                $("#approval_cont").val(approval_cont);

                const sigData = (data && data.data && data.data.signature_data) ? data.data.signature_data : approval_signature_temp;
                $("#approval_signature").val(sigData);

                let imgSRc = "/data/file/approval/" + data.data.fil_name;
                let imgs = `<img src='${imgSRc}' />`;
                $("." + ele).html(imgs);
            }
        }
    });
}

//반려하기
function singReject(){
    if (!confirm("해당 결재내역을 반려 처리 하시겠습니까?")) {
        return false;
    }

    $("#building_info_pop").show();

    let sign_id = "<?php echo $sign_id; ?>";
    let mb_id = "<?php echo $member['mb_id']; ?>";

    let sendData = {'sign_id': sign_id, 'mb_id':mb_id};

    setTimeout(() => {
        $.ajax({
            type: "POST",
            url: "./approval_form_reject.php",
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
                        $("#building_info_pop").hide();
                        window.location.reload();
                    }, 700);
                }
            }
        });
    }, 50);
}

function singCheck(){
    let mb_id = "<?php echo $member['mb_id']; ?>";
    let sign_id = "<?php echo $sign_id; ?>";
    let approval_signature = $("#approval_signature").val();
    let approval_cont = $("#approval_cont").val();

    if(approval_signature == ""){
        alert('서명을 입력해주세요.');
        return false;
    }

    if(approval_cont == ""){
        alert('결재 단계 정보가 없습니다. 다시 서명하기를 눌러주세요.');
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

               // ✅ 수정: 현재 페이지(approval_info.php)로 reload
               setTimeout(() => {
                    location.replace("/adm/approval_info.php?w=u&sign_id=" + sign_id);
                }, 150);
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
                                        <input type="text" name="hp_name[]" class="bansang_ipt ver2">
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
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';
