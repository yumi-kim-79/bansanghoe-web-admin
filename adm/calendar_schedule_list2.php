<?php
require_once './_common.php';

$sql_common = " from a_calendar ";

$year            = ( $toYear )? $toYear : date( "Y" );

// 연간 모드: 월 미선택('all' 또는 빈값)이면 그 해 전체 조회 (방식 A — 우측 목록만 연간)
$is_year = ($toMonth === 'all' || $toMonth === '');
$month            = ( $toMonth && $toMonth !== 'all' )? $toMonth : date( "m" );

$now_month = $year.'-'.sprintf('%02d', $month);

// 조회 날짜 prefix: 연간이면 'YYYY', 월간이면 'YYYY-MM' (cal_date / process_date LIKE 'prefix%')
$date_like = $is_year ? "{$year}" : $now_month;

$sql_search1 = "";
$sql_search2 = "";

// 단지명 검색 필터
$building_stx = isset($_POST['building_stx']) ? trim($_POST['building_stx']) : '';
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
    $sql_search = " and cal_date like '{$date_like}%' ";
}else{
    $sql_search = " and cal_date like '{$date_like}%' and cal_code = '{$calcode}' ";

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

// 처리이력 일괄 로드 (occurrence별 개별 SELECT 제거 — 조회 방식만 변경, 판정 기준은 기존과 동일)
//  키: "{cal_idx}_{process_date}", 값: process_id. 키 존재 = 처리완료(기존 COUNT(*)>0 과 동치)
//  범위: 조회기간(date_like: 월간='YYYY-MM' / 연간='YYYY') + cal_code/building (a_calendar 조인).
$proc_map = [];
$proc_sql = "SELECT p.cal_idx, p.process_date, p.process_id
             FROM a_calendar_process AS p
             JOIN a_calendar AS c ON c.cal_idx = p.cal_idx
             WHERE p.process_date like '{$date_like}%' {$sql_search1} {$building_id_filter}";
$proc_res = sql_query($proc_sql);
while($proc_row = sql_fetch_array($proc_res)){
    $proc_map[$proc_row['cal_idx'] . '_' . $proc_row['process_date']] = $proc_row['process_id'];
}

//반복설정 없는 일정 + noti_repeat='N'인 예외 레코드
//noti_repeat='MONTH'/'YEAR'인 예외 레코드는 반복 쿼리에서 처리되므로 여기서 제외
$sql_no = "SELECT * FROM a_calendar WHERE is_del = 0 and noti_repeat = 'N' {$sql_search} {$sql_search2} {$building_id_filter} ORDER BY cal_date asc, cal_idx desc";
$result2 = sql_query($sql_no);

$total_array = array();

while($row_n = sql_fetch_array($result2)){

    $proc_key = $row_n['cal_idx'] . '_' . $row_n['cal_date'];
    if(array_key_exists($proc_key, $proc_map)){
        $row_n['is_process'] = 1;
        $row_n['process_id'] = $proc_map[$proc_key];
    }else{
        $row_n['is_process'] = 0;
        $row_n['process_id'] = '';
    }

    array_push($total_array, $row_n);
}


$def_date = date("Y-m", strtotime($now_month)); //기준날짜
$end_date = $is_year ? "{$year}-12-31" : date("Y-m-t", strtotime($now_month)); // 조회범위 끝 (연간=연말 / 월간=월말)

// 삭제된 예외 레코드 날짜 목록 (반복일정에서 특정 날짜 제외용)
// key: "parent_cal_idx_날짜" → 해당 날짜에 예외 처리가 있으면 원본 반복 스킵
$exception_dates = [];
$exc_sql = "SELECT exception_idx, cal_date FROM a_calendar WHERE exception_idx IS NOT NULL AND exception_idx != '' AND exception_idx != '0' AND exception_idx != 0 {$sql_search1} {$building_id_filter}";
$exc_res = sql_query($exc_sql);
while($exc_row = sql_fetch_array($exc_res)){
    $exception_dates[$exc_row['exception_idx'] . '_' . $exc_row['cal_date']] = true;
}

//반복설정 월간인 경우 (noti_repeat='MONTH'인 레코드 모두 포함 - 기존 예외 레코드도 반복 표시)
$sql_month = "SELECT * FROM a_calendar WHERE is_del = 0 and noti_repeat = 'MONTH' {$sql_search1} {$building_id_filter} ORDER BY cal_date asc, cal_idx desc";
$result_m = sql_query($sql_month);

// 펼침 대상 월: 월간 모드는 해당 월 1개, 연간 모드는 1~12월 (12개월 루프로 "감싸기")
$months_loop = $is_year ? range(1, 12) : array(intval($month));

while($row_m = sql_fetch_array($result_m)){

    $parent_start = $row_m['cal_date'];   // 부모 반복 시작일 (occurrence 루프 간 불변 보존)
    $parent_edate = $row_m['cal_edate'];  // 부모 마감일
    $rep_day      = date("d", strtotime($parent_start)); // 월간 반복 일자 고정

    //시작일이 조회범위 끝보다 크면(아직 시작 안 함) 부모 전체 제외
    if($parent_start > $end_date){
        continue;
    }

    foreach($months_loop as $loop_m){

        $date_month = $year.'-'.sprintf('%02d', $loop_m).'-'.$rep_day; //YYYY-MM-dd occurrence

        // ★ R1: occurrence가 부모 시작일 이전이면 제외 (연간 12개월 중 시작 전 달 차단)
        if($date_month < $parent_start){
            continue;
        }

        // 해당 날짜에 예외 레코드가 있으면 원본 스킵 (예외 레코드가 대신 표시됨)
        if(isset($exception_dates[$row_m['cal_idx'] . '_' . $date_month])){
            continue;
        }

        //달력에서 날짜 선택한 경우
        if($selectDate != ""){
            //선택한 날짜와 다를경우 제외
            if($date_month != $selectDate){
                continue;
            }
        }

        //occurrence가 조회범위 끝보다 클경우 제외
        if($date_month > $end_date){
            continue;
        }

        //일정에 마감날짜 있는 경우 occurrence가 마감날짜보다 클경우 제외
        if($parent_edate != ''){
            if($date_month > $parent_edate){
                continue;
            }
        }

        // 부모를 복사해 occurrence로 push (원본 $row_m 변형 금지 — 12개월 루프 오염 방지)
        $item = $row_m;
        $item['cal_date'] = $date_month;

        // 일정 처리 확인 -- 해당 일정에 해당 날짜로 처리되었는지 확인
        $proc_key = $item['cal_idx'] . '_' . $date_month;
        if(array_key_exists($proc_key, $proc_map)){
            $item['is_process'] = 1;
            $item['process_id'] = $proc_map[$proc_key];
        }else{
            $item['is_process'] = 0;
            $item['process_id'] = '';
        }

        array_push($total_array, $item);
    }
}

$def_year = date("Y", strtotime($now_month)); // 연간 기준날짜

//반복설정 연간인 경우 (noti_repeat='YEAR'인 레코드 모두 포함)
$sql_year = "SELECT * FROM a_calendar WHERE is_del = 0 and noti_repeat = 'YEAR' {$sql_search1} {$building_id_filter} ORDER BY cal_date asc, cal_idx desc";
$result_y = sql_query($sql_year);



$start_date = $is_year ? "{$year}-01-01" : date("Y-m-01", strtotime($now_month)); // 조회범위 시작 (연간=연초 / 월간=월초)

while($row_y = sql_fetch_array($result_y)){


    $date_year = $def_year.'-'.date("m-d", strtotime($row_y['cal_date'])); //연간 반복이므로 월일자만 고정

    // 해당 날짜에 예외 레코드가 있으면 원본 스킵
    if(isset($exception_dates[$row_y['cal_idx'] . '_' . $date_year])){
        continue;
    }

    $row_y['cal_date'] = $date_year;

    //달력에서 날짜 선택한 경우
    if($selectDate != ""){

        //선택한 날짜와 다를경우 제외
        if($date_year != $selectDate){
            continue;
        }
    }

    //일정이 시작날짜보다 작을경우 제외
    if($date_year < $start_date){
        continue;
    }

    //일정이 종료날짜보다 클경우 제외
    if($date_year > $end_date){
        continue;
    }

    //일정에 마감날짜 있는 경우 날짜가 마감날짜보다 클경우 제외
    if($row_y['cal_edate'] != ''){
        if($row_y['cal_date'] > $row_y['cal_edate']){
            continue;
        }
    }

    // 일정 처리 확인
    $proc_key = $row_y['cal_idx'] . '_' . $row_y['cal_date'];
    if(array_key_exists($proc_key, $proc_map)){
        $row_y['is_process'] = 1;
        $row_y['process_id'] = $proc_map[$proc_key];
    }else{
        $row_y['is_process'] = 0;
        $row_y['process_id'] = '';
    }

    array_push($total_array, $row_y);
}

// 중복 제거: 같은 날짜+단지+캘린더종류에 여러 건이 있으면 최신(cal_idx 큰) 것만 유지
// (원본+예외가 동시에 반복 쿼리에 포함되는 경우 방지)
$dedup_map = [];
$deduped_array = [];
foreach($total_array as $item){
    $dedup_key = $item['cal_date'] . '_' . $item['building_id'] . '_' . $item['cal_code'] . '_' . $item['cal_title'];
    if(!isset($dedup_map[$dedup_key]) || $item['cal_idx'] > $dedup_map[$dedup_key]['cal_idx']){
        $dedup_map[$dedup_key] = $item;
    }
}
$total_array = array_values($dedup_map);

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

$limit_go = 10; // 한 페이지에 보여줄 목록 수
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
