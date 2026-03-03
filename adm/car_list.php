<?php
$sub_menu = "300300";
require_once './_common.php';


auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from a_building_ho as ho
                left join a_post_addr as post on ho.post_id = post.post_idx
                left join a_building as building on ho.building_id = building.building_id
                left join a_building_dong as dong on ho.dong_id = dong.dong_id
                 ";


$sql_search = " where (1) and ho.is_del = '0' and building.is_use = 1 ";
//$sql_search2 = " SELECT 1 FROM a_building_car car WHERE car.ho_id = ho.ho_id AND car.is_del = 0 ";


if ($stx) {
    if($sfl != "car_type_list" && $sfl != "car_name_list"){
        $sql_search .= " and ( ";
    }
    switch ($sfl) {
        case 'mb_point':
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'building_name':
            $sql_search .= " (building.{$sfl} like '%{$stx}%') ";
            break;
        case 'ho_tenant':
            $sql_search .= " (ho.{$sfl} like '%{$stx}%') ";
            break;
        case 'ho_tenant_hp':
            $sql_search .= " (ho.{$sfl} like '%{$stx}%') ";
            break;
        case 'car_type_list':
            $sql_common .= " left join a_building_car as car on ho.ho_id = car.ho_id and car.is_del = 0 ";
            $sql_search .= " and car.car_type LIKE '%{$stx}%' ";
            break;
        case 'car_name_list':
            $sql_common .= " left join a_building_car as car on ho.ho_id = car.ho_id and car.is_del = 0 ";
            $sql_search .= " and car.car_name LIKE '%{$stx}%' ";
            break;
    }
    if($sfl != "car_type_list" && $sfl != "car_name_list"){
        $sql_search .= " ) ";
    }
}

if($post_id){
    $sql_search .= " and ho.post_id = '{$post_id}' ";

    $qstr .= '&post_id='.$post_id;
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




$sql_order = " order by building.building_name asc, CAST(dong.dong_name AS UNSIGNED) ASC, (ho.ho_name REGEXP '^[0-9]+$') ASC,  CAST(ho.ho_name AS UNSIGNED), ho.ho_name ASC, ho_id desc ";
//$sql_order = " order by building.building_name asc, CAST(dong.dong_name AS UNSIGNED) ASC, CAST(ho.ho_name AS UNSIGNED) ASC, ho.ho_id desc ";



// $sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_search2} {$sql_order} ";
//HAVING car_name_list IS NOT NULL AND car_name_list != ''
$sql = " select ho.ho_id, ho.ho_name, ho.ho_tenant, ho.ho_tenant_hp, ho.post_id, ho.dong_id, ho.building_id, post.post_name, building.building_name, building.is_use, dong.dong_name {$sql_common} {$sql_search} GROUP BY ho.ho_id {$sql_order} ";
// echo $sql.'<br>';


$res = sql_query($sql);
$total_count = sql_num_rows($res);



$rows = $config['cf_page_rows'];
//$rows = 5;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$g5['title'] = "차량관리";
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');



//HAVING car_name_list IS NOT NULL AND car_name_list != ''
$sql = " select ho.ho_id, ho.ho_name, ho.ho_tenant, ho.ho_tenant_hp, ho.post_id, ho.dong_id, ho.building_id, post.post_name, building.building_name, building.is_use, dong.dong_name {$sql_common} {$sql_search} GROUP BY ho.ho_id {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 12;

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $sql.'<br>';
    //exit;
}
//echo $st_status;
//echo $sub_menu;

?>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

    <label for="sfl" class="sound_only">검색대상</label>
   
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
                <option value="ho_tenant" <?php echo get_selected($sfl, "ho_tenant"); ?>>입주자명</option>
                <option value="ho_tenant_hp" <?php echo get_selected($sfl, "ho_tenant_hp"); ?>>입주자 연락처</option>
                <option value="car_type_list" <?php echo get_selected($sfl, "car_type_list"); ?>>차종</option>
                <option value="car_name_list" <?php echo get_selected($sfl, "car_name_list"); ?>>차량번호</option>
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
    <a href="./car_list_excel_download.php?<?php echo $qstr;?>" class="btn btn_04">차량관리 엑셀 다운로드</a>
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
                    <th>입주자</th>
                    <th>입주자 연락처</th>
                    <th>등록차량</th>
                    <th>등록차량</th>
                    <th>등록차량</th>
                    <th scope="col" id="mb_list_mng">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {

                    $car_type_list = array();
                    $car_name_list = array();

                    $car_sql = "SELECT * FROM a_building_car WHERE ho_id = '{$row['ho_id']}' AND is_del = 0 ORDER BY car_id ASC";
                    $car_res = sql_query($car_sql);
                    $car_total = sql_num_rows($car_res);

                    for($k=0;$car_row = sql_fetch_array($car_res);$k++){
                        
                        array_push($car_type_list, $car_row['car_type']);
                        array_push($car_name_list, $car_row['car_name']);
                    }

                    // if($sfl == "car_type_list" || $sfl == "car_name_list"){

                    //     if($car_total == 0) continue;
                    // }

                ?>

                    <tr class="<?php echo $bg; ?>">
                        <!-- <td headers="mb_list_chk" class="td_chk" >
                            <input type="hidden" name="st_id[<?php echo $i ?>]" value="<?php echo $row['st_id'] ?>" id="st_id_<?php echo $i ?>">
                            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['st_name']); ?> <?php echo get_text($row['st_name']); ?>님</label>
                            <input type="checkbox" name="chk[]" value="<?php echo $i; ?>" id="chk_<?php echo $i ?>">
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
                        <td><?php echo $row['ho_tenant']; ?></td>
                        <td><?php echo $row['ho_tenant_hp']; ?></td>
                        <td>
                            <?php if($car_type_list[0] != '' && $car_name_list[0] != ''){?>
                            <?php echo '차종 : '.$car_type_list[0]; ?> <br>
                            <?php echo '번호 : '.$car_name_list[0] ?? "-"; ?>
                            <?php }?>
                        </td>
                        <td>
                            <?php if($car_type_list[1] != '' && $car_name_list[1] != ''){?>
                                <?php echo '차종 : '.$car_type_list[1]; ?> <br>
                                <?php echo '번호 : '.$car_name_list[1] ?? "-"; ?>
                            <?php }?>
                        </td>
                        <td>
                            <?php if($car_type_list[2] != '' && $car_name_list[2] != ''){?>
                                <?php echo '차종 : '.$car_type_list[2]; ?> <br>
                                <?php echo '번호 : '.$car_name_list[2] ?? "-"; ?>
                            <?php }?>
                        </td>
                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                            <a href="./car_form.php?<?=$qstr;?>&amp;w=u&amp;ho_id=<? echo $row['ho_id']; ?>" class="btn btn_03">관리</a>
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
        <?php if ($is_admin == 'super') { ?>
            <a href="./car_form.php" id="member_add" class="btn btn_03">차량정보 추가</a>
        <?php } ?>
    </div> -->


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
$(function(){
    $("#dates").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
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
