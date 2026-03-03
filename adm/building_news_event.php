<?php
$sub_menu = "200800";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_building_bbs as bb left join a_post_addr as post on bb.post_id = post.post_idx left join a_building as building on bb.building_id = building.building_id 
left join g5_member as mem on mem.mb_id = bb.wid ";

$sql_search = " where (1) and bb.is_del = '0' and bb.bbs_type = 'event' ";

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_point':
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_level':
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'mb_name':
            $sql_search .= " (mem.mb_name like '%{$stx}%') ";
            break;
        case 'building_name':
            if($stx == '전체'){
                $sql_search .= " (bb.building_id = '-1') ";
            }else{
                $sql_search .= " (building.{$sfl} like '%{$stx}%') ";
            }
            break;
        default:
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($created_at){
    $sql_search .= " and bb.created_at like '{$created_at}%' ";

    $qstr .= '&created_at='.$created_at;
}

if($is_view == "1"){
    $sql_search .= " and is_view = 1 ";

    $qstr .= '&is_view='.$is_view;
}else if($is_view == "0"){
    $sql_search .= " and is_view = 0 ";

    $qstr .= '&is_view='.$is_view;
}

if($post_id){
    $sql_search .= " and bb.post_id = '{$post_id}' ";

    $qstr .= '&post_id='.$post_id;
}

if ($is_admin != 'super') {
    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
}

if (!$sst) {
    $sst = "std.st_idx";
    $sod = "desc";
}

$sql_order = " order by bb.bb_id desc ";

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

$g5['title'] = "이벤트";
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$sql = " select bb.*, mem.mb_name, post.post_name, IFNULL(building.building_name, '전체') as building_name {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

$colspan = 11;

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql;
}
//echo $st_status;
//echo $sub_menu;

?>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
    <label for="sfl" class="sound_only">검색대상</label>
    <div class="serach_box">
        <div class="sch_label">작성일</div>
        <div class="sch_selects ver_flex">
            <input type="text" name="created_at" class="bansang_ipt ver2 ipt_date" id="dates" value="<?php echo $created_at; ?>">
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">상태</div>
        <div class="sch_selects ver_flex gap15">
            <div class="sch_radios">
                <input type="radio" name="is_view" id="status1" value="" <?php echo $is_view == "" ? "checked" : ""?>>
                <label for="status1">전체</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="is_view" id="status2" value="1" <?php echo $is_view == "1" ? "checked" : ""?>>
                <label for="status2">노출</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="is_view" id="status3" value="0" <?php echo $is_view == "0" ? "checked" : ""?>>
                <label for="status3">미노출</label>
            </div>
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
                <option value="bb_title" <?php echo get_selected($sfl, "bb_title"); ?>>제목</option>
                <option value="mb_name" <?php echo get_selected($sfl, "mb_name"); ?>>작성자</option>
            </select>
            <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="stx" value="<?php echo $stx ?>" id="stx"  class=" bansang_ipt ver2" size="50">
            <button type="submit" class="bansang_btns ver1">검색</button>
            <!-- <input type="submit" class="btn_submit" value="검색"> -->
        </div>
    </div>

</form>

<!-- <div class="local_desc01 local_desc">
    <p>
        회원자료 삭제 시 다른 회원이 기존 회원아이디를 사용하지 못하도록 회원아이디, 이름, 닉네임은 삭제하지 않고 영구 보관합니다.
    </p>
</div> -->


<form name="fbuilindbbslist" id="fbuilindbbslist" action="./building_news_info_list_update.php" onsubmit="return fbuilindbbslist_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="bbs_type" value="event">

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
                    <th>지역</th>
                    <th>단지명</th>
                    <th>제목</th>
                    <th>부서</th>
                    <th>등록자</th>
                    <th>작성일</th>
                    <th>상태</th>
                    <th>인쇄</th>
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
                            <input type="checkbox" name="chk[]" value="<?php echo $row['bb_id']; ?>" id="chk_<?php echo $i ?>">
                        </td>
                        <td>
                            <?php
                            
                            $startNumber = $total_count - (($page - 1) * $rows);
                            echo $startNumber - $i;
                            // echo $total_count - $startNumber;
                            ?>
                        </td>
                        <td><?php echo $row['post_name']; ?></td>
                        <td><?php echo $row['building_name']; ?></td>
                        <td><?php echo $row['bb_title']; ?></td>
                        <td>
                            <?php 
                            echo $row['mb_name'];
                            ?>
                        </td>
                        <td><?php echo get_member($row['wid'])['mb_name']; ?></td>
                        <td><?php echo date("Y-m-d", strtotime($row['created_at'])); ?></td>
                        <td><?php echo $row['is_view'] == '1' ? '노출' : '미노출'; ?></td>
                        <td>
                            <button type="button" onclick="print_info('<?php echo $row['bb_id'];?>');" class="btn btn_02">인쇄</button>
                        </td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./building_news_event_form.php?<?=$qstr;?>&amp;w=u&amp;bb_id=<? echo $row['bb_id']; ?>" class="btn btn_03">관리</a>
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
            <a href="./building_news_event_form.php" id="member_add" class="btn btn_03">이벤트 등록</a>
        <?php } ?>

    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
$(function(){
    $("#dates").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
});

function print_info(bb_idx) // 회원 엑셀 업로드를 위하여 추가
{ 

    var opt = "width=810,height=1200,left=10,top=10"; 
    var url = "./building_news_print.php?bb_idx=" + bb_idx;

    window.open(url, "win_news", opt); 

    return false; 

}

function fbuilindbbslist_submit(f) {
    if (!is_checked("chk[]")) {
        alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if (document.pressed == "선택삭제") {
        if (!confirm("선택한 이벤트를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
require_once './admin.tail.php';
