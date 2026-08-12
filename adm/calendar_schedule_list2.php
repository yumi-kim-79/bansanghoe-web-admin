<?php
require_once './_common.php';

$sql_common = " from a_calendar ";

// 전체 기간(연도 'all'): 특정 건물 전 기간 — 다년 펼침은 STEP 2. 'all'이면 안전 폴백(현재 연도)
$is_all_year = ($toYear === 'all');
$year            = ( $toYear && $toYear !== 'all' )? $toYear : date( "Y" );

// 연간 모드: 월 미선택('all' 또는 빈값)이면 그 해 전체 조회 (방식 A — 우측 목록만 연간)
$is_year = ($toMonth === 'all' || $toMonth === '');
$month            = ( $toMonth && $toMonth !== 'all' )? $toMonth : date( "m" );

$now_month = $year.'-'.sprintf('%02d', $month);

// 조회 날짜 prefix: 연간이면 'YYYY', 월간이면 'YYYY-MM' (cal_date / process_date LIKE 'prefix%')
$date_like = $is_year ? "{$year}" : $now_month;

// 반복 펼침 경계(범위): 끝 = 전체기간(year'all')='이번 달 말' / 연간=연말 / 월간=월말
//                     시작 = 전체기간=사실상 무제한('1000-01-01') / 연간=연초 / 월간=월초
$end_date   = $is_all_year ? date("Y-m-t") : ($is_year ? "{$year}-12-31" : date("Y-m-t", strtotime($now_month)));
$start_date = $is_all_year ? "1000-01-01"  : ($is_year ? "{$year}-01-01" : date("Y-m-01", strtotime($now_month)));

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

// ③ 전체 기간(연도 'all')은 특정 건물 필수 — 미지정 시 무거운 전수 펼침 차단 (백엔드 안전망)
if($is_all_year && $building_stx == ""){
    echo '<div class="cal_schedule_empty">단지를 먼저 검색·선택해 주세요.<br>(전체 기간 조회는 단지 선택이 필요합니다)</div>';
    exit;
}

// 비반복/예외 날짜 조건: 전체기간은 prefix 못 쓰니 'cal_date <= 이번달말', 그 외는 'cal_date like prefix%'
$date_cond = $is_all_year ? " and cal_date <= '{$end_date}' " : " and cal_date like '{$date_like}%' ";
if($calcode == "schedule"){
    $sql_search = $date_cond;
}else{
    $sql_search = $date_cond . " and cal_code = '{$calcode}' ";

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
// 전체기간은 process_date prefix 못 쓰니 '<= 이번달말'. 그 외는 prefix.
$proc_date_cond = $is_all_year ? "p.process_date <= '{$end_date}'" : "p.process_date like '{$date_like}%'";
$proc_sql = "SELECT p.cal_idx, p.process_date, p.process_id
             FROM a_calendar_process AS p
             JOIN a_calendar AS c ON c.cal_idx = p.cal_idx
             WHERE {$proc_date_cond} {$sql_search1} {$building_id_filter}";
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


$def_date = date("Y-m", strtotime($now_month)); //기준날짜 (참고용. 범위는 상단 $start_date/$end_date 사용)

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

// MONTH 펼침: 월간=해당 월 1개 / 연간=그 해 1~12월 / 전체기간=부모시작~이번달(여러 해)
//  effective 범위 안에서 월 단위 스테핑. 월/연간 모드는 기존(STEP C)과 동일 범위로 떨어짐(아래 논리 동일).
while($row_m = sql_fetch_array($result_m)){

    $parent_start = $row_m['cal_date'];   // 부모 반복 시작일 (불변 보존)
    $parent_edate = $row_m['cal_edate'];  // 부모 마감일
    $rep_day      = date("d", strtotime($parent_start)); // 월간 반복 일자 고정

    // effective 범위: 시작 = max(부모시작, 조회범위시작) / 끝 = min(부모마감(있으면), 조회범위끝)
    $eff_start = ($parent_start > $start_date) ? $parent_start : $start_date;
    $eff_end   = $end_date;
    if($parent_edate != '' && $parent_edate < $eff_end){ $eff_end = $parent_edate; }

    // 시작이 끝보다 뒤면(아직 시작 안 함 / 이미 종료) 펼침 없음
    if($eff_start > $eff_end){
        continue;
    }

    // 월 단위 스테핑 (해를 넘나듦). occurrence 일자는 rep_day 고정.
    $cur_y = (int)date("Y", strtotime($eff_start));
    $cur_m = (int)date("n", strtotime($eff_start));
    $end_y = (int)date("Y", strtotime($eff_end));
    $end_m = (int)date("n", strtotime($eff_end));

    while($cur_y < $end_y || ($cur_y === $end_y && $cur_m <= $end_m)){

        $date_month = sprintf('%04d-%02d', $cur_y, $cur_m).'-'.$rep_day; //YYYY-MM-dd occurrence

        // 다음 스텝 미리 진행 (이후 continue 안전 — 무한루프 방지)
        $cur_m++; if($cur_m > 12){ $cur_m = 1; $cur_y++; }

        // ★ R1: occurrence가 부모 시작일 이전이면 제외
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

        // 부모를 복사해 occurrence로 push (원본 $row_m 변형 금지)
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



while($row_y = sql_fetch_array($result_y)){

    // 전체 기간(year'all')은 연 단위 스테핑(부모 시작연~올해). 그 외(월/연간)는 기존 STEP C 로직 그대로.
    if($is_all_year){
        $y_md = date("m-d", strtotime($row_y['cal_date'])); //연간 반복 월일 고정
        $cy   = (int)date("Y", strtotime($row_y['cal_date'])); //부모 시작연
        $ey   = (int)date("Y", strtotime($end_date)); //올해(이번 달 말의 연도)
        for(; $cy <= $ey; $cy++){
            $date_year = $cy.'-'.$y_md;
            if(isset($exception_dates[$row_y['cal_idx'] . '_' . $date_year])){ continue; }
            if($selectDate != "" && $date_year != $selectDate){ continue; }
            if($date_year < $start_date){ continue; }
            if($date_year > $end_date){ continue; }
            if($row_y['cal_edate'] != '' && $date_year > $row_y['cal_edate']){ continue; }
            $item = $row_y;
            $item['cal_date'] = $date_year;
            $proc_key = $item['cal_idx'] . '_' . $date_year;
            if(array_key_exists($proc_key, $proc_map)){
                $item['is_process'] = 1;
                $item['process_id'] = $proc_map[$proc_key];
            }else{
                $item['is_process'] = 0;
                $item['process_id'] = '';
            }
            array_push($total_array, $item);
        }
        continue; //다음 부모로
    }

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

// ★중복 제거 — "같은 일정이 두 번 펼쳐진 경우"만 제거한다 (2026-08 수정)
//
// 기존: 날짜+단지+종류+제목+작성자가 같으면 **무조건 1건만** 남겼다(cal_idx 큰 것).
//   → 같은 호실 건을 전출/전입으로 나눠 등록하면 제목이 같아서 별개 일정이 통째로 사라졌다.
//     실제로 전출정산에서 **미처리 건이 화면에 안 보여** 업무 누락 위험이 있었고,
//     엑셀 다운로드(dedup 없음)와 건수가 어긋나는 원인이기도 했다.
//
// 수정: 같은 그룹 안에서도 아래 둘만 제거한다.
//   ① 예외 레코드(exception_idx)가 대체하는 **부모 반복 일정**
//   ② 같은 cal_idx 가 두 번 펼쳐진 경우
//   부모-예외 관계가 아닌 별개 일정(cal_idx 다름)은 모두 남긴다.
$dedup_groups = [];
foreach($total_array as $item){
    $dedup_key = $item['cal_date'] . '_' . $item['building_id'] . '_' . $item['cal_code'] . '_' . $item['cal_title'] . '_' . $item['wid'];
    $dedup_groups[$dedup_key][] = $item;
}

$deduped_array = [];
foreach($dedup_groups as $items){
    if(count($items) === 1){
        $deduped_array[] = $items[0];
        continue;
    }

    // 이 그룹의 예외 레코드가 대체하는 부모 cal_idx 수집
    $overridden = [];
    foreach($items as $it){
        $ex = isset($it['exception_idx']) ? trim((string)$it['exception_idx']) : '';
        if($ex !== '' && $ex !== '0') $overridden[$ex] = true;
    }

    $seen_idx = [];
    foreach($items as $it){
        $idx = (string)$it['cal_idx'];
        if(isset($overridden[$idx])) continue; // ① 예외로 대체된 부모
        if(isset($seen_idx[$idx]))  continue;  // ② 같은 레코드 중복 펼침
        $seen_idx[$idx] = true;
        $deduped_array[] = $it;
    }
}
$total_array = $deduped_array;

// 처리상태 필터 (②): is_process 는 occurrence별로 이미 계산됨 → dedup 후·페이징 전에 거르기만
//  (펼침/예외/부모/dedup·연간 로직 무변경. 월/연간/연도전체 모든 모드 공통 적용)
$process_status = isset($_POST['process_status']) ? $_POST['process_status'] : '';
if($process_status === 'done'){
    $total_array = array_values(array_filter($total_array, function($item){ return $item['is_process'] == 1; }));
}elseif($process_status === 'todo'){
    $total_array = array_values(array_filter($total_array, function($item){ return $item['is_process'] == 0; }));
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
