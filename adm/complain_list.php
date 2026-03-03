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
        case 'mb_point':
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_level':
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'mb_name':
            $sql_search .= " (mb.{$sfl} like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " (complain.{$sfl} like '%{$stx}%') ";
            break;
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

if($complain_status){
    $sql_search .= " and complain.complain_status = '{$complain_status}' ";

    $qstr .= '&complain_status='.$complain_status;
}

if($post_id){

    $sql_search .= " and complain.post_id = '{$post_id}' ";

    $qstr .= '&post_id='.$post_id;

}

if($building_name){

    $sql_search .= " and building.building_name like '%{$building_name}%' ";

    $qstr .= '&building_name='.$building_name;
}

if($dong_id){

    $sql_search .= " and complain.dong_id = '{$dong_id}' ";

    $qstr .= '&dong_id='.$dong_id;

}

if($ho_id){

    $sql_search .= " and complain.ho_id = '{$ho_id}' ";

    $qstr .= '&ho_id='.$ho_id;

}

if($mng_department){
    
        $sql_search .= " and complain.mng_department = '{$mng_department}' ";
    
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

$sql_order = " order by complain.complain_idx desc ";

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

if($type == "progress"){
    $g5['title'] = "민원";
}else{
    $g5['title'] = "민원(이전자료)";
}

require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$grade_sql = "SELECT * FROM a_grade WHERE is_del = 0 ORDER BY is_prior asc, gidx asc";
$grade_res = sql_query($grade_sql);


$sql = " select complain.*, post.post_name, building.building_name, building.is_use, dong.dong_name, ho.ho_name, cs.cs_name, mb.mb_name {$sql_common} {$sql_search} {$sql_search2} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 15;

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $sql;
}
//echo $st_status;
//echo $sub_menu;

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
                <input type="radio" name="complain_status" id="status2" value="CB" <?php echo $complain_status == "CB" ? "checked" : ""?>>
                <label for="status2">할당대기</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="complain_status" id="status3" value="CA" <?php echo $complain_status == "CA" ? "checked" : ""?>>
                <label for="status3">접수대기</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="complain_status" id="status4" value="CC" <?php echo $complain_status == "CC" ? "checked" : ""?>>
                <label for="status4">진행중</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="complain_status" id="status5" value="CD" <?php echo $complain_status == "CD" ? "checked" : ""?>>
                <label for="status5">완료</label>
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
        <div class="sch_label">단지</div>
        <div class="sch_selects">
            <div class="sch_ipt_boxs">
                <div class="sch_result_box sch_result_box1">
                </div>
                <input type="hidden" name="building_id" id="building_id" value="<?php echo $building_id; ?>">
                <input type="text" name="building_name" id="building_name" class="bansang_ipt ver2 building_name_sch" size="50" value="<?php echo $building_name; ?>">
            </div>
        </div>
        <script>
            $(document).on("keyup", ".building_name_sch", function(){
                let sch_text = this.value;
                
                console.log('keyup',sch_text);

                if(sch_text != ""){
                    let post_id = $("#post_id option:selected").val();

                    $.ajax({

                    url : "./building_mng_sch_text.php", //ajax 통신할 파일
                    type : "POST", // 형식
                    data: { "sch_category":"building_name", "sch_text":sch_text, "type":"Y", "post_id":post_id}, //파라미터 값
                    success: function(msg){ //성공시 이벤트

                    
                        console.log(msg);
                        $(".sch_result_box1").html(msg); //.select_box2에 html로 나타내라..
                    }
                    })
                }else{
                    $(".sch_result_box1").html("");
                }
            
            });

            function sch_handler(text, bid){
                $(".sch_result_box1").html("");
                $(".building_name_sch").val(text);
                $("#building_id").val(bid);

                $.ajax({

                url : "./building_mng_sch_ho_dong.ajax.php", //ajax 통신할 파일
                type : "POST", // 형식
                data: { "building_id":bid}, //파라미터 값
                success: function(msg){ //성공시 이벤트

                    console.log(msg);
                    $("#dong_id").html(msg); //.select_box2에 html로 나타내라..
                }
                })
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
                <?php
                while($dong_row = sql_fetch_array($dong_res)){
                ?>
                <option value="<?php echo $dong_row['dong_id'];?>" <?php echo get_selected($dong_row['dong_id'], $dong_id); ?>><?php echo $dong_row['dong_name']; ?></option>
                <?php }?>
            </select>
            <?php
            $sql_ho = "SELECT * FROM a_building_ho WHERE dong_id = '{$dong_id}' and is_del = 0";
            $res_ho = sql_query($sql_ho);
            ?>
            <select name="ho_id" id="ho_id" class="bansang_sel">
                <option value="">호수 선택</option>
                <?php
                while($row_ho = sql_fetch_array($res_ho)){
                ?>
                <option value="<?php echo $row_ho['ho_id']?>" <?php echo get_selected($row_ho['ho_id'], $ho_id); ?>><?php echo $row_ho['ho_name'];?></option>
                <?php }?>
            </select>
        </div>
        <script>
            function dong_change(){
                var dongSelect = document.getElementById("dong_id");
                var dongValue = dongSelect.options[dongSelect.selectedIndex].value;

                $.ajax({

                url : "./building_ho_ajax.php", //ajax 통신할 파일
                type : "POST", // 형식
                data: { "dong_id":dongValue}, //파라미터 값
                success: function(msg){ //성공시 이벤트

                    //console.log(msg);
                    $("#ho_id").html(msg);
                }

                });
            }
        </script>
    </div>
    <div class="serach_box">
        <div class="sch_label">담당자 부서</div>
        <div class="sch_selects ver_flex building_dong_ho">
            <?php
            $depart_sql = "SELECT * FROM a_mng_department WHERE is_del = 0 ORDER BY is_prior asc, md_idx asc";
            $depart_res = sql_query($depart_sql);
            ?>
            <select name="mng_department" id="mng_department" class="bansang_sel" onchange="dapart_change();" <?php echo $row['complain_status'] == 'CD' ? 'readonly' : '';?>>
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
                <option value="complain_hp" <?php echo get_selected($sfl, "complain_hp"); ?>>연락처</option>
                <option value="wname" <?php echo get_selected($sfl, "wname"); ?>>작성자</option>
                <option value="complain_title" <?php echo get_selected($sfl, "complain_title"); ?>>제목</option>
                <option value="mb_name" <?php echo get_selected($sfl, "mb_name"); ?>>담당자</option>
            </select>
            <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="stx" value="<?php echo $stx ?>" id="stx"  class=" bansang_ipt ver2" size="50">
            <button type="submit" class="bansang_btns ver1">검색</button>
        </div>
    </div>

</form>

<form name="fcomplainlist" id="fcomplainlist" action="./complain_list_update.php" onsubmit="return fcomplainlist_submit(this);" method="post">
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
                    <!-- <th scope="col" id="mb_list_chk" >
                        <label for="chkall" class="sound_only">회원 전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th> -->
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
                    <th>담당자 부서</th>
                    <th>담당자</th>
                    <th>완료날짜</th>
                    <th>상태</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {

                    $mngs = get_manger($row['mng_id']);
              
                    $md_name = get_department_name($row['mng_department']);
                    $mng_name = get_manger($row['mng_id'])['mng_name'];

                    $members = $row['complain_type'] == 'admin' ? get_member($row['complain_id']) : get_user($row['complain_id']);
                ?>

                    <tr class="<?php echo $row['complain_type'] == 'admin' ? '' : 'status_n'; ?>">
                        <!-- <td headers="mb_list_chk" class="td_chk" >
                            <input type="checkbox" name="chk[]" value="<?php echo $row['complain_idx']; ?>" id="chk_<?php echo $i ?>">
                        </td> -->
                        <td>
                            <?php
                            $startNumber = $total_count - (($page - 1) * $rows);
                            echo $startNumber - $i;
                            // echo $total_count - $startNumber;
                            ?>
                        </td>
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
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./complain_form.php?type=<?php echo $type;?>&<?=$qstr;?>&amp;w=u&amp;complain_idx=<? echo $row['complain_idx']; ?>" class="btn btn_03">관리</a>
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
        <!-- <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02"> -->
        <?php if ($is_admin == 'super' && $type=="progress") { ?>
            <a href="./complain_form.php?type=<?=$type;?>" id="member_add" class="btn btn_03">민원 등록</a>
        <?php } ?>
    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
$(function(){
    //minDate:"0d"
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
});

function fcomplainlist_submit(f) {
    if (!is_checked("chk[]")) {
        alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if (document.pressed == "선택삭제") {
        if (!confirm("선택한 민원을 정말 삭제하시겠습니까?")) {
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
