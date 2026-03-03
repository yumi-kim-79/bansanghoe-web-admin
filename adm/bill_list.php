<?php
$sub_menu = "300600";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_bill as bill
                left join a_post_addr as post on bill.post_id = post.post_idx 
                left join a_building as building on bill.building_id = building.building_id 
                left join g5_member as mb on bill.wid = mb.mb_id ";

$sql_search = " where (1) and bill.is_del = '0' and building.is_use = 1 ";


if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_point':
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_level':
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'mb_tel':
        case 'mb_name':
            $sql_search .= " (mb.{$sfl} like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " (building.{$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}


if($status){
    if($status == "RS"){
        $sql_search .= " and bill.is_submit = 'R' ";
    }else if($status == "S"){
        $sql_search .= " and bill.is_submit = 'Y' ";
    }else{
        $sql_search .= " and bill.is_submit = 'N' ";
    }
     
    $qstr .= '&status='.$status;
}


if($bill_year){
    $sql_search .= " and bill.bill_year = '{$bill_year}' ";

    $qstr .= '&bill_year='.$bill_year;
}

if($bill_month){
    $sql_search .= " and bill.bill_month = '{$bill_month}' ";

    $qstr .= '&bill_month='.$bill_month;
}

if($post_id){
    $sql_search .= " and post.post_idx = '{$post_id}' ";

    $qstr .= '&post_id='.$post_id;
}

if($st_gender == '0'){
    $sql_search .= " and std.st_gender = '{$st_gender}' ";

    $qstr .= '&st_gender=0';
}else if($st_gender == '1'){
    $sql_search .= " and std.st_gender = '{$st_gender}' ";

    $qstr .= '&st_gender=1';
}

if($sst == 'deleted_at'){
    $sql_search2 .= " and std.is_del = 1 ";
}

if ($is_admin != 'super') {
    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
}

if (!$sst) {
    $sst = "std.st_idx";
    $sod = "desc";
}

$sql_order = " order by bill.bill_year + 1 desc, bill.bill_month + 1 desc, bill.bill_id desc ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
//echo $sql.'<br>';
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
//$rows = 5;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// 탈퇴회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and std.is_del = 1 {$sql_order} ";
//echo $sql;
$row = sql_fetch($sql);
$leave_count = $row['cnt'];

$sql = " select count(*) as cnt {$sql_common} {$sql_search} and std.st_status = 2 {$sql_order} ";
//echo $sql;
$row = sql_fetch($sql);
$stop_count = $row['cnt'];

$listall = '<a href="' . $_SERVER['SCRIPT_NAME'] . '" class="ov_listall">전체목록</a>';

$g5['title'] = "고지서 관리";
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$grade_sql = "SELECT * FROM a_grade WHERE is_del = 0 ORDER BY is_prior asc, gidx asc";
$grade_res = sql_query($grade_sql);


$sql = " select bill.*, post.post_idx, post.post_name, building.building_name, building.is_use, mb.mb_name {$sql_common} {$sql_search} {$sql_search2} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 10;

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

$nowYear = date("Y");
$bfYear = $nowYear - 1;
$afYear = $nowYear + 1;

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $sql;
}
//echo $st_status;
//echo $sub_menu;

?>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

    <label for="sfl" class="sound_only">검색대상</label>
    <div class="serach_box">
        <div class="sch_label">상태</div>
        <div class="sch_selects ver_flex gap15">
        <div class="sch_radios">
                <input type="radio" name="status" id="status0" value="" <?php echo $status == '' ? 'checked' : '';?>>
                <label for="status0">전체</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="status" id="status1" value="SS" <?php echo $status == 'SS' ? 'checked' : '';?>>
                <label for="status1">저장</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="status" id="status2" value="RS" <?php echo $status == 'RS' ? 'checked' : '';?>>
                <label for="status2">예약발행</label>
            </div>
            <!-- <div class="sch_radios">
                <input type="radio" name="status" id="status3" value="방문 중">
                <label for="status3">방문 중</label>
            </div> -->
            <div class="sch_radios">
                <input type="radio" name="status" id="status4" value="S" <?php echo $status == 'S' ? 'checked' : '';?>>
                <label for="status4">발행</label>
            </div>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">년/월</div>
        <div class="sch_selects ver_flex">
            <select name="bill_year" id="bill_year" class="bansang_sel">
                <option value="">년</option>
                <?php for($i=$bfYear;$i<=$afYear;$i++){?>
                    <option value="<?php echo $i; ?>" <?php echo $bill_year == $i ? 'selected' : '';?>><?php echo $i; ?>년</option>
                <?php }?>
            </select>
            <select name="bill_month" id="bill_month" class="bansang_sel">
                <option value="">월</option>
                <?php for($i=1;$i<=12;$i++){?>
                    <option value="<?php echo $i; ?>" <?php echo $bill_month == $i ? 'selected' : '';?>><?php echo $i; ?>월</option>
                <?php }?>
            </select>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">지역</div>
        <div class="sch_selects">
            <select name="post_id" id="post_id" class="bansang_sel">
                <option value="">지역 선택</option>
                <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                    <option value="<?php echo $post_row['post_idx']; ?>" <?php echo get_selected($post_id, $post_row['post_idx']); ?>><?php echo $post_row['post_name']; ?></option>
                <?php }?>
            </select>
        </div>
    </div>
    
    <div class="serach_box">
        <div class="sch_label">검색어</div>
        <div class="sch_selects ver_flex">
            <select name="sfl" id="sfl" class="bansang_sel">
                <option value="building_name" <?php echo get_selected($sfl, "building_name"); ?>>단지명</option>
                <option value="mb_name" <?php echo get_selected($sfl, "mb_name"); ?>>작성자명</option>
            </select>
            <div class="sch_ipt_boxs">
                <div class="sch_result_box sch_result_box1">
                </div>
                <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
                <input type="text" name="stx" value="<?php echo $stx ?>" id="stx"  class="bansang_ipt ver2 building_name_sch" size="50">
            </div>
            <button type="submit" class="bansang_btns ver1">검색</button>
        </div>
    </div>

</form>
<script>
     $(document).on("keyup", ".building_name_sch", function(){
        let sch_text = this.value;
        let sch_category = $("#sfl option:selected").val();
        console.log('keyup',sch_text);

        if(sch_text != "" && sch_category == 'building_name'){
           
            let type = "<?php echo $type; ?>";

            console.log('building_name', sch_category);
            $.ajax({

            url : "./house_hold_list_sch_text.php", //ajax 통신할 파일
            type : "POST", // 형식
            data: { "sch_category":sch_category, "sch_text":sch_text, "type":"Y"}, //파라미터 값
            success: function(msg){ //성공시 이벤트

             
                console.log(msg);
                $(".sch_result_box1").html(msg); //.select_box2에 html로 나타내라..
            }
            })
        }else{
            $(".sch_result_box1").html("");
        }
      
    });

    function sch_handler(text){
        $(".sch_result_box1").html("");
        $(".building_name_sch").val(text);
    }
</script>
<!-- <div class="local_desc01 local_desc">
    <p>
        회원자료 삭제 시 다른 회원이 기존 회원아이디를 사용하지 못하도록 회원아이디, 이름, 닉네임은 삭제하지 않고 영구 보관합니다.
    </p>
</div> -->


<form name="fstudentlist" id="fstudentlist" action="./student_list_update.php" onsubmit="return fstudentlist_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">

    <div class="tbl_head01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr>
                    <!-- <th scope="col" id="mb_list_chk" >
                        <label for="chkall" class="sound_only">회원 전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th> -->
                    <th>번호</th>
                    <th>지역</th>
                    <th>단지명</th>
                    <th>고지서 월</th>
                    <th>납부기한</th>
                    <th>부서</th>
                    <th>등록자</th>
                    <th>상태</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                
                    $writer_info = get_manger($row['wid']);

                    //print_r2($writer_info);
                    $status = '';
                    switch($row['is_submit']){
                        case 'N':
                            $status = '저장';
                            break;
                        case 'R':
                            $status = '예약발행';
                            break;
                        case 'C':
                            $status = '발행취소';
                            break;
                        case 'Y':
                            $status = '발행';
                            break;
                    }
                ?>

                    <tr class="<?php echo $bg; ?>">
                        <!-- <td headers="mb_list_chk" class="td_chk" >
                            <input type="checkbox" name="chk[]" value="<?php echo $row['bill_id']; ?>" id="chk_<?php echo $i ?>">
                        </td> -->
                        <td>
                            <?php
                            
                            $startNumber = $total_count - (($page - 1) * $rows);
                            echo $startNumber - $i;
                            // echo $total_count - $startNumber;
                            ?>
                        </td>
                        <td><?php echo $row['post_name']; ?></td>
                        <td><?php echo $row['building_name']; ?></td>
                        <td><?php echo $row['bill_year'].'.'.$row['bill_month']; ?></td>
                        <td><?php echo $row['bill_due_date']; ?></td>
                        <td><?php echo $writer_info['md_name']; ?></td>
                        <td><?php echo $row['mb_name']; ?></td>
                        <td><?php echo $status; ?></td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./bill_form.php?<?=$qstr;?>&amp;w=u&amp;bill_id=<? echo $row['bill_id']; ?>" class="btn btn_03">관리</a>
                        </td>
                    </tr>
                <?php
                }
                if ($i == 0) {
                    echo "<tr><td colspan=\"" . $colspan . "\" class=\"empty_table\">자료가 없습니다.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="btn_fixed_top">
        <!-- <button type="button" onClick="popOpen('account_number_pop');" class="btn btn_02">납부계좌 관리</button>
        <button type="button" onClick="popOpen('bill_content_pop');" class="btn btn_02">고지서 공지사항 관리</button> -->
        <?php if ($is_admin == 'super') { ?>
            <a href="./bill_form.php" id="member_add" class="btn btn_03">고지서 추가</a>
        <?php } ?>
    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<?php
$bill_mng_info = sql_fetch("SELECT * FROM a_bill_mng");
?>
<div class="cm_pop" id="account_number_pop" >
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('account_number_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">고지서 납부계좌</div>
        <div class="bill_account_box_wrapper mgt20">
            <input type="text" name="bill_account_name" id="bill_account_name" class="frm_input" placeholder="은행명을 입력하세요." value="<?php echo $bill_mng_info['bill_account_name']; ?>">
        </div>
        <div class="bill_account_box_wrapper mgt10">
            <input type="text" name="bill_account" id="bill_account" class="frm_input" placeholder="납부 계좌를 입력하세요." value="<?php echo $bill_mng_info['bill_account']; ?>">
        </div>
		<div class="cm_pop_btn_box flex_ver">
            <button type="button" class="cm_pop_btn" onClick="popClose('account_number_pop');">취소</button>
			<button type="button" class="cm_pop_btn ver2" onClick="bill_bank_info_save();">저장</button>
		</div>
        <script>
            function bill_bank_info_save(){
                let bill_account_name = $("#bill_account_name").val();
                let bill_account = $("#bill_account").val();

                let sendData = {'bill_account_name':bill_account_name, 'bill_account':bill_account, 'types':'bank'};

                $.ajax({
                    type: "POST",
                    url: "./bill_account_info_ajax.php",
                    data: sendData,
                    cache: false,
                    async: false,
                    dataType: "json",
                    success: function(data) {
                        console.log('data:::', data);

                        if(data.result == false) { 
                            alert(data.msg);
                            $("#" + data.data).focus();
                            //$(".btn_submit").attr('disabled', false);
                            return false;
                        }else{
                            alert(data.msg);

                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }
                    }
                });
            }
        </script>
	</div>
</div>

<div class="cm_pop" id="bill_content_pop" >
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('bill_content_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">고지서 공지사항</div>
        <div class="bill_account_box_wrapper mgt20">
            <textarea name="bill_memo" id="bill_memo"><?php echo $bill_mng_info['bill_memo']; ?></textarea>
        </div>
		<div class="cm_pop_btn_box flex_ver">
            <button type="button" class="cm_pop_btn" onClick="popClose('account_number_pop');">취소</button>
			<button type="button" class="cm_pop_btn ver2" onClick="bill_bank_memo_save();">저장</button>
		</div>
        <script>
            function bill_bank_memo_save(){
                let bill_memo = $("#bill_memo").val();

                let sendData = {'bill_memo':bill_memo, 'types':'info'};

                $.ajax({
                    type: "POST",
                    url: "./bill_account_info_ajax.php",
                    data: sendData,
                    cache: false,
                    async: false,
                    dataType: "json",
                    success: function(data) {
                        console.log('data:::', data);

                        if(data.result == false) { 
                            alert(data.msg);
                            $("#" + data.data).focus();
                            //$(".btn_submit").attr('disabled', false);
                            return false;
                        }else{
                            alert(data.msg);

                            setTimeout(() => {
                                location.reload();
                            }, 300);
                        }
                    }
                });
            }
        </script>
	</div>
</div>

<script>
$(function(){
    $("#dates").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
});

function fstudentlist_submit(f) {
    if (!is_checked("chk[]")) {
        alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if (document.pressed == "선택삭제") {
        if (!confirm("선택한 회원을 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    if (document.pressed == "선택승인") {
        if (!confirm("선택한 회원을 승인하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
require_once './admin.tail.php';
