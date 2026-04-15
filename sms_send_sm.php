<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$mng_infos = get_manger($member['mb_id']);

// 담당 단지 또는 전체
if($member['mb_level'] >= 10){
    $building_sql = "SELECT b.building_id, b.building_name, p.post_name FROM a_building AS b
                     LEFT JOIN a_post_addr AS p ON b.post_id = p.post_idx
                     WHERE b.is_del = 0 AND b.is_use = 1 ORDER BY p.post_name ASC, b.building_name ASC";
} else {
    $building_sql = "SELECT b.building_id, b.building_name, p.post_name FROM a_mng_building AS mb
                     LEFT JOIN a_building AS b ON mb.building_id = b.building_id
                     LEFT JOIN a_post_addr AS p ON b.post_id = p.post_idx
                     WHERE mb.mb_id = '{$member['mb_id']}' AND mb.is_del = 0 AND b.is_use = 1
                     ORDER BY p.post_name ASC, b.building_name ASC";
}
$building_res = sql_query($building_sql);

$building_list = [];
while($br = sql_fetch_array($building_res)){
    $building_list[] = [
        'id' => $br['building_id'],
        'name' => $br['building_name'],
        'post' => $br['post_name'],
        'label' => $br['post_name'] . ' ' . $br['building_name']
    ];
}
$building_json = json_encode($building_list, JSON_UNESCAPED_UNICODE);

$param_building_id = isset($_GET['building_id']) ? intval($_GET['building_id']) : 0;
?>

<style>
.sms_info_box { background:#f0f7ff; border-radius:8px; padding:12px; margin-bottom:15px; font-size:12px; color:#555; line-height:1.8; }
.sms_info_box b { color:#333; }

/* 프로그레스 */
.sms_progress_bar { margin-top:15px; }
.sms_progress_header { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; }
.sms_progress_label { font-size:13px; font-weight:600; color:#333; }
.sms_progress_count { font-size:13px; font-weight:700; color:#388FCD; }
.sms_progress_track { height:8px; background:#e4e4e4; border-radius:4px; overflow:hidden; }
.sms_progress_fill { height:100%; background:#388FCD; border-radius:4px; transition:width 0.3s; }

/* 현재 발송 대상 */
.sms_current_target { margin-top:10px; padding:12px; background:#d4edda; border-radius:8px; font-size:14px; color:#155724; text-align:center; display:none; }
.sms_current_target b { font-size:15px; }

/* 발송 안내 */
.sms_sending_guide { margin-top:8px; padding:10px; background:#fff3cd; border-radius:6px; font-size:12px; color:#856404; text-align:center; font-weight:600; display:none; }

/* 중단 버튼 */
.sms_stop_btn { display:none; margin-top:10px; width:100%; padding:12px; background:#dc3545; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; }

/* 하이라이트 */
.sm_highlight { background-color:#fff3cd !important; border-left:3px solid #ffc107; transition:background 0.3s; }

/* 단지 검색 자동완성 */
.sm_sch_wrap { position:relative; }
.sm_sch_input { width:100%; box-sizing:border-box; }
.sm_sch_clear { position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; font-size:18px; color:#999; cursor:pointer; display:none; padding:4px; }
.sm_sch_dropdown { position:absolute; left:0; right:0; top:100%; z-index:100; background:#fff; border:1px solid #e4e4e4; border-top:none; border-radius:0 0 8px 8px; max-height:200px; overflow-y:auto; display:none; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.sm_sch_item { padding:10px 12px; font-size:13px; cursor:pointer; border-bottom:1px solid #f5f5f5; }
.sm_sch_item:last-child { border-bottom:none; }
.sm_sch_item:hover, .sm_sch_item.active { background:#f0f7ff; }
.sm_sch_item .sch_post { color:#388FCD; font-size:11px; margin-right:4px; }
.sm_sch_item .sch_name { font-weight:600; }
.sm_sch_empty { padding:15px; text-align:center; color:#999; font-size:13px; }
.sm_selected_badge { display:inline-flex; align-items:center; gap:6px; background:#388FCD; color:#fff; padding:6px 10px; border-radius:6px; font-size:13px; font-weight:600; margin-top:6px; }
.sm_selected_badge button { background:none; border:none; color:#fff; font-size:16px; cursor:pointer; padding:0 2px; opacity:0.8; }
</style>

<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <!-- 단지/동 선택 -->
            <div class="regi_list">
                <li>
                    <p class="regi_list_title">단지 선택</p>
                    <div class="ipt_box">
                        <div class="sm_sch_wrap">
                            <input type="text" id="sm_building_search" class="bansang_ipt ver2 sm_sch_input" placeholder="단지명 또는 지역명을 입력하세요" autocomplete="off">
                            <button type="button" class="sm_sch_clear" id="sm_sch_clear" onclick="smClearBuilding();">&times;</button>
                            <div class="sm_sch_dropdown" id="sm_sch_dropdown"></div>
                        </div>
                        <input type="hidden" id="sm_building" value="">
                        <div id="sm_selected_building"></div>
                    </div>
                </li>
                <li>
                    <p class="regi_list_title">동 선택</p>
                    <div class="ipt_box ipt_flex">
                        <select id="sm_dong" class="bansang_sel">
                            <option value="">전체</option>
                        </select>
                        <button type="button" class="bansang_btns ver1" onclick="smLoadRecipients();">조회</button>
                    </div>
                </li>
            </div>

            <!-- 대상 목록 -->
            <div style="margin-top:15px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <label style="font-size:13px;font-weight:600;">
                        <input type="checkbox" id="sm_chkall" onchange="smCheckAll(this);" checked> 전체선택
                    </label>
                    <span id="sm_total" style="font-size:13px;color:#388FCD;font-weight:600;"></span>
                </div>
                <div id="sm_recipient_list" style="max-height:250px;overflow-y:auto;border:1px solid #e4e4e4;border-radius:8px;">
                    <div style="padding:30px;text-align:center;color:#999;font-size:13px;">단지를 선택하고 조회해주세요.</div>
                </div>
            </div>

            <!-- 문자 내용 -->
            <div style="margin-top:20px;">
                <p class="regi_list_title">문자 내용</p>
                <div class="ipt_box">
                    <textarea id="sm_message" class="bansang_ipt ver2 ta" style="height:120px;" placeholder="문자 내용을 입력하세요." oninput="smCountChar();"></textarea>
                    <div style="text-align:right;font-size:11px;color:#999;margin-top:3px;">
                        <span id="sm_char_count" style="font-weight:700;color:#333;">0</span>자 (90자↓ SMS / 초과 LMS)
                    </div>
                </div>
            </div>

            <!-- 안내 -->
            <div style="margin-top:20px;" id="sms_info_section">
                <div class="sms_info_box">
                    <b>자동 순차 발송 안내</b><br>
                    - 선택된 입주민들에게 <b>개별 SMS</b>로 발송됩니다<br>
                    - 그룹 메시지가 아니므로 수신자끼리 서로 보이지 않습니다<br>
                    - <b>3초 간격</b>으로 자동으로 다음 발신자로 넘어갑니다<br>
                    - 발송 버튼을 못 누른 발신자는 임시저장됩니다<br>
                    - 자동 발송 이후 다시 보내시면 됩니다<br>
                    - 인원수 제한 없음 (30명 이상도 가능)
                </div>
            </div>

            <!-- 진행 상황 -->
            <div id="sms_progress_area" style="display:none;">
                <div class="sms_progress_bar">
                    <div class="sms_progress_header">
                        <span class="sms_progress_label">발송 진행</span>
                        <span class="sms_progress_count" id="sms_progress_text">0 / 0</span>
                    </div>
                    <div class="sms_progress_track">
                        <div class="sms_progress_fill" id="sms_progress_fill" style="width:0%;"></div>
                    </div>
                </div>
                <div class="sms_current_target" id="sms_current_target"></div>
                <div class="sms_sending_guide" id="sms_sending_guide">문자앱에서 [발송] 버튼을 누른 후 돌아오세요!</div>
                <button type="button" class="sms_stop_btn" id="sms_stop_btn" onclick="smStopSending();">발송 중단</button>
            </div>
        </div>

        <!-- 하단 버튼 -->
        <div class="fix_btn_back_box"></div>
        <div class="fix_btn_box ver3" id="sms_fix_btns">
            <button type="button" class="fix_btn on" id="sms_start_btn" onclick="smStartAutoSend();">자동 순차 발송 시작</button>
        </div>
    </div>
</div>

<script>
var smRecipients = [];
var smBuildingData = <?php echo $building_json; ?>;
var smSelectedBuildingId = '';

var smSending = false;

/* ===== 단지 검색 자동완성 ===== */
var smSchActiveIdx = -1;

$("#sm_building_search").on("input", function(){
    var keyword = $(this).val().trim();
    smSchActiveIdx = -1;
    if(keyword.length == 0){ $("#sm_sch_dropdown").hide(); $("#sm_sch_clear").hide(); return; }
    $("#sm_sch_clear").show();

    var filtered = smBuildingData.filter(function(b){
        return b.label.indexOf(keyword) > -1 || b.name.indexOf(keyword) > -1 || b.post.indexOf(keyword) > -1;
    });

    var html = '';
    if(filtered.length > 0){
        filtered.forEach(function(b, idx){
            html += '<div class="sm_sch_item" data-idx="' + idx + '" data-id="' + b.id + '" data-label="' + b.label + '">'
                + '<span class="sch_post">' + b.post + '</span>'
                + '<span class="sch_name">' + b.name + '</span></div>';
        });
    } else {
        html = '<div class="sm_sch_empty">검색 결과가 없습니다.</div>';
    }
    $("#sm_sch_dropdown").html(html).show();
});

$("#sm_building_search").on("keydown", function(e){
    var $items = $("#sm_sch_dropdown .sm_sch_item");
    if($items.length == 0) return;
    if(e.keyCode == 40){ e.preventDefault(); smSchActiveIdx = Math.min(smSchActiveIdx + 1, $items.length - 1); $items.removeClass("active").eq(smSchActiveIdx).addClass("active"); }
    else if(e.keyCode == 38){ e.preventDefault(); smSchActiveIdx = Math.max(smSchActiveIdx - 1, 0); $items.removeClass("active").eq(smSchActiveIdx).addClass("active"); }
    else if(e.keyCode == 13){ e.preventDefault(); if(smSchActiveIdx >= 0) $items.eq(smSchActiveIdx).click(); }
});

$(document).on("click", ".sm_sch_item", function(){ smSelectBuilding($(this).data("id"), $(this).data("label")); });
$(document).on("click", function(e){ if(!$(e.target).closest(".sm_sch_wrap").length) $("#sm_sch_dropdown").hide(); });
$("#sm_building_search").on("focus", function(){ if($(this).val().trim().length > 0 && !smSelectedBuildingId) $(this).trigger("input"); });

function smSelectBuilding(bid, label){
    smSelectedBuildingId = bid;
    $("#sm_building").val(bid);
    $("#sm_building_search").val("").hide();
    $("#sm_sch_clear").hide();
    $("#sm_sch_dropdown").hide();
    $("#sm_selected_building").html('<div class="sm_selected_badge">' + label + ' <button type="button" onclick="smClearBuilding();">&times;</button></div>');
    $("#sm_dong").html('<option value="">전체</option>');
    $.ajax({ url:"/adm/building_dong_ajax.php", type:"POST", data:{building_id:bid, all:'Y'}, success:function(msg){ $("#sm_dong").html(msg); } });
}

function smClearBuilding(){
    smSelectedBuildingId = '';
    $("#sm_building").val('');
    $("#sm_building_search").val("").show().focus();
    $("#sm_sch_clear").hide();
    $("#sm_sch_dropdown").hide();
    $("#sm_selected_building").html('');
    $("#sm_dong").html('<option value="">전체</option>');
    $("#sm_recipient_list").html('<div style="padding:30px;text-align:center;color:#999;font-size:13px;">단지를 선택하고 조회해주세요.</div>');
    $("#sm_total").text('');
}

/* ===== 대상 목록 ===== */
function smLoadRecipients(){
    var bid = $("#sm_building").val();
    if(!bid){ showToast('단지를 선택해주세요.'); return; }
    var did = $("#sm_dong").val();
    var url = '/api/sms_recipient_api.php?action=recipients&building_id=' + bid;
    if(did && did != '-1') url += '&dong_id=' + did;

    $.ajax({ url: url, dataType: 'json', success: function(data){
        if(!data.success){ showToast(data.msg || '조회 실패'); return; }
        smRecipients = data.detail_list;

        var html = '';
        data.detail_list.forEach(function(r){
            html += '<label style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-bottom:1px solid #f0f0f0;font-size:13px;">'
                + '<input type="checkbox" class="sm_chk" data-ho="' + r.ho_name + '" data-name="' + r.name + '" value="' + r.phone + '" checked>'
                + '<span style="min-width:35px;">' + r.ho_name + '호</span>'
                + '<span style="font-weight:600;min-width:45px;">' + r.name + '</span>'
                + '<span style="color:#388FCD;">' + r.phone + '</span></label>';
        });
        if(!html) html = '<div style="padding:30px;text-align:center;color:#999;">대상이 없습니다.</div>';
        $("#sm_recipient_list").html(html);
        $("#sm_chkall").prop("checked", true);
        $("#sm_total").text(data.detail_list.length + "명");
        smResetUI();
    }, error: function(xhr){ showToast('조회 오류 (코드: ' + xhr.status + ')'); }
    });
}

function smCheckAll(src){ $(".sm_chk").prop("checked", src.checked); }
function smCountChar(){ $("#sm_char_count").text($("#sm_message").val().length); }

function getSmCheckedRecipients(){
    var list = [];
    $(".sm_chk:checked").each(function(){
        list.push({ phone: $(this).val(), ho_name: $(this).data('ho'), name: $(this).data('name') });
    });
    var seen = {};
    return list.filter(function(r){
        if(seen[r.phone]) return false;
        seen[r.phone] = true;
        return true;
    });
}

function smResetUI(){
    smSending = false;
    $("#sms_progress_area").hide();
    $("#sms_info_section").show();
    $("#sms_start_btn").text("자동 순차 발송 시작").prop("disabled", false).css("opacity", 1);
    $("#sms_fix_btns").show();
    $(".sm_chk").each(function(){ $(this).closest("label").removeClass("sm_highlight"); });
}

/* ===== 자동 순차 발송 (setTimeout 방식) ===== */
function smStartAutoSend(){
    if(smSending) return;

    var recipients = getSmCheckedRecipients();
    var msg = $("#sm_message").val().trim();

    if(recipients.length == 0){ showToast('발송 대상을 선택해주세요.'); return; }
    if(!msg){ showToast('문자 내용을 입력해주세요.'); return; }

    if(!confirm('선택된 ' + recipients.length + '명에게 개별 SMS를 순차 발송합니다.\n\n3초마다 자동으로 다음 발신자로 변경됩니다\n각 발신자마다 3초 안에 [발송] 버튼을 눌러주세요\n못 누른 문자는 임시저장되며 나중에 다시 보낼 수 있습니다\n\n계속하시겠습니까?')){
        return;
    }

    smSending = true;
    var total = recipients.length;
    var index = 0;

    var ua = navigator.userAgent.toLowerCase();
    var isIOS = (ua.indexOf('iphone') > -1 || ua.indexOf('ipad') > -1);
    var bodySep = isIOS ? '&' : '?';

    // UI 전환
    $("#sms_info_section").hide();
    $("#sms_progress_area").show();
    $("#sms_stop_btn").show();
    $("#sms_sending_guide").show();
    $("#sms_start_btn").text("발송 중...").prop("disabled", true).css("opacity", 0.5);

    function sendNext(){
        if(!smSending || index >= total){
            // 완료 또는 중단
            if(index >= total){
                smSending = false;
                $("#sms_sending_guide").hide();
                $("#sms_stop_btn").hide();
                $("#sms_current_target").css("background", "#d4edda").html('<b>발송 완료!</b><br>총 ' + total + '명에게 발송함').show();
                $("#sms_progress_text").text(total + " / " + total);
                $("#sms_progress_fill").css("width", "100%");
                $("#sms_start_btn").text("발송 완료").css("opacity", 1);
                alert('모든 발송이 완료되었습니다!\n총 ' + total + '명에게 발송함');
            }
            return;
        }

        var r = recipients[index];
        var pct = Math.round(((index + 1) / total) * 100);

        // 프로그레스 업데이트
        $("#sms_progress_text").text((index + 1) + " / " + total);
        $("#sms_progress_fill").css("width", pct + "%");
        $("#sms_current_target").css("background", "#d4edda").html('<b>' + (index + 1) + '번째 발송</b><br>' + r.ho_name + '호 ' + r.name + '<br>' + r.phone).show();

        // 목록 하이라이트
        $(".sm_chk").each(function(){
            var $label = $(this).closest("label");
            $label.removeClass("sm_highlight");
            if($(this).val() == r.phone) $label.addClass("sm_highlight");
        });

        // 스크롤
        var $highlighted = $(".sm_highlight");
        if($highlighted.length){
            var $container = $("#sm_recipient_list");
            $container.scrollTop($container.scrollTop() + $highlighted.offset().top - $container.offset().top - 50);
        }

        // SMS URI 호출
        window.location.href = 'sms:' + r.phone + bodySep + 'body=' + encodeURIComponent(msg);

        index++;
        setTimeout(sendNext, 3000);
    }

    sendNext();
}

/* ===== 발송 중단 ===== */
function smStopSending(){
    if(!smSending) return;
    if(confirm('발송을 중단하시겠습니까?')){
        smSending = false;
        $("#sms_sending_guide").hide();
        $("#sms_stop_btn").hide();
        $("#sms_current_target").css("background", "#f8d7da").html('<b>발송 중단됨</b>').show();
        $("#sms_start_btn").text("발송 중단됨").css("opacity", 1);
    }
}

/* ===== URL 파라미터로 단지 자동 선택 ===== */
$(function(){
    var paramBid = <?php echo $param_building_id; ?>;
    if(paramBid){
        var found = smBuildingData.filter(function(b){ return b.id == paramBid; });
        if(found.length > 0) smSelectBuilding(found[0].id, found[0].label);
    }
});
</script>

<?php include_once(G5_PATH.'/tail.php'); ?>
