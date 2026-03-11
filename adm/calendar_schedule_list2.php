<?php
require_once './_common.php';

$sql_common = " from a_calendar ";

$year            = ( $toYear )? $toYear : date( "Y" );
$month            = ( $toMonth )? $toMonth : date( "m" );

//echo sprintf('%02d', $month);

$now_month = $year.'-'.sprintf('%02d', $month);

$sql_search1 = "";
$sql_search2 = "";

// 단지명 검색 필터
$building_stx = isset($building_stx) ? trim($building_stx) : '';
$building_id_filter = "";
if ($building_stx != "") {
    $b_sql = "SELECT building_id FROM a_building WHERE building_name LIKE '%" . sql_real_escape_string($building_stx) . "%' AND is_del = 0";
    $b_result = sql_query($b_sql);
    $b_ids = [];
    while ($b_row = sql_fetch_array($b_result)) {
        $b_ids[] = (int)$b_row['building_id'];
    }
    if (!empty($b_ids)) {
        $building_id_filter = " AND building_id IN (" . implode(',', $b_ids) . ") ";
    } else {
        $building_id_filter = " AND building_id = -1 ";
    }
}

if($calcode == "schedule"){
    $sql_search = " and cal_date like '{$now_month}%' ";
}else{
    $sql_search = " and cal_date like '{$now_month}%' and cal_code = '{$calcode}' ";

    $sql_search1 = " and cal_code = '{$calcode}' ";
}

if($selectDate != ""){

    if($calcode == "schedule"){
        $sql_search = " ";
    }else{
        $sql_search = " and cal_code = '{$calcode}' ";
    }

    $sql_search2 = " and cal_date = '{$selectDate}' ";
}

//반복설정 없는 일정
$sql_no = "SELECT * FROM a_calendar WHERE is_del = 0 and noti_repeat = 'N' {$sql_search} {$sql_search2} {$building_id_filter} ORDER BY cal_date asc, cal_idx desc";
$result2 = sql_query($sql_no);

$total_array = array();

while($row_n = sql_fetch_array($result2)){

    $process_sql = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_calendar_process WHERE cal_idx = {$row_n['cal_idx']} and process_date = '{$row_n['cal_date']}'");
    //print_r2($process_sql).'<br>';

    if($process_sql['cnt'] > 0){
        $row_n['is_process'] = 1;
        $row_n['process_id'] = $process_sql['process_id'];
    }else{
        $row_n['is_process'] = 0;
        $row_n['process_id'] = '';
    }

    array_push($total_array, $row_n);
}


$def_date = date("Y-m", strtotime($now_month)); //기준날짜
$end_date = date("Y-m-t", strtotime($now_month)); // 달의 마지막 날짜

//반복설정 월간인 경우
$sql_month = "SELECT * FROM a_calendar WHERE is_del = 0 and noti_repeat = 'MONTH' {$sql_search1} {$building_id_filter} ORDER BY cal_date asc, cal_idx desc";
$result_m = sql_query($sql_month);

while($row_m = sql_fetch_array($result_m)){

    //일정이 기준날짜보다 클경우 제외
    if($row_m['cal_date'] > $end_date){
        continue;
    }

    $date_month = $def_date.'-'.date("d", strtotime($row_m['cal_date'])); //월간 반복이므로 일자만 고정

    $row_m['cal_date'] = $date_month; // 날짜 변경

    //달력에서 날짜 선택한 경우
    if($selectDate != ""){

        //선택한 날짜와 다를경우 제외
        if($date_month != $selectDate){
            continue;
        }
    }

    //일정이 기준날짜보다 클경우 제외
    if($date_month > $end_date){
        continue;
    }

    //일정에 마감날짜 있는 경우 날짜가 마감날짜보다 클경우 제외
    if($row_m['cal_edate'] != ''){

        if($row_m['cal_date'] > $row_m['cal_edate']){
            continue;
        }
    }


    // 일정 처리 확인 -- 해당 일정에 해당 날짜로 처리되었는지 확인
    $process_sql = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_calendar_process WHERE cal_idx = {$row_m['cal_idx']} and process_date = '{$row_m['cal_date']}'");
    //print_r2($process_sql).'<br>';

    if($process_sql['cnt'] > 0){
        $row_m['is_process'] = 1;
        $row_m['process_id'] = $process_sql['process_id'];
    }else{
        $row_m['is_process'] = 0;
        $row_m['process_id'] = '';
    }

    array_push($total_array, $row_m);
}

// print_r2($total_array);

$def_year = date("Y", strtotime($now_month)); // 연간 기준날짜

//반복설정 연간인 경우
$sql_year = "SELECT * FROM a_calendar WHERE is_del = 0 and noti_repeat = 'YEAR' {$sql_search1} {$building_id_filter} ORDER BY cal_date asc, cal_idx desc";
$result_y = sql_query($sql_year);



$start_date = date("Y-m-01", strtotime($now_month)); // 연간 기준날짜

// echo $start_date.'<br>';

while($row_y = sql_fetch_array($result_y)){

    
    $date_year = $def_year.'-'.date("m-d", strtotime($row_y['cal_date'])); //연간 반복이므로 월일자만 고정

    $row_y['cal_date'] = $date_year;

    //달력에서 날짜 선택한 경우
    if($selectDate != ""){

        //선택한 날짜와 다를경우 제외
        if($date_year != $selectDate){
            continue;
        }
    }

    // echo $date_year.'<br>';
    //일정이 시작날짜보다 작을경우 제외
    if($date_year < $start_date){
        continue;
    }

    //일정이 종료날짜보다 클경우 제외
    if($date_year > $end_date){
        continue;
    }

    array_push($total_array, $row_y);
}

//날짜순서, 인덱스에 맞게 다시 정렬
usort($total_array, function($a, $b) {
    $dateCompare = strcmp($a['cal_date'], $b['cal_date']); // 날짜 오름차순
    if ($dateCompare === 0) {
        return $b['cal_idx'] <=> $a['cal_idx']; // 같은 날짜면 cal_idx 내림차순
    }
    return $dateCompare;
});

if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}

$limit_go = 6; // 한 페이지에 보여줄 목록 수
$total = count($total_array); // 전체 게시물 수

$total_pages = ceil($total / $limit_go); // 전체 페이지 수

$offset = ($page - 1) * $limit_go; // 시작 위치

//페이징 처리
$pageData = array_slice($total_array, $offset, $limit_go);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $sql_no.'<br>';
    // echo $sql_month.'<br>';
    // echo $sql_year.'<br>';
} 

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $sql;
?>
<?php foreach ($pageData as $row) {
    
    $building_info = get_builiding_info($row['building_id']);

    $building_name = $building_info['building_name'];    
?>
<div class="cal_schedule_box">
    <a href="./calendar_form2.php?w=u&cal_code=<?php echo $calcode;?>&cal_idx=<?php echo $row['cal_idx']; ?>&cal_date_def=<?php echo $row['cal_date']; ?>">
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
<?php if(count($pageData)==0){?>
<div class="cal_schedule_empty">
    등록된 일정이 없습니다.
</div>
<?php }?>
<?php echo get_paging_ajax(5, $page, $total_pages); ?>
