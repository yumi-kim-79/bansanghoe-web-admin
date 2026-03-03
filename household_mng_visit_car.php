<?php
require_once './_common.php';

$year   = ( $toYear )? $toYear : date( "Y" );
$month  = ( $toMonth )? $toMonth : date( "m" );

// 이전, 다음 만들기
$prevYear        = ( $month == 1 )? ( $year - 1 ) : $year;
$prevMonth       = ( $month == 1 )? 12 : ( $month - 1 );
$nextYear        = ( $month == 12 )? ( $year + 1 ) : $year;
$nextMonth       = ( $month == 12 )? 1 : ( $month + 1 );

$date = $toYear.'-'.$toMonth;

if($prevMonth < 10){
    $prevMonth = "0".$prevMonth;
}else{
    $prevMonth = $prevMonth;
}

if($nextMonth < 10){
    $nextMonth = "0".$nextMonth;
}else{
    $nextMonth = $nextMonth;
}

$visit_car_sql = "SELECT * FROM a_building_visit_car WHERE ho_id = '{$ho_id}' and visit_date like '{$date}%' ORDER BY car_id asc";
if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
// echo $visit_car_sql;
}
$visit_car_res = sql_query($visit_car_sql);
?>
<div class="cal_header_wrap mgt20">
    <section class="cal_header">
        <button type="button" onClick="moveCal_vc('<?php echo $prevYear?>', '<?php echo $prevMonth?>');">
            <img src="/images/icon_cal_prev.svg" alt="">
        </button>
        <p><?php echo $year; ?>년 <?php echo $month; ?>월</p>
        <button type="button" onClick="moveCal_vc('<?php echo $nextYear?>', '<?php echo $nextMonth?>');">
            <img src="/images/icon_cal_next.svg" alt="">
        </button>
    </section>
</div>
<div class="car_visit_pop_wrap mgt20">
<?php
for($i=0;$visit_car_row = sql_fetch_array($visit_car_res);$i++){
    $dates = date("Y-m-d");
    //echo $visit_car_row['visit_date'].'<Br>';
    //echo $visit_car_row['out_status'].'<br>';
    //echo $visit_car_row['visit_date'] > $dates ? '1' : '0';
    $visit_days = $visit_car_row['visit_day'] - 1;
    $endDay = date('Y.m.d', strtotime($visit_car_row['visit_date'].'+'.$visit_days.'day'));
?>
<div class="car_visit_pop_box">
    <div class="car_visit_infos">
        <div class="car_visit_info_box">
            <div class="car_visit_info_label">차량번호</div>
            <div class="car_visit_info_text"><?php echo $visit_car_row['visit_car_name'].' '.$visit_car_row['visit_car_number']; ?></div>
        </div>
        <div class="car_visit_info_box">
            <div class="car_visit_info_label">방문자 연락처</div>
            <div class="car_visit_info_text">
                <a href="tel:<?php echo $visit_car_row['visit_hp']; ?>">
                    <img src="/images/phone_icons_b.svg" alt="">
                    <span><?php echo $visit_car_row['visit_hp']; ?></span>
                </a>
            </div>
        </div>
        <div class="car_visit_info_box">
            <div class="car_visit_info_label">방문기간</div>
            <div class="car_visit_info_text"><?php echo $visit_car_row['visit_date']; ?> ~ <?php echo $endDay; ?></div>
        </div>
    </div>
    <?php if($visit_car_row['out_status'] == 'N' && $visit_car_row['visit_date'] < $dates){?>
        <div class="car_vist_pop_dates">출차 : <?php echo date("Y.m.d", strtotime($visit_car_row['visit_date'])); ?></div>
    <?php }else{ ?>
        <div class="car_vist_pop_dates">출차 : <?php echo date("Y.m.d H:i", strtotime($visit_car_row['out_at'])); ?></div>
    <?php }?>
   
</div>
<?php }?>
<?php if($i==0){?>
<div class="car_visit_empty">방문차량 정보가 없습니다.</div>
<?php }?>
</div>