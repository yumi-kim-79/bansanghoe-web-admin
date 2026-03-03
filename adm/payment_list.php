<?php
$sub_menu = "810200";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_senior as sn left join a_contract as ct on sn.ct_idx = ct.ct_idx left join a_building as building on ct.building_id = building.building_id ";

$sql_search = " where (1) and sn.not_use = '0' and sn.sn_name != '' ";


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


if($banner_sdate != "" && $banner_edate == ""){
    $sql_search .= " and banner_edate >= '{$banner_sdate}' ";

    $qstr .= '&banner_sdate='.$banner_sdate;
}else if($banner_sdate == "" && $banner_edate != ""){
    $sql_search .= " and banner_sdate <= '{$banner_edate}' ";

    $qstr .= '&banner_edate='.$banner_edate;

}else if($banner_sdate != "" && $banner_edate != ""){
    $sql_search .= " and banner_sdate <= '{$banner_edate}' and banner_edate >= '{$banner_sdate}' ";

    $qstr .= '&banner_sdate='.$banner_sdate.'&banner_edate='.$banner_edate;
}


if($banner_area){
    $sql_search .= " and banner_area = '{$banner_area}' ";

    $qstr .= '&banner_area='.$banner_area;
}

if ($is_admin != 'super') {
    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
}

if (!$sst) {
    $sst = "std.st_idx";
    $sod = "desc";
}

$sql_order = " order by sn.sn_idx desc ";

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

$g5['title'] = "선임자 정보 관리";

require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$sql = " select sn.*, ct.building_id, ct.company_name, ct.industry_name, building.building_name, DATEDIFF(sn.edu_edate, CURDATE()) as dday {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
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
        <div class="sch_label">업종</div>
        <div class="sch_selects ver_flex">
            <input type="text" name="banner_sdate" class="bansang_ipt ver2 ipt_date" value="<?php echo $banner_sdate; ?>"> ~ 
            <input type="text" name="banner_edate" class="bansang_ipt ver2 ipt_date" value="<?php echo $banner_edate; ?>">
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">단지명</div>
        <div class="sch_selects">
            <input type="text" name="building_name" value="<?php echo $stx ?>" id="stx"  class="bansang_ipt ver2" size="50">
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">선임자명</div>
        <div class="sch_selects">
            <input type="text" name="sn_name" value="<?php echo $stx ?>" id="stx"  class="bansang_ipt ver2" size="50">
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">정렬순서</div>
        <div class="sch_selects ver_flex">
            <select name="sfl" id="sfl" class="bansang_sel">
                <option value="banner_name" <?php echo get_selected($sfl, "banner_name"); ?>>단지명 순</option>
                <option value="banner_name" <?php echo get_selected($sfl, "banner_name"); ?>>D-day순</option>
            </select>
            <button type="submit" class="bansang_btns ver1">검색</button>
        </div>
    </div>

</form>

<!-- <div class="local_desc01 local_desc">
    <p>
        회원자료 삭제 시 다른 회원이 기존 회원아이디를 사용하지 못하도록 회원아이디, 이름, 닉네임은 삭제하지 않고 영구 보관합니다.
    </p>
</div> -->


<form name="fbannerlist" id="fbannerlist" action="./banner_list_update.php" onsubmit="return fbannerlist_submit(this);" method="post">
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
                    <th>단지명</th>
                    <th>업체명</th>
                    <th>업종</th>
                    <th>선임자명</th>
                    <th>연락처</th>
                    <th>선임일</th>
                    <th>선임기간</th>
                    <th>교육이수일</th>
                    <th>교육만료일</th>
                    <th>D-day</th>
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
                            <input type="checkbox" name="chk[]" value="<?php echo $row['banner_id']; ?>" id="chk_<?php echo $i ?>">
                        </td>
                        <td><?php echo $row['building_name']; ?></td>
                        <td><?php echo $row['company_name']; ?></td>
                        <td><?php echo $row['industry_name']; ?></td>
                        <td><?php echo $row['sn_name']; ?></td>
                        <td><?php echo $row['sn_hp']; ?></td>
                        <td><?php echo $row['sn_date']; ?></td>
                        <td><?php echo $row['sn_sdate'].' ~ '.$row['sn_edate']; ?></td>
                        <td><?php echo $row['edu_sdate'].' ~ '.$row['edu_edate']; ?></td>
                        <td><?php echo $row['edu_edate']; ?></td>
                        <td><?php echo $row['dday'] == '' ? '0' : $row['dday']; ?></td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <!-- <a href="./banner_form.php?<?=$qstr;?>&amp;w=u&amp;banner_id=<? echo $row['banner_id']; ?>" class="btn btn_03">관리</a> -->
                            <button class="btn btn_03">관리</button>
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
        <!-- <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
        <?php if ($is_admin == 'super') { ?>
            <a href="./banner_form.php" id="member_add" class="btn btn_03">배너 등록</a>
        <?php } ?> -->
    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
$(function(){
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"-365d" });
});

function fbannerlist_submit(f) {
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
