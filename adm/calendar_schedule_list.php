<?php
require_once './_common.php';

$sql_common = " from a_calendar ";

$year            = ( $toYear )? $toYear : date( "Y" );
$month            = ( $toMonth )? $toMonth : date( "m" );

//echo sprintf('%02d', $month);

$now_month = $year.'-'.sprintf('%02d', $month);

$sql_search2 = "";

if($calcode == "schedule"){
    $sql_search = " where (1) and cal_date like '{$now_month}%' and is_del = '0' ";
}else{
    $sql_search = " where (1) and cal_date like '{$now_month}%' and cal_code = '{$calcode}' and is_del = '0' ";
}

if($selectDate != ""){

    if($calcode == "schedule"){
        $sql_search = " where (1) and is_del = '0' ";
    }else{
        $sql_search = " where (1) and cal_code = '{$calcode}' and is_del = '0' ";
    }

    $sql_search2 = " and cal_date = '{$selectDate}' ";
}


$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_search2} ORDER BY cal_date asc, cal_idx desc ";
//echo $sql.'<br>';
$row = sql_fetch($sql);
$total_count = $row['cnt'];

// $rows = $config['cf_page_rows'];
$rows = 6;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$from_record = ($page - 1) * $rows; // 시작 열을 구함



$sql = "SELECT * FROM a_calendar {$sql_search} {$sql_search2} ORDER BY cal_date asc, cal_idx desc limit {$from_record}, {$rows}";
// echo $sql;
$result = sql_query($sql);

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $sql;
?>
<?php for($i=0;$row=sql_fetch_array($result);$i++){
    
    $building_info = get_builiding_info($row['building_id']);

    $building_name = $building_info['building_name'];
?>
<div class="cal_schedule_box">
    <a href="./calendar_form.php?w=u&cal_code=<?php echo $calcode;?>&cal_idx=<?php echo $row['cal_idx']; ?>">
        <div class="cal_schedule_date_box">
            <div class="cal_schedule_date"><?php echo $row['cal_date']; ?></div>
            <?php if($row['is_process']){?>
            <div class="sm_schedule_status">처리완료</div>
            <?php }?>
        </div>
        <?php if($building_name != ''){?>
        <div class="cal_schedule_building"><?php echo $building_name; ?></div>
        <?php }?>
        <div class="cal_schedule_title"><?php echo $row['cal_title']; ?></div>
        <div class="cal_schedule_contbox">
            <div class="cal_schedule_writer">작성자 - <?php echo get_manger($row['wid'])['md_name']; ?> <?php echo get_member($row['wid'])['mb_name']; ?></div>
            <div class="cal_schedule_writer">담당자 - <?php echo get_department_name($row['mng_department']); ?>  <?php echo get_member($row['mng_id'])['mb_name']; ?></div>
        </div>

        <?php if($row['is_process']){?>
        <div class="cal_schedule_writer mgt5">처리자 - <?php echo get_manger($row['process_id'])['md_name']; ?>  <?php echo get_member($row['process_id'])['mb_name']; ?></div>
        <?php }?>
    </a>
</div>
<?php }?>
<?php if($i==0){?>
<div class="cal_schedule_empty">
    등록된 일정이 없습니다.
</div>
<?php }?>
<?php echo get_paging_ajax(5, $page, $total_page); ?>