<?php
$sub_menu = "500200";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from question_answer qa   
                left outer join admin a1 on qa.admin = a1.seq 
                left outer join admin a2 on qa.assigner = a2.seq 
                left outer join admin a3 on qa.create_admin = a3.seq 
                left outer join estate e on qa.estate = e.seq 
                left outer join house h on qa.house = h.seq 
                left outer join user u on h.tenant = u.seq ";

$sql_search = " where (1) ";


if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'rname':
            $sql_search .= " (u.name like '%{$stx}%') ";
            break;
        case 'rhp':
            $sql_search .= " (u.contact like '%{$stx}%') ";
            break;
        case 'sbname':
            $sql_search .= " (a3.username like '%{$stx}%') ";
            break;
        case 'complain_title':
            $sql_search .= " (qa.title like '%{$stx}%') ";
            break;
        case 'complete_name':  
            $sql_search .= " (a1.username like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " (qa.title like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($date_type == "wdate" && $dates != ""){
    $sql_search .= " and DATE(qa.create_date) = '{$dates}' ";
    $qstr .= '&wdate='.$wdate.'&dates='.$dates;
}else if($date_type == "edate" && $dates != ""){
    $sql_search .= " and DATE(qa.answer_date) = '{$dates}' ";
    $qstr .= '&wdate='.$wdate.'&dates='.$dates;
}

if($complain_status != ""){
    $sql_search .= " and qa.status = '{$complain_status}' ";
    $qstr .= '&complain_status='.$complain_status;
}

if($post_name){
    $sql_search .= " and e.address like '{$post_name}%' ";
    $qstr .= '&post_name='.$post_name;
}

if($building_name){
    $sql_search .= " and e.name like '%{$building_name}%' ";
    $qstr .= '&building_name='.$building_name;
}

if ($is_admin != 'super') {
    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
}

$sql_order = " order by qa.seq desc ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);
if ($page < 1) { $page = 1; }
$from_record = ($page - 1) * $rows;

$listall = '<a href="' . $_SERVER['SCRIPT_NAME'] . '" class="ov_listall">전체목록</a>';

$g5['title'] = "민원(이전자료)";

require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$sql = " select qa.seq,
	qa.answer,
	qa.answer_date,
	qa.answer_type,
	qa.create_date,
	qa.question,
	qa.status as 'complain_status',
	qa.title as 'complain_title',
	qa.admin,
    a1.duty,
	concat(a1.username, '(' ,a1.nick_name, ')') as 'complete_name',
	qa.estate,
	e.name as 'building_name',
	qa.house,
	concat(h.dong, '동 ' ,h.ho, '호') as 'house_hold',
	qa.field_comment,
	qa.register_type,
	qa.allocate_type,
	qa.handle_status,
	qa.assigner,
	concat(a2.username, '(' ,a2.nick_name, ')') as 'wname',
	qa.assign_date_time,
	qa.important,
	qa.applier,
	qa.applier_contact,
	qa.create_date_time as 'created_at',
	qa.create_admin,
	concat(a3.username, '(' ,a3.nick_name, ')') as 'sbname',
	u.name as 'rname',
	u.contact as 'rhp',
    e.address,
    h.dong,
    h.ho {$sql_common} {$sql_search} {$sql_search2} {$sql_order} limit {$from_record}, {$rows} ";

$result = sql_query($sql);

$colspan = 16;

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){ echo $sql; }
?>


<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
    <label for="sfl" class="sound_only">검색대상</label>
   
    <div class="serach_box">
        <div class="sch_label">민원 날짜</div>
        <div class="sch_selects ver_flex gap15">
            <div class="sch_radios">
                <input type="radio" name="date_type" id="date_type1" value="wdate" <?php echo $date_type == "wdate" || $date_type == "" ? "checked" : "";?>>
                <label for="date_type1">민원 접수일</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="date_type" id="date_type2" value="edate" <?php echo $date_type == "edate" ? "checked" : "";?>>
                <label for="date_type2">민원 완료일</label>
            </div>
            <input type="text" name="dates" class="bansang_ipt ver2 ipt_date" value="<?php echo $dates; ?>">
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">상태</div>
        <div class="sch_selects ver_flex gap15">
            <div class="sch_radios">
                <input type="radio" name="complain_status" id="status1" value="" <?php echo $complain_status == "" ? "checked" : ""?>>
                <label for="status1">전체</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="complain_status" id="status3" value="0" <?php echo $complain_status == "0" ? "checked" : ""?>>
                <label for="status3">접수대기</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="complain_status" id="status4" value="2" <?php echo $complain_status == "2" ? "checked" : ""?>>
                <label for="status4">진행중</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="complain_status" id="status5" value="1" <?php echo $complain_status == "1" ? "checked" : ""?>>
                <label for="status5">완료</label>
            </div>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">지역</div>
        <div class="sch_selects">
            <select name="post_name" id="post_name" class="bansang_sel">
                <option value="">지역 선택</option>
                <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                    <option value="<?php echo $post_row['post_name']; ?>" <?php echo get_selected($post_name, $post_row['post_name']); ?>><?php echo $post_row['post_name']; ?></option>
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
    </div>
    
    <div class="serach_box">
        <div class="sch_label">검색어</div>
        <div class="sch_selects ver_flex">
            <select name="sfl" id="sfl" class="bansang_sel">
                <option value="rname" <?php echo get_selected($sfl, "rname"); ?>>민원인</option>
                <option value="rhp" <?php echo get_selected($sfl, "rhp"); ?>>연락처</option>
                <option value="sbname" <?php echo get_selected($sfl, "sbname"); ?>>작성자</option>
                <option value="complain_title" <?php echo get_selected($sfl, "complain_title"); ?>>제목</option>
                <option value="complete_name" <?php echo get_selected($sfl, "complete_name"); ?>>담당자</option>
            </select>
            <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="bansang_ipt ver2" size="50">
            <button type="submit" class="bansang_btns ver1">검색</button>
        </div>
    </div>
</form>

<!-- ========== 엑셀 다운로드 버튼 영역 ========== -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
    <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
        <input type="checkbox" id="chkAllTop" onclick="complainCheckAll(this)">
        <span>전체선택</span>
    </label>
    <button type="button" onclick="complainExcelDownload()" class="btn btn_03" style="background:#217346; border-color:#217346;">
        &#128229; 선택 엑셀 다운로드
    </button>
</div>

<form name="fcomplainlist" id="fcomplainlist" action="./complain_list_update.php" onsubmit="return fcomplainlist_submit(this);" method="post">
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
                    <th scope="col" style="width:40px;">
                        <input type="checkbox" id="chkAllHead" onclick="complainCheckAll(this)">
                    </th>
                    <th>번호</th>
                    <th>지역</th>
                    <th>단지명</th>
                    <th>동</th>
                    <th>호수</th>
                    <th>접수날짜</th>
                    <th>민원인</th>
                    <th>연락처</th>
                    <th>작성자</th>
                    <th>민원 제목</th>
                    <th>담당자 직급</th>
                    <th>담당자</th>
                    <th>완료날짜</th>
                    <th>상태</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                ?>
                    <tr class="<?php echo $row['register_type'] == 'ADMIN' ? '' : 'status_n'; ?>">
                        <td class="td_chk">
                            <input type="checkbox" class="complain_chk" name="chk[]" value="<?php echo $row['seq']; ?>">
                        </td>
                        <td>
                            <?php
                            $startNumber = $total_count - (($page - 1) * $rows);
                            echo $startNumber - $i;
                            ?>
                        </td>
                        <td>
                            <?php
                            $addr = explode(" ", $row['address']);
                            echo $addr[0];
                            ?>
                        </td>
                        <td><?php echo $row['building_name']; ?></td>
                        <td><?php echo $row['dong'] != '' ? $row['dong'].'동' : ''; ?></td>
                        <td><?php echo $row['ho'] != '' ? $row['ho'].'호' : ''; ?></td>
                        <td><?php echo $row['create_date'] != "" ? date("Y-m-d", strtotime($row['create_date'])) : ""; ?></td>
                        <td><?php echo $row['rname']; ?></td>
                        <td><?php echo $row['rhp']; ?></td>
                        <td><?php echo $row['sbname']; ?></td>
                        <td><?php echo $row['complain_title']; ?></td>
                        <td><?php echo $row['duty']; ?></td>
                        <td><?php echo $row['complete_name']; ?></td>
                        <td><?php echo $row['answer_date'] != "" ? date("Y-m-d", strtotime($row['answer_date'])) : ""; ?></td>
                        <td>
                            <?php
                            $status = '';
                            switch($row['complain_status']){
                                case "0": $status = "접수대기"; break;
                                case "1": $status = "완료"; break;
                                case "2": $status = "진행중"; break;
                            }
                            echo $status;
                            ?>
                        </td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./complain_form_bf.php?<?=$qstr;?>&amp;w=u&amp;complain_idx=<?php echo $row['seq']; ?>" class="btn btn_03">관리</a>
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

<!-- 엑셀 다운로드용 숨김 폼 -->
<form id="fExcelDownload" action="./complain_excel_bf.php" method="post" target="_blank">
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
$(function(){
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
});

// 전체선택 (상단/헤더 체크박스 연동)
function complainCheckAll(source) {
    var checked = source.checked;
    document.querySelectorAll('.complain_chk').forEach(function(cb){ cb.checked = checked; });
    document.getElementById('chkAllTop').checked = checked;
    document.getElementById('chkAllHead').checked = checked;
}

// 엑셀 다운로드
function complainExcelDownload() {
    var checked = document.querySelectorAll('.complain_chk:checked');
    if (checked.length === 0) {
        alert('다운로드할 민원을 하나 이상 선택해주세요.');
        return;
    }

    var form = document.getElementById('fExcelDownload');

    // 기존에 추가된 idx 인풋 제거
    form.querySelectorAll('input[name="idx[]"]').forEach(function(el){ el.remove(); });

    checked.forEach(function(cb) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'idx[]';
        input.value = cb.value;
        form.appendChild(input);
    });

    form.submit();
}

function fcomplainlist_submit(f) {
    if (!is_checked("chk[]")) {
        alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
        return false;
    }
    if (document.pressed == "선택삭제") {
        if (!confirm("선택한 민원을 정말 삭제하시겠습니까?")) { return false; }
    }
    return true;
}
</script>

<?php
require_once './admin.tail.php';
