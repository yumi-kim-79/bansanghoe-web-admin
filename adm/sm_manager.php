<?php
$sub_menu = "900100";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_mng ";

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    $sql_search = " where (1) and is_del = '0' ";
}else{
    $sql_search = " where (1) and is_del = '0' and mng_id != 'admin' ";
}

// $sql_search = " where (1) and is_del = '0' and mng_id != 'admin' ";

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

if($mng_status){
    $sql_search .= " and mng_status = '{$mng_status}' ";

    $qstr .= '&mng_status='.$mng_status;
}

if($mng_certi){
    $sql_search .= " and mng_certi = '{$mng_certi}' ";

    $qstr .= '&mng_certi='.$mng_certi;
}

if($mng_department){
    $sql_search .= " and mng_department = '{$mng_department}' ";

    $qstr .= '&mng_department='.$mng_department;
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

$sql_order = " order by mng_idx desc ";

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

$g5['title'] = "담당자 관리";

require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$grade_sql = "SELECT * FROM a_grade WHERE is_del = 0 ORDER BY is_prior asc, gidx asc";
$grade_res = sql_query($grade_sql);


$sql = " select * {$sql_common} {$sql_search} {$sql_search2} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 9;

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
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
        <div class="sch_label">상태</div>
        <div class="sch_selects ver_flex gap15">
            <div class="sch_radios">
                <input type="radio" name="mng_status" id="status1" value="" <?php echo $mng_status == "" ? "checked" : ""; ?>>
                <label for="status1">전체</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="mng_status" id="status2" value="1" <?php echo $mng_status == "1" ? "checked" : ""; ?>>
                <label for="status2">정상</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="mng_status" id="status3" value="2" <?php echo $mng_status == "2" ? "checked" : ""; ?>>
                <label for="status3">퇴사</label>
            </div>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">부서</div>
        <div class="sch_selects ver_flex">
            <?php
            $dpt_list = "SELECT * FROM a_mng_department WHERE is_del = 0 ORDER BY is_prior asc, md_idx asc";
            $dpt_res = sql_query($dpt_list);
            ?>
            <select name="mng_department" id="mng_department" class="bansang_sel">
                <option value="">부서 전체</option>
                <?php while($dpt_row = sql_fetch_array($dpt_res)){?>
                    <option value="<?php echo $dpt_row['md_idx'];?>" <?php echo get_selected($mng_department, $dpt_row['md_idx']); ?>><?php echo $dpt_row['md_name'];?></option>
                <?php }?>
            </select>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">등급</div>
        <div class="sch_selects ver_flex">
            <select name="mng_certi" id="mng_certi" class="bansang_sel">
                <option value="">등급 전체</option>
                <option value="A" <?php echo get_selected($mng_certi, 'A'); ?>>A</option>
                <option value="B" <?php echo get_selected($mng_certi, 'B'); ?>>B</option>
                <option value="C" <?php echo get_selected($mng_certi, 'C'); ?>>C</option>
                <option value="D" <?php echo get_selected($mng_certi, 'D'); ?>>D</option>
            </select>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">검색어</div>
        <div class="sch_selects ver_flex">
            <select name="sfl" id="sfl" class="bansang_sel">
                <option value="mng_name" <?php echo get_selected($sfl, "mng_name"); ?>>담당자</option>
                <option value="mng_hp" <?php echo get_selected($sfl, "mng_hp"); ?>>전화번호</option>
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
                    <!-- <th scope="col" id="mb_list_chk" >
                        <label for="chkall" class="sound_only">회원 전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th> -->
                    <th>번호</th>
                    <th>부서</th>
                    <th>직급</th>
                    <th>등급</th>
                    <th>담당단지 수</th>
                    <th>이름</th>
                    <th>전화번호</th>
                    <th>상태</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                  
                    $cnt_building = sql_fetch("SELECT COUNT(*) as cnt FROM a_mng_building as mng_b LEFT JOIN a_building as building on mng_b.building_id = building.building_id WHERE mng_b.is_del = 0 and mng_b.mb_id = '{$row['mng_id']}' and building.is_use = 1");

                    
                ?>

                    <tr class="<?php echo $bg; ?>">
                        <!-- <td headers="mb_list_chk" class="td_chk" >
                            <input type="hidden" name="st_id[<?php echo $i ?>]" value="<?php echo $row['st_id'] ?>" id="st_id_<?php echo $i ?>">
                            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['st_name']); ?> <?php echo get_text($row['st_name']); ?>님</label>
                            <input type="checkbox" name="chk[]" value="<?php echo $i; ?>" id="chk_<?php echo $i ?>">
                        </td> -->
                        <td>
                            <?php
                            
                            $startNumber = $total_count - (($page - 1) * $rows);
                            echo $startNumber - $i;
                            // echo $total_count - $startNumber;
                            ?>
                        </td>
                        <td><?php echo get_department_name($row['mng_department']); ?></td>
                        <td><?php echo get_mng_grade_name($row['mng_grades']); ?></td>
                        <td><?php echo $row['mng_certi']; ?></td>
                        <td>
                            <!-- <?php
                            if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                                echo "SELECT COUNT(*) as cnt FROM a_mng_building as mng_b LEFT JOIN a_building as building on mng_b.building_id = building.building_id WHERE mng_b.is_del = 0 and mng_b.mb_id = '{$row['mng_id']}' and building.is_use = 1"."<br>";
                            }
                            ?> -->
                            <?php echo $cnt_building['cnt']; ?>
                        </td>
                        <td><?php echo $row['mng_name']; ?></td>
                        <td><?php echo $row['mng_hp']; ?></td>
                        <td><?php echo $row['mng_status'] == "1" ? "정상" : "퇴사"; ?></td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./sm_manager_form.php?<?=$qstr;?>&amp;w=u&amp;mng_id=<? echo $row['mng_id']; ?>" class="btn btn_03">관리</a>
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
            <a href="./sm_manager_form.php" id="member_add" class="btn btn_03">담당자 등록</a>
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
