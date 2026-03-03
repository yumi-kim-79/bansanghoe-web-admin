<?php
$sub_menu = "400100";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_expense_report as ex
                left join a_post_addr as post on ex.post_id = post.post_idx
                left join a_building as building on ex.building_id = building.building_id
                left join a_building_dong as dong on ex.dong_id = dong.dong_id 
                left join g5_member as mb on ex.wid = mb.mb_id";


$sql_search = " where (1) and ex.is_del = '0' and building.is_use = 1 ";


if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_point':
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_name':
            $sql_search .= " (mb.{$sfl} like '%{$stx}%') ";
            break;
        case 'building_name':
            $sql_search .= " (building.{$sfl} like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " (ex.{$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($ex_status){
    $sql_search .= " and ex.ex_status = '{$ex_status}' ";

    $qstr .= '&ex_status='.$ex_status;
}

if($dates){
    $sql_search .= " and ex.created_at like '{$dates}%' ";

    $qstr .= '&dates='.$dates;
}

if($post_id){
    $sql_search .= " and post.post_idx = '{$post_id}' ";

    $qstr .= '&post_id='.$post_id;
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

$sql_order = " order by ex.ex_id desc ";

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

$g5['title'] = "품의서";
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$sql = " select ex.*, post.post_idx, post.post_name, building.building_name, building.is_use, dong.dong_name, mb.mb_name {$sql_common} {$sql_search} {$sql_search2} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 11;

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql;
}

?>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

    <label for="sfl" class="sound_only">검색대상</label>
    <div class="serach_box">
        <div class="sch_label">상태</div>
        <div class="sch_selects ver_flex gap15">
            <div class="sch_radios">
                <input type="radio" name="ex_status" id="status1" value="" <?php echo $ex_status == "" ? "checked" : "";?>>
                <label for="status1">전체</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="ex_status" id="status2" value="E" <?php echo $ex_status == "E" ? "checked" : "";?>>
                <label for="status2">승인완료</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="ex_status" id="status3" value="N" <?php echo $ex_status == "N" ? "checked" : "";?>>
                <label for="status3">승인대기</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="ex_status" id="status4" value="P" <?php echo $ex_status == "P" ? "checked" : "";?>>
                <label for="status4">승인중</label>
            </div>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">등록일</div>
        <div class="sch_selects ver_flex">
            <input type="text" name="dates" class="bansang_ipt ver2 ipt_date" id="dates" value="<?php echo $dates; ?>">
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
                <option value="ex_title" <?php echo get_selected($sfl, "ex_title"); ?>>제목</option>
                <option value="mb_name" <?php echo get_selected($sfl, "mb_name"); ?>>작성자</option>
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

<form name="fexpenselist" id="fexpenselist" action="./expense_list_update.php" onsubmit="return fexpenselist_submit(this);" method="post">
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
                    <th>동</th>
                    <th>등록일</th>
                    <th>작성자</th>
                    <th>제목</th>
                    <th>관리단 결재자</th>
                    <th>상태</th>
                    <th>시행자</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {

                    //print_r2($row);
                ?>

                    <tr class="<?php echo $bg; ?>">
                        <!-- <td headers="mb_list_chk" class="td_chk" >
                            <input type="checkbox" name="chk[]" value="<?php echo $row['ex_id']; ?>" id="chk_<?php echo $i ?>">
                        </td> -->
                        <td>
                            <?php
                            $startNumber = $total_count - (($page - 1) * $rows);
                            echo $startNumber - $i;
                            // echo $total_count - $startNumber;
                            ?>
                            </td>
                        </td>
                        <td><?php echo $row['post_name']; ?></td>
                        <td><?php echo $row['building_name']; ?></td>
                        <td><?php echo $row['dong_id'] == '-1' ? '전체' : $row['dong_name'].'동'; ?></td>
                        <td><?php echo date("Y-m-d", strtotime($row['created_at'])); ?></td>
                        <td><?php echo $row['ex_name']; ?></td>
                        <td><?php echo $row['ex_title']; ?></td>
                        <td><button type="button" onclick="sign_off_list_handler('<?php echo $row['ex_id']; ?>', <?php echo $row['building_id']; ?>, '<?php echo $row['dong_id']; ?>')" class="btn btn_02">결재자</button></td>
                        <td>
                            <?php
                            switch($row['ex_status']){
                                case "N":
                                    $status = "승인대기";
                                    break;
                                case "P":
                                    $status = "승인중";
                                    break;
                                case "E":
                                    $status = "승인완료";
                                    break;
                            }
                            echo $status; 
                            ?>
                        </td>
                        <td>
                            <?php if($row['enforce_deaprt'] != ""){?>
                            <button type="button" onclick="enforce_list_handler('<?php echo $row['ex_id']; ?>', '<?php echo $row['dong_id']; ?>')" class="btn btn_02">시행자</button>
                            <?php }else {?>
                                -
                            <?php }?>
                        </td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./expense_form.php?<?=$qstr;?>&amp;w=u&amp;ex_id=<? echo $row['ex_id']; ?>" class="btn btn_03">관리</a>
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
            <a href="./expense_form.php" id="member_add" class="btn btn_03">품의서 등록</a>
        <?php } ?>
    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<div class="cm_pop" id="sign_off_list_pop" >
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('sign_off_list_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">관리단 결재자</div>
        <div class="sign_off_pop_conts">
        </div>
		<div class="cm_pop_btn_box flex_ver flex_ver_ta">
			<button type="button" class="cm_pop_btn ver2" onClick="popClose('sign_off_list_pop');">확인</button>
		</div>
	</div>
</div>

<div class="cm_pop" id="expense_mng_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont ver_s">
        <div class="cm_pop_close_btn" onClick="popClose('expense_mng_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">시행자</div>
        <div class="expense_mng_wrapper">
        </div>
		<div class="cm_pop_btn_box flex_ver flex_ver_ta">
			<button type="button" class="cm_pop_btn ver2" onClick="popClose('expense_mng_pop');">확인</button>
		</div>
	</div>
</div>

<script>
$(function(){
    //minDate:"0d"
    $("#dates").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
});

function sign_off_list_handler(idx, building_id, dong_id){
    

    console.log('idx', idx);

    $.ajax({

    url : "./expense_list_mng_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "ex_id":idx, "building_id":building_id, "dong_id":dong_id}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        $(".sign_off_pop_conts").html(msg);

        popOpen('sign_off_list_pop');
    }

    });
}

function enforce_list_handler(idx, dong_id){
    

    console.log('idx', idx);

    $.ajax({

    url : "./expense_list_enforce_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "ex_id":idx, "dong_id":dong_id}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        $(".expense_mng_wrapper").html(msg);

        popOpen('expense_mng_pop');
    }

    });
}


function fexpenselist_submit(f) {
    if (!is_checked("chk[]")) {
        alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if (document.pressed == "선택삭제") {
        if (!confirm("선택한 품의서를 정말 삭제하시겠습니까?")) {
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
