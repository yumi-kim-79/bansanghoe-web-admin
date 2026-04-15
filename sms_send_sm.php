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
</style>

<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <!-- 단지/동 선택 -->
            <div class="regi_list">
                <li>
                    <p class="regi_list_title">단지 선택</p>
                    <div class="ipt_box">
                        <select id="sm_building" class="bansang_sel" onchange="smBuildingChange();">
                            <option value="">단지를 선택하세요</option>
                            <?php while($br = sql_fetch_array($building_res)){?>
                            <option value="<?php echo $br['building_id'];?>"><?php echo $br['post_name'].' '.$br['building_name'];?></option>
                            <?php }?>
                        </select>
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
                    <button type="button" class="sms_mode_tab on" onclick="smSwitchMode('copy');">번호 복사 후 발송</button>
                    <button type="button" class="sms_mode_tab" onclick="smSwitchMode('individual');">한 명씩 개별 발송</button>
                </div>

                <!-- 옵션1: 번호 복사 방식 -->
                <div id="sms_mode_copy">
                    <div class="sms_info_box">
                        <b>사용 방법:</b><br>
                        1. 아래 버튼을 누르면 선택한 대상의 전화번호가 복사됩니다.<br>
                        2. 문자 앱이 열리면 받는 사람란에 붙여넣기 하세요.<br>
                        3. 문자 내용은 자동으로 입력됩니다.
                    </div>
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
            <button type="button" class="fix_btn on" onclick="smCopyAndSend();">전화번호 복사 + 문자앱 열기</button>
        </div>
    </div>
</div>

<script>
var smRecipients = [];
var smSentCount = 0;
var smCurrentMode = 'copy';

function smBuildingChange(){
    var bid = $("#sm_building").val();
    $("#sm_dong").html('<option value="">전체</option>');
    if(!bid) return;
    $.ajax({ url:"/adm/building_dong_ajax.php", type:"POST", data:{building_id:bid, all:'Y'}, success:function(msg){ $("#sm_dong").html(msg); } });
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

        // 체크박스 목록
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

        // 개별 발송 목록 업데이트
        smRenderIndividualList();
    }, error: function(xhr){ showToast('조회 오류 (코드: ' + xhr.status + ')'); }
    });
}

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
    if(smCurrentMode == 'individual') smRenderIndividualList();
}

function smCountChar(){ $("#sm_char_count").text($("#sm_message").val().length); }

function getSmCheckedRecipients(){
    var list = [];
    $(".sm_chk:checked").each(function(){
        list.push({
            phone: $(this).val(),
            ho_name: $(this).data('ho'),
            name: $(this).data('name')
        });
    });
    // 전화번호 중복 제거
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
    } else {
        $(".sms_mode_tab").eq(1).addClass("on");
        $("#sms_mode_copy").hide();
        $("#sms_mode_individual").show();
        $("#sms_fix_btns, #sms_fix_btns_back").hide();
        smRenderIndividualList();
    }
}

/* ===== 옵션1: 번호 복사 + 문자앱 열기 ===== */
function smCopyAndSend(){
    var checked = getSmCheckedRecipients();
    var msg = $("#sm_message").val();
    if(checked.length == 0){ showToast('대상을 선택해주세요.'); return; }
    if(!msg){ showToast('문자 내용을 입력해주세요.'); return; }

    var phones = checked.map(function(r){ return r.phone; });
    var phoneText = phones.join(', ');

    // 클립보드에 전화번호 복사
    if(navigator.clipboard && navigator.clipboard.writeText){
        navigator.clipboard.writeText(phoneText).then(function(){
            alert(phones.length + '명의 전화번호가 복사되었습니다.\n\n문자 앱이 열리면 받는 사람란에 붙여넣기 하세요.');
            smOpenSmsWithBody(msg);
        }).catch(function(){
            smFallbackCopy(phoneText, phones.length, msg);
        });
    } else {
        smFallbackCopy(phoneText, phones.length, msg);
    }
}

function smFallbackCopy(text, count, msg){
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    alert(count + '명의 전화번호가 복사되었습니다.\n\n문자 앱이 열리면 받는 사람란에 붙여넣기 하세요.');
    smOpenSmsWithBody(msg);
}

function smOpenSmsWithBody(msg){
    var ua = navigator.userAgent.toLowerCase();
    var isIOS = (ua.indexOf('iphone') > -1 || ua.indexOf('ipad') > -1);
    var bodySep = isIOS ? '&' : '?';
    window.location.href = 'sms:' + bodySep + 'body=' + encodeURIComponent(msg);
}

/* ===== 옵션2: 개별 발송 ===== */
function smSendIndividual(idx, phone){
    var msg = $("#sm_message").val();
    if(!msg){ showToast('문자 내용을 입력해주세요.'); return; }

    var $btn = $("#sms_ind_" + idx + " .sms_ind_btn");
    if($btn.hasClass('sent')) return;

    $btn.addClass('sent').text('발송됨');
    smSentCount++;
    var total = getSmCheckedRecipients().length;
    smUpdateProgress(total);

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
</script>

<?php include_once(G5_PATH.'/tail.php'); ?>
