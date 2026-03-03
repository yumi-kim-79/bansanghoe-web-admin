<?php
include "../inc/_common.php";

$noti_page      = $page != '' ? $page : '1';
$year           = ( $toYear )? $toYear : date( "Y" );
$month          = ( $toMonth )? $toMonth : date( "m" );

$month = str_pad($month, 2, "0", STR_PAD_LEFT);


///
$now_month = $year.'-'.sprintf('%02d', $month);

$sql_search1 = "";
$sql_search2 = "";


if($calendar_code == ""){
    $calendar_code = "schedule";
}

if($calendar_code == "schedule"){
    $sql_search = " and cal.cal_date like '{$now_month}%' ";
}else{
    $sql_search = " and cal.cal_date like '{$now_month}%' and cal.cal_code = '{$calendar_code}' ";

    $sql_search1 = " and cal.cal_code = '{$calendar_code}' ";
}

if($checkDate != ""){

    if($calendar_code == "schedule"){
        $sql_search = " ";
    }else{
        $sql_search = " and cal.cal_code = '{$calendar_code}' ";
    }

    $sql_search2 = " and cal_date = '{$checkDate}' ";
}

if($sch_text != ""){
    $sql_search .= " and building.building_name like '%{$sch_text}%' ";
    $sql_search1 .= " and building.building_name like '%{$sch_text}%' ";
    // $sql_sch .= " and mng.mng_name like '%{$sch_text}%' ";
}


$schedule_sql = "SELECT cal.*, mng.mng_name, building.building_name FROM a_calendar as cal
                 LEFT JOIN a_mng as mng on cal.mng_id = mng.mng_id
                 LEFT JOIN a_building as building ON cal.building_id = building.building_id
                 WHERE cal.is_del = 0 {$sql_date} {$sql_sch} ORDER BY cal.cal_date asc, cal.cal_idx desc {$sql_limit}";
// echo $schedule_sql;
// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $schedule_sql.'<br>';

$schedule_res = sql_query($schedule_sql);

//반복설정 없는 일정
$sql_no = "SELECT cal.*, building.building_name FROM a_calendar as cal
           LEFT JOIN a_building as building ON cal.building_id = building.building_id
           WHERE cal.is_del = 0 and cal.noti_repeat = 'N' {$sql_search} {$sql_search2} ORDER BY cal.cal_date asc, cal.cal_idx desc";
// echo $sql_no.'<br>';
// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $sql_no.'<br>';
$result2 = sql_query($sql_no);

$total_array = array();

while($row_n = sql_fetch_array($result2)){

    $process_sql = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_calendar_process WHERE cal_idx = {$row_n['cal_idx']} and process_date = '{$row_n['cal_date']}'");

    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        // echo "SELECT *, COUNT(*) as cnt FROM a_calendar_process WHERE cal_idx = {$row_n['cal_idx']} and process_date = '{$row_n['cal_date']}'"."<br>";
    }
    //print_r2($process_sql).'<br>';
   

    if($process_sql['cnt'] > 0){
        $row_n['is_process'] = 1;
        $row_n['process_id'] = $process_sql['process_id'];

        // echo "SELECT *, COUNT(*) as cnt FROM a_calendar_process WHERE cal_idx = {$row_m['cal_idx']} and process_date = '{$row_m['cal_date']}'"."<br>";
    }else{
        $row_n['is_process'] = 0;
        $row_n['process_id'] = '';
    }

    array_push($total_array, $row_n);
}

$def_date = date("Y-m", strtotime($now_month)); //기준날짜
$end_date = date("Y-m-t", strtotime($now_month)); // 달의 마지막 날짜


//반복설정 월간인 경우
$sql_month = "SELECT cal.*, building.building_name FROM a_calendar as cal
              LEFT JOIN a_building as building ON cal.building_id = building.building_id
              WHERE cal.is_del = 0 and cal.noti_repeat = 'MONTH' {$sql_search1} ORDER BY cal.cal_date asc, cal.cal_idx desc";
$result_m = sql_query($sql_month);

while($row_m = sql_fetch_array($result_m)){
    //일정이 기준날짜보다 클경우 제외
    if($row_m['cal_date'] > $end_date){
        continue;
    }

    $date_month = $def_date.'-'.date("d", strtotime($row_m['cal_date'])); //월간 반복이므로 일자만 고정

    $row_m['cal_date'] = $date_month; // 날짜 변경

    //달력에서 날짜 선택한 경우
    if($checkDate != ""){

        //선택한 날짜와 다를경우 제외
        if($date_month != $checkDate){
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

    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        // echo "SELECT *, COUNT(*) as cnt FROM a_calendar_process WHERE cal_idx = {$row_m['cal_idx']} and process_date = '{$row_m['cal_date']}'"."<br>";
    }
    //print_r2($process_sql).'<br>';
   

    if($process_sql['cnt'] > 0){
        $row_m['is_process'] = 1;
        $row_m['process_id'] = $process_sql['process_id'];

        // echo "SELECT *, COUNT(*) as cnt FROM a_calendar_process WHERE cal_idx = {$row_m['cal_idx']} and process_date = '{$row_m['cal_date']}'"."<br>";
    }else{
        $row_m['is_process'] = 0;
        $row_m['process_id'] = '';
    }

    array_push($total_array, $row_m);
}

$def_year = date("Y", strtotime($now_month)); // 연간 기준날짜

//반복설정 연간인 경우
$sql_year = "SELECT cal.*, building.building_name FROM a_calendar as cal
             LEFT JOIN a_building as building ON cal.building_id = building.building_id
             WHERE cal.is_del = 0 and cal.noti_repeat = 'YEAR' {$sql_search1} ORDER BY cal.cal_date asc, cal.cal_idx desc";
$result_y = sql_query($sql_year);

$start_date = date("Y-m-01", strtotime($now_month)); // 연간 기준날짜


while($row_y = sql_fetch_array($result_y)){

    
    $date_year = $def_year.'-'.date("m-d", strtotime($row_y['cal_date'])); //연간 반복이므로 월일자만 고정

    $row_y['cal_date'] = $date_year;

    //달력에서 날짜 선택한 경우
    if($checkDate != ""){

        //선택한 날짜와 다를경우 제외
        if($date_year != $checkDate){
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

$limit_go = 5; // 한 페이지에 보여줄 목록 수
$total = count($total_array); // 전체 게시물 수

$total_pages = ceil($total / $limit_go); // 전체 페이지 수

$offset = ($page - 1) * $limit_go; // 시작 위치

//페이징 처리
$pageData = array_slice($total_array, $offset, $limit_go);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $sql_no.'<br>';
    // echo $sql_month.'<br>';
    // echo $sql_year.'<br>';

    // print_r2($total_array);
    // echo $offset.'<br>';
    // print_r2($pageData);
} 
// print_r2($total_array);
// echo $sql_month.'<br>';
// echo $end_date.'<br>';
?>
<?php foreach ($pageData as $schedule_row) {
    
    $building_info = get_builiding_info($schedule_row['building_id']);

    $building_name = $building_info['building_name'];    
?>
<div class="sm_schedule_box">
    <a href="/schedule_add2.php?w=u&cal_idx=<?php echo $schedule_row['cal_idx']; ?>&cal_code=<?php echo $schedule_row['cal_code']; ?>&cal_date_def=<?php echo $schedule_row['cal_date']; ?>">
        <div class="sm_schedule_box_top">
            <div class="sm_schedule_date"><?php echo $schedule_row['cal_date']; ?></div>
            <?php if($schedule_row['is_process']){ ?>
            <div class="sm_schedule_status">처리완료</div>
            <?php }?>
        </div>
        <div class="sm_schedule_box_tit mgt5 mgb5">
            <?php echo $building_name; ?>
        </div>
        <div class="sm_schedule_box_mid mgt5 mgb5">
            <?php echo $schedule_row['cal_title']?>
        </div>
        <div class="sm_schedule_box_bot">
            <div class="sm_schedule_box_bot2">
                <div class="sm_sche_bot_cont">작성자: <?php echo get_manger($schedule_row['wid'])['md_name']; ?> <?php echo get_member($schedule_row['wid'])['mb_name']; ?></div>
                <div class="sm_sche_bot_cont">담당자: 
                    <?php echo get_department_name($schedule_row['mng_department']); ?> 
                     <?php echo get_member($schedule_row['mng_id'])['mb_name']; ?>
                </div>
            </div>
            <?php if($schedule_row['is_process']){
                $process_name = get_manger($schedule_row['process_id']);
            ?>
            <div class="sm_schedule_box_bot2">
                <div class="sm_sche_bot_cont">
                    처리자: 
                    <?php echo get_manger($schedule_row['process_id'])['md_name']; ?>  <?php echo get_member($schedule_row['process_id'])['mb_name']; ?>
                </div>
            </div>
            <?php }?>
        </div>
    </a>
</div>
<?php }?>
<?php if(count($pageData)==0){?>
<div class="complain_empty">
    등록된 일정이 없습니다.
</div>
<?php }?>
<?php echo get_paging_ajax(5, $page, $total_pages); ?>