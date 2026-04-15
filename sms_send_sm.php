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

// 단지 목록을 JSON으로 변환 (검색용)
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

// URL 파라미터로 전달된 building_id
$param_building_id = isset($_GET['building_id']) ? intval($_GET['building_id']) : 0;
?>

<style>
.sms_mode_tabs { display:flex; gap:0; margin-bottom:15px; border:1px solid #388FCD; border-radius:8px; overflow:hidden; }
.sms_mode_tab { flex:1; text-align:center; padding:10px 0; font-size:13px; font-weight:600; color:#388FCD; background:#fff; border:none; cursor:pointer; }
.sms_mode_tab.on { background:#388FCD; color:#fff; }
.sms_mode_tab + .sms_mode_tab { border-left:1px solid #388FCD; }

.sms_info_box { background:#f0f7ff; border-radius:8px; padding:12px; margin-bottom:15px; font-size:12px; color:#555; line-height:1.6; }
.sms_info_box b { color:#333; }

.sms_individual_list { max-height:350px; overflow-y:auto; }
.sms_ind_item { display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border-bottom:1px solid #f0f0f0; }
.sms_ind_info { font-size:13px; }
.sms_ind_info .ind_ho { color:#666; margin-right:6px; }
.sms_ind_info .ind_name { font-weight:600; margin-right:8px; }
.sms_ind_info .ind_phone { color:#388FCD; }
.sms_ind_btn { flex-shrink:0; padding:6px 12px; font-size:12px; font-weight:600; color:#fff; background:#388FCD; border:none; border-radius:6px; cursor:pointer; }
.sms_ind_btn.sent { background:#999; }

.sms_progress { font-size:12px; color:#388FCD; font-weight:600; text-align:center; padding:10px; }

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

/* 그룹 복사 */
.sms_group_box { border:1px solid #e4e4e4; border-radius:8px; margin-bottom:10px; overflow:hidden; }
.sms_group_header { display:flex; justify-content:space-between; align-items:center; padding:10px 12px; background:#f8f9fa; }
.sms_group_title { font-size:13px; font-weight:600; color:#333; }
.sms_group_copy_btn { padding:6px 14px; font-size:12px; font-weight:600; color:#fff; background:#388FCD; border:none; border-radius:6px; cursor:pointer; }
.sms_group_copy_btn.copied { background:#4caf50; }
.sms_group_phones { padding:8px 12px; font-size:12px; color:#666; line-height:1.5; max-height:80px; overflow-y:auto; word-break:break-all; }
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

            <!-- 발송 방식 선택 -->
            <div style="margin-top:20px;">
                <p class="regi_list_title">발송 방식</p>
                <div class="sms_mode_tabs">
                    <button type="button" class="sms_mode_tab on" onclick="smSwitchMode('copy');">번호 복사 발송</button>
                    <button type="button" class="sms_mode_tab" onclick="smSwitchMode('individual');">한 명씩 개별 발송</button>
                </div>

                <!-- 옵션1: 번호 복사 방식 -->
                <div id="sms_mode_copy">
                    <div class="sms_info_box">
                        <b>사용 방법:</b><br>
                        1. 아래 버튼으로 전화번호를 복사하세요.<br>
                        2. 문자 앱을 열고 받는 사람란에 붙여넣기 하세요.<br>
                        3. 30명 초과 시 자동으로 그룹이 나뉩니다. 그룹별로 복사 후 발송하세요.
                    </div>
                    <div id="sms_group_area"></div>
                </div>

                <!-- 옵션2: 개별 발송 방식 -->
                <div id="sms_mode_individual" style="display:none;">
                    <div class="sms_info_box">
                        <b>사용 방법:</b><br>
                        각 대상 옆의 "문자 보내기" 버튼을 눌러 한 명씩 발송하세요.<br>
                        문자 내용이 자동으로 입력된 상태로 문자 앱이 열립니다.
                    </div>
                    <div id="sms_ind_list_wrap" class="sms_individual_list" style="border:1px solid #e4e4e4;border-radius:8px;">
                        <div style="padding:20px;text-align:center;color:#999;font-size:13px;">대상을 조회한 후 표시됩니다.</div>
                    </div>
                    <div id="sms_ind_progress" class="sms_progress" style="display:none;"></div>
                </div>
            </div>
        </div>

        <!-- 하단 버튼 (옵션1 전용) -->
        <div class="fix_btn_back_box" id="sms_fix_btns_back"></div>
        <div class="fix_btn_box ver3" id="sms_fix_btns">
            <button type="button" class="fix_btn on" onclick="smCopyAllPhones();">전체 번호 복사</button>
        </div>
    </div>
</div>

<script>
var smRecipients = [];
var smSentCount = 0;
var smCurrentMode = 'copy';
var smBuildingData = <?php echo $building_json; ?>;
var smSelectedBuildingId = '';
var SM_GROUP_SIZE = 30;

/* ===== 단지 검색 자동완성 ===== */
var smSchActiveIdx = -1;

$("#sm_building_search").on("input", function(){
    var keyword = $(this).val().trim();
    smSchActiveIdx = -1;

    if(keyword.length == 0){
        $("#sm_sch_dropdown").hide();
        $("#sm_sch_clear").hide();
        return;
    }
    $("#sm_sch_clear").show();

    var filtered = smBuildingData.filter(function(b){
        return b.label.indexOf(keyword) > -1 || b.name.indexOf(keyword) > -1 || b.post.indexOf(keyword) > -1;
    });

    var html = '';
    if(filtered.length > 0){
        filtered.forEach(function(b, idx){
            html += '<div class="sm_sch_item" data-idx="' + idx + '" data-id="' + b.id + '" data-label="' + b.label + '">'
                + '<span class="sch_post">' + b.post + '</span>'
                + '<span class="sch_name">' + b.name + '</span>'
                + '</div>';
        });
    } else {
        html = '<div class="sm_sch_empty">검색 결과가 없습니다.</div>';
    }
    $("#sm_sch_dropdown").html(html).show();
});

$("#sm_building_search").on("keydown", function(e){
    var $items = $("#sm_sch_dropdown .sm_sch_item");
    if($items.length == 0) return;

    if(e.keyCode == 40){
        e.preventDefault();
        smSchActiveIdx = Math.min(smSchActiveIdx + 1, $items.length - 1);
        $items.removeClass("active").eq(smSchActiveIdx).addClass("active");
    } else if(e.keyCode == 38){
        e.preventDefault();
        smSchActiveIdx = Math.max(smSchActiveIdx - 1, 0);
        $items.removeClass("active").eq(smSchActiveIdx).addClass("active");
    } else if(e.keyCode == 13){
        e.preventDefault();
        if(smSchActiveIdx >= 0) $items.eq(smSchActiveIdx).click();
    }
});

$(document).on("click", ".sm_sch_item", function(){
    smSelectBuilding($(this).data("id"), $(this).data("label"));
});

$(document).on("click", function(e){
    if(!$(e.target).closest(".sm_sch_wrap").length) $("#sm_sch_dropdown").hide();
});

$("#sm_building_search").on("focus", function(){
    if($(this).val().trim().length > 0 && !smSelectedBuildingId) $(this).trigger("input");
});

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
    $("#sms_group_area").html('');
}

function smLoadRecipients(){
    var bid = $("#sm_building").val();
    if(!bid){ showToast('단지를 선택해주세요.'); return; }
    var did = $("#sm_dong").val();
    var url = '/api/sms_recipient_api.php?action=recipients&building_id=' + bid;
    if(did && did != '-1') url += '&dong_id=' + did;

    $.ajax({ url: url, dataType: 'json', success: function(data){
        if(!data.success){ showToast(data.msg || '조회 실패'); return; }
        smRecipients = data.detail_list;
        smSentCount = 0;

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

        smRenderGroups();
        smRenderIndividualList();
    }, error: function(xhr){ showToast('조회 오류 (코드: ' + xhr.status + ')'); }
    });
}

/* ===== 그룹 분할 복사 UI ===== */
function smRenderGroups(){
    var checked = getSmCheckedRecipients();
    var phones = checked.map(function(r){ return r.phone; });

    if(phones.length == 0){
        $("#sms_group_area").html('');
        return;
    }

    // 30명 이하면 그룹 분할 없이 단일 표시
    if(phones.length <= SM_GROUP_SIZE){
        $("#sms_group_area").html(
            '<div class="sms_group_box">'
            + '<div class="sms_group_header">'
            + '<span class="sms_group_title">전체 ' + phones.length + '명</span>'
            + '<button type="button" class="sms_group_copy_btn" onclick="smCopyGroup(0);">번호 복사</button>'
            + '</div>'
            + '<div class="sms_group_phones" id="sms_gphones_0">' + phones.join(', ') + '</div>'
            + '</div>'
        );
        return;
    }

    // 30명 초과: 그룹 분할
    var groups = [];
    for(var i = 0; i < phones.length; i += SM_GROUP_SIZE){
        groups.push(phones.slice(i, i + SM_GROUP_SIZE));
    }

    var html = '<div style="font-size:12px;color:#e65100;font-weight:600;margin-bottom:8px;">'
        + phones.length + '명 = ' + groups.length + '개 그룹 (그룹당 최대 ' + SM_GROUP_SIZE + '명). 그룹별로 복사 후 발송하세요.'
        + '</div>';

    groups.forEach(function(g, idx){
        var start = idx * SM_GROUP_SIZE + 1;
        var end = start + g.length - 1;
        html += '<div class="sms_group_box">'
            + '<div class="sms_group_header">'
            + '<span class="sms_group_title">그룹 ' + (idx+1) + ' (' + g.length + '명, ' + start + '~' + end + '번)</span>'
            + '<button type="button" class="sms_group_copy_btn" id="sms_gbtn_' + idx + '" onclick="smCopyGroup(' + idx + ');">번호 복사</button>'
            + '</div>'
            + '<div class="sms_group_phones" id="sms_gphones_' + idx + '">' + g.join(', ') + '</div>'
            + '</div>';
    });

    $("#sms_group_area").html(html);
}

function smCopyGroup(groupIdx){
    var text = $("#sms_gphones_" + groupIdx).text();
    if(!text){ showToast('복사할 번호가 없습니다.'); return; }

    smDoCopy(text, function(){
        var $btn = $("#sms_gbtn_" + groupIdx);
        if($btn.length == 0) $btn = $(".sms_group_copy_btn").eq(groupIdx);
        $btn.addClass('copied').text('복사됨');
        setTimeout(function(){ $btn.removeClass('copied').text('번호 복사'); }, 2000);
        showToast('전화번호가 복사되었습니다. 문자 앱에서 붙여넣기 하세요.');
    });
}

function smCopyAllPhones(){
    var checked = getSmCheckedRecipients();
    if(checked.length == 0){ showToast('대상을 선택해주세요.'); return; }

    var phones = checked.map(function(r){ return r.phone; });

    if(phones.length > SM_GROUP_SIZE){
        showToast('30명 초과입니다. 위 그룹별 복사 버튼을 이용해주세요.');
        return;
    }

    smDoCopy(phones.join(', '), function(){
        showToast(phones.length + '명 전화번호가 복사되었습니다.');
    });
}

function smDoCopy(text, onSuccess){
    if(navigator.clipboard && navigator.clipboard.writeText){
        navigator.clipboard.writeText(text).then(onSuccess).catch(function(){
            smFallbackCopy(text);
            onSuccess();
        });
    } else {
        smFallbackCopy(text);
        onSuccess();
    }
}

function smFallbackCopy(text){
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
}

/* ===== 공통 ===== */
function smRenderIndividualList(){
    var checked = getSmCheckedRecipients();
    if(checked.length == 0){
        $("#sms_ind_list_wrap").html('<div style="padding:20px;text-align:center;color:#999;font-size:13px;">선택된 대상이 없습니다.</div>');
        $("#sms_ind_progress").hide();
        return;
    }
    var html = '';
    checked.forEach(function(r, idx){
        html += '<div class="sms_ind_item" id="sms_ind_' + idx + '">'
            + '<span class="sms_ind_info">'
            + '<span class="ind_ho">' + r.ho_name + '호</span>'
            + '<span class="ind_name">' + r.name + '</span>'
            + '<span class="ind_phone">' + r.phone + '</span>'
            + '</span>'
            + '<button type="button" class="sms_ind_btn" onclick="smSendIndividual(' + idx + ',\'' + r.phone + '\');">문자 보내기</button>'
            + '</div>';
    });
    $("#sms_ind_list_wrap").html(html);
    smSentCount = 0;
    smUpdateProgress(checked.length);
}

function smCheckAll(src){
    $(".sm_chk").prop("checked", src.checked);
    smRenderGroups();
    if(smCurrentMode == 'individual') smRenderIndividualList();
}

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

function smSwitchMode(mode){
    smCurrentMode = mode;
    $(".sms_mode_tab").removeClass("on");
    if(mode == 'copy'){
        $(".sms_mode_tab").eq(0).addClass("on");
        $("#sms_mode_copy").show();
        $("#sms_mode_individual").hide();
        $("#sms_fix_btns, #sms_fix_btns_back").show();
        smRenderGroups();
    } else {
        $(".sms_mode_tab").eq(1).addClass("on");
        $("#sms_mode_copy").hide();
        $("#sms_mode_individual").show();
        $("#sms_fix_btns, #sms_fix_btns_back").hide();
        smRenderIndividualList();
    }
}

/* ===== 옵션2: 개별 발송 ===== */
function smSendIndividual(idx, phone){
    var msg = $("#sm_message").val();
    if(!msg){ showToast('문자 내용을 입력해주세요.'); return; }

    var $btn = $("#sms_ind_" + idx + " .sms_ind_btn");
    if($btn.hasClass('sent')) return;

    $btn.addClass('sent').text('발송됨');
    smSentCount++;
    smUpdateProgress(getSmCheckedRecipients().length);

    var ua = navigator.userAgent.toLowerCase();
    var isIOS = (ua.indexOf('iphone') > -1 || ua.indexOf('ipad') > -1);
    var bodySep = isIOS ? '&' : '?';
    window.location.href = 'sms:' + phone + bodySep + 'body=' + encodeURIComponent(msg);
}

function smUpdateProgress(total){
    if(smSentCount > 0){
        $("#sms_ind_progress").show().text(smSentCount + ' / ' + total + '명 발송 완료');
    } else {
        $("#sms_ind_progress").hide();
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
