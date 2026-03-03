<?php
$sub_menu = "300200";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_mng_team as mng
                left join a_post_addr as post on mng.post_id = post.post_idx
                left join a_building as building on mng.build_id = building.building_id
                left join a_building_dong as dong on mng.dong_id = dong.dong_id
                left join a_building_ho as ho on mng.ho_id = ho.ho_id 
                left join a_mng_team_grade as grade on mng.mt_grade = grade.gr_id ";


$sql_search = " where (1) and mng.is_del = '0' and building.is_use = 1 ";


if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_point':
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_level':
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'building_name':
            $sql_search .= " (building.{$sfl} like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " (mng.{$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($st_grade){
    $sql_search .= " and std.st_grade = '{$st_grade}' ";

    $qstr .= '&st_grade='.$st_grade;
}

if($post_id){
    $sql_search .= " and mng.post_id = '{$post_id}' ";

    $qstr .= '&post_id='.$post_id;

    $sql_building = "SELECT * FROM a_building WHERE post_id = '{$post_id}' and is_del = 0";
    $res_building = sql_query($sql_building);
}

if($building_id){
    $sql_search .= " and mng.build_id = '{$building_id}' ";

    $qstr .= '&building_id='.$building_id;

    $sql_dong = "SELECT * FROM a_building_dong WHERE building_id = '{$building_id}' and is_del = 0";
    $res_dong = sql_query($sql_dong);
}

if($dong_id){
    $sql_search .= " and mng.dong_id = '{$dong_id}' ";

    $qstr .= '&dong_id='.$dong_id;
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

$sql_order = " order by mng.mt_id desc ";

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


$g5['title'] = "관리단";
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');


$sql = " select mng.*, post.post_name, building.building_name, building.is_use, dong.dong_name, ho.ho_name, grade.gr_name {$sql_common} {$sql_search} {$sql_search2} {$sql_order} limit {$from_record}, {$rows} ";
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
        <div class="sch_label">지역</div>
        <div class="sch_selects ver_flex">
            <select name="post_id" id="post_id" class="bansang_sel" onchange="post_change();">
                <option value="">지역 선택</option>
                <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                    <option value="<?php echo $post_row['post_idx']; ?>" <?php echo get_selected($post_id, $post_row['post_idx']); ?>><?php echo $post_row['post_name']; ?></option>
                <?php }?>
            </select>
            <select name="building_id" id="building_id" class="bansang_sel" onchange="building_change();">
                <option value="">단지 선택</option>
                <?php while($row_building = sql_fetch_array($res_building)){ ?>
                <option value="<?php echo $row_building['building_id']?>" <?php echo get_selected($building_id, $row_building['building_id']); ?>><?php echo $row_building['building_name'];?></option>
                <?php }?>
            </select>
            <select name="dong_id" id="dong_id" class="bansang_sel">
                <option value="">동 선택</option>
                <?php while($row_dong = sql_fetch_array($res_dong)){ ?>
                <option value="<?php echo $row_dong['dong_id']?>" <?php echo get_selected($dong_id, $row_dong['dong_id']); ?>><?php echo $row_dong['dong_name'];?>동</option>
                <?php }?>
            </select>
        </div>
        <script>
        function post_change(){
            var postSelect = document.getElementById("post_id");
            var postValue = postSelect.options[postSelect.selectedIndex].value;

            console.log('postValue', postValue);

            $.ajax({

            url : "./post_building_ajax.php", //ajax 통신할 파일
            type : "POST", // 형식
            data: { "post_id":postValue}, //파라미터 값
            success: function(msg){ //성공시 이벤트

                //console.log(msg);
                $("#building_id").html(msg);
            }

            });
        }

        function building_change(){
            var buildingSelect = document.getElementById("building_id");
            var buildingValue = buildingSelect.options[buildingSelect.selectedIndex].value;

            console.log('buildingValue', buildingValue);

            $.ajax({

            url : "./building_dong_ajax.php", //ajax 통신할 파일
            type : "POST", // 형식
            data: { "building_id":buildingValue}, //파라미터 값
            success: function(msg){ //성공시 이벤트

                //console.log(msg);
                $("#dong_id").html(msg);
            }

            });
        }
        </script>
    </div>
    <div class="serach_box">
        <div class="sch_label">검색어</div>
        <div class="sch_selects ver_flex">
            <select name="sfl" id="sfl" class="bansang_sel">
                <option value="building_name" <?php echo get_selected($sfl, "building_name"); ?>>단지명</option>
                <option value="mt_name" <?php echo get_selected($sfl, "mt_name"); ?>>이름</option>
                <option value="mt_hp" <?php echo get_selected($sfl, "mt_hp"); ?>>연락처</option>
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
           
            let type = "<?php echo $type; ?>";

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
<!-- <div class="local_desc01 local_desc">
    <p>
        회원자료 삭제 시 다른 회원이 기존 회원아이디를 사용하지 못하도록 회원아이디, 이름, 닉네임은 삭제하지 않고 영구 보관합니다.
    </p>
</div> -->

<?php if($total_count > 0){?>
<div class="excel_download_wrap">
    <a href="./manage_list_excel.php?<?php echo $qstr;?>" class="btn btn_04">관리단 엑셀 다운로드</a>
</div>
<?php }?>
<form name="fmanagelist" id="fmanagelist" action="./manage_list_update.php" onsubmit="return fmanagelist_submit(this);" method="post">
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
                    <th>번호</th>
                    <th>지역</th>
                    <th>단지명</th>
                    <th>동</th>
                    <th>호수</th>
                    <th>구분</th>
                    <th>이름</th>
                    <th>연락처</th>
                    <th>직책</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                ?>

                    <tr class="<?php echo $bg; ?>">
                        <td headers="mb_list_chk" class="td_chk" >
                            <input type="checkbox" name="chk[]" value="<?php echo $row['mt_id']; ?>" id="chk_<?php echo $i ?>">
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
                        <td><?php echo $row['mt_type'] == 'OUT' ? "-" : $row['dong_name'].'동'; ?></td>
                        <td><?php echo $row['mt_type'] == 'OUT' ? "-" : $row['ho_name'].'호'; ?></td>
                        <td><?php echo $row['mt_type'] == "IN" ? "입주민" : "외부인"; ?></td>
                        <td><?php echo $row['mt_name']; ?></td>
                        <td><?php echo $row['mt_hp']; ?></td>
                        <td><?php echo $row['gr_name']; ?></td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./manage_form.php?<?=$qstr;?>&amp;w=u&amp;mt_id=<? echo $row['mt_id']; ?>" class="btn btn_03">관리</a>
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
         <button type="button" onClick="popOpen('mng_grade_pop');" class="btn btn_01">관리단 직책관리</button>
        <?php if ($is_admin == 'super') { ?>
            <a href="./manage_form.php" id="member_add" class="btn btn_03">관리단 추가</a>
        <?php } ?>
    </div>


</form>

<?php 
$mng_grade_list = "SELECT * FROM a_mng_team_grade WHERE is_del = 0 ORDER BY gr_id asc";
$mng_grade_res = sql_query($mng_grade_list);
$mng_grade_total = sql_num_rows($mng_grade_res);
?>
<div class="cm_pop" id="mng_grade_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('mng_grade_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">관리단 직책 관리</div>
        <form id="mng_grade" method="post">
            <div class="mng_grade_box_wrapper">
                <?php if($mng_grade_total > 0){?>
                    <?php for($i=0;$mng_grade_row = sql_fetch_array($mng_grade_res);$i++){?>
                        <div class="mng_grade_box_wrap">
                            <div class="mng_grade_box">
                                <input type="hidden" name="gr_id[]" value="<?php echo $mng_grade_row['gr_id']; ?>">
                                <input type="text" name="grade[]" class="bansang_ipt ver2" value="<?php echo $mng_grade_row['gr_name']; ?>">
                            </div>
                            <?php if($i == 0){?>
                            <button type="button" onclick="mng_grade_add();" class="bansang_btns ver1">직책 추가</button>
                            <?php }else{ ?>
                            <div class="mng_grade_del">
                                <input type="checkbox" name="mng_grade_del[<?php echo $i; ?>]" id="mng_grade_del<?php echo $i+1;?>" >
                                <label for="mng_grade_del<?php echo $i+1;?>">삭제</label>
                            </div>
                            <?php }?>
                        </div>
                    <?php }?>
                <?php }else{ ?>
                <div class="mng_grade_box_wrap">
                    <div class="mng_grade_box">
                        <input type="text" name="grade[]" class="bansang_ipt ver2">
                    </div>
                    <button type="button" onclick="mng_grade_add();" class="bansang_btns ver1">직책 추가</button>
                </div>
                <?php }?>
            </div>
            <p class="cm_pop_desc_adm">* 입주민에게 지정한 직책을 지울 경우 데이터 혼란이 올 수 있습니다.
            꼭 직책을 변경 한 후 삭제 해주세요.</p>
            <!-- <p class="cm_pop_desc2">학생이 휴원상태인 경우 관리자에게 문의해주세요:)</p> -->
            <div class="cm_pop_btn_box">
                <button type="button" class="cm_pop_btn ver2 grade_save_btn" onClick="grade_save();">저장</button>
            </div>
        </form>
	</div>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
$(function(){
    $("#dates").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
});

function mng_grade_add(){

    let html = `<div class="mng_grade_box_wrap">
                <div class="mng_grade_box">
                    <input type="text" name="grade[]" class="bansang_ipt ver2" required>
                </div>
                <button type="button" onclick="mng_grade_remove(this);" class="bansang_btns ver2">삭제</button>
            </div>`;
    $(".mng_grade_box_wrapper").append(html);
}

//매니저 직책 삭제
function mng_grade_remove(ele){
    ele.closest('.mng_grade_box_wrap').remove();
}

function grade_save(){

    $(".grade_save_btn").attr('disabled', true);

    var form1 = $("#mng_grade").serialize();
    //console.log(form1);

    $.ajax({
        type: "POST",
        url: "./manage_list_grade.php",
        data: form1,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);
            if(data.result == false) { 
                alert(data.msg);
                $(".grade_save_btn").attr('disabled', false);
                return false;
            }else{
                alert(data.msg);

                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        },
        error:function(e){
            alert(e);
        }
    });
}

function fmanagelist_submit(f) {
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
