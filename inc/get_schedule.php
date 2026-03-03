<?php
include "../inc/_common.php";

$noti_page      = $page != '' ? $page : '1';
$year           = ( $toYear )? $toYear : date( "Y" );
$month          = ( $toMonth )? $toMonth : date( "m" );

$month = str_pad($month, 2, "0", STR_PAD_LEFT);

$sql_sch = "";
if($calendar_code != "schedule"){
    $sql_sch = " and cal.cal_code = '{$calendar_code}' ";
}

if($sch_text != ""){
    $sql_sch .= " and building.building_name like '%{$sch_text}%' ";
    // $sql_sch .= " and mng.mng_name like '%{$sch_text}%' ";
}

$sql_date = "";
if($checkDate != ""){
    $sql_date = " and cal.cal_date = '{$checkDate}' ";

    $empty_msg = $checkDate."에 등록된 스케줄이 없습니다.";
}else{
    $sql_date = " and cal.cal_date like '{$year}-{$month}%' ";

    $empty_msg = $year."년 ".$month."월에 등록된 스케줄이 없습니다.";
}


$sql_schedules = " SELECT COUNT(*) as cnt FROM a_calendar as cal
                    LEFT JOIN a_mng as mng on cal.mng_id = mng.mng_id
                    LEFT JOIN a_building as building ON cal.building_id = building.building_id
                    WHERE cal.is_del = 0 {$sql_date} {$sql_sch} ORDER BY cal.cal_date asc, cal.cal_idx desc ";
// echo $sql_push.'<br>';
// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $sql_schedules.'<br>';
$row_scheules = sql_fetch($sql_schedules);
$total_count = $row_scheules['cnt'];


// $noti_rows = $config['cf_page_rows'];
$noti_rows = 5;
$noti_total_page  = ceil($total_count / $noti_rows);  // 전체 페이지 계산
if ($noti_page < 1) {
    $noti_page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$noti_from_record = ($noti_page - 1) * $noti_rows; // 시작 열을 구함


// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $noti_page.'<br>';

//$sql_limit = "";

$sql_limit = "limit {$noti_from_record}, {$noti_rows}";


$schedule_sql = "SELECT cal.*, mng.mng_name, building.building_name FROM a_calendar as cal
                 LEFT JOIN a_mng as mng on cal.mng_id = mng.mng_id
                 LEFT JOIN a_building as building ON cal.building_id = building.building_id
                 WHERE cal.is_del = 0 {$sql_date} {$sql_sch} ORDER BY cal.cal_date asc, cal.cal_idx desc {$sql_limit}";
// echo $schedule_sql;
// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $schedule_sql.'<br>';

$schedule_res = sql_query($schedule_sql);

for($i=0;$schedule_row = sql_fetch_array($schedule_res);$i++){

    $mng_infos = get_manger($schedule_row['mng_id']);
    $writer = "";

    // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $schedule_row['mng_id'].'<br>';

    if($schedule_row['wid'] != "admin"){
        $writer_infos = get_manger($schedule_row['wid']);

        // print_r2($writer_infos);

        $writer = $writer_infos['md_name'].' '.$writer_infos['mng_name'];
    }else{
        $writer = "신반상회";
    }
?>
<div class="sm_schedule_box">
    <a href="/schedule_add.php?w=i&cal_idx=<?php echo $schedule_row['cal_idx']; ?>&cal_code=<?php echo $schedule_row['cal_code']; ?>">
        <div class="sm_schedule_box_top">
            <div class="sm_schedule_date"><?php echo $schedule_row['cal_date']; ?></div>
            <div class="sm_schedule_status"><?php echo $schedule_row['is_process'] ? '처리완료' : ''?></div>
        </div>
        <div class="sm_schedule_box_tit mgt5 mgb5">
            <?php echo $schedule_row['building_name']?>
        </div>
        <div class="sm_schedule_box_mid mgt5 mgb5">
            <?php echo $schedule_row['cal_title']?>
        </div>
        <div class="sm_schedule_box_bot">
            <div class="sm_schedule_box_bot2">
                <div class="sm_sche_bot_cont">작성자: <?php echo $writer; ?></div>
                <div class="sm_sche_bot_cont">담당자: 
                    <!-- <?php echo $schedule_row['mng_department'] == '-1' ? '전체' : $mng_infos['md_name'].' '.$mng_infos['mng_name'];?> -->
                    <?php echo get_department_name($schedule_row['mng_department']); ?>
                    <?php echo $mng_infos['mng_name']; ?>
                </div>
            </div>
            <?php if($schedule_row['is_process']){
                $process_name = get_manger($schedule_row['process_id']);
            ?>
            <div class="sm_schedule_box_bot2">
                <div class="sm_sche_bot_cont">처리자: <?php echo $process_name['md_name']; ?> <?php echo $process_name['mng_name']; ?></div>
            </div>
            <?php }?>
        </div>
    </a>
</div>

<?php }?>
<?php if($i==0){?>
<div class="complain_empty"><?php echo $empty_msg; ?></div>
<?php }?>
<?php echo get_paging_ajax(5, $noti_page, $noti_total_page); ?>