<?php
require_once "./_common.php";

if($code == "car"){
    $sql_sch = "";

    if($sch_text != ""){
        if($sch_type == "car_name"){
            $sql_sch = " and car.car_name like '%{$sch_text}%' ";
        }else{
            $sql_sch = " and car.car_type like '%{$sch_text}%' ";
        }
    }


    $car_sql = "SELECT car.*, dong.dong_name, ho.ho_name FROM a_building_car as car
                LEFT JOIN a_building_dong as dong on car.dong_id = dong.dong_id
                LEFT JOIN a_building_ho as ho on car.ho_id = ho.ho_id
                WHERE car.is_del = 0 and car.building_id = '{$building_id}' and car_type != '' and car_name != '' {$sql_sch} ORDER BY dong.dong_name + 1 asc, ho.ho_name + 1 asc, car.car_id asc";
    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) {
        // echo $car_sql;
    }
    $car_res = sql_query($car_sql);
}else{

    $dates = date("Y-m-d");

    $sql_sch = "";
    $sql_sch2 = "";

    if($sch_text != ""){
        if($sch_type == "car_name"){
            $sql_sch2 = " and vs_car.visit_car_number like '%{$sch_text}%' ";
        }else{
            $sql_sch2 = " and vs_car.visit_car_name like '%{$sch_text}%' ";
        }
    }

    if($sch_date != ""){
        $sql_sch .= " and vs_car.visit_date = '{$sch_date}' ";
    }


    $car_sql = "SELECT vs_car.*, dong.dong_name, ho.ho_name FROM a_building_visit_car as vs_car
                LEFT JOIN a_building_dong as dong on vs_car.dong_id = dong.dong_id
                LEFT JOIN a_building_ho as ho on vs_car.ho_id = ho.ho_id
                WHERE vs_car.building_id = '{$building_id}' {$sql_sch} {$sql_sch2} and vs_car.is_del = 0
                ORDER BY vs_car.car_id asc";
    $car_res = sql_query($car_sql);
}

//echo $car_sql.'<br>';
?>
<?php if($code == "car"){?>
<?php for($i=0;$car_row = sql_fetch_array($car_res);$i++){
    
    // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) print_r2($car_row);
    ?>
<li>
    <a href="javascript:;">
        <div class="hh_left">
            <div class="hh_left_dong"><?php echo $car_row['dong_name']; ?>동</div>
            <div class="hh_left_ho"><?php echo $car_row['ho_name']; ?>호</div>
        </div>
        <div class="hh_right"><?php echo $car_row['car_type']; ?> <?php echo $car_row['car_name']; ?></div>
    </a>
</li>
<?php }?>
<?php if($i==0){?>
<li class="empty_li">등록된 차량정보가 없습니다.</li>
<?php }?>
<?php }else{ ?>
    <?php for($i=0;$car_row = sql_fetch_array($car_res);$i++){
        
        $visit_days = $car_row['visit_day'] - 1;
        $endDay = date('Y.m.d', strtotime($car_row['visit_date'].'+'.$visit_days.'day'));
    ?>
    <div class="mng_cont_box_wrap">
        <div class="mng_cont_box">
            <div class="mng_cont_label">방문 동/호수</div>
            <div class="mng_cont_infos"><?php echo $car_row['dong_name']; ?>동 <?php echo $car_row['ho_name']; ?>호</div>
        </div>
        <div class="mng_cont_box">
            <div class="mng_cont_label">차량번호</div>
            <div class="mng_cont_infos"><?php echo $car_row['visit_car_name']; ?> <?php echo $car_row['visit_car_number']; ?></div>
        </div>
        <div class="mng_cont_box tel">
            <div class="mng_cont_label">방문자 연락처</div>
            <div class="mng_cont_infos tel">
                <?php echo $car_row['visit_hp'];?>
                <a href="tel:<?php echo $car_row['visit_hp'];?>" class="tel_btn"><i><img src="/images/phone_icons.svg" alt=""></i>전화걸기</a>
            </div>
        </div>
        <div class="mng_cont_box">
            <div class="mng_cont_label">방문기간</div>
            <div class="mng_cont_infos"><?php echo date("Y.m.d", strtotime($car_row['visit_date'])); ?> ~ <?php echo $endDay; ?></div>
        </div>
        <div class="mng_cont_box">
            <div class="mng_cont_label">출차</div>
            <?php if($car_row['out_status'] == 'N'){?>
            <div class="mng_cont_infos"><?php echo $car_row['visit_date'] < $dates ? date("Y.m.d", strtotime($car_row['visit_date'])) : '-' ?></div>
            <?php }else{ ?>
                <div class="mng_cont_infos"><?php echo date("Y.m.d H:i", strtotime($car_row['out_at'])); ?></div>
            <?php }?>
        </div>
    </div>
    <?php }?>
    <?php if($i==0){?>
    <div class="mng_cont_empty">등록된 방문차량 정보가 없습니다.</div>
    <?php }?>
<?php }?>