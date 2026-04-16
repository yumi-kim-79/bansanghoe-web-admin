<?php
$sub_menu = "200900";
require_once './_common.php';
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = "SMS 단체문자 발송";
require_once './admin.head.php';

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);
?>

<style>
.sms_wrap {display:flex;gap:20px;margin-top:10px;}
.sms_left {flex:1;min-width:0;}
.sms_right {width:400px;flex-shrink:0;}
.sms_section {border:1px solid #d6dce7;border-radius:8px;padding:15px;margin-bottom:15px;}
.sms_section h3 {margin:0 0 10px;font-size:14px;font-weight:700;color:#333;}
.recipient_list {max-height:400px;overflow-y:auto;border:1px solid #e4e4e4;border-radius:6px;}
.recipient_item {display:flex;align-items:center;gap:8px;padding:6px 10px;border-bottom:1px solid #f0f0f0;font-size:12px;}
.recipient_item:hover {background:#f8f9fa;}
.recipient_item .r_dong {color:#666;min-width:40px;}
.recipient_item .r_ho {min-width:40px;}
.recipient_item .r_name {min-width:50px;font-weight:600;}
.recipient_item .r_phone {color:#388FCD;}
.sms_counter {text-align:right;font-size:12px;color:#999;margin-top:5px;}
.sms_counter .count_num {font-weight:700;color:#333;}
.sms_actions {display:flex;gap:8px;margin-top:10px;}
.sms_actions .btn {flex:1;text-align:center;}
.copy_result {display:none;color:#388FCD;font-size:12px;margin-top:5px;font-weight:600;}
</style>

<div class="sms_wrap">
    <div class="sms_left">
        <div class="sms_section">
            <h3>발송 대상 선택</h3>
            <div class="serach_box" style="display:flex;gap:10px;flex-wrap:wrap;">
                <select id="sms_post_id" class="bansang_sel" onchange="smsPostChange();">
                    <option value="">지역 선택</option>
                    <?php for($i=0;$pr=sql_fetch_array($post_res);$i++){?>
                    <option value="<?php echo $pr['post_idx'];?>"><?php echo $pr['post_name'];?></option>
                    <?php }?>
                </select>
                <select id="sms_building_id" class="bansang_sel" onchange="smsBuildingChange();">
                    <option value="">단지 선택</option>
                </select>
                <select id="sms_dong_id" class="bansang_sel">
                    <option value="">동 전체</option>
                </select>
                <button type="button" class="bansang_btns ver1" onclick="smsLoadRecipients();">조회</button>
            </div>
        </div>

        <div class="sms_section">
            <h3>발송 대상 목록 <span id="sms_total" style="color:#388FCD;font-weight:400;"></span></h3>
            <div style="margin-bottom:8px;">
                <label style="cursor:pointer;font-size:12px;font-weight:600;">
                    <input type="checkbox" id="sms_chkall" onchange="smsCheckAll(this);"> 전체선택
                </label>
            </div>
            <div class="recipient_list" id="recipient_list">
                <div style="padding:30px;text-align:center;color:#999;">단지를 선택하고 조회해주세요.</div>
            </div>
        </div>
    </div>

    <div class="sms_right">
        <div class="sms_section">
            <h3>문자 내용 작성</h3>
            <textarea id="sms_message" class="bansang_ipt ver2 full ta" style="height:200px;" placeholder="문자 내용을 입력하세요." oninput="smsCountChar();"></textarea>
            <div class="sms_counter">
                <span class="count_num" id="sms_char_count">0</span>자
                (90자 이하: SMS / 초과: LMS)
            </div>
        </div>

        <div class="sms_section">
            <h3>발송 방법</h3>
            <div style="margin-bottom:15px;padding:10px;background:#e8f5e9;border-radius:6px;">
                <b style="font-size:13px;">SMS API 단체 발송</b><br>
                <span style="font-size:11px;color:#666;">버튼 1번 클릭으로 즉시 전체 발송 (건당 SMS 9원 / LMS 29원)</span>
            </div>
            <button type="button" class="btn btn_02" style="width:100%;background:#28a745;border-color:#28a745;" onclick="admSendBulkAPI();">단체 발송</button>

            <div style="margin-top:20px;padding-top:15px;border-top:1px solid #e4e4e4;">
                <b style="font-size:12px;color:#666;">기존 방법 (수동)</b>
                <div class="sms_actions" style="margin-top:8px;">
                    <button type="button" class="btn btn_03" onclick="smsCopyPhones();">전화번호 복사</button>
                    <button type="button" class="btn btn_03" onclick="smsCopyMessage();">문자내용 복사</button>
                </div>
                <div class="copy_result" id="copy_result">✅ 클립보드에 복사되었습니다!</div>
            </div>
        </div>
    </div>
</div>

<script>
var recipientData = [];

function smsPostChange(){
    var postId = $("#sms_post_id").val();
    $("#sms_building_id").html('<option value="">단지 선택</option>');
    $("#sms_dong_id").html('<option value="">동 전체</option>');
    if(!postId) return;
    $.ajax({ url:"./post_building_ajax.php", type:"POST", data:{post_id:postId}, success:function(msg){ $("#sms_building_id").html(msg); } });
}

function smsBuildingChange(){
    var bid = $("#sms_building_id").val();
    $("#sms_dong_id").html('<option value="">동 전체</option>');
    if(!bid) return;
    $.ajax({ url:"./building_dong_ajax.php", type:"POST", data:{building_id:bid, all:'Y'}, success:function(msg){ $("#sms_dong_id").html(msg); } });
}

function smsLoadRecipients(){
    var bid = $("#sms_building_id").val();
    if(!bid){ alert('단지를 선택해주세요.'); return; }
    var did = $("#sms_dong_id").val();
    var url = '/api/sms_recipient_api.php?action=recipients&building_id=' + bid;
    if(did && did != '-1' && did != '') url += '&dong_id=' + did;

    $.ajax({
        url: url,
        dataType: 'json',
        success: function(data){
            if(!data.success){ alert(data.msg || '조회 실패'); return; }
            recipientData = data.detail_list;
            renderRecipients();
        },
        error: function(xhr){
            console.log('API error:', xhr.status, xhr.responseText);
            alert('조회 중 오류가 발생했습니다. (코드: ' + xhr.status + ')');
        }
    });
}

function renderRecipients(){
    var html = '';
    recipientData.forEach(function(r){
        html += '<label class="recipient_item"><input type="checkbox" class="sms_chk" value="' + r.phone + '" checked>'
            + '<span class="r_dong">' + r.dong_id + '</span>'
            + '<span class="r_ho">' + r.ho_name + '호</span>'
            + '<span class="r_name">' + r.name + '</span>'
            + '<span class="r_phone">' + r.phone + '</span></label>';
    });
    if(!html) html = '<div style="padding:30px;text-align:center;color:#999;">조회된 대상이 없습니다.</div>';
    $("#recipient_list").html(html);
    $("#sms_chkall").prop("checked", true);
    $("#sms_total").text("(" + recipientData.length + "명)");
}

function smsCheckAll(src){
    $(".sms_chk").prop("checked", src.checked);
}

function smsCountChar(){
    var len = $("#sms_message").val().length;
    $("#sms_char_count").text(len);
}

function getSelectedPhones(){
    var phones = [];
    $(".sms_chk:checked").each(function(){ phones.push($(this).val()); });
    return [...new Set(phones)]; // 중복 제거
}

function smsCopyPhones(){
    var phones = getSelectedPhones();
    if(phones.length == 0){ alert('발송 대상을 선택해주세요.'); return; }
    navigator.clipboard.writeText(phones.join(',')).then(function(){
        $("#copy_result").text("✅ " + phones.length + "명 전화번호 복사됨!").fadeIn().delay(2000).fadeOut();
    });
}

function smsCopyMessage(){
    var msg = $("#sms_message").val();
    if(!msg){ alert('문자 내용을 입력해주세요.'); return; }
    navigator.clipboard.writeText(msg).then(function(){
        $("#copy_result").text("✅ 문자 내용 복사됨!").fadeIn().delay(2000).fadeOut();
    });
}

function admSendBulkAPI(){
    var phones = getSelectedPhones();
    var msg = $("#sms_message").val().trim();
    if(phones.length == 0){ alert('발송 대상을 선택해주세요.'); return; }
    if(!msg){ alert('문자 내용을 입력해주세요.'); return; }

    var msgLen = msg.length;
    var costPerMsg = (msgLen <= 45) ? 9 : 29;
    var msgType = (msgLen <= 45) ? 'SMS' : 'LMS';
    var totalCost = phones.length * costPerMsg;

    if(!confirm(phones.length + '명에게 ' + msgType + ' 단체 발송합니다.\n\n예상 비용: ' + totalCost.toLocaleString() + '원\n(건당 ' + costPerMsg + '원 x ' + phones.length + '건)\n\n계속하시겠습니까?')){
        return;
    }

    $("body").append('<div id="sms_api_loading" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);z-index:9999;display:flex;align-items:center;justify-content:center;"><div style="background:#fff;padding:30px;border-radius:10px;text-align:center;"><div style="font-size:16px;margin-bottom:10px;font-weight:600;">발송 중...</div><div style="font-size:13px;color:#666;">잠시만 기다려주세요</div></div></div>');

    $.ajax({
        url: '/api/ncloud_sms_send.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            recipients: phones,
            message: msg,
            building_id: $("#sms_building_id").val() || 0
        }),
        dataType: 'json',
        timeout: 30000,
        success: function(data){
            $("#sms_api_loading").remove();
            if(data.success){
                alert('발송 완료!\n\n성공: ' + data.success_count + '건\n실패: ' + data.fail_count + '건\n비용: ' + (data.cost || 0).toLocaleString() + '원\n유형: ' + (data.type || 'SMS'));
            } else {
                alert('발송 실패\n\n' + (data.message || '알 수 없는 오류'));
            }
        },
        error: function(xhr){
            $("#sms_api_loading").remove();
            alert('발송 중 오류가 발생했습니다.\n(코드: ' + xhr.status + ')');
        }
    });
}
</script>

<?php require_once './admin.tail.php'; ?>
