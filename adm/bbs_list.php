<?php
require_once './_common.php';

switch($bbs_code){
    case "notice":
        $sub_menu = "920100";
    break;
    case "security":
        $sub_menu = "920200";
    break;
    case "bill":
        $sub_menu = "920300";
    break;
    case "onsite_schedule":
        $sub_menu = "920400";
    break;
    case "team_leader":
        $sub_menu = "920500";
    break;
    case "etc1":
        $sub_menu = "920600";
    break;
    case "etc2":
        $sub_menu = "920700";
    break;
    case "etc3":
        $sub_menu = "920800";
    break;
    case "etc4":
        $sub_menu = "920900";
    break;
    case "etc5":
        $sub_menu = "920910";
    break;
}

$bbs_setting = sql_fetch("SELECT bbs_title FROM a_bbs_setting WHERE bbs_code = '{$bbs_code}'");
//echo $bbs_code;

auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_bbs ";

$sql_search = " where (1) and bbs_code = '{$bbs_code}' and is_del = '0' ";


$qstr .= "&bbs_code={$bbs_code}";

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

if($st_grade){
    $sql_search .= " and std.st_grade = '{$st_grade}' ";

    $qstr .= '&st_grade='.$st_grade;
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

$sql_order = " order by bbs_idx desc ";

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

$g5['title'] = "사내용 게시판 - ".$bbs_setting['bbs_title'];

require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$grade_sql = "SELECT * FROM a_grade WHERE is_del = 0 ORDER BY is_prior asc, gidx asc";
$grade_res = sql_query($grade_sql);


$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 5;

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
    <input type="hidden" name="bbs_code" value="<?php echo $bbs_code; ?>">
    <label for="sfl" class="sound_only">검색대상</label>
    <div class="serach_box">
        <div class="sch_label">검색어</div>
        <div class="sch_selects ver_flex">
            <select name="sfl" id="sfl" class="bansang_sel">
                <option value="bbs_title" <?php echo get_selected($sfl, "bbs_title"); ?>>제목</option>
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

<style>
    .bbs_tables {width: 100%;table-layout:fixed;}
    .bbs_tables tr th.bbs_th1 {width: 100px;}
    .bbs_tables tr th.bbs_th3 {width: 180px;}
</style>
<form name="fbbslist" id="fbbslist" action="./bbs_list_update.php" onsubmit="return fbbslist_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="bbs_code" value="<?php echo $bbs_code; ?>">
    <input type="hidden" name="token" value="">

    <div class="tbl_head01 tbl_wrap">
        <table class="bbs_tables">
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr>
                    <!-- <th scope="col" id="mb_list_chk" >
                        <label for="chkall" class="sound_only">회원 전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th> -->
                    <th class="bbs_th1">번호</th>
                    <th class="bbs_th2">제목</th>
                    <th class="bbs_th3">작성일</th>
                    <th class="bbs_th1">노출여부</th>
                    <th class="bbs_th1">상세</th>
                    <!-- <th scope="col" id="mb_list_mng" class="bbs_th1">관리</th> -->
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
                        <!-- <td headers="mb_list_chk" class="td_chk" >
                            <input type="checkbox" name="chk[]" value="<?php echo $row['bbs_idx']; ?>" id="chk_<?php echo $i ?>">
                        </td> -->
                        <td>
                            <?php
                            
                            $startNumber = $total_count - (($page - 1) * $rows);
                            echo $startNumber - $i;
                            // echo $total_count - $startNumber;
                            ?>
                        </td>
                        <td><?php echo $row['bbs_title']; ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td><?php echo $row['is_view'] == '1' ? '노출' : '미노출'; ?></td>
                        <td class="td_mng td_mng_s">
                            <a href="./bbs_view.php?bbs_code=<?php echo $bbs_code?>&<?=$qstr;?>&amp;w=u&amp;bbs_idx=<? echo $row['bbs_idx']; ?>" class="btn btn_03">상세</a>
                        </td>
                        <!-- <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./bbs_form.php?bbs_code=<?php echo $bbs_code?>&<?=$qstr;?>&amp;w=u&amp;bbs_idx=<? echo $row['bbs_idx']; ?>" class="btn btn_03">관리</a>
                        </td> -->
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
        <!-- <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02"> -->
        <?php if ($is_admin == 'super') { ?>
            <a href="./bbs_form.php?bbs_code=<?php echo $bbs_code?>" id="member_add" class="btn btn_03">게시글 등록</a>
        <?php } ?>
    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
$(function(){
    $("#dates").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
});

function fbbslist_submit(f) {
    if (!is_checked("chk[]")) {
        alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if (document.pressed == "선택삭제") {
        if (!confirm("선택한 게시글을 정말 삭제하시겠습니까?")) {
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
