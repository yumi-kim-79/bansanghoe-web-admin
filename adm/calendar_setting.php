<?php
$sub_menu = "930910";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_calendar_setting ";

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

$sql_order = " order by is_prior asc, cal_id asc ";

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

$g5['title'] = "캘린더 관리";

require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$grade_sql = "SELECT * FROM a_grade WHERE is_del = 0 ORDER BY is_prior asc, gidx asc";
$grade_res = sql_query($grade_sql);


$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 6;

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


<form name="fcalendarsetting" id="fcalendarsetting" action="./calendar_setting_update.php" onsubmit="return fcalendarsetting_submit(this);" method="post">
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
                    <th>캘린더명</th>
                    <th>작성권한</th>
                    <th>읽기권한</th>
                    <th>노출여부</th>
                    <!-- <th scope="col" id="mb_list_mng">관리</th> -->
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                ?>

                    <tr class="<?php echo $bg; ?>">
                        <!-- <td headers="mb_list_chk" class="td_chk" >
                            <input type="hidden" name="st_id[<?php echo $i ?>]" value="<?php echo $row['st_id'] ?>" id="st_id_<?php echo $i ?>">
                            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['st_name']); ?> <?php echo get_text($row['st_name']); ?>님</label>
                            <input type="checkbox" name="chk[]" value="<?php echo $i; ?>" id="chk_<?php echo $i ?>">
                        </td> -->
                        <td>
                            <?php 
                            $codes = mb_substr($row['cal_code'], 0, 3); 
                            if($codes == 'etc'){
                            ?>
                            <input type="hidden" name="cal_id[]" value="<?php echo $row['cal_id']; ?>">
                            <input type="text" name="cal_name[]" class="frm_input" value="<?php echo $row['cal_name']; ?>" style="text-align:center;">
                            <?php }else{ ?>
                                <?php  echo $row['cal_name']; ?>
                            <?php }?>
                        </td>
                        <td>
                            <?php
                            $cal_write_auth = explode(",", $row['cal_write_auth']);

                            if($row['cal_write_auth'] == "A,B,C,D"){
                                echo "전체";
                            }else{
                                
                                $auth_names = "";
                                for($j=0;$j<count($cal_write_auth);$j++){
                                    $auth_row = sql_fetch("SELECT * FROM a_auth WHERE auth_code = '{$cal_write_auth[$j]}'");
                                    
                                    $auth_names .= $j != 0 ? ", " : "";
                                    $auth_names .= $auth_row['auth_name'];
                                }

                                echo $auth_names;
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $cal_red_auth = explode(",", $row['cal_red_auth']);

                            if($row['cal_red_auth'] == "A,B,C,D"){
                                echo "전체";
                            }else{
                                
                                $auth_names = "";
                                for($j=0;$j<count($cal_red_auth);$j++){
                                    $auth_row = sql_fetch("SELECT * FROM a_auth WHERE auth_code = '{$cal_red_auth[$j]}'");
                                    
                                    $auth_names .= $j != 0 ? ", " : "";
                                    $auth_names .= $auth_row['auth_name'];
                                }

                                echo $auth_names;
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            $codes = mb_substr($row['cal_code'], 0, 3); 
                            if($codes == 'etc'){
                            ?>
                            <select name="is_view[]" id="is_view<?php echo $i + 1;?>">
                                <option value="1" <?php echo $row['is_view'] == '1' ? 'selected' : ''; ?>>노출</option>
                                <option value="0" <?php echo $row['is_view'] == '0' ? 'selected' : ''; ?>>미노출</option>
                            </select>
                            <?php }else{ ?>
                            <?php echo $row['is_view'] == '0' ? '미노출' : '노출'; ?>
                            <?php }?>
                        </td>
                        <!-- <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./banner_form.php?<?=$qstr;?>&amp;w=u&amp;st_idx=<? echo $row['st_idx']; ?>" class="btn btn_03">관리</a>
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
        <input type="submit" name="act_button" value="수정" onclick="document.pressed=this.value" class="btn btn_03">
        <!-- <?php if ($is_admin == 'super') { ?>
            <a href="./banner_form.php" id="member_add" class="btn btn_03">배너 등록</a>
        <?php } ?> -->
    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
$(function(){
    $("#dates").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
});



function fcalendarsetting_submit(f) {
    // if (!is_checked("chk[]")) {
    //     alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
    //     return false;
    // }

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
