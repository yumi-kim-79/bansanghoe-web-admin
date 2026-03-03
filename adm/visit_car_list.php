<?php
$sub_menu = "300400";
require_once './_common.php';

$today = date("Y-m-d");

auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_building_visit_car as vc 
                left join a_building as building on vc.building_id = building.building_id 
                left join a_post_addr as post on building.post_id = post.post_idx
                left join a_building_dong as dong on vc.dong_id = dong.dong_id
                left join a_building_ho as ho on vc.ho_id = ho.ho_id
                ";


$sql_search = " where (1) and vc.is_del = 0 and building.is_use = 1 ";


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
            $sql_search .= " (vc.{$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($status){
    
    if($status == "N"){
        $sql_search .= " and vc.out_status = '{$status}' AND DATE_ADD(vc.visit_date, INTERVAL (vc.visit_day - 1) DAY) >= '{$today}' ";
    }else{
        $sql_search .= " and vc.out_status = '{$status}' OR ( vc.out_status = 'N' and DATE_ADD(vc.visit_date, INTERVAL (vc.visit_day - 1) DAY) < '{$today}' ) ";
    }

    $qstr .= '&status='.$status;
}

if($visit_date != "" && $end_date == ""){
    $sql_search .= " and DATE_ADD(vc.visit_date, INTERVAL (vc.visit_day - 1) DAY) >= '{$visit_date}' ";

    $qstr .= '&visit_date='.$visit_date;
}else if($visit_date == "" && $end_date != ""){
    $sql_search .= " and visit_date <= '{$end_date}' ";

    $qstr .= '&end_date='.$end_date;

}else if($visit_date != "" && $end_date != ""){
    $sql_search .= " and visit_date <= '{$end_date}' and DATE_ADD(vc.visit_date, INTERVAL (vc.visit_day - 1) DAY) >= '{$visit_date}' ";

    $qstr .= '&visit_date='.$visit_date.'&end_date='.$end_date;
}

if($post_id){
    $sql_search .= " and building.post_id = '{$post_id}' ";

    $qstr .= '&post_id='.$post_id;
}

if($building_name){
    $sql_search .= " and building.building_name like '%{$building_name}%' ";

    $qstr .= '&building_name='.$building_name;
}

if($dong_id){
    $sql_search .= " and vc.dong_id = '{$dong_id}' ";

    $qstr .= '&dong_id='.$dong_id;
}

if($ho_id){
    $sql_search .= " and vc.ho_id = '{$ho_id}' ";

    $qstr .= '&ho_id='.$ho_id;
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

$sql_order = " order by vc.visit_date desc, vc.car_id desc, building.building_name asc, dong.dong_name asc, ho.ho_name asc ";
// $sql_order = " order by building.building_name asc, dong.dong_name + 1 asc, ho.ho_name + 1 asc ";

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

$g5['title'] = "방문차량 관리";
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$grade_sql = "SELECT * FROM a_grade WHERE is_del = 0 ORDER BY is_prior asc, gidx asc";
$grade_res = sql_query($grade_sql);


$sql = " select vc.*, building.post_id, building.building_name, building.is_use, post.post_name, dong.dong_name, ho.ho_name, DATE_ADD(vc.visit_date, INTERVAL (vc.visit_day - 1) DAY) as end_day {$sql_common} {$sql_search} {$sql_search2} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 10;

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

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
                <input type="radio" name="status" id="status1" value="" checked>
                <label for="status1">전체</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="status" id="status2" value="N" <?php echo $status == 'N' ? 'checked' : '';?>>
                <label for="status2">방문 전 / 방문 중</label>
            </div>
            <!-- <div class="sch_radios">
                <input type="radio" name="status" id="status3" value="방문 중">
                <label for="status3">방문 중</label>
            </div> -->
            <div class="sch_radios">
                <input type="radio" name="status" id="status4" value="Y" <?php echo $status == 'Y' ? 'checked' : '';?>>
                <label for="status4">출차</label>
            </div>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">방문기간</div>
        <div class="sch_selects ver_flex">
            <div class="ipt_date_boxs">
                <input type="text" name="visit_date" class="bansang_ipt ver2 ipt_date ipt_date_visit"  value="<?php echo $visit_date; ?>" >
                <!-- <button type="button" onclick="date_del('ipt_date_visit', 'date_del_btn')" class="date_del_btn <?php echo $visit_date != '' ? '' : 'date_del_btn_hd'; ?> date_del_btn1">
                    <span></span>
                    <span></span>
                </button> -->
            </div> ~
            <div class="ipt_date_boxs">
                <input type="text" name="end_date" class="bansang_ipt ver2 ipt_date ipt_date_visit_end"  value="<?php echo $end_date; ?>" >
                <!-- <button type="button" onclick="date_del('ipt_date_visit_end', 'date_del_btn2')" class="date_del_btn <?php echo $end_date != '' ? '' : 'date_del_btn_hd'; ?> date_del_btn2">
                    <span></span>
                    <span></span>
                </button> -->
            </div>
            <!-- <script>
                function date_del(ele, btnele){
                    $("." + ele).val("");
                    $("." + btnele).hide();
                }
            </script> -->
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">지역</div>
        <div class="sch_selects">
            <select name="post_id" id="post_id" class="bansang_sel" onchange="post_change();">
                <option value="">지역 선택</option>
                <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                    <option value="<?php echo $post_row['post_idx']; ?>" <?php echo get_selected($post_id, $post_row['post_idx']); ?>><?php echo $post_row['post_name']; ?></option>
                <?php }?>
            </select>
            <script>
                function post_change(){
                    $("#building_id").val("");
                    $("#building_name").val("");
                    $(".sch_result_box1").html("");
                }
            </script>
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
        <div class="sch_label">검색어</div>
        <div class="sch_selects ver_flex">
            <select name="sfl" id="sfl" class="bansang_sel">
                <option value="visit_hp" <?php echo get_selected($sfl, "visit_hp"); ?>>연락처</option>
                <option value="visit_car_name" <?php echo get_selected($sfl, "visit_car_name"); ?>>차종</option>
                <option value="visit_car_number" <?php echo get_selected($sfl, "visit_car_number"); ?>>차량번호</option>
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

<?php if($total_count > 0){?>
<div class="excel_download_wrap">
    <a href="./visit_car_list_excel.php?<?php echo $qstr;?>" class="btn btn_04">방문차량 엑셀 다운로드</a>
</div>
<?php }?>
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
                    <th>지역</th>
                    <th>단지명</th>
                    <th>동</th>
                    <th>호수</th>
                    <th>방문기간</th>
                    <th>차량</th>
                    <th>연락처</th>
                    <th>상태</th>
                    <th>출차시간</th>
                    <!-- <th scope="col" id="mb_list_mng">관리</th> -->
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
                        <!-- <td headers="mb_list_chk" class="td_chk" >
                            <input type="checkbox" name="chk[]" value="<?php echo $row['car_id']; ?>" id="chk_<?php echo $i ?>">
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
                        <td><?php echo $row['dong_name'].'동'; ?></td>
                        <td><?php echo $row['ho_name'].'호'; ?></td>
                        <td>
                            <?php
                             $visit_days = $row['visit_day'] - 1;

                             $endDayT = $visit_days > 0 ? ' ~ '.$row['end_day'] : '';
                             echo $row['visit_date'].''.$endDayT; 
                             ?>
                        </td>
                        <td><?php echo $row['visit_car_name'].' '.$row['visit_car_number']; ?></td>
                        <td><?php echo $row['visit_hp']; ?></td>
                        <td>
                            <?php
                            $dates = date("Y-m-d");

                            if($row['out_status'] == 'N'){
                               
                                if($row['end_day'] < $dates){
                                    echo "출차";
                                }else{
                                    echo "방문 전 / 방문 중";
                                }
                            }else{
                                echo "출차";
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if($row['out_status'] == 'N'){
                               
                                if($row['end_day'] < $dates){
                                    echo date("Y.m.d", strtotime($row['end_day']));
                                }else{
                                    echo "-";
                                }
                            }else{
                                echo date("Y.m.d H:i", strtotime($row['out_at']));
                            }
                            
                            ?>
                        </td>
                        <!-- <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./car_form.php?type=<?php echo $type;?>&<?=$qstr;?>&amp;w=u&amp;st_idx=<? echo $row['st_idx']; ?>" class="btn btn_03">관리</a>
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
        <!-- <?php if ($is_admin == 'super') { ?>
            <a href="./car_form.php" id="member_add" class="btn btn_03">차량정보 추가</a>
        <?php } ?> -->
    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
$(function(){
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"-365d", onSelect: function(dateText, inst) {
        console.log("선택된 날짜: ", inst); // 선택된 날짜를 콘솔에 출력
        // 다른 처리 로직도 추가 가능
        $(this).siblings(".date_del_btn").show();
    }
     });
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
