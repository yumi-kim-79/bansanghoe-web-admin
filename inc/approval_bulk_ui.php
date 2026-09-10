<?php
if (!defined('_GNUBOARD_')) exit;
/**
 * 일괄결재 공통 UI — 2026-09 신설
 *  관리자웹(adm/approval_document_list.php)과 매니저앱(approval_document.php)이 함께 include 한다.
 *
 *  쓰는 법:  목록에서 선택한 sign_id 배열을 넘긴다.
 *      apBulkOpen([12, 34, 56]);
 *
 *  흐름
 *   1) 서명 받기 — 저장된 서명이 있으면 그걸 쓰고, 없으면 그 자리에서 한 번만 그린다
 *   2) /approval_bulk_check.php 로 선택 건 전부 전송 (건별 독립 처리)
 *   3) 승인된 건마다 문서 PNG 를 다시 만들어야 하는데, 이건 브라우저만 할 수 있다.
 *      숨김 프레임에 문서 페이지를 하나씩 띄워 순차 캡처하고 진행률을 보여준다.
 *   4) 결과 요약 후 목록 새로고침
 */
$ap_bulk_mb_id = isset($member['mb_id']) ? $member['mb_id'] : '';
?>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<style>
#ap_bulk_wrap{position:fixed;top:0;left:0;right:0;bottom:0;z-index:100000;display:none;background:rgba(0,0,0,.5);align-items:center;justify-content:center;}
#ap_bulk_box{background:#fff;border-radius:10px;width:calc(100% - 36px);max-width:440px;max-height:90vh;overflow:auto;box-shadow:0 10px 40px rgba(0,0,0,.3);}
#ap_bulk_box .apb_head{padding:16px 20px;border-bottom:1px solid #eee;font-size:16px;font-weight:600;color:#222;}
#ap_bulk_box .apb_body{padding:18px 20px;font-size:14px;line-height:1.6;color:#333;}
#ap_bulk_box .apb_foot{display:flex;border-top:1px solid #eee;}
#ap_bulk_box .apb_foot button{flex:1;padding:14px;border:0;font-size:15px;cursor:pointer;}
#ap_bulk_box .apb_cancel{background:#f5f5f5;color:#444;}
#ap_bulk_box .apb_ok{background:#3b6ea5;color:#fff;}
#ap_bulk_box .apb_ok[disabled]{background:#9db6cd;cursor:default;}
.apb_cnt{color:#3b6ea5;font-weight:600;}
.apb_signbox{margin-top:14px;border:1px solid #ddd;border-radius:6px;background:#fafafa;text-align:center;padding:10px;}
.apb_signbox img{max-height:110px;}
#ap_bulk_canvas{width:100%;height:150px;background:#fff;border:1px dashed #bbb;border-radius:4px;touch-action:none;}
.apb_link{margin-top:8px;font-size:13px;}
.apb_link a{color:#3b6ea5;text-decoration:underline;cursor:pointer;}
.apb_bar{margin-top:12px;height:8px;background:#eee;border-radius:4px;overflow:hidden;}
.apb_bar i{display:block;height:100%;width:0;background:#3b6ea5;transition:width .2s;}
.apb_note{margin-top:10px;font-size:13px;color:#777;}
.apb_faillist{margin-top:10px;font-size:13px;color:#a3252c;max-height:150px;overflow:auto;}
.apb_faillist li{list-style:none;padding:3px 0;}
#ap_bulk_frame{position:fixed;left:-9999px;top:0;width:900px;height:1200px;border:0;}
</style>

<div id="ap_bulk_wrap">
    <div id="ap_bulk_box">
        <div class="apb_head" id="apb_title">일괄 결재</div>
        <div class="apb_body" id="apb_body"></div>
        <div class="apb_foot" id="apb_foot">
            <button type="button" class="apb_cancel" onclick="apBulkClose()">취소</button>
            <button type="button" class="apb_ok" id="apb_ok" onclick="apBulkRun()">결재하기</button>
        </div>
    </div>
</div>
<iframe id="ap_bulk_frame" title="문서 반영"></iframe>

<script>
var AP_BULK_MB_ID   = "<?php echo $ap_bulk_mb_id; ?>";
var apBulkIds       = [];
var apBulkSignData  = "";
var apBulkPad       = null;
var apBulkBusy      = false;

function apBulkClose(){
    if(apBulkBusy) return;               // 처리 중에는 닫지 않는다
    document.getElementById('ap_bulk_wrap').style.display = 'none';
    apBulkPad = null;
}

function apBulkBody(html){ document.getElementById('apb_body').innerHTML = html; }
function apBulkFoot(show){ document.getElementById('apb_foot').style.display = show ? 'flex' : 'none'; }

/** 목록에서 선택한 sign_id 배열로 시작 */
function apBulkOpen(ids){
    if(!ids || ids.length === 0){
        alert('결재할 문서를 하나 이상 선택해 주세요.');
        return;
    }
    if(ids.length > 100){
        alert('한 번에 최대 100건까지 결재할 수 있습니다.');
        return;
    }

    apBulkIds      = ids;
    apBulkSignData = "";
    apBulkBusy     = false;

    document.getElementById('apb_title').textContent = '일괄 결재';
    apBulkFoot(true);
    document.getElementById('apb_ok').disabled = true;
    document.getElementById('ap_bulk_wrap').style.display = 'flex';

    apBulkBody('<div>선택한 <span class="apb_cnt">' + ids.length + '건</span>을 한 번의 서명으로 결재합니다.</div>'
             + '<div class="apb_note">서명을 불러오는 중입니다...</div>');

    // 저장된 서명 조회
    $.ajax({
        type: "POST",
        url: "/sign_load_ajax.php",
        data: {mb_id: AP_BULK_MB_ID},
        cache: false,
        dataType: "json",
        success: function(res){
            var sig = (res && res.data && res.data.signature_data) ? res.data.signature_data : '';
            if(sig){
                apBulkSignData = sig;
                apBulkShowSaved(sig);
            }else{
                apBulkShowPad();
            }
        },
        error: function(){ apBulkShowPad(); }
    });
}

/** 저장된 서명 사용 화면 */
function apBulkShowSaved(sig){
    apBulkBody('<div>선택한 <span class="apb_cnt">' + apBulkIds.length + '건</span>을 아래 서명으로 결재합니다.</div>'
             + '<div class="apb_signbox"><img src="' + sig + '" alt="저장된 서명"></div>'
             + '<div class="apb_link"><a onclick="apBulkShowPad()">다시 서명하기</a></div>');
    document.getElementById('apb_ok').disabled = false;
}

/** 새로 서명하는 화면 */
function apBulkShowPad(){
    apBulkSignData = "";
    apBulkBody('<div>선택한 <span class="apb_cnt">' + apBulkIds.length + '건</span>에 사용할 서명을 그려 주세요.</div>'
             + '<div class="apb_signbox"><canvas id="ap_bulk_canvas"></canvas></div>'
             + '<div class="apb_link"><a onclick="apBulkClearPad()">지우기</a></div>');

    var canvas = document.getElementById('ap_bulk_canvas');
    var ratio  = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width  = canvas.offsetWidth  * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);

    apBulkPad = new SignaturePad(canvas);
    document.getElementById('apb_ok').disabled = false;
}

function apBulkClearPad(){ if(apBulkPad) apBulkPad.clear(); }

/** [결재하기] — 서버로 일괄 전송 */
function apBulkRun(){
    var sign = apBulkSignData;

    if(!sign && apBulkPad){
        if(apBulkPad.isEmpty()){ alert('서명을 입력해 주세요.'); return; }
        sign = apBulkPad.toDataURL("image/png");
    }
    if(!sign){ alert('서명을 입력해 주세요.'); return; }

    apBulkBusy = true;
    apBulkFoot(false);
    document.getElementById('apb_title').textContent = '결재 처리 중';
    apBulkBody('<div>' + apBulkIds.length + '건을 결재하고 있습니다.</div>'
             + '<div class="apb_note">창을 닫지 마세요.</div>');

    $.ajax({
        type: "POST",
        url: "/approval_bulk_check.php",
        data: {idx_list: apBulkIds.join(','), signdata: sign},
        cache: false,
        dataType: "json",
        success: function(res){
            var d = (res && res.data) ? res.data : {};
            var ok = (d.success || []).map(function(x){ return x.sign_id; });

            if(!res || res.result === false){
                apBulkBusy = false;
                apBulkFinish(0, (d.failed || []), (res ? res.msg : '결재에 실패했습니다.'));
                return;
            }
            // 승인된 건들의 문서 이미지를 순차로 다시 만든다
            apBulkCapture(ok, (d.failed || []));
        },
        error: function(xhr, status, err){
            apBulkBusy = false;
            console.log('[일괄결재] 요청 실패', status, err, xhr && xhr.responseText);
            apBulkFinish(0, [], '요청을 처리하지 못했습니다.\n네트워크 상태를 확인한 뒤 다시 시도해 주세요.');
        }
    });
}

/**
 * 승인된 건의 문서 PNG 재생성.
 *  문서 이미지는 브라우저가 화면을 그려서 만드는 구조라 서버에서 만들 수 없다.
 *  숨김 프레임에 한 건씩 띄우고, 그 페이지가 보내오는 완료 신호를 기다린다.
 */
function apBulkCapture(ids, failed){
    var total = ids.length;

    if(total === 0){ apBulkBusy = false; apBulkFinish(0, failed, ''); return; }

    var frame   = document.getElementById('ap_bulk_frame');
    var idx     = 0;
    var timer   = null;
    var handled = false;

    document.getElementById('apb_title').textContent = '문서에 서명 반영 중';

    function render(){
        var pct = Math.round((idx / total) * 100);
        apBulkBody('<div>결재는 완료되었습니다. 문서에 서명을 반영하고 있습니다.</div>'
                 + '<div class="apb_bar"><i style="width:' + pct + '%"></i></div>'
                 + '<div class="apb_note">' + idx + ' / ' + total + '건</div>'
                 + '<div class="apb_note">창을 닫지 마세요.</div>');
    }

    function next(){
        handled = false;
        if(timer){ clearTimeout(timer); timer = null; }

        if(idx >= total){
            window.removeEventListener('message', onMsg);
            frame.src = 'about:blank';
            apBulkBusy = false;
            apBulkFinish(total, failed, '');
            return;
        }

        render();
        frame.src = '/holiday_request_sample.php?mem_type=bulk&sign_id=' + ids[idx];

        // 한 건이 끝나지 않아도 전체가 멈추지 않도록 상한을 둔다
        timer = setTimeout(function(){
            if(handled) return;
            handled = true;
            console.log('[일괄결재] 문서 반영 시간 초과 sign_id=' + ids[idx]);
            idx++;
            next();
        }, 30000);
    }

    function onMsg(e){
        if(!e.data || e.data.ap !== 'capture_done') return;
        if(handled) return;
        handled = true;
        if(!e.data.ok) console.log('[일괄결재] 문서 반영 실패 sign_id=' + e.data.sign_id + ' ' + (e.data.msg || ''));
        idx++;
        next();
    }

    window.addEventListener('message', onMsg);
    next();
}

/** 결과 요약 */
function apBulkFinish(okCnt, failed, errMsg){
    apBulkFoot(true);
    document.getElementById('apb_ok').style.display = 'none';
    document.querySelector('#ap_bulk_box .apb_cancel').textContent = '닫기';
    document.querySelector('#ap_bulk_box .apb_cancel').onclick = function(){ location.reload(); };
    document.getElementById('apb_title').textContent = '결재 결과';

    var html = '';
    if(okCnt > 0) html += '<div><span class="apb_cnt">' + okCnt + '건</span> 결재가 완료되었습니다.</div>';
    if(errMsg)    html += '<div class="apb_note" style="white-space:pre-line;color:#a3252c;">' + errMsg + '</div>';

    if(failed && failed.length > 0){
        html += '<div class="apb_note">아래 ' + failed.length + '건은 결재되지 않았습니다.</div><ul class="apb_faillist">';
        for(var i=0; i<failed.length; i++){
            html += '<li>· 결재번호 ' + failed[i].sign_id + ' — ' + failed[i].msg + '</li>';
        }
        html += '</ul>';
    }
    apBulkBody(html);
}
</script>
