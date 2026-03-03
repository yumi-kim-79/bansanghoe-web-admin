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
    // echo $sql_car;
    $res_car = sql_query($sql_car);
}else{
    
    $sql_where = "";
    if($code == "my_visit"){
        //and vs_car.created_at >= '{$ho_tenant_at_de}'
        $sql_where = " and vs_car.mb_id = '{$mb_id}' ";
    }

    $sql_where_sch = "";
    if($schText != ""){
        $sql_where_sch = " and (vs_car.visit_car_name like '%{$schText}%' or vs_car.visit_car_number like '%{$schText}%') ";
    }


    // $order_by = "ORDER BY vs_car.ho_id asc, vs_car.car_id asc";
    $order_by = "ORDER BY vs_car.visit_date desc, vs_car.ho_id, vs_car.car_id asc"; //251202 방문일 기준정렬로 변경

    $sql_car = "SELECT vs_car.*, dong.dong_name, ho.ho_name FROM a_building_visit_car as vs_car
                LEFT JOIN a_building_dong as dong on vs_car.dong_id = dong.dong_id
                LEFT JOIN a_building_ho as ho on vs_car.ho_id = ho.ho_id
                WHERE vs_car.is_del = 0 and vs_car.building_id = '{$building_id}'
                {$sql_where}
                {$sql_where_sch}
                {$order_by}";
    // echo $sql_car;

    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        echo $sql_car.'<br>';
    }
    $res_car = sql_query($sql_car);
}

for($i=0;$row_car = sql_fetch_array($res_car);$i++){

    $dates = date("Y-m-d");
    
    //echo $row_car['visit_date'].'<br>';
    //echo $dates;
?>
<div class="car_boxs_wrap">
    <div class="car_boxs">
        <div class="car_boxs_l"><?php echo $code != "in" ? "방문 " : ""; ?>동/호수</div>
        <div class="car_boxs_r"><?php echo $row_car['dong_name']; ?>동 <?php echo $row_car['ho_name']; ?>호</div>
    </div>
    <div class="car_boxs">
        <div class="car_boxs_l">차량정보</div>
        <div class="car_boxs_r"><?php echo $code == "in" ? '차종 : '.$row_car['car_type'] : '차종 : '.$row_car['visit_car_name']; ?> <?php echo $code == "in" ? ' / 번호 : '.$row_car['car_name'] : ' / 번호 : '.$row_car['visit_car_number']; ?></div>
    </div>
    <?php if($code != "in"){
    $visit_days = $row_car['visit_day'] - 1;
    $endDay = date('Y.m.d', strtotime($row_car['visit_date'].'+'.$visit_days.'day'));
    $endDay_ft = date('Y-m-d', strtotime($row_car['visit_date'].'+'.$visit_days.'day'));
    ?>
    <div class="car_boxs">
        <div class="car_boxs_l">방문기간</div>
        <div class="car_boxs_r"><?php echo date("Y.m.d", strtotime($row_car['visit_date'])); ?> ~ <?php echo $endDay; ?></div>
    </div>
    <?php
    
    //echo $row_car['out_status'].'<br>';
    //echo $endDay_ft < $dates ? '1' : 0;
    ?>
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
</div>
<?php }?>
<?php if($i == 0){?>
    <div class="faq_empty_box">
        <?php echo $code != "in" ? "방문 " : ""; ?>등록된 차량이 없습니다.
    </div>
<?php }?>