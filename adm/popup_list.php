<?php
$sub_menu = "100100";
require_once './_common.php';

//echo $bbs_code;

auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_popup ";

$sql_search = " where (1) and is_del = '0' ";


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
        case 'pr_name':
            $sql_search .= " (par.{$sfl} like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($pop_app){
    $sql_search .= " and pop_app = '{$pop_app}' ";

    $qstr .= '&pop_app='.$pop_app;
}

if($is_view == '0'){
    $sql_search .= " and is_view = '{$is_view}' ";

    $qstr .= '&is_view=0';
}else if($is_view == '1'){
    $sql_search .= " and is_view = '{$is_view}' ";

    $qstr .= '&is_view=1';
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

$sql_order = " order by is_prior asc, pop_id desc ";

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

$g5['title'] = "팝업 관리";

require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$grade_sql = "SELECT * FROM a_grade WHERE is_del = 0 ORDER BY is_prior asc, gidx asc";
$grade_res = sql_query($grade_sql);


$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 7;

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql;
}
//echo $st_status;
//echo $sub_menu;

?>
<!-- <div class="local_ov01 local_ov">
    <span class="btn_ov01"><span class="ov_txt">총 단지 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
    <a href="?sst=deleted_at&amp;sod=desc&amp;sfl=<?php echo $sfl ?>&amp;stx=<?php echo $stx ?>" class="btn_ov01" data-tooltip-text="탈퇴된 순으로 정렬합니다.&#xa;전체 데이터를 출력합니다."> <span class="ov_txt">운영 </span><span class="ov_num"><?php echo number_format($leave_count) ?>건</span></a>
    <span class="btn_ov01"><span class="ov_txt">해지 </span><span class="ov_num"> <?php echo number_format($stop_count) ?>건 </span></span>
</div> -->


<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

    <label for="sfl" class="sound_only">검색대상</label>
    <div class="serach_box">
        <div class="sch_label">앱</div>
        <div class="sch_selects ver_flex gap15">
            <div class="sch_radios">
                <input type="radio" name="pop_app" id="pop_app1" value="" <?php echo $pop_app == '' ? 'checked' : ''; ?>>
                <label for="pop_app1">전체</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="pop_app" id="pop_app2" value="user" <?php echo $pop_app == 'user' ? 'checked' : ''; ?>>
                <label for="pop_app2">반상회앱</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="pop_app" id="pop_app3" value="sm_mng" <?php echo $pop_app == 'sm_mng' ? 'checked' : ''; ?>>
                <label for="pop_app3">SM매니저앱</label>
            </div>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">상태</div>
        <div class="sch_selects ver_flex gap15">
            <div class="sch_radios">
                <input type="radio" name="is_view" id="is_view1" value="" <?php echo $is_view == '' ? 'checked' : ''; ?>>
                <label for="is_view1">전체</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="is_view" id="is_view2" value="1" <?php echo $is_view == '1' ? 'checked' : ''; ?>>
                <label for="is_view2">노출</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="is_view" id="is_view3" value="0" <?php echo $is_view == '0' ? 'checked' : ''; ?>>
                <label for="is_view3">미노출</label>
            </div>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">검색어</div>
        <div class="sch_selects ver_flex">
            <select name="sfl" id="sfl" class="bansang_sel">
                <option value="pop_title" <?php echo get_selected($sfl, "pop_title"); ?>>제목</option>
            </select>
            <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="stx" value="<?php echo $stx ?>" id="stx"  class="bansang_ipt ver2" size="50">
            <button type="submit" class="bansang_btns ver1">검색</button>
        </div>
    </div>

</form>

<!-- <div class="local_desc01 local_desc">
    <p>
        회원자료 삭제 시 다른 회원이 기존 회원아이디를 사용하지 못하도록 회원아이디, 이름, 닉네임은 삭제하지 않고 영구 보관합니다.
    </p>
</div> -->


<form name="fpopuplist" id="fpopuplist" action="./popup_list_update.php" onsubmit="return fpopuplist_submit(this);" method="post">
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
                    <th scope="col" id="mb_list_chk" >
                        <label for="chkall" class="sound_only">회원 전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th>
                    <th>번호</th>
                    <th>제목</th>
                    <th>앱</th>
                    <th>우선순위</th>
                    <th>노출여부</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                    $class_sql = "SELECT * FROM a_class WHERE is_del = 0 and gidx = '{$row['st_grade']}' order by is_prior asc, cl_idx asc";
                    //echo $class_sql;
                    $class_res = sql_query($class_sql);
                ?>

                    <tr class="<?php echo $bg; ?>">
                        <td headers="mb_list_chk" class="td_chk" >
                            <input type="checkbox" name="chk[]" value="<?php echo $row['pop_id']; ?>" id="chk_<?php echo $i ?>">
                        </td>
                        <td>
                            <?php
                            
                            $startNumber = $total_count - (($page - 1) * $rows);
                            echo $startNumber - $i;
                            // echo $total_count - $startNumber;
                            ?>
                        </td>
                        <td><?php echo $row['pop_title']; ?></td>
                        <td><?php echo $row['pop_app'] == 'user' ? '반상회앱' : 'SM매니저앱'; ?></td>
                        <td><?php echo $row['is_prior']; ?></td>
                        <td><?php echo $row['is_view'] ? '노출' : '미노출'; ?></td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./popup_form.php?<?=$qstr;?>&amp;w=u&amp;pop_id=<? echo $row['pop_id']; ?>" class="btn btn_03">관리</a>
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
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
        <?php if ($is_admin == 'super') { ?>
            <a href="./popup_form.php" id="member_add" class="btn btn_03">팝업 등록</a>
        <?php } ?>
    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
$(function(){
    $("#dates").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
});

function fpopuplist_submit(f) {
    if (!is_checked("chk[]")) {
        alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if (document.pressed == "선택삭제") {
        if (!confirm("선택한 팝업을 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    // if (document.pressed == "선택승인") {
    //     if (!confirm("선택한 회원을 승인하시겠습니까?")) {
    //         return false;
    //     }
    // }

    return true;
}
</script>

<?php
require_once './admin.tail.php';
