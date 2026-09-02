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

// [2026-08] 예외 레코드 조회용 — 테이블 별칭(cal.) 없이 쓰는 cal_code 조건
$sql_code_only = ($calendar_code == "schedule") ? "" : " and cal_code = '{$calendar_code}' ";


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


// [2026-08] 아래 전체조회 쿼리 삭제
//  $sql_date / $sql_sch / $sql_limit 이 정의된 적이 없어 조건 없이 a_calendar 전체를 읽어오는 쿼리였고,
//  결과($schedule_res)는 어디에서도 쓰이지 않았다. 목록은 아래 3개 쿼리(N / MONTH / YEAR)로만 만들어진다.

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

// ★ [2026-08] 예외 레코드 날짜 목록 — 관리자웹(adm/calendar_schedule_list2.php)과 동일 규칙
//  반복 일정의 특정 날짜를 수정하거나 "이 날짜만 삭제"하면 그 날짜용 예외 레코드가 따로 생긴다.
//  (exception_idx = 원본 cal_idx, noti_repeat = 'N', 삭제인 경우 is_del = 1)
//  앱은 이 규칙을 몰라서
//   ① 수정한 반복 일정이 [원본 + 예외] 2건으로 보이고, 원본 쪽은 처리완료가 안 붙었으며
//   ② 관리자웹에서 "이 날짜만 삭제"한 일정이 앱에 그대로 남아 있었다.
//  → 해당 날짜에 예외가 있으면 원본 반복은 펼치지 않는다. (is_del 무관 — 삭제 예외도 가림막)
$exception_dates = array();
$exc_sql = "SELECT cal_idx, exception_idx, cal_date FROM a_calendar
            WHERE exception_idx IS NOT NULL AND exception_idx != '' AND exception_idx != '0' AND exception_idx != 0
              AND cal_date like '{$now_month}%' {$sql_code_only}";
$exc_res = sql_query($exc_sql);
while($exc_row = sql_fetch_array($exc_res)){
    $exception_dates[$exc_row['exception_idx'] . '_' . $exc_row['cal_date']] = true;
}



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

    $parent_start = $row_m['cal_date']; //원본 반복 시작일 (덮어쓰기 전 보존)

    $date_month = $def_date.'-'.date("d", strtotime($row_m['cal_date'])); //월간 반복이므로 일자만 고정

    //반복 시작일 이전 날짜에는 펼치지 않는다
    if($date_month < $parent_start){
        continue;
    }

    //해당 날짜에 예외 레코드가 있으면 원본은 건너뛴다 (예외가 대신 표시 / 삭제 예외면 미표시)
    if(isset($exception_dates[$row_m['cal_idx'] . '_' . $date_month])){
        continue;
    }

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

    $parent_start_y = $row_y['cal_date']; //원본 반복 시작일 (덮어쓰기 전 보존)

    $date_year = $def_year.'-'.date("m-d", strtotime($row_y['cal_date'])); //연간 반복이므로 월일자만 고정

    //해당 날짜에 예외 레코드가 있으면 원본은 건너뛴다 (예외가 대신 표시 / 삭제 예외면 미표시)
    if(isset($exception_dates[$row_y['cal_idx'] . '_' . $date_year])){
        continue;
    }

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

    //반복 시작일 이전이면 제외
    if($date_year < $parent_start_y){
        continue;
    }

    //일정에 마감날짜 있는 경우 날짜가 마감날짜보다 클경우 제외
    if($row_y['cal_edate'] != ''){
        if($date_year > $row_y['cal_edate']){
            continue;
        }
    }

    // 일정 처리 확인 — 기존에 이 조회가 빠져 있어 연간 반복은 앱에서 항상 미처리로 보였다
    $process_sql = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_calendar_process WHERE cal_idx = {$row_y['cal_idx']} and process_date = '{$row_y['cal_date']}'");

    if($process_sql['cnt'] > 0){
        $row_y['is_process'] = 1;
        $row_y['process_id'] = $process_sql['process_id'];
    }else{
        $row_y['is_process'] = 0;
        $row_y['process_id'] = '';
    }

    array_push($total_array, $row_y);
}

// ★ [2026-08] 중복 제거 — 관리자웹(adm/calendar_schedule_list2.php)과 동일 규칙
//  같은 날짜/단지/종류/제목/작성자 그룹 안에서
//   ① 예외 레코드가 대체하는 원본 반복 일정
//   ② 같은 cal_idx 가 두 번 펼쳐진 경우
//  만 제거한다. 별개 일정(cal_idx 다름)은 모두 남긴다.
$dedup_groups = array();
foreach($total_array as $item){
    $dedup_key = $item['cal_date'] . '_' . $item['building_id'] . '_' . $item['cal_code'] . '_' . $item['cal_title'] . '_' . $item['wid'];
    $dedup_groups[$dedup_key][] = $item;
}

$deduped_array = array();
foreach($dedup_groups as $items){
    if(count($items) === 1){
        $deduped_array[] = $items[0];
        continue;
    }

    $overridden = array();
    foreach($items as $it){
        $ex = isset($it['exception_idx']) ? trim((string)$it['exception_idx']) : '';
        if($ex !== '' && $ex !== '0') $overridden[$ex] = true;
    }

    $seen_idx = array();
    foreach($items as $it){
        $idx = (string)$it['cal_idx'];
        if(isset($overridden[$idx])) continue; // ① 예외로 대체된 원본
        if(isset($seen_idx[$idx]))  continue;  // ② 같은 레코드 중복 펼침
        $seen_idx[$idx] = true;
        $deduped_array[] = $it;
    }
}
$total_array = $deduped_array;


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

$limit_go = 8; // 한 페이지에 보여줄 목록 수
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