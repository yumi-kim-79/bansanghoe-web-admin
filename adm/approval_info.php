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
                    <div class="sign_boxs_img sign_boxs_imgs1" style="position:relative;">
                        <?php if($sign_img_row){?>
                        <img src="/data/file/approval/<?php echo $sign_img_row['fil_name']; ?>" alt="">
                        <?php if(!empty($sign_img_row['created_at'])): ?>
                        <span class="sign_timestamp"><?php echo date('y.m.d H:i', strtotime($sign_img_row['created_at'])); ?></span>
                        <?php endif; ?>
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
                    <div class="sign_boxs_img sign_boxs_imgs2" style="position:relative;">
                        <?php if($sign_img_row2){?>
                        <img src="/data/file/approval/<?php echo $sign_img_row2['fil_name']; ?>" alt="">
                        <?php if(!empty($sign_img_row2['created_at'])): ?>
                        <span class="sign_timestamp"><?php echo date('y.m.d H:i', strtotime($sign_img_row2['created_at'])); ?></span>
                        <?php endif; ?>
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
                    <div class="sign_boxs_img sign_boxs_imgs3" style="position:relative;">
                        <?php if($sign_img_row3){?>
                        <img src="/data/file/approval/<?php echo $sign_img_row3['fil_name']; ?>" alt="">
                        <?php if(!empty($sign_img_row3['created_at'])): ?>
                        <span class="sign_timestamp"><?php echo date('y.m.d H:i', strtotime($sign_img_row3['created_at'])); ?></span>
                        <?php endif; ?>
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
/* ─────────────────────────────────────────────────────────────
 * ★결재 버튼이 "눌러도 아무 반응 없음" 문제 대응 (2026-08-25)
 *
 *   증상: 상세페이지에서 [서명 불러오기] [반려하기] [승인하기] 만 무반응.
 *        [다시 서명하기] 는 정상. 특정 PC/계정에서만. 재부팅하면 정상.
 *
 *   원인이 될 수 있는 경로가 세 곳이었고, 모두 **화면에 아무것도 남기지 않고 끝난다**.
 *     ① 브라우저가 confirm/alert 를 차단한 경우
 *        (크롬에서 "이 페이지에 추가 대화상자를 표시하지 않음" 을 체크하면
 *         이후 confirm() 은 사용자에게 보이지 않고 즉시 false 를 돌려준다)
 *        → `if(!confirm(...)) return false;` 가 조용히 종료된다.
 *        → [다시 서명하기] 만 confirm/alert 를 안 써서 유일하게 동작했다.
 *        → 브라우저를 완전히 종료(재부팅)하면 해제되므로 "재부팅하면 정상" 과 맞는다.
 *     ② ajax 에 error 핸들러가 없었다.
 *        세션 만료 등으로 응답이 JSON 이 아니면 jQuery 는 error 로 빠지는데,
 *        error 가 없으니 아무 일도 일어나지 않는다.
 *     ③ 반려하기는 "반려 중입니다" 오버레이를 먼저 띄우는데,
 *        실패 시 이를 내리는 코드가 없어 화면이 멈춘 것처럼 보였다.
 *
 *   → 아래 도우미로 **대화상자가 막혀도 항상 화면에 메시지가 보이도록** 바꾼다.
 * ───────────────────────────────────────────────────────────── */

/** 화면 상단 배너 안내 — alert 과 달리 브라우저가 막을 수 없다 */
function apNotice(msg, type){
    let box = document.getElementById('ap_notice_box');
    if(!box){
        box = document.createElement('div');
        box.id = 'ap_notice_box';
        box.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:99999;'
            + 'padding:14px 48px 14px 18px;font-size:14px;line-height:1.6;'
            + 'box-shadow:0 2px 8px rgba(0,0,0,.15);white-space:pre-line;display:none;';
        const x = document.createElement('button');
        x.type = 'button';
        x.textContent = '✕';
        x.style.cssText = 'position:absolute;top:8px;right:12px;border:0;background:none;'
            + 'font-size:18px;cursor:pointer;color:inherit;';
        x.onclick = function(){ box.style.display = 'none'; };
        box.appendChild(document.createElement('span')).id = 'ap_notice_msg';
        box.appendChild(x);
        document.body.appendChild(box);
    }
    const err = (type === 'error');
    box.style.background = err ? '#fdecea' : '#e8f4ff';
    box.style.color      = err ? '#a3252c' : '#12507b';
    box.style.borderBottom = '1px solid ' + (err ? '#f5c6cb' : '#b6ddff');
    document.getElementById('ap_notice_msg').textContent = msg;
    box.style.display = 'block';
    window.scrollTo({top: 0, behavior: 'smooth'});
    console.log('[결재]', msg);
}

/** confirm 대체 — 브라우저가 대화상자를 막고 있으면 그 사실을 알려준다 */
function apConfirm(msg){
    const t0 = Date.now();
    let ok = false;
    try { ok = window.confirm(msg); } catch(e) { ok = false; }

    // 사람이 읽고 누르면 최소 수백 ms 는 걸린다.
    // 즉시 false 가 돌아왔다면 대화상자가 뜨지 않은 것이다.
    if(!ok && (Date.now() - t0) < 60){
        apNotice(
            '브라우저가 이 페이지의 확인창을 차단하고 있어 진행할 수 없습니다.\n'
          + '페이지를 새로고침(F5)한 뒤 다시 시도해 주세요. '
          + '그래도 같으면 브라우저를 완전히 닫았다가 다시 열어주세요.', 'error');
        return false;
    }
    return ok;
}

/** ajax 실패 공통 처리 — 예전에는 error 핸들러가 없어 아무 반응이 없었다 */
function apAjaxFail(xhr, status, err){
    $("#building_info_pop").hide();
    let msg = '요청을 처리하지 못했습니다.';
    if(xhr && xhr.status === 0){
        msg += '\n네트워크 연결을 확인해 주세요.';
    }else if(xhr && (xhr.status === 401 || xhr.status === 403)){
        msg += '\n로그인이 풀렸을 수 있습니다. 다시 로그인해 주세요.';
    }else if(status === 'parsererror'){
        msg += '\n로그인이 풀렸을 수 있습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.';
    }else if(xhr){
        msg += '\n(오류코드 ' + xhr.status + ')';
    }
    apNotice(msg, 'error');
    console.log('[결재] ajax 실패', status, err, xhr && xhr.responseText);
}

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

// [항목3] ESC 키로 확대팝업 닫기 (X버튼·배경클릭과 함께 3중 닫기)
$(document).on('keydown', function(e){
	if(e.key === 'Escape' && $("#big_size_pop").is(":visible")){
		bigSizeOff();
	}
});

$(function(){
    $("#wdate, .ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
});

function signLoad(id, ele, approval_cont){
    if(!apConfirm("저장된 서명을 불러오시겠습니까?")) return false;
    let approval_signature_temp = $("#approval_signature_temp").val();

    let sendData = {'mb_id': id};

    $.ajax({
        type: "POST",
        url: "/sign_load_ajax.php",
        data: sendData,
        cache: false,
        async: true,          // ★동기 요청은 브라우저를 멈추게 하고 크롬이 경고한다(2026-08)
        dataType: "json",
        error: apAjaxFail,    // ★없으면 실패 시 아무 반응이 없다
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) {
                apNotice(data.msg, 'error');
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
    if (!apConfirm("해당 결재내역을 반려 처리 하시겠습니까?")) {
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
            async: true,          // ★동기 요청 제거 (2026-08)
            dataType: "json",
            error: apAjaxFail,    // ★실패 시 "반려 중입니다" 오버레이가 안 내려가 멈춘 것처럼 보였다
            success: function(data) {
                console.log('data:::', data);

                if(data.result == false) {
                    $("#building_info_pop").hide();
                    apNotice(data.msg, 'error');
                    return false;
                }else{

                $("#building_info_pop").hide();
                apNotice(data.msg);

                setTimeout(() => {
                        window.location.reload();
                    }, 1500);   // 배너를 읽을 시간 확보 (alert 이 아니라 안 막히므로)
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

    // ★검증 실패를 alert 으로만 알리면, 브라우저가 대화상자를 막았을 때
    //   사용자에게는 "버튼이 안 눌린다"로 보인다. 반드시 화면에 남긴다.
    if(approval_signature == ""){
        apNotice('서명이 없습니다. [다시 서명하기] 또는 [서명 불러오기] 로 먼저 서명해 주세요.', 'error');
        return false;
    }

    if(approval_cont == ""){
        apNotice('결재 단계 정보가 없습니다. [다시 서명하기] 를 눌러 서명한 뒤 승인해 주세요.', 'error');
        return false;
    }

    let sendData = {'mb_id': mb_id, "sign_id":sign_id, "signdata":approval_signature, "data":approval_cont};

    $.ajax({
        type: "POST",
        url: "./approval_form_check.php",
        data: sendData,
        cache: false,
        async: true,          // ★동기 요청 제거 (2026-08)
        dataType: "json",
        error: apAjaxFail,    // ★없으면 실패 시 아무 반응이 없다
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) {
                apNotice(data.msg, 'error');
                return false;
            }else{

               apNotice(data.msg);

               // ✅ 수정: 현재 페이지(approval_info.php)로 reload
               setTimeout(() => {
                    location.replace("/holiday_request_sample.php?sign_id=" + sign_id + "&mem_type=sign_user");
                }, 1500);   // 배너를 읽을 시간 확보
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
        apNotice('서명을 입력하세요.', 'error');
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
                                            <option value="1.5">1.5일</option>
                                            <option value="2">2일</option>
                                            <option value="2.5">2.5일</option>
                                            <option value="3">3일</option>
                                            <option value="3.5">3.5일</option>
                                            <option value="4">4일</option>
                                            <option value="4.5">4.5일</option>
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
                                        <input type="text" name="hp_date[]" class="bansang_ipt ver2 ipt_date hp_sdate" required>
                                    </div>
                                </div>
                                <div class="paid_holiday_info_box">
                                    <div class="paid_holiday_info_label">종료일</div>
                                    <div class="paid_holiday_info_ipt">
                                        <input type="text" name="hp_edate[]" class="bansang_ipt ver2 ipt_date hp_edate">
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
// [연차 종료일] 공휴일 PHP→JS 주입 (올해+내년)
$_hol = [];
$_hy = date('Y');
$_hr = sql_query("SELECT holiday_date FROM a_holiday WHERE holiday_date >= '{$_hy}-01-01' AND holiday_date <= '".($_hy+1)."-12-31'");
while($_hrow = sql_fetch_array($_hr)) $_hol[] = $_hrow['holiday_date'];
?>
<script>
window.bansangHolidays = <?php echo json_encode($_hol); ?>;
(function($){
    const HALF_VALUES = ['am_half','pm_half','halfhalf','half_half'];
    function fmtYmd(d){ const y=d.getFullYear(), m=('0'+(d.getMonth()+1)).slice(-2), dd=('0'+d.getDate()).slice(-2); return y+'-'+m+'-'+dd; }
    function isBusinessDay(s){ if(!s) return false; const d=new Date(s), g=d.getDay(); if(g===0||g===6) return false; return !(window.bansangHolidays||[]).includes(s); }
    function countBusinessDays(s,e){ let c=0,d=new Date(s); const end=new Date(e); while(d<=end){ if(isBusinessDay(fmtYmd(d))) c++; d.setDate(d.getDate()+1); } return c; }
    function addBusinessDays(s,n){ let d=new Date(s),c=0; while(true){ if(isBusinessDay(fmtYmd(d))) c++; if(c>=n) break; d.setDate(d.getDate()+1); } return fmtYmd(d); }
    let _syncing=false;
    function applyDayChange($day,$sd,$ed){
        const v=$day.val();
        if(v==='1' || HALF_VALUES.includes(v)){ $ed.prop('readonly',true).addClass('disabled'); _syncing=true; if($sd.val()) $ed.val($sd.val()); _syncing=false; }
        else if(['2','3','4','5'].includes(v)){ $ed.prop('readonly',false).removeClass('disabled'); if($sd.val()){ _syncing=true; $ed.val(addBusinessDays($sd.val(),parseInt(v))); _syncing=false; } }
        else { $ed.prop('readonly',false).removeClass('disabled'); }
    }
    function applySdateChange($day,$sd,$ed){
        if(_syncing) return; const v=$day.val();
        if(v==='1' || HALF_VALUES.includes(v)){ _syncing=true; $ed.val($sd.val()); _syncing=false; }
        else if(['2','3','4','5'].includes(v) && $sd.val()){ _syncing=true; $ed.val(addBusinessDays($sd.val(),parseInt(v))); _syncing=false; }
    }
    function applyEdateChange($day,$sd,$ed){
        if(_syncing) return; const v=$day.val();
        if(['2','3','4','5'].includes(v) && $sd.val() && $ed.val()){
            const days=countBusinessDays($sd.val(),$ed.val());
            if(days>=2 && days<=5){ _syncing=true; $day.val(String(days)); _syncing=false; }
            else console.warn('평일 카운트 select 범위(2-5) 초과:', days);
        }
    }
    var ROW='.paid_holiday_request_wrapper, .holiday_pay_wrap';
    $(document).on('change','select[name="hp_day[]"]',function(){ var $r=$(this).closest(ROW); applyDayChange($(this), $r.find('input[name="hp_date[]"]'), $r.find('input[name="hp_edate[]"]')); });
    $(document).on('change','input[name="hp_date[]"]',function(){ var $r=$(this).closest(ROW); applySdateChange($r.find('select[name="hp_day[]"]'), $(this), $r.find('input[name="hp_edate[]"]')); });
    $(document).on('change','input[name="hp_edate[]"]',function(){ var $r=$(this).closest(ROW); applyEdateChange($r.find('select[name="hp_day[]"]'), $r.find('input[name="hp_date[]"]'), $(this)); });
    $(document).on('change','#holiday_day',function(){ applyDayChange($(this),$('#holiday_date'),$('#holiday_edate')); });
    $(document).on('change','#holiday_date',function(){ applySdateChange($('#holiday_day'),$(this),$('#holiday_edate')); });
    $(document).on('change','#holiday_edate',function(){ applyEdateChange($('#holiday_day'),$('#holiday_date'),$(this)); });
    $(function(){
        $('select[name="hp_day[]"]').each(function(){ var $r=$(this).closest(ROW); applyDayChange($(this), $r.find('input[name="hp_date[]"]'), $r.find('input[name="hp_edate[]"]')); });
        if($('#holiday_day').length) applyDayChange($('#holiday_day'),$('#holiday_date'),$('#holiday_edate'));
    });
})(jQuery);
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';
