<?php
$sub_menu = "300300";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '추가';
}

$g5['title'] .= '차량관리 ' . $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;

$sql = "SELECT * FROM a_building_ho
        WHERE ho_id = {$ho_id}";
$row = sql_fetch($sql);

//단지정보
$building_sql = "SELECT * FROM a_building WHERE building_id = '{$row['building_id']}'";
$building_row = sql_fetch($building_sql);

//지역정보
$post_sql = "SELECT * FROM a_post_addr WHERE post_idx = '{$row['post_id']}'";
$post_row = sql_fetch($post_sql);

//동 정보
$dong_sql = "SELECT * FROM a_building_dong WHERE dong_id = '{$row['dong_id']}'";
$dong_row = sql_fetch($dong_sql);

//차량정보
$car_sql = "SELECT * FROM a_building_car WHERE ho_id = '{$row['ho_id']}' and is_del = 0 ";
$car_res = sql_query($car_sql);
$car_total = sql_num_rows($car_res);

$car_totals = 3 - $car_total;

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql;

    //print_r2($row);
}
$qstr .= '&ho_id='.$ho_id;

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
?>

<form name="fcar" id="fcar" action="./car_form_update.php" onsubmit="return fcar_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="ho_id" value="<?php echo $row['ho_id']; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">차량관리 정보</h2>
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_4">
                <col>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th>지역</th>
                    <td>
                        <input type="hidden" name="building_id" value="<?php echo $row['post_id']; ?>">
                        <input type="text" name="post_name" id="post_name" class="bansang_ipt" value="<?php echo $post_row['post_name']; ?>" size="50" readonly>
                    </td>
                    <th>단지</th>
                    <td>
                        <input type="hidden" name="building_id" value="<?php echo $row['building_id']; ?>">
                        <input type="text" name="building_name" id="building_name" class="bansang_ipt" value="<?php echo $building_row['building_name']; ?>" size="50" readonly>
                    </td>
                </tr>
                <tr>
                    <th>동</th>
                    <td>
                        <input type="hidden" name="dong_id" value="<?php echo $row['dong_id']; ?>">
                        <input type="text" name="dong_name" id="dong_name" class="bansang_ipt" value="<?php echo $dong_row['dong_name'].'동'; ?>" readonly>
                    </td>
                    <th>호수</th>
                    <td><input type="text" name="ho_name" id="ho_name" class="bansang_ipt" value="<?php echo $row['ho_name'].'호'; ?>" readonly></td>
                </tr>
                <tr>
                    <th>이름</th>
                    <td>
                        <input type="text" name="ho_tenant" id="ho_tenant" class="bansang_ipt" value="<?php echo $row['ho_tenant']; ?>" readonly>
                    </td>
                    <th>연락처</th>
                    <td>
                        <input type="hidden" name="tenant_id" id="tenant_id" value="<?php echo get_user_hp($row['ho_tenant_hp'])['mb_id'];?>">
                        <input type="text" name="ho_tenant_hp" id="ho_tenant_hp" class="bansang_ipt" value="<?php echo $row['ho_tenant_hp']; ?>" readonly>
                    </td>
                </tr>
                <tr>
                    <th>등록차량</th>
                    <td colspan="3">
                        <div class="car_list_wrap">
                            <?php for($i=0;$car_row = sql_fetch_array($car_res);$i++){ ?>
                            <div class="car_list_inner">
                                <input type="hidden" name="car_id[]" value="<?php echo $car_row['car_id']; ?>">
                                <div class="car_list_box">
                                    <input type="text" name="car_type[]" class="bansang_ipt ver2" placeholder="차종을 입력하세요." value="<?php echo $car_row['car_type']; ?>">
                                </div>
                                <div class="car_list_box">
                                    <input type="text" name="car_name[]" class="bansang_ipt ver2" placeholder="차량번호를 입력하세요." value="<?php echo $car_row['car_name']; ?>">
                                </div>
                                <?php if($i==0){?>
                                <?php }else{ ?>
                                <div class="dong_del_box">
                                    <input type="checkbox" name="car_del[<?php echo $i; ?>]" id="car_del<?php echo $i + 1; ?>" value="1">
                                    <label for="car_del<?php echo $i + 1; ?>">삭제</label>
                                </div>
                                <?php }?>
                            </div>
                            <?php }?>
                            <?php for($i=0;$i<$car_totals;$i++){?>
                            <div class="car_list_inner">
                                <input type="hidden" name="car_id[]" value="<?php echo $car_row['car_id']; ?>">
                                <div class="car_list_box">
                                    <input type="text" name="car_type[]" class="bansang_ipt ver2" placeholder="차종을 입력하세요.">
                                </div>
                                <div class="car_list_box">
                                    <input type="text" name="car_name[]" class="bansang_ipt ver2" placeholder="차량번호를 입력하세요.">
                                </div>
                            </div>
                            <?php }?>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm">방문차량 정보</h2>
        <div class="sub_table_wrap">
        
        </div>
    </div>
    <div class="btn_fixed_top">
        <a href="./car_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <input type="submit" value="저장" class="btn_submit btn btn_02" accesskey='s'>
    </div>
</form>

<script>
let nowYear = "<?php echo $year != '' ? $year : date("Y");?>";
let nowMonth = "<?php echo $month != '' ? $month : date("m");?>";

visit_car_list(nowYear, nowMonth, "");

function visit_car_list(year, month, page = '1'){

    if(page == "") page = "<?php echo $page;?>";

    $.ajax({
        type: "POST",
        url: "./car_form_visit_ajax.php",
        data: {toYear:year, toMonth:month, ho_id:"<?php echo $ho_id; ?>", page:page}, 
        cache: false,
        async: true,
        contentType : "application/x-www-form-urlencoded; charset=UTF-8",
        success: function(data) {
            $(".sub_table_wrap").empty().append(data);
        }
    });
}

function fcar_submit(f) {
    

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

