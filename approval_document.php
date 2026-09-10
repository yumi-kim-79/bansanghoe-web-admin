<?php
include_once('./_common.php');

// [항목5/6] 뒤로가기 캐시(BFCache) 무력화 — WebView goBack 시 항상 최신 목록
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

include_once(G5_PATH.'/head_sm.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$mng_infos = get_manger($member['mb_id']);

$depart_sql = "SELECT * FROM a_mng_department WHERE is_del = 0 ORDER BY is_prior asc, md_idx asc";
$depart_res = sql_query($depart_sql);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <ul class="tab_lnb ver4">
                <li class="tab01 on" onclick="tab_handler('1', 'all')">결재관리</li>
                <li class="tab02" onclick="tab_handler('2', 'success')">승인건</li>
                <li class="tab03" onclick="tab_handler('3', 'reject')">반려건</li>
                <li class="tab04" onclick="tab_handler('4', 'my_approval')">내결재</li>
            </ul>
            <div class="ipt_box ipt_flex ipt_box_ver2 mgt20">
                <div class="date_form_box date_form_box_ipt">
                    <input type="text" name="approval_sdate" id="approval_sdate" class="bansang_ipt ipt_date ver2" readonly>
                </div>
                <div class="date_form_box">~</div>
                <div class="date_form_box date_form_box_ipt">
                    <input type="text" name="approval_edate" id="approval_edate" class="bansang_ipt ipt_date ver2" readonly>
                </div>
            </div>
            <div class="form_select_box flex_ver mgt10">
                <div class="ipt_box ipt_flex sch_boxs">
                    <select name="department_type" id="department_type" class="bansang_sel">
                        <option value="">부서 전체</option>
                        <?php while($depart_row = sql_fetch_array($depart_res)){?>
                            <option value="<?php echo $depart_row['md_idx'];?>"><?php echo $depart_row['md_name'];?></option>
                        <?php }?>
                    </select>
                    <div class="sch_input_form_box">
                        <input type="text" name="sch_text" id="sch_text" placeholder="작성자 이름을 입력해주세요." class="bansang_ipt ver2">
                        <button type="button" class="sch_button" onclick="schHandler();">
                            <img src="/images/sch_icons.svg" alt="">
                        </button>
                    </div>
                    <!-- <select name="writers" id="writers" class="bansang_sel">
                        <option value="">작성자 전체</option>
                    </select> -->
                </div>
                
            </div>
            <div class="content_box_wrap nm ver2">
            </div>
        </div>
    </div>
</div>

<!-- [일괄결재 2026-09] 내결재 탭에서만 뜨는 선택바. 하단 탭(86px) 위에 얹는다. -->
<div id="ap_bulk_bar">
    <label class="apbar_all"><input type="checkbox" id="ap_bulk_all"> 전체선택</label>
    <button type="button" id="ap_bulk_go" onclick="apBulkGo();" disabled>선택 결재 <b>0</b>건</button>
</div>
<style>
#ap_bulk_bar{position:fixed;left:0;right:0;bottom:86px;z-index:98;display:none;align-items:center;justify-content:space-between;gap:12px;
             width:100%;max-width:1024px;margin:0 auto;padding:10px 18px;background:#fff;border-top:1px solid #eee;box-shadow:0 -2px 10px rgba(0,0,0,.06);}
#ap_bulk_bar .apbar_all{display:flex;align-items:center;gap:7px;font-size:14px;color:#444;}
#ap_bulk_bar .apbar_all input{width:20px;height:20px;accent-color:#3b6ea5;}
#ap_bulk_go{padding:11px 18px;border:0;border-radius:6px;background:#3b6ea5;color:#fff;font-size:15px;font-weight:600;}
#ap_bulk_go[disabled]{background:#c3d0dd;}
#wrappers.has_bulk_bar{padding-bottom:150px;}
</style>
<script>
// [항목5/6] BFCache 복원(WebView goBack 포함) 시 강제 새로고침 → tab_handler 재실행으로 내결재 탭 + 최신
window.addEventListener('pageshow', function(event){
    if(event.persisted){ location.reload(); }
});

let mng_certi = "<?php echo $mng_infos['mng_certi']; ?>";
let tabIdx = "<?php echo $tabIdx ?? '4'; ?>";
let tabCode = "<?php echo $tabCode ?? 'my_approval'; ?>";

tab_handler(tabIdx, tabCode);

function tab_handler(index, code){
    tabIdx = index;
    tabCode = code;

    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");

    let mb_id = "<?php echo $member['mb_id']; ?>";
   

    $.ajax({

    url : "/approval_document_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "code":code, "mb_id":mb_id, "mng_chk":"Y", "mng_certi":mng_certi}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        //console.log(msg);
        $(".content_box_wrap").html(msg);
        apBulkBarSync();
    }

    });
}

function schHandler(){
    let mb_id = "<?php echo $member['mb_id']; ?>";
    let approval_sdate = $("#approval_sdate").val();
    let approval_edate = $("#approval_edate").val();
    let department_type = $("#department_type option:selected").val();
    let sch_text = $("#sch_text").val();

    if(approval_edate != "" && approval_sdate == ""){
        showToast('시작일을 입력하세요.');
        return false;
    }

    if(approval_edate == "" && approval_sdate != ""){
        showToast('종료일을 입력하세요.');
        return false;
    }

    if(approval_edate != "" && approval_sdate != ""){

        if(approval_edate < approval_sdate){
            showToast('종료일이 시작일보다 이전일 수 없습니다.');
            return false;
        }
    }

    $.ajax({

    url : "/approval_document_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "code":tabCode, "mb_id":mb_id, "mng_chk":"Y", "approval_sdate":approval_sdate, "approval_edate":approval_edate, "department_type":department_type, "sch_text":sch_text, "mng_certi":mng_certi}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        //console.log(msg);
        $(".content_box_wrap").html(msg);
        apBulkBarSync();
    }

    });
}

$(function(){
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d", minDate:"-365d" });
});

// ───── [일괄결재 2026-09] 내결재 탭 선택바 ─────────────────────────────
//  목록이 ajax 로 갈아끼워지므로, 목록을 그린 뒤 apBulkBarSync() 를 호출해 상태를 맞춘다.
function apBulkBarSync(){
    var bar   = document.getElementById('ap_bulk_bar');
    var items = document.querySelectorAll('.ap_bulk_chk');
    var show  = (tabCode === 'my_approval' && items.length > 0);

    bar.style.display = show ? 'flex' : 'none';
    document.getElementById('wrappers').classList.toggle('has_bulk_bar', show);

    var all = document.getElementById('ap_bulk_all');
    if(all) all.checked = false;
    apBulkCount();
}

function apBulkCount(){
    var n = document.querySelectorAll('.ap_bulk_chk:checked').length;
    document.querySelector('#ap_bulk_go b').textContent = n;
    document.getElementById('ap_bulk_go').disabled = (n === 0);
}

$(document).on('change', '.ap_bulk_chk', function(){
    apBulkCount();
    var total   = document.querySelectorAll('.ap_bulk_chk').length;
    var checked = document.querySelectorAll('.ap_bulk_chk:checked').length;
    var all = document.getElementById('ap_bulk_all');
    if(all) all.checked = (total > 0 && total === checked);
});

$(document).on('change', '#ap_bulk_all', function(){
    var on = this.checked;
    document.querySelectorAll('.ap_bulk_chk').forEach(function(c){ c.checked = on; });
    apBulkCount();
});

function apBulkGo(){
    var ids = [];
    document.querySelectorAll('.ap_bulk_chk:checked').forEach(function(c){
        var v = parseInt(c.value, 10);
        if(v > 0) ids.push(v);
    });
    if(ids.length === 0){ showToast('결재할 문서를 하나 이상 선택해 주세요.'); return; }
    apBulkOpen(ids);   // inc/approval_bulk_ui.php
}
</script>
<?php
// [일괄결재 2026-09] 서명 팝업 + 진행률 + 문서 재캡처 공통 UI (관리자웹과 동일 파일)
include_once(G5_PATH.'/inc/approval_bulk_ui.php');
?>
<?php
include_once(G5_PATH.'/tail.php');
?>