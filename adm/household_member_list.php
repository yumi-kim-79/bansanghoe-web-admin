<?php
$sub_menu = "300110";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_building_household as hh
                LEFT JOIN a_building_ho as ho on hh.ho_id = ho.ho_id 
                LEFT JOIN a_building as building on hh.building_id = building.building_id 
                LEFT JOIN a_building_dong as dong on hh.dong_id = dong.dong_id 
                LEFT JOIN a_post_addr as post on hh.post_id = post.post_idx ";

$sql_search = " where (1) and hh.is_del = '0' and building.is_use = 1 and ho.ho_status = 'Y' and hh.hh_relationship != '' ";

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
        case 'building_name':
            $sql_search .= " (building.{$sfl} like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " (ho.{$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($ho_tenant_at){
    $sql_search .= " and ho.ho_tenant_at = '{$ho_tenant_at}' ";

    $qstr .= '&ho_tenant_at='.$ho_tenant_at;
}

if($ho_status == '0'){
    $sql_search .= " and ho.ho_status = '{$ho_status}' ";

    $qstr .= '&ho_status=0';
}else if($ho_status == '1'){
    $sql_search .= " and ho.ho_status = '{$ho_status}' ";

    $qstr .= '&ho_status=1';
}

if($post_id){
    $sql_search .= " and ho.post_id = '{$post_id}' ";

    $qstr .= '&post_id='.$post_id;

    $sql_building = "SELECT * FROM a_building WHERE post_id = '{$post_id}' and is_del = 0";
    $res_building = sql_query($sql_building);
}

if($building_id){
    $sql_search .= " and ho.building_id = '{$building_id}' ";

    $qstr .= '&building_id='.$building_id;

    $sql_dong = "SELECT * FROM a_building_dong WHERE building_id = '{$building_id}' and is_del = 0";
    $res_dong = sql_query($sql_dong);
}

if($building_id_sch){
    $building_id_sch_t = implode(',', $building_id_sch);
    $sql_search .= " and ho.building_id in ({$building_id_sch_t}) ";

    foreach($building_id_sch as $key => $val){
        $qstr .= '&building_id_sch[]='.$val;
    }
}

if($dong_id){
    $sql_search .= " and ho.dong_id = '{$dong_id}' ";

    $qstr .= '&dong_id='.$dong_id;
}

if($sst == 'deleted_at'){
    $sql_search2 .= " and std.is_del = 1 ";
}

if ($is_admin != 'super') {
    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
}

if (!$sst) {
    $sst = "ho_id";
    $sod = "desc";
}

$sql_order = " order by building.building_name asc, dong.dong_name + 1 asc, ho.ho_name + 1 asc, ho.ho_id desc ";
$sql_order2 = " order by building.building_name asc, dong.dong_name + 0 asc, (ho.ho_name REGEXP '^[0-9]+$') ASC, CAST(ho.ho_name AS UNSIGNED), ho.ho_name ASC, ho.ho_id desc";
// $sql_order = " order by building.building_name asc, dong.dong_name + 1 asc, (ho.ho_name REGEXP '^[0-9]+$') ASC,  CAST(ho.ho_name AS UNSIGNED), ho.ho_name ASC, ho.ho_id desc ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
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

$g5['title'] = "세대구성원";
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');


$sql = " select hh.*, ho.ho_name, ho.ho_status, ho.ho_tenant, ho.ho_tenant_hp, ho.ho_tenant_at, building.building_name, building.is_use, dong.dong_name, post.post_name {$sql_common} {$sql_search} {$sql_search2} {$sql_order2} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 14;

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);


if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $sql;
}
//echo $st_status;
//echo $sub_menu;

?>

<link rel="stylesheet" href="/css/select2.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

    <label for="sfl" class="sound_only">검색대상</label>
    <?php
    $building_sql = "SELECT * FROM a_building WHERE is_del = 0 ORDER BY building_name asc, building_id desc";
    $building_res = sql_query($building_sql);
    ?>
    <div class="serach_box">
        <div class="sch_label">단지</div>
        <div class="sch_selects ver_flex gap15">
            <div class="multi_select_wrap">
                <select multiple class="select" id="building_id_sch" name="building_id_sch[]" style="min-width:335px;" multiple="multiple">
                    <option value="">선택</option>
                    <?php while($building_sel_row = sql_fetch_array($building_res)){ ?>
                        <option value="<?php echo $building_sel_row['building_id']; ?>"><?php echo $building_sel_row['building_name']; ?></option>
                    <?php }?>
                </select>
            </div>
        </div>
        <button type="submit" class="bansang_btns ver1">검색</button>
    </div>
    <script>
        $(document).ready(function() {
            $("#building_id_sch").select2(
                {
                    placeholder: "단지를 선택하세요", // 기본 placeholder 설정
                    language: {
                        noResults: function() {
                            return "검색 결과가 없습니다."; // 원하는 문구로 변경
                        }
                    }
                }
            );

             // URL 파라미터에서 선택값 가져오기
            const urlParams = new URLSearchParams(window.location.search);
            const selectedBuildings = urlParams.getAll('building_id_sch[]'); // 배열 파라미터 읽기
            // 선택값 적용
            if (selectedBuildings.length > 0) {
                $("#building_id_sch").val(selectedBuildings).trigger('change');
            }
        })
    </script>
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
<!-- house_hold_list_excel -->
<div class="excel_download_wrap">
    <a href="./household_member_list_excel.php?<?php echo $qstr;?>" class="btn btn_04">세대 구성원 엑셀 다운로드</a>
</div>
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
                    <th>입주자</th>
                    <th>입주자 연락처</th>
                    <th>입주일</th>
                    <th>세대구성원</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                    
                    //차량 수
                    $car_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_car WHERE ho_id = '{$row['ho_id']}' and is_del = 0");

                    //세대구성원 수
                    $hh_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_household WHERE ho_id = '{$row['ho_id']}' and is_del = 0");
                ?>

                    <tr class="<?php echo $bg; ?>">
                        <!-- <td headers="mb_list_chk" class="td_chk" >
                            <input type="hidden" name="st_id[<?php echo $i ?>]" value="<?php echo $row['st_id'] ?>" id="st_id_<?php echo $i ?>">
                            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['st_name']); ?> <?php echo get_text($row['st_name']); ?>님</label>
                            <input type="checkbox" name="chk[]" value="<?php echo $row['ho_id']; ?>" id="chk_<?php echo $i ?>">
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
                        <td><?php echo $row['ho_status'] == 'N' ? '-' : $row['ho_tenant']; ?></td>
                        <td><?php echo $row['ho_status'] == 'N' ? '-' : $row['ho_tenant_hp']; ?></td>
                        <td><?php echo $row['ho_status'] == 'N' ? '-' : $row['ho_tenant_at']; ?></td>
                        <td>
                            관계 : <?php echo $row['hh_relationship']; ?><br>
                            이름 : <?php echo $row['hh_name']; ?><br>
                            연락처 : <?php echo $row['hh_hp'] == '' ? '-' : $row['hh_hp']; ?><br>
                        </td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./household_member_form.php?<?=$qstr;?>&amp;w=u&amp;ho_id=<? echo $row['ho_id']; ?>" class="btn btn_03">관리</a>
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

    <!-- <div class="btn_fixed_top">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    </div> -->


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
$(function(){
    $("#dates").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
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
