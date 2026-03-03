<?php
$sub_menu = "600100";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_online_vote as vt
                left join a_post_addr as post on vt.post_id = post.post_idx
                left join a_building as building on vt.building_id = building.building_id
                left join a_building_dong as dong on vt.dong_id = dong.dong_id 
                left join g5_member as mb on vt.wid = mb.mb_id ";


$sql_search = " where (1) and vt.is_del = '0' and building.is_use = 1 ";


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
        case 'mb_name':
            $sql_search .= " (mb.{$sfl} like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " (vt.{$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($vt_period_type == "period"){

    $sql_search .= " and vt_period_type = '{$vt_period_type}' ";

    $qstr .= "&vt_period_type=".$vt_period_type;

}else if($vt_period_type == "personnel"){

    $sql_search .= " and vt_period_type = '{$vt_period_type}' ";

    $qstr .= "&vt_period_type=".$vt_period_type;

}else if($vt_period_type == "period_not"){

    $sql_search .= " and vt_period_type = '{$vt_period_type}' ";

    $qstr .= "&vt_period_type=".$vt_period_type;
}

if($post_id){
    $sql_search .= " and post.post_idx = '{$post_id}' ";

    $qstr .= "&post_id=".$post_id;
}

if($vt_status){
    $sql_search .= " and vt.vt_status = '{$vt_status}' ";

    $qstr .= "&vt_status=".$vt_status;
}

if($building_name){
    $sql_search .= " and building.building_name like '%{$building_name}%' ";

    $qstr .= "&building_name=".$building_name;
}

if($dong_id){

    $sql_search .= " and vt.dong_id = '{$dong_id}' ";

    $qstr .= '&dong_id='.$dong_id;

}

if($vote_sdate != "" && $vote_edate == ""){
    $sql_search .= " and vt_edate >= '{$vote_sdate}' ";

    $qstr .= '&vote_sdate='.$vote_sdate;
}else if($vote_sdate == "" && $vote_edate != ""){
    $sql_search .= " and vt_sdate <= '{$vote_edate}' ";

    $qstr .= '&vote_edate='.$vote_edate;

}else if($vote_sdate != "" && $vote_edate != ""){
    $sql_search .= " and vt_sdate <= '{$vote_edate}' and vt_edate >= '{$vote_sdate}' ";

    $qstr .= '&vote_sdate='.$vote_sdate.'&vote_edate='.$vote_edate;
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

$sql_order = " order by vt.vt_id desc ";

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

$g5['title'] = "온라인투표";

require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$grade_sql = "SELECT * FROM a_grade WHERE is_del = 0 ORDER BY is_prior asc, gidx asc";
$grade_res = sql_query($grade_sql);


$sql = " select vt.*, post.post_idx, post.post_name, building.building_name, building.is_use, dong.dong_name, mb.mb_name {$sql_common} {$sql_search} {$sql_search2} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 11;

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql;
  
    //echo '<br>'.$page;
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
        <div class="sch_label">투표 종류</div>
        <div class="sch_selects ver_flex gap15">
            <div class="sch_radios">
                <input type="radio" name="vt_period_type" id="date_type0" value="" <?php echo $vt_period_type == "" ? "checked" : "";?>>
                <label for="date_type0">전체</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="vt_period_type" id="date_type1" value="period" <?php echo $vt_period_type == "period" ? "checked" : "";?>>
                <label for="date_type1">기간</label>
            </div>
            <input type="text" name="vote_sdate" id="vote_sdate" value="<?php echo $vote_sdate; ?>" class="bansang_ipt <?php echo $vt_period_type == 'period' ? 'ver2' :''; ?> ipt_date ipt_date2" <?php echo $vt_period_type == 'period' ? '' :'disabled'; ?> > 
            ~
            <input type="text" name="vote_edate" id="vote_edate" value="<?php echo $vote_edate; ?>" class="bansang_ipt <?php echo $vt_period_type == 'period' ? 'ver2' :''; ?> ipt_date ipt_date2" <?php echo $vt_period_type == 'period' ? '' :'disabled'; ?>>
            <div class="sch_radios">
                <input type="radio" name="vt_period_type" id="date_type2" value="personnel" <?php echo $vt_period_type == "personnel" ? "checked" : "";?>>
                <label for="date_type2">인원 수 마감</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="vt_period_type" id="date_type3" value="period_not" <?php echo $vt_period_type == "period_not" ? "checked" : "";?>>
                <label for="date_type3">무기한</label>
            </div>
        </div>
    </div>
    <script>
        $("input[name='vt_period_type']").on('change', function() {
            let selectedValue = $("input[name='vt_period_type']:checked").val();
            // console.log("선택된 값:", selectedValue);

            if(selectedValue == "period"){
                $(".ipt_date2").addClass("ver2");
                $(".ipt_date2").attr("disabled", false);
            }else{
                $(".ipt_date2").removeClass("ver2");
                $(".ipt_date2").attr("disabled", true);
            }
            // 필요 시 다른 동작도 추가 가능
            // if (selectedValue === "period") { ... }
        });
    </script>
    <div class="serach_box">
        <div class="sch_label">상태</div>
        <div class="sch_selects ver_flex gap15">
            <div class="sch_radios">
                <input type="radio" name="vt_status" id="status1" value="" <?php echo $vt_status == "" ? "checked" : "";?>>
                <label for="status1">전체</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="vt_status" id="status2" value="1" <?php echo $vt_status == "1" ? "checked" : "";?>>
                <label for="status2">진행중</label>
            </div>
            <div class="sch_radios">
                <input type="radio" name="vt_status" id="status3" value="2" <?php echo $vt_status == "2" ? "checked" : "";?>>
                <label for="status3">종료</label>
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
        <div class="sch_label">동</div>
        <div class="sch_selects">
            <?php
            $dong_sql = "SELECT * FROM a_building_dong WHERE building_id = '{$building_id}' ORDER BY dong_name asc, dong_id desc";
            $dong_res = sql_query($dong_sql);
            ?>
            <select name="dong_id" id="dong_id" class="bansang_sel">
                <option value="">동 선택</option>
                <?php
                while($dong_row = sql_fetch_array($dong_res)){
                ?>
                <option value="<?php echo $dong_row['dong_id'];?>" <?php echo get_selected($dong_row['dong_id'], $dong_id); ?>><?php echo $dong_row['dong_name']; ?></option>
                <?php }?>
            </select>
        </div>
    </div>
    <div class="serach_box">
        <div class="sch_label">검색어</div>
        <div class="sch_selects ver_flex">
            <select name="sfl" id="sfl" class="bansang_sel">
                <option value="mb_name" <?php echo get_selected($sfl, "mb_name"); ?>>담당자</option>
                <option value="vt_title" <?php echo get_selected($sfl, "vt_title"); ?>>투표주제</option>
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


<form name="fonlinevotelist" id="fonlinevotelist" action="./online_vote_list_update.php" onsubmit="return fonlinevotelist_submit(this);" method="post">
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
                    <th>부서</th>
                    <th>담당자</th>
                    <th>투표 주제</th>
                    <th>투표 기간</th>
                    <th>상태</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                ?>

                    <tr class="<?php echo $bg; ?>">
                        <!-- <td headers="mb_list_chk" class="td_chk" >
                            <input type="checkbox" name="chk[]" value="<?php echo $row['vt_id']; ?>" id="chk_<?php echo $i ?>">
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
                        <td><?php echo $row['dong_id'] != '-1' ? $row['dong_name'].'동' : '전체'; ?></td>
                        <td>
                            <?php 
                            if($row['wid'] == 'admin'){
                                echo "최고관리자";
                            }else{
                                //$mbs = get_member($row['wid']);

                                $mng_sql = "SELECT mng.*, depart.md_name FROM a_mng as mng
                                            LEFT JOIN a_mng_department as depart on depart.md_idx = mng.mng_department
                                            WHERE mng.mng_id = '{$row['wid']}'";
                                $mng_row = sql_fetch($mng_sql);

                                echo $mng_row['md_name'];
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if($row['wid'] == 'admin'){
                                $mbs = get_member($row['wid']);
                                echo $mbs['mb_name'];
                            }else{
                                //$mbs = get_member($row['wid']);

                                $mng_sql = "SELECT mng.*, grades.mg_name FROM a_mng as mng
                                            LEFT JOIN a_mng_grade as grades on grades.mg_idx = mng.mng_grades
                                            WHERE mng.mng_id = '{$row['wid']}'";
                                $mng_row = sql_fetch($mng_sql);

                                echo $mng_row['mng_name'].' '.$mng_row['mg_name'];
                            }
                            ?>
                        </td>
                        <td><?php echo $row['vt_title']; ?></td>
                        <td>
                            <?php
                            if($row['vt_period_type'] == "period"){
                                echo $row['vt_sdate']." ~ ".$row['vt_edate']; 
                            }else{
                                echo "-";
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            switch($row['vt_status']){
                                case "0":
                                    echo "대기";
                                    break;
                                case "1":
                                    echo "진행중";
                                    break;
                                case "2":
                                    echo "종료";
                                    break;
                            }
                            ?>
                        </td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./online_vote_form.php?<?=$qstr;?>&amp;w=u&amp;vt_id=<? echo $row['vt_id']; ?>" class="btn btn_03">관리</a>
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
        <?php if ($is_admin == 'super') { ?>
            <a href="./online_vote_form.php" id="member_add" class="btn btn_03">투표 등록</a>
        <?php } ?>
    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
$(function(){
    //minDate:"0d"
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
});

function fstudentlist_submit(f) {
    if (!is_checked("chk[]")) {
        alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if (document.pressed == "선택삭제") {
        if (!confirm("선택한 투표를 정말 삭제하시겠습니까?")) {
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
