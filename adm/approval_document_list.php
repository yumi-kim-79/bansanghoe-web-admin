<?php
$sub_menu = "800200";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " FROM a_sign_off as sign_off
                LEFT JOIN a_sign_off_category AS cate ON sign_off.sign_off_category = cate.sign_cate_code
                LEFT JOIN a_mng AS mng ON sign_off.mng_id = mng.mng_id ";

$mb_ids = $member['mb_id'];

$mng_infos = get_manger($mb_ids);

if($mng_infos['mng_certi'] != 'D'){
    $sql_search = " WHERE sign_off.is_del = 0 ";
}else{
    $sql_search = " WHERE sign_off.is_del = 0 and sign_off.mng_id = '{$mb_ids}' ";
}

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
            $sql_search .= " (mng.{$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($sdate != "" && $edate == ""){
    $sql_search .= " and sign_off.wdate >= '{$sdate}' ";
    $qstr .= '&sdate='.$sdate;
}else if($sdate == "" && $edate != ""){
    $sql_search .= " and sign_off.wdate <= '{$edate}' ";
    $qstr .= '&edate='.$edate;
}else if($sdate != "" && $edate != ""){
    $sql_search .= " and (sign_off.wdate >= '{$sdate}' and sign_off.wdate <= '{$edate}')";
    $qstr .= '&sdate='.$sdate.'&edate='.$edate;
}

if($sign_off_category){
    $sql_search .= " and sign_off.sign_off_category = '{$sign_off_category}' ";
    $qstr .= '&sign_off_category='.$sign_off_category;
}

if($sign_off_status){
    $sql_search .= " and sign_off.sign_status = '{$sign_off_status}' ";
    $qstr .= '&sign_off_status='.$sign_off_status;
}

if($mng_department){
    $sql_search .= " and sign_off.mng_department = '{$mng_department}' ";
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

$sql_order = " order by sign_off.sign_id desc ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);
if ($page < 1) {
    $page = 1;
}
$from_record = ($page - 1) * $rows;

$sql = " select count(*) as cnt {$sql_common} {$sql_search} and std.is_del = 1 {$sql_order} ";
$row = sql_fetch($sql);
$leave_count = $row['cnt'];

$sql = " select count(*) as cnt {$sql_common} {$sql_search} and std.st_status = 2 {$sql_order} ";
$row = sql_fetch($sql);
$stop_count = $row['cnt'];

$listall = '<a href="' . $_SERVER['SCRIPT_NAME'] . '" class="ov_listall">전체목록</a>';

$g5['title'] = "결재서류함";

require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$grade_sql = "SELECT * FROM a_grade WHERE is_del = 0 ORDER BY is_prior asc, gidx asc";
$grade_res = sql_query($grade_sql);

$sql = " SELECT sign_off.*, cate.sign_cate_name, mng.mng_name {$sql_common} {$sql_search} {$sql_search2} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 7;

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $sql.'<br>';
}
?>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

    <label for="sfl" class="sound_only">검색대상</label>
    
    <div class="serach_box">
        <div class="sch_label">날짜</div>
        <div class="sch_selects ver_flex">
            <input type="text" name="sdate" class="bansang_ipt ver2 ipt_date" value="<?php echo $sdate; ?>"> ~ <input type="text" name="edate" class="bansang_ipt ver2 ipt_date" value="<?php echo $edate; ?>">
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">종류</div>
        <div class="sch_selects">
            <?php
            $sign_cate = "SELECT * FROM a_sign_off_category WHERE is_del = 0 ORDER BY is_prior asc, sign_cate_id asc";
            $sign_cate_res = sql_query($sign_cate);
            ?>
            <select name="sign_off_category" id="sign_off_category" class="bansang_sel">
                <option value="">결제서류 전체</option>
                <?php while($sign_cate_row = sql_fetch_array($sign_cate_res)){?>
                    <option value="<?php echo $sign_cate_row['sign_cate_code']; ?>" <?php echo get_selected($sign_off_category, $sign_cate_row['sign_cate_code']); ?>><?php echo $sign_cate_row['sign_cate_name']; ?></option>
                <?php }?>
            </select>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">상태</div>
        <div class="sch_selects ver_flex gap15">
            <div class="sch_radios">
                <input type="radio" name="sign_off_status" id="status1" value="" <?php echo $sign_off_status == "" ? "checked" : ""; ?>>
                <label for="status1">전체</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="sign_off_status" id="status2" value="E" <?php echo $sign_off_status == "E" ? "checked" : ""; ?>>
                <label for="status2">승인</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="sign_off_status" id="status3" value="N" <?php echo $sign_off_status == "N" ? "checked" : ""; ?>>
                <label for="status3">승인대기</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="sign_off_status" id="status4" value="P" <?php echo $sign_off_status == "P" ? "checked" : ""; ?>>
                <label for="status4">승인중</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="sign_off_status" id="status5" value="R" <?php echo $sign_off_status == "R" ? "checked" : ""; ?>>
                <label for="status5">반려</label>
            </div>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">부서</div>
        <div class="sch_selects">
            <?php
            $department_sql = "SELECT * FROM a_mng_department WHERE is_del = 0 ORDER BY is_prior asc, md_idx asc";
            $department_res = sql_query($department_sql);
            ?>
            <select name="mng_department" id="mng_department" class="bansang_sel">
                <option value="">부서 전체</option>
                <?php while($department_row = sql_fetch_array($department_res)){?>
                    <option value="<?php echo $department_row['md_idx']; ?>" <?php echo get_selected($mng_department, $department_row['md_idx']); ?>><?php echo $department_row['md_name']; ?></option>
                <?php }?>
            </select>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">검색어</div>
        <div class="sch_selects ver_flex">
            <select name="sfl" id="sfl" class="bansang_sel">
                <option value="mng_name" <?php echo get_selected($sfl, "mng_name"); ?>>작성자</option>
            </select>
            <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="bansang_ipt ver2" size="50">
            <button type="submit" class="bansang_btns ver1">검색</button>
        </div>
    </div>

</form>

<form name="fstudentlist" id="fstudentlist" action="./student_list_update.php" onsubmit="return fstudentlist_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">

    <div class="tbl_head01 tbl_head03 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr>
                    <th>번호</th>
                    <th>결재종류</th>
                    <th>등록일</th>
                    <th>부서</th>
                    <th>작성자</th>
                    <th>상태</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                    $class_sql = "SELECT * FROM a_class WHERE is_del = 0 and gidx = '{$row['st_grade']}' order by is_prior asc, cl_idx asc";
                    $class_res = sql_query($class_sql);
                ?>
                    <tr class="<?php echo $row['mng_id'] == $mb_ids ? 'status_n' : ''; ?>">
                        <td>
                            <?php
                            $startNumber = $total_count - (($page - 1) * $rows);
                            echo $startNumber - $i;
                            ?>
                        </td>
                        <td><?php echo approval_category_name($row['sign_off_category']); ?></td>
                        <td><?php echo $row['wdate']; ?></td>
                        <td><?php echo get_department_name($row['mng_department']); ?></td>
                        <td><?php echo get_member($row['mng_id'])['mb_name']; ?></td>
                        <td style='text-align:center;'>
                            <?php
                            switch($row['sign_status']){
                                case "N": $status = "승인대기"; break;
                                case "P": $status = "승인중"; break;
                                case "E": $status = "승인완료"; break;
                                case "R": $status = "반려"; break;
                            }
                            echo $status;
                            $adm_sign_steps = [];
                            for($si=1;$si<=3;$si++){
                                $mng_id_key = "sign_off_mng_id{$si}";
                                $status_key = $si == 1 ? "sign_off_status" : "sign_off_status{$si}";
                                $mng_id_val = $row[$mng_id_key];
                                if($mng_id_val != ''){
                                    $mng_info = sql_fetch("SELECT mg.mg_name FROM a_mng as m LEFT JOIN a_mng_grade as mg ON m.mng_grades = mg.mg_idx WHERE m.mng_id = '{$mng_id_val}'");
                                    $adm_sign_steps[] = ['grades' => $mng_info['mg_name'] ? $mng_info['mg_name'] : $si.'차', 'signed' => $row[$status_key]];
                                }
                            }
                            if(count($adm_sign_steps) > 0){
                                echo '<div class="adm_sign_steps">';
                                foreach($adm_sign_steps as $step){
                                    $cls = $step['signed'] == 1 ? 'adm_signed' : 'adm_unsigned';
                                    $icon = $step['signed'] == 1 ? '✓' : '–';
                                    echo "<span class='adm_sign_step {$cls}'>{$step['grades']} {$icon}</span>";
                                }
                                echo '</div>';
                            }
                            ?>
                        </td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./approval_info.php?<?=$qstr;?>&amp;w=u&amp;sign_id=<? echo $row['sign_id']; ?>" class="btn btn_03">관리</a>
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
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
$(function(){
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
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

?>
<style>
.adm_sign_steps{display:flex;flex-wrap:wrap;gap:4px;margin-top:4px;justify-content:center;}
td .adm_sign_steps{text-align:center;}
.adm_sign_step{display:inline-block;padding:2px 7px;border-radius:10px;font-size:11px;border:1px solid #ddd;}
.adm_signed{background:#e8f5e9;border-color:#4caf50;color:#2e7d32;}
.adm_unsigned{background:#f5f5f5;border-color:#ccc;color:#999;}
</style>
<?php
require_once './admin.tail.php';
