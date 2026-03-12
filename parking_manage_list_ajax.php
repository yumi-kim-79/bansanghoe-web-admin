<?php
require_once "./_common.php";

if($code == "in"){

    $sql_where_sch = "";
    if($schText != ""){
        $sql_where_sch = " and (car_type like '%{$schText}%' or car_name like '%{$schText}%') ";
    }

    $sql_car = "SELECT car.*, dong.dong_name, ho.ho_name FROM a_building_car as car
                LEFT JOIN a_building_dong as dong on car.dong_id = dong.dong_id
                LEFT JOIN a_building_ho as ho on car.ho_id = ho.ho_id
                WHERE car.is_del = 0 and car.building_id = '{$building_id}' and car.car_name != '' and car.car_type != ''
                {$sql_where_sch}
                ORDER BY ho_id asc, car_id asc";
    $res_car = sql_query($sql_car);
}else{

    $sql_where = "";
    if($code == "my_visit"){
        $sql_where = " and vs_car.mb_id = '{$mb_id}' ";
    }

    $sql_where_sch = "";
    if($schText != ""){
        $sql_where_sch = " and (vs_car.visit_car_name like '%{$schText}%' or vs_car.visit_car_number like '%{$schText}%') ";
    }

    $order_by = "ORDER BY vs_car.visit_date desc, vs_car.ho_id, vs_car.car_id asc";

    $sql_car = "SELECT vs_car.*, dong.dong_name, ho.ho_name FROM a_building_visit_car as vs_car
                LEFT JOIN a_building_dong as dong on vs_car.dong_id = dong.dong_id
                LEFT JOIN a_building_ho as ho on vs_car.ho_id = ho.ho_id
                WHERE vs_car.is_del = 0 and vs_car.building_id = '{$building_id}'
                {$sql_where}
                {$sql_where_sch}
                {$order_by}";

    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        echo $sql_car.'<br>';
    }
    $res_car = sql_query($sql_car);
}

// ── 차량번호 마스킹 함수: 4자리 숫자 앞 2자리를 ** 로 교체 → **62 ──────────
function maskCarNumber($carNumber) {
    return preg_replace('/(\d{2})(\d{2})/', '**$2', $carNumber);
}

for($i=0;$row_car = sql_fetch_array($res_car);$i++){

    $dates = date("Y-m-d");

    // 입주민 탭: 차량번호 마스킹
    $car_name_masked = $code == "in" ? maskCarNumber($row_car['car_name']) : maskCarNumber($row_car['visit_car_number']);
    $car_type        = $code == "in" ? $row_car['car_type'] : $row_car['visit_car_name'];
    $car_number_raw  = $code == "in" ? $row_car['car_name'] : $row_car['visit_car_number'];
    $ho_id_val       = $row_car['ho_id'];
?>
<div class="car_boxs_wrap">
    <div class="car_boxs">
        <div class="car_boxs_l"><?php echo $code != "in" ? "방문 " : ""; ?>동/호수</div>
        <div class="car_boxs_r car_boxs_r_unit">
            <?php echo $row_car['dong_name']; ?>동 <?php echo $row_car['ho_name']; ?>호
            <?php if($code == "in"){?>
                <!-- ★ 입주민 뱃지 -->
                <span class="resident_badge">등록된 입주민 차량입니다.</span>
            <?php }?>
        </div>
    </div>
    <div class="car_boxs">
        <div class="car_boxs_l">차량정보</div>
        <!-- ★ 차량번호 마스킹 적용 -->
        <div class="car_boxs_r">차종 : <?php echo $car_type; ?> / 번호 : <?php echo $car_name_masked; ?></div>
    </div>
    <?php if($code != "in"){
        $visit_days = $row_car['visit_day'] - 1;
        $endDay     = date('Y.m.d', strtotime($row_car['visit_date'].'+'.$visit_days.'day'));
        $endDay_ft  = date('Y-m-d', strtotime($row_car['visit_date'].'+'.$visit_days.'day'));
    ?>
    <div class="car_boxs">
        <div class="car_boxs_l">방문기간</div>
        <div class="car_boxs_r"><?php echo date("Y.m.d", strtotime($row_car['visit_date'])); ?> ~ <?php echo $endDay; ?></div>
    </div>
    <?php if($row_car['out_status'] == 'N' && $endDay_ft >= $dates){?>
        <?php if($row_car['mb_id'] == $mb_id){?>
        <div class="car_boxs">
            <button type="button" onclick="visit_car_out_pop('<?php echo $row_car['car_id']; ?>');">출차</button>
        </div>
        <div class="car_boxs">
            <button type="button" onclick="visit_car_update_pop('<?php echo $row_car['car_id']; ?>')" class="ver2">수정하기</button>
        </div>
        <?php }?>
    <?php }else{ ?>
    <div class="car_boxs">
        <button type="button" class="disabled" disabled>출차 : <?php echo $row_car['out_status'] == 'N' ? date("Y.m.d", strtotime($row_car['visit_date'])) : date("Y.m.d H:i", strtotime($row_car['out_at'])); ?></button>
    </div>
    <?php }?>
    <?php }?>

    <?php if($code == "in"){?>
    <!-- ★ 이동주차 요청 버튼 (내 호실 제외) -->
    <?php if($row_car['ho_id'] != $user_building['ho_id']){?>
    <div class="car_boxs">
        <button type="button"
            class="move_request_btn"
            onclick="moveRequestHandler('<?php echo $ho_id_val; ?>', '<?php echo $car_number_raw; ?>');">
            🚗 이동 주차 요청
        </button>
    </div>
    <?php }?>
    <?php }?>
</div>
<?php }?>
<?php if($i == 0){?>
    <div class="faq_empty_box">
        <?php echo $code != "in" ? "방문 " : ""; ?>등록된 차량이 없습니다.
    </div>
<?php }?>

<style>
/* ── 입주민 뱃지 ── */
.car_boxs_r_unit {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
}
.resident_badge {
    display: inline-block;
    background-color: #EAF4FF;
    color: #1A87D0;
    border: 1px solid #1A87D0;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 10px;
}

/* ── 이동주차 요청 버튼 ── */
.move_request_btn {
    width: 100%;
    padding: 10px 0;
    background-color: #FFF5F5;
    color: #E53935;
    border: 1.5px solid #E53935;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}
.move_request_btn:active {
    background-color: #FFEBEE;
}
</style>

<script>
// ── 이동주차 요청 확인 팝업 → parking_move_request_ajax.php 호출 ──
function moveRequestHandler(hoId, carNumber) {
    if (!confirm('이동 주차를 요청하시겠습니까?\n차량번호: ' + carNumber)) return;

    $.ajax({
        type: 'POST',
        url: '/parking_move_request_ajax.php',
        data: {
            target_ho_id:    hoId,
            car_number:      carNumber,
            requester_ho_id: '<?php echo $user_building['ho_id']; ?>',
        },
        dataType: 'json',
        success: function(data) {
            showToast(data.msg);
        },
        error: function() {
            showToast('요청 처리 중 오류가 발생했습니다.');
        }
    });
}
</script>