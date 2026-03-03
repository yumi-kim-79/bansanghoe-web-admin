<?php
include_once('./_common.php');
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
                <li class="tab01 on" onclick="tab_handler('1', 'all')">결재 관리</li>
                <li class="tab02" onclick="tab_handler('2', 'success')">결재 승인건</li>
                <li class="tab03" onclick="tab_handler('3', 'reject')">결재 반려건</li>
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
<script>
let mng_certi = "<?php echo $mng_infos['mng_certi']; ?>";
let tabIdx = "<?php echo $tabIdx ?? '1'; ?>";
let tabCode = "<?php echo $tabCode ?? 'all'; ?>";

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
    }

    });
}

$(function(){
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d", minDate:"-365d" });
});
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>