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
        </div>

        <!-- 하단 버튼 -->
        <div class="fix_btn_back_box"></div>
        <div class="fix_btn_box ver3">
            <button type="button" class="fix_btn" onclick="smCopyPhones();">번호복사</button>
            <button type="button" class="fix_btn on" onclick="smOpenSmsApp();">문자 앱으로 발송하기</button>
        </div>
    </div>
</div>

<script>
var smRecipients = [];

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
        var html = '';
        data.detail_list.forEach(function(r){
            html += '<label style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-bottom:1px solid #f0f0f0;font-size:13px;">'
                + '<input type="checkbox" class="sm_chk" value="' + r.phone + '" checked>'
                + '<span style="min-width:35px;">' + r.ho_name + '호</span>'
                + '<span style="font-weight:600;min-width:45px;">' + r.name + '</span>'
                + '<span style="color:#388FCD;">' + r.phone + '</span></label>';
        });
        if(!html) html = '<div style="padding:30px;text-align:center;color:#999;">대상이 없습니다.</div>';
        $("#sm_recipient_list").html(html);
        $("#sm_chkall").prop("checked", true);
        $("#sm_total").text(data.detail_list.length + "명");
    }, error: function(xhr){ showToast('조회 오류 (코드: ' + xhr.status + ')'); }
    });
}

function smCheckAll(src){ $(".sm_chk").prop("checked", src.checked); }
function smCountChar(){ $("#sm_char_count").text($("#sm_message").val().length); }

function getSmPhones(){
    var phones = [];
    $(".sm_chk:checked").each(function(){ phones.push($(this).val()); });
    return [...new Set(phones)];
}

function smCopyPhones(){
    var phones = getSmPhones();
    if(phones.length == 0){ showToast('대상을 선택해주세요.'); return; }
    navigator.clipboard.writeText(phones.join(',')).then(function(){
        showToast(phones.length + '명 전화번호 복사됨');
    });
}

function smOpenSmsApp(){
    var phones = getSmPhones();
    var msg = $("#sm_message").val();
    if(phones.length == 0){ showToast('대상을 선택해주세요.'); return; }
    if(!msg){ showToast('문자 내용을 입력해주세요.'); return; }

    // iOS: sms:번호&body=내용, Android: sms:번호?body=내용
    var ua = navigator.userAgent.toLowerCase();
    var sep = (ua.indexOf('iphone') > -1 || ua.indexOf('ipad') > -1) ? '&' : '?';
    window.location.href = 'sms:' + phones.join(',') + sep + 'body=' + encodeURIComponent(msg);
}
</script>

<?php include_once(G5_PATH.'/tail.php'); ?>
