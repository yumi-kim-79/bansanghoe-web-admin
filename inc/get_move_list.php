<?php
include "../inc/_common.php";

$year            = ( $toYear )? $toYear : date( "Y" );
$month            = ( $toMonth )? $toMonth : date( "m" );

$month = str_pad($month, 2, "0", STR_PAD_LEFT);

$mng_building = get_mng_building($mb_id);
$mng_building_t = "'".implode("','", $mng_building)."'";

$sql_where = "";
if($checkDate != ""){
    $sql_where = " and move_r.mv_date = '{$checkDate}' ";

    $empty_msg = $checkDate."에 등록된 전출세대가 없습니다.";
}else{
    $sql_where = " and move_r.mv_date like '{$year}-{$month}%' ";

    $empty_msg = $year."년 ".$month."월에 등록된 전출세대가 없습니다.";

}

$move_sql = "SELECT move_r.*, building.building_name, building.is_use, dong.dong_name, ho.ho_name FROM a_move_request as move_r
             LEFT JOIN a_building as building ON move_r.building_id = building.building_id
             LEFT JOIN a_building_dong as dong ON move_r.dong_id = dong.dong_id
             LEFT JOIN a_building_ho as ho ON move_r.ho_id = ho.ho_id
             WHERE building.is_use = 1 and move_r.building_id IN ({$mng_building_t}) {$sql_where} ORDER BY move_r.mv_date asc, move_r.mv_idx desc";

// echo $move_sql;
$move_res = sql_query($move_sql);

for($i=0;$move_row = sql_fetch_array($move_res);$i++){
?>
<div class="sm_schedule_box">
    <a href="/sm_move.php?mv_idx=<?php echo $move_row['mv_idx']; ?>">
        <div class="sm_schedule_box_top">
            <div class="sm_schedule_date"><?php echo $move_row['mv_date']; ?></div>
        </div>
        <div class="sm_schedule_box_addr mgt5">
        <?php echo $move_row['building_name']; ?> <?php echo $move_row['dong_name']; ?> <?php echo $move_row['ho_name']; ?>
        </div>
        <div class="sm_schedule_box_mid mgt5">
            전출 신청 건입니다.
        </div>
    </a>
</div>
<?php }?>
<?php if($i==0){?>
<div class="complain_empty"><?php echo $empty_msg; ?></div>
<?php }?>