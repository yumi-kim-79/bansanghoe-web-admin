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
.recipient_item .r_bname {color:#388FCD;font-weight:600;min-width:60px;}
.recipient_item .r_dong {color:#666;min-width:40px;}
.recipient_item .r_ho {min-width:40px;}
.recipient_item .r_name {min-width:50px;font-weight:600;}
.recipient_item .r_phone {color:#388FCD;}
.sms_counter {text-align:right;font-size:12px;color:#999;margin-top:5px;}
.sms_counter .count_num {font-weight:700;color:#333;}
.sms_actions {display:flex;gap:8px;margin-top:10px;}
.sms_actions .btn {flex:1;text-align:center;}
.copy_result {display:none;color:#388FCD;font-size:12px;margin-top:5px;font-weight:600;}

/* 검색 */
.sms_search_wrap {position:relative;}
.sms_search_wrap input {width:100%;box-sizing:border-box;padding-left:32px;}
.sms_search_wrap .sms_sch_icon {position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#999;font-size:14px;pointer-events:none;}
.sms_search_wrap .sms_sch_clear {position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;color:#999;font-size:16px;cursor:pointer;display:none;padding:2px 4px;}
.sms_sch_highlight {background:#fff3cd;padding:0 1px;border-radius:2px;}

/* 단지 카드 */
.sms_bld_list {max-height:400px;overflow-y:auto;}
.sms_bld_card {display:flex;justify-content:space-between;align-items:center;padding:12px 15px;border:1px solid #e4e4e4;border-radius:8px;margin-bottom:8px;cursor:pointer;transition:all 0.2s;}
.sms_bld_card:hover {border-color:#388FCD;background:#f0f7ff;}
.sms_bld_info {flex:1;}
.sms_bld_name {font-size:14px;font-weight:700;color:#333;}
.sms_bld_post {font-size:11px;color:#666;margin-top:2px;}
.sms_bld_count {font-size:12px;color:#388FCD;font-weight:600;white-space:nowrap;}

/* 선택 단지 헤더 */
.sms_selected_header {display:flex;align-items:center;gap:10px;margin-bottom:10px;}
.sms_back_btn {background:none;border:1px solid #d6dce7;border-radius:6px;padding:6px 12px;font-size:12px;cursor:pointer;color:#666;font-weight:600;}
.sms_back_btn:hover {background:#f8f9fa;}
.sms_selected_name {font-size:15px;font-weight:700;color:#388FCD;}

/* 입주민 필터 */
.sms_recipient_filter {position:relative;margin-bottom:8px;}
.sms_recipient_filter input {width:100%;box-sizing:border-box;padding-left:32px;}
.sms_recipient_filter .sms_sch_icon {position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#999;font-size:14px;pointer-events:none;}
</style>

<div class="sms_wrap">
    <div class="sms_left">
        <!-- 1단계: 단지 검색 -->
        <div class="sms_section" id="sms_step1">
            <h3>1단계: 단지 선택</h3>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
                <select id="sms_post_id" class="bansang_sel" onchange="smsPostChange();">
                    <option value="">지역 선택</option>
                    <?php for($i=0;$pr=sql_fetch_array($post_res);$i++){?>
                    <option value="<?php echo $pr['post_idx'];?>"><?php echo $pr['post_name'];?></option>
                    <?php }?>
                </select>
                <select id="sms_building_id" class="bansang_sel" style="display:none;">
                    <option value="">단지 선택</option>
                </select>
            </div>
            <div class="sms_search_wrap">
                <span class="sms_sch_icon">&#128269;</span>
                <input type="text" id="sms_bld_search" class="bansang_ipt ver2" placeholder="단지명 검색..." oninput="smsBldSearch();">
                <button type="button" class="sms_sch_clear" id="sms_bld_clear" onclick="smsBldClearSearch();">&times;</button>
            </div>
            <div class="sms_bld_list" id="sms_bld_list" style="margin-top:10px;">
                <div style="padding:20px;text-align:center;color:#999;font-size:13px;">단지명을 검색하거나 지역을 선택하세요.</div>
            </div>
        </div>

        <!-- 2단계: 입주민 목록 -->
        <div class="sms_section" id="sms_step2" style="display:none;">
            <div class="sms_selected_header">
                <button type="button" class="sms_back_btn" onclick="smsBackToStep1();">&larr; 단지 다시 선택</button>
                <span class="sms_selected_name" id="sms_selected_bld_name"></span>
            </div>
            <div style="display:flex;gap:10px;margin-bottom:10px;">
                <select id="sms_dong_id" class="bansang_sel">
                    <option value="">동 전체</option>
                </select>
                <button type="button" class="bansang_btns ver1" onclick="smsLoadRecipients();">조회</button>
            </div>
            <h3>발송 대상 목록 <span id="sms_total" style="color:#388FCD;font-weight:400;"></span></h3>
            <div class="sms_recipient_filter">
                <span class="sms_sch_icon">&#128269;</span>
                <input type="text" id="sms_rcpt_search" class="bansang_ipt ver2" placeholder="이름, 전화번호, 동호수 검색..." oninput="smsRcptFilter();">
            </div>
            <div style="margin-bottom:8px;">
                <label style="cursor:pointer;font-size:12px;font-weight:600;">
                    <input type="checkbox" id="sms_chkall" onchange="smsCheckAll(this);"> 전체선택
                </label>
            </div>
            <div class="recipient_list" id="recipient_list">
                <div style="padding:30px;text-align:center;color:#999;">조회 버튼을 눌러주세요.</div>
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
                <div class="copy_result" id="copy_result"></div>
            </div>
        </div>
    </div>
</div>

<script>
var recipientData = [];
var selectedBuildingId = '';
var selectedBuildingName = '';

/* ===== 1단계: 단지 검색 ===== */
function smsPostChange(){
    var postId = $("#sms_post_id").val();
    if(!postId){ smsBldSearch(); return; }
    // 지역 선택 시 해당 지역 단지 목록 로드
    $.ajax({
        url: '/api/sms_recipient_api.php?action=buildings&keyword=',
        dataType: 'json',
        success: function(data){
            if(!data.success) return;
            // 지역별 필터는 서버에서 안 하므로 클라이언트에서 필터
            // (API에 post_id 파라미터 없으므로 전체 로드 후 필터)
            smsBldRender(data.buildings);
        }
    });
}

var smsBldTimer = null;
function smsBldSearch(){
    var keyword = $("#sms_bld_search").val().trim();
    $("#sms_bld_clear").toggle(keyword.length > 0);
    clearTimeout(smsBldTimer);

    if(keyword.length === 0 && !$("#sms_post_id").val()){
        $("#sms_bld_list").html('<div style="padding:20px;text-align:center;color:#999;font-size:13px;">단지명을 검색하거나 지역을 선택하세요.</div>');
        return;
    }

    smsBldTimer = setTimeout(function(){
        $.ajax({
            url: '/api/sms_recipient_api.php?action=buildings&keyword=' + encodeURIComponent(keyword),
            dataType: 'json',
            success: function(data){
                if(!data.success) return;
                smsBldRender(data.buildings, keyword);
            }
        });
    }, 300);
}

function smsBldRender(buildings, keyword){
    keyword = (keyword || '').trim().toLowerCase();
    var html = '';
    buildings.forEach(function(b){
        var bname = b.building_name;
        var pname = b.post_name || '';
        if(keyword){
            bname = smsHL(bname, keyword);
            pname = smsHL(pname, keyword);
        }
        html += '<div class="sms_bld_card" onclick="smsSelectBuilding(' + b.building_id + ',\'' + b.building_name.replace(/'/g, "\\'") + '\',\'' + (b.post_name || '').replace(/'/g, "\\'") + '\');">'
            + '<div class="sms_bld_info"><div class="sms_bld_name">' + bname + '</div><div class="sms_bld_post">' + pname + '</div></div>'
            + '<div class="sms_bld_count">' + b.ho_count + '명</div>'
            + '</div>';
    });
    if(!html) html = '<div style="padding:20px;text-align:center;color:#999;font-size:13px;">' + (keyword ? '검색 결과가 없습니다.' : '단지가 없습니다.') + '</div>';
    $("#sms_bld_list").html(html);
}

function smsBldClearSearch(){
    $("#sms_bld_search").val('');
    $("#sms_bld_clear").hide();
    $("#sms_bld_list").html('<div style="padding:20px;text-align:center;color:#999;font-size:13px;">단지명을 검색하거나 지역을 선택하세요.</div>');
}

/* ===== 단지 선택 → 2단계 전환 ===== */
function smsSelectBuilding(bid, bname, pname){
    selectedBuildingId = bid;
    selectedBuildingName = bname;
    $("#sms_selected_bld_name").text((pname ? pname + ' ' : '') + bname);

    // 동 목록 로드
    $("#sms_dong_id").html('<option value="">동 전체</option>');
    $.ajax({ url:"./building_dong_ajax.php", type:"POST", data:{building_id:bid, all:'Y'}, success:function(msg){ $("#sms_dong_id").html(msg); } });

    // UI 전환
    $("#sms_step1").hide();
    $("#sms_step2").show();

    // 자동 조회
    smsLoadRecipients();
}

function smsBackToStep1(){
    selectedBuildingId = '';
    selectedBuildingName = '';
    recipientData = [];
    $("#sms_step2").hide();
    $("#sms_step1").show();
    $("#sms_rcpt_search").val('');
    $("#sms_total").text('');
    $("#recipient_list").html('<div style="padding:30px;text-align:center;color:#999;">조회 버튼을 눌러주세요.</div>');
}

/* ===== 2단계: 입주민 목록 ===== */
function smsLoadRecipients(){
    if(!selectedBuildingId){ alert('단지를 선택해주세요.'); return; }
    var did = $("#sms_dong_id").val();
    var url = '/api/sms_recipient_api.php?action=recipients&building_id=' + selectedBuildingId;
    if(did && did != '-1' && did != '') url += '&dong_id=' + did;

    $.ajax({
        url: url,
        dataType: 'json',
        success: function(data){
            if(!data.success){ alert(data.msg || '조회 실패'); return; }
            recipientData = data.detail_list;
            $("#sms_rcpt_search").val('');
            renderRecipients();
        },
        error: function(xhr){
            alert('조회 오류 (코드: ' + xhr.status + ')');
        }
    });
}

function renderRecipients(keyword){
    keyword = (keyword || '').trim().toLowerCase();
    var filtered = recipientData;
    if(keyword){
        filtered = recipientData.filter(function(r){
            return (r.name && r.name.toLowerCase().indexOf(keyword) > -1)
                || (r.phone && r.phone.indexOf(keyword) > -1)
                || (String(r.dong_id).indexOf(keyword) > -1)
                || (r.ho_name && r.ho_name.indexOf(keyword) > -1);
        });
    }

    var html = '';
    filtered.forEach(function(r){
        var dong = String(r.dong_id);
        var ho = r.ho_name;
        var name = r.name;
        var phone = r.phone;
        if(keyword){
            dong = smsHL(dong, keyword);
            ho = smsHL(ho, keyword);
            name = smsHL(name, keyword);
            phone = smsHL(phone, keyword);
        }
        html += '<label class="recipient_item"><input type="checkbox" class="sms_chk" value="' + r.phone + '" checked>'
            + '<span class="r_dong">' + dong + '</span>'
            + '<span class="r_ho">' + ho + '호</span>'
            + '<span class="r_name">' + name + '</span>'
            + '<span class="r_phone">' + phone + '</span></label>';
    });
    if(!html) html = '<div style="padding:30px;text-align:center;color:#999;">' + (keyword ? '검색 결과가 없습니다.' : '조회된 대상이 없습니다.') + '</div>';
    $("#recipient_list").html(html);
    $("#sms_chkall").prop("checked", true);

    if(keyword){
        $("#sms_total").text("(" + filtered.length + "/" + recipientData.length + "명)");
    } else {
        $("#sms_total").text("(" + recipientData.length + "명)");
    }
}

function smsHL(text, kw){
    if(!text || !kw) return text;
    var idx = text.toLowerCase().indexOf(kw.toLowerCase());
    if(idx === -1) return text;
    return text.substring(0, idx) + '<span class="sms_sch_highlight">' + text.substring(idx, idx + kw.length) + '</span>' + text.substring(idx + kw.length);
}

var smsRcptTimer = null;
function smsRcptFilter(){
    clearTimeout(smsRcptTimer);
    smsRcptTimer = setTimeout(function(){
        renderRecipients($("#sms_rcpt_search").val());
    }, 200);
}

/* ===== 공통 ===== */
function smsCheckAll(src){ $(".sms_chk:visible").prop("checked", src.checked); }
function smsCountChar(){ $("#sms_char_count").text($("#sms_message").val().length); }

function getSelectedPhones(){
    var phones = [];
    $(".sms_chk:checked").each(function(){ phones.push($(this).val()); });
    return [...new Set(phones)];
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
        data: JSON.stringify({ recipients: phones, message: msg, building_id: selectedBuildingId || 0 }),
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

/* ===== 초기 로드: 전체 단지 목록 ===== */
$(function(){
    $.ajax({
        url: '/api/sms_recipient_api.php?action=buildings&keyword=',
        dataType: 'json',
        success: function(data){
            if(data.success) smsBldRender(data.buildings);
        }
    });
});
</script>

<?php require_once './admin.tail.php'; ?>
