<?php
$sub_menu = "100200";
require_once './_common.php';

//echo $bbs_code;

auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_push_send_history ";

$sql_search = " where (1) ";


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
        case 'created_at':
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
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

$sql_order = " order by ph_idx desc ";

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

$g5['title'] = "푸시알림 관리";

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

    <label for="sfl" class="sound_only">검색대상</label>
    <div class="serach_box">
        <div class="sch_label">검색어</div>
        <div class="sch_selects ver_flex">
            <select name="sfl" id="sfl" class="bansang_sel">
                <option value="created_at" <?php echo get_selected($sfl, "created_at"); ?>>발송날짜</option>
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
                    <th>번호</th>
                    <th>발송날짜</th>
                    <th>내용</th>
                    <th>발송인원</th>
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
                         <td>
                            <?php
                            
                            $startNumber = $total_count - (($page - 1) * $rows);
                            echo $startNumber - $i;
                            // echo $total_count - $startNumber;
                            ?>
                        </td>
                        <td><?php echo date("Y-m-d", strtotime($row['created_at'])); ?></td>
                        <td><?php echo $row['push_content']; ?></td>
                        <td><?php echo $row['ph_cnt']; ?></td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./push_form.php?<?=$qstr;?>&amp;w=u&amp;push_id=<? echo $row['ph_idx']; ?>" class="btn btn_03">관리</a>
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
        <?php if ($is_admin == 'super') { ?>
            <a href="./push_form.php" id="member_add" class="btn btn_03">푸시 발송</a>
        <?php } ?>
    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

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
