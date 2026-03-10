<?php
$sub_menu = $_GET['type'] == "progress" ? "500100" : "500200";
require_once './_common.php';

auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_online_complain as complain
                left join a_post_addr as post on complain.post_id = post.post_idx
                left join a_building as building on complain.building_id = building.building_id
                left join a_building_dong as dong on complain.dong_id = dong.dong_id
                left join a_building_ho as ho on complain.ho_id = ho.ho_id
                left join a_complain_status as cs on complain.complain_status = cs.cs_code 
                left join g5_member as mb on complain.mng_id = mb.mb_id ";

if($type == "progress"){
    $sql_search = " where (1) and complain.is_del = 0 and building.is_use = 1 ";
    $qstr .= '&type='.$type;
}else{
    $sql_search = " where (1) and complain.is_before = 1 and building.is_use = 1 ";
    $qstr .= '&type='.$type;
}

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_name': $sql_search .= " (mb.{$sfl} like '%{$stx}%') "; break;
        default:        $sql_search .= " (complain.{$sfl} like '%{$stx}%') "; break;
    }
    $sql_search .= " ) ";
}
if($date_type == "wdate" && $dates != ""){
    $sql_search .= " and complain.wdate = '{$dates}' ";
    $qstr .= '&wdate='.$wdate.'&dates='.$dates;
}else if($date_type == "edate" && $dates != ""){
    $sql_search .= " and complain.edate = '{$dates}' ";
    $qstr .= '&wdate='.$wdate.'&dates='.$dates;
}
if($complain_status){ $sql_search .= " and complain.complain_status = '{$complain_status}' "; $qstr .= '&complain_status='.$complain_status; }
if($post_id){         $sql_search .= " and complain.post_id = '{$post_id}' ";                  $qstr .= '&post_id='.$post_id; }
if($building_name){   $sql_search .= " and building.building_name like '%{$building_name}%' "; $qstr .= '&building_name='.$building_name; }
if($dong_id){         $sql_search .= " and complain.dong_id = '{$dong_id}' ";                  $qstr .= '&dong_id='.$dong_id; }
if($ho_id){           $sql_search .= " and complain.ho_id = '{$ho_id}' ";                      $qstr .= '&ho_id='.$ho_id; }
if($mng_department){  $sql_search .= " and complain.mng_department = '{$mng_department}' ";    $qstr .= '&mng_department='.$mng_department; }
if ($is_admin != 'super') { $sql_search .= " and mb_level <= '{$member['mb_level']}' "; }

$sql_order = " order by complain.complain_idx desc ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page = ceil($total_count / $rows);
if ($page < 1) $page = 1;
$from_record = ($page - 1) * $rows;

// 전체 idx 목록 (전체선택용)
$all_idx_sql = " select complain.complain_idx {$sql_common} {$sql_search} {$sql_order} ";
$all_idx_res = sql_query($all_idx_sql);
$all_idx_list = [];
while ($all_row = sql_fetch_array($all_idx_res)) {
    $all_idx_list[] = $all_row['complain_idx'];
}
$all_idx_json = json_encode($all_idx_list);

if($type == "progress"){ $g5['title'] = "민원"; }
else{ $g5['title'] = "민원(이전자료)"; }

require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

$sql = " select complain.*, post.post_name, building.building_name, building.is_use, dong.dong_name, ho.ho_name, cs.cs_name, mb.mb_name {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 16;
?>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
    <div class="serach_box">
        <div class="sch_label">민원 날짜</div>
        <div class="sch_selects ver_flex gap15">
            <div class="sch_radios"><input type="radio" name="date_type" id="date_type1" value="wdate" <?php echo $date_type == "wdate" || $date_type == "" ? "checked" : "";?>><label for="date_type1">민원 접수일</label></div>
            <div class="sch_radios"><input type="radio" name="date_type" id="date_type2" value="edate" <?php echo $date_type == "edate" ? "checked" : "";?>><label for="date_type2">민원 완료일</label></div>
            <input type="text" name="dates" class="bansang_ipt ver2 ipt_date" value="<?php echo $dates; ?>">
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">상태</div>
        <div class="sch_selects ver_flex gap15">
            <div class="sch_radios"><input type="radio" name="complain_status" id="status1" value="" <?php echo $complain_status == "" ? "checked" : ""?>><label for="status1">전체</label></div>
            <div class="sch_radios"><input type="radio" name="complain_status" id="status2" value="CB" <?php echo $complain_status == "CB" ? "checked" : ""?>><label for="status2">할당대기</label></div>
            <div class="sch_radios"><input type="radio" name="complain_status" id="status3" value="CA" <?php echo $complain_status == "CA" ? "checked" : ""?>><label for="status3">접수대기</label></div>
            <div class="sch_radios"><input type="radio" name="complain_status" id="status4" value="CC" <?php echo $complain_status == "CC" ? "checked" : ""?>><label for="status4">진행중</label></div>
            <div class="sch_radios"><input type="radio" name="complain_status" id="status5" value="CD" <?php echo $complain_status == "CD" ? "checked" : ""?>><label for="status5">완료</label></div>
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
        <div class="sch_label">단지</div>
        <div class="sch_selects">
            <div class="sch_ipt_boxs">
                <div class="sch_result_box sch_result_box1"></div>
                <input type="hidden" name="building_id" id="building_id" value="<?php echo $building_id; ?>">
                <input type="text" name="building_name" id="building_name" class="bansang_ipt ver2 building_name_sch" size="50" value="<?php echo $building_name; ?>">
            </div>
        </div>
        <script>
        $(document).on("keyup", ".building_name_sch", function(){
            let sch_text = this.value;
            if(sch_text != ""){
                let post_id = $("#post_id option:selected").val();
                $.ajax({ url:"./building_mng_sch_text.php", type:"POST", data:{"sch_category":"building_name","sch_text":sch_text,"type":"Y","post_id":post_id}, success:function(msg){ $(".sch_result_box1").html(msg); } });
            } else { $(".sch_result_box1").html(""); }
        });
        function sch_handler(text, bid){
            $(".sch_result_box1").html(""); $(".building_name_sch").val(text); $("#building_id").val(bid);
            $.ajax({ url:"./building_mng_sch_ho_dong.ajax.php", type:"POST", data:{"building_id":bid}, success:function(msg){ $("#dong_id").html(msg); } });
        }
        </script>
    </div>
    <div class="serach_box">
        <div class="sch_label">동/호수</div>
        <div class="sch_selects ver_flex building_dong_ho">
            <?php
            $dong_sql = "SELECT * FROM a_building_dong WHERE building_id = '{$building_id}' ORDER BY dong_name asc, dong_id desc";
            $dong_res = sql_query($dong_sql);
            ?>
            <select name="dong_id" id="dong_id" class="bansang_sel" onchange="dong_change();">
                <option value="">동 선택</option>
                <?php while($dong_row = sql_fetch_array($dong_res)){ ?><option value="<?php echo $dong_row['dong_id'];?>" <?php echo get_selected($dong_row['dong_id'], $dong_id); ?>><?php echo $dong_row['dong_name']; ?></option><?php }?>
            </select>
            <?php $sql_ho = "SELECT * FROM a_building_ho WHERE dong_id = '{$dong_id}' and is_del = 0"; $res_ho = sql_query($sql_ho); ?>
            <select name="ho_id" id="ho_id" class="bansang_sel">
                <option value="">호수 선택</option>
                <?php while($row_ho = sql_fetch_array($res_ho)){ ?><option value="<?php echo $row_ho['ho_id']?>" <?php echo get_selected($row_ho['ho_id'], $ho_id); ?>><?php echo $row_ho['ho_name'];?></option><?php }?>
            </select>
        </div>
        <script>
        function dong_change(){
            var dongValue = document.getElementById("dong_id").options[document.getElementById("dong_id").selectedIndex].value;
            $.ajax({ url:"./building_ho_ajax.php", type:"POST", data:{"dong_id":dongValue}, success:function(msg){ $("#ho_id").html(msg); } });
        }
        </script>
    </div>
    <div class="serach_box">
        <div class="sch_label">담당자 부서</div>
        <div class="sch_selects ver_flex building_dong_ho">
            <?php $depart_sql = "SELECT * FROM a_mng_department WHERE is_del = 0 ORDER BY is_prior asc, md_idx asc"; $depart_res = sql_query($depart_sql); ?>
            <select name="mng_department" id="mng_department" class="bansang_sel">
                <option value="">선택하세요</option>
                <?php for($i=0;$depart_row = sql_fetch_array($depart_res);$i++){?>
                <option value="<?php echo $depart_row['md_idx']; ?>" <?php echo get_selected($mng_department, $depart_row['md_idx']); ?>><?php echo $depart_row['md_name']; ?></option>
                <?php }?>
            </select>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">검색어</div>
        <div class="sch_selects ver_flex">
            <select name="sfl" id="sfl" class="bansang_sel">
                <option value="complain_name" <?php echo get_selected($sfl, "complain_name"); ?>>민원인</option>
                <option value="complain_hp"   <?php echo get_selected($sfl, "complain_hp"); ?>>연락처</option>
                <option value="wname"         <?php echo get_selected($sfl, "wname"); ?>>작성자</option>
                <option value="complain_title" <?php echo get_selected($sfl, "complain_title"); ?>>제목</option>
                <option value="mb_name"       <?php echo get_selected($sfl, "mb_name"); ?>>담당자</option>
            </select>
            <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="bansang_ipt ver2" size="50">
            <button type="submit" class="bansang_btns ver1">검색</button>
        </div>
    </div>
</form>

<!-- 전체 idx JSON (전체선택 다운로드용) -->
<script>var ALL_IDX = <?php echo $all_idx_json; ?>;</script>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
    <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
        <input type="checkbox" id="chkAllTop" onchange="complainCheckAll(this)">
        <span id="chkAllLabel">전체선택 (현재 페이지)</span>
    </label>
    <button type="button" onclick="complainExcelDownload()" class="btn btn_03" style="background:#217346; border-color:#217346;">
        &#128229; 선택 엑셀 다운로드
    </button>
</div>

<form name="fcomplainlist" id="fcomplainlist" action="./complain_list_update.php" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="type" value="<?php echo $type;?>">

    <div class="tbl_head01 tbl_head03 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr>
                    <th style="width:40px;"><input type="checkbox" id="chkAllHead" onchange="complainCheckAll(this)"></th>
                    <th>번호</th><th>지역</th><th>단지명</th><th>동</th><th>호수</th>
                    <th>접수날짜</th><th>민원인</th><th>연락처</th><th>작성자</th><th>민원 제목</th>
                    <th>담당자 부서</th><th>담당자</th><th>완료날짜</th><th>상태</th><th>관리</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $row = sql_fetch_array($result); $i++) {
                    $md_name  = get_department_name($row['mng_department']);
                    $mng_name = get_manger($row['mng_id'])['mng_name'];
                    $startNumber = $total_count - (($page - 1) * $rows);
                ?>
                <tr class="<?php echo $row['complain_type'] == 'admin' ? '' : 'status_n'; ?>">
                    <td><input type="checkbox" class="complain_chk" value="<?php echo $row['complain_idx']; ?>"></td>
                    <td><?php echo $startNumber - $i; ?></td>
                    <td><?php echo $row['post_name']; ?></td>
                    <td><?php echo $row['building_name']; ?></td>
                    <td><?php echo $row['dong_name'] != '' ? $row['dong_name'].'동' : ''; ?></td>
                    <td><?php echo $row['ho_name'] != '' ? $row['ho_name'].'호' : ''; ?></td>
                    <td><?php echo $row['wdate']; ?></td>
                    <td><?php echo $row['complain_name']; ?></td>
                    <td><?php echo $row['complain_hp']; ?></td>
                    <td><?php echo $row['wname']; ?></td>
                    <td><?php echo $row['complain_title']; ?></td>
                    <td><?php echo $md_name; ?></td>
                    <td><?php echo $mng_name; ?></td>
                    <td><?php echo $row['edate']; ?></td>
                    <td><?php echo $row['cs_name']; ?></td>
                    <td class="td_mng td_mng_s">
                        <a href="./complain_form.php?type=<?php echo $type;?>&<?=$qstr;?>&w=u&complain_idx=<?php echo $row['complain_idx']; ?>" class="btn btn_03">관리</a>
                    </td>
                </tr>
                <?php } if ($i == 0) echo "<tr><td colspan='{$colspan}' class='empty_table'>자료가 없습니다.</td></tr>"; ?>
            </tbody>
        </table>
    </div>

    <div class="btn_fixed_top">
        <?php if ($is_admin == 'super' && $type=="progress") { ?>
        <a href="./complain_form.php?type=<?=$type;?>" class="btn btn_03">민원 등록</a>
        <?php } ?>
    </div>
</form>

<!-- 엑셀 다운로드 폼 -->
<form id="fExcelDownload" action="./complain_excel.php" method="post">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&page='); ?>

<script>
$(function(){
    $(".ipt_date").datepicker({ changeMonth:true, changeYear:true, dateFormat:"yy-mm-dd", showButtonPanel:true, yearRange:"c-99:c+99", maxDate:"+365d" });
});

var isAllSelected = false; // 전체 페이지 선택 여부

function complainCheckAll(source) {
    var checked = source.checked;

    if (checked) {
        // 체크 시: 전체 페이지 선택할지 현재 페이지만 선택할지 물어봄
        var totalAll = ALL_IDX.length;
        var currentPage = document.querySelectorAll('.complain_chk').length;

        if (totalAll > currentPage) {
            if (confirm('현재 페이지(' + currentPage + '건)만 선택하시겠습니까?\n\n[확인] 현재 페이지만\n[취소] 전체 ' + totalAll + '건 모두 선택')) {
                // 현재 페이지만
                isAllSelected = false;
                document.querySelectorAll('.complain_chk').forEach(function(cb){ cb.checked = true; });
                document.getElementById('chkAllLabel').innerText = '전체선택 (현재 페이지 ' + currentPage + '건 선택됨)';
            } else {
                // 전체 선택
                isAllSelected = true;
                document.querySelectorAll('.complain_chk').forEach(function(cb){ cb.checked = true; });
                document.getElementById('chkAllLabel').innerText = '✅ 전체 ' + totalAll + '건 선택됨';
            }
        } else {
            isAllSelected = false;
            document.querySelectorAll('.complain_chk').forEach(function(cb){ cb.checked = true; });
            document.getElementById('chkAllLabel').innerText = '전체선택 (' + currentPage + '건)';
        }
    } else {
        // 해제
        isAllSelected = false;
        document.querySelectorAll('.complain_chk').forEach(function(cb){ cb.checked = false; });
        document.getElementById('chkAllLabel').innerText = '전체선택 (현재 페이지)';
    }

    document.getElementById('chkAllTop').checked  = checked;
    document.getElementById('chkAllHead').checked = checked;
}

function complainExcelDownload() {
    var idxList = [];

    if (isAllSelected) {
        idxList = ALL_IDX;
    } else {
        document.querySelectorAll('.complain_chk:checked').forEach(function(cb){ idxList.push(cb.value); });
    }

    if (idxList.length === 0) {
        alert('다운로드할 민원을 하나 이상 선택해주세요.');
        return;
    }

    var form = document.getElementById('fExcelDownload');
    // 기존 idx_str 인풋 제거 후 콤마 구분 문자열 하나로 전송 (max_input_vars 우회)
    form.querySelectorAll('input[name="idx_str"]').forEach(function(el){ el.remove(); });
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'idx_str';
    input.value = idxList.join(',');
    form.appendChild(input);

    form.submit();
}
</script>

<?php require_once './admin.tail.php'; ?>
