<?php
include "../inc/_common.php";

/********** 사용자 설정값 **********/
$startYear        = date( "Y" );
$endYear        = date( "Y" ) + 4;

/********** 입력값 **********/
$year            = ( $toYear )? $toYear : date( "Y" );
$is_year         = ($toMonth === 'all'); // 연간 보기 — 그리드는 현재월로 표시, 월 드롭다운만 '전체'
$month            = ( $toMonth && $toMonth !== 'all' )? $toMonth : date( "m" );
$doms            = array( "일", "월", "화", "수", "목", "금", "토" );

/********** 계산값 **********/
$mktime            = mktime( 0, 0, 0, $month, 1, $year );
$days            = date( "t", $mktime );
$startDay        = date( "w", $mktime );

$prevDayCount    = date( "t", mktime( 0, 0, 0, $month, 0, $year ) ) - $startDay + 1;

$nowDayCount    = 1;
$nextDayCount    = 1;

$prevYear        = ( $month == 1 )? ( $year - 1 ) : $year;
$prevMonth        = ( $month == 1 )? 12 : ( $month - 1 );
$nextYear        = ( $month == 12 )? ( $year + 1 ) : $year;
$nextMonth        = ( $month == 12 )? 1 : ( $month + 1 );

$setRows	= ceil( ( $startDay + $days ) / 7 );

$now_month = $year.'-'.sprintf('%02d', $month);

$startDate = $now_month.'-01';
$endDate = $year.'-'.sprintf('%02d', $month).'-'.sprintf('%02d', $days);

$sql_where = " and is_del = 0 and cal_date like '{$now_month}%' ";
if($calcode != "schedule"){
    $sql_where .= " and is_del = 0 and cal_code = '{$calcode}'  ";
    $sql_where1 = " and cal_code = '{$calcode}' ";
}

$sql = "SELECT * FROM a_calendar WHERE (1) {$sql_where} ORDER BY cal_idx desc";
$res = sql_query($sql);

$date_arr = array();

while($row = sql_fetch_array($res)){
    array_push($date_arr, $row['cal_date']);
}

$yearD = date("Y") + 10;

// 반복일정 (noti_repeat가 MONTH/YEAR인 모든 레코드)
$sql2 = "SELECT * FROM a_calendar WHERE is_del = 0 and noti_repeat != 'N' {$sql_where1} ";
$res2 = sql_query($sql2);

// 예외 날짜 맵 (삭제된 예외 포함)
$exc_dates_dot = [];
$exc_dot_sql = "SELECT exception_idx, cal_date FROM a_calendar WHERE exception_idx IS NOT NULL AND exception_idx != '' AND exception_idx != '0' AND exception_idx != 0 AND is_del = 1 {$sql_where1}";
$exc_dot_res = sql_query($exc_dot_sql);
while($exc_dot = sql_fetch_array($exc_dot_res)) $exc_dates_dot[$exc_dot['exception_idx'] . '_' . $exc_dot['cal_date']] = true;

$def_year = date("Y", strtotime($now_month));
$def_date = date("Y-m", strtotime($now_month));

foreach ($res2 as $r) {
    if($r['noti_repeat'] == "MONTH"){
        $date_month = $def_date.'-'.date("d", strtotime($r['cal_date']));

        // 해당 날짜에 삭제된 예외가 있으면 dot 표시 안 함
        if(isset($exc_dates_dot[$r['cal_idx'] . '_' . $date_month])) continue;

        // cal_edate 체크
        if($r['cal_edate'] != '' && $r['cal_edate'] !== null && $date_month > $r['cal_edate']) continue;

        // 반복 시작일이 현재 월 마지막 날 이전이면 표시
        if($date_month <= $endDate && $r['cal_date'] <= $endDate){
            array_push($date_arr, $date_month);
        }
    }
    if($r['noti_repeat'] == "YEAR"){
        $date_year = $def_year.'-'.date("m-d", strtotime($r['cal_date']));

        if(isset($exc_dates_dot[$r['cal_idx'] . '_' . $date_year])) continue;
        if($r['cal_edate'] != '' && $r['cal_edate'] !== null && $date_year > $r['cal_edate']) continue;

        if($date_year <= $endDate){
            array_push($date_arr, $date_year);
        }
    }
}

$res_date = array_values(array_unique($date_arr));
sort($res_date);
?>

<div class="cal_header_new" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
    <div class="cal_header_select_box">
        <div class="cal_header_label">년도</div>
        <div class="cal_sel_with_nav">
            <button type="button" class="cal_nav_arrow" onclick="calYearMove(-1);" aria-label="전년">&lt;</button>
            <select name="cal_year" id="cal_year" class="bansang_sel" onchange="cal_year_change();">
                <?php for($y = 2024;$y<=$yearD;$y++){?>
                <option value="<?php echo $y; ?>" <?php echo get_selected($year, $y); ?>><?php echo $y; ?></option>
                <?php }?>
            </select>
            <button type="button" class="cal_nav_arrow" onclick="calYearMove(1);" aria-label="익년">&gt;</button>
        </div>
    </div>
    <div class="cal_header_select_box">
        <div class="cal_header_label">월</div>
        <div class="cal_sel_with_nav">
            <button type="button" class="cal_nav_arrow" onclick="calMonthMove(-1);" aria-label="전월">&lt;</button>
            <select name="cal_month" id="cal_month" class="bansang_sel" onchange="cal_month_change();">
                <option value="all" <?php echo $is_year ? 'selected' : ''; ?>>전체</option>
                <?php for($i=1;$i<=12;$i++){
                     $monthzero = str_pad($i, 2, "0", STR_PAD_LEFT);
                ?>
                <option value="<?php echo $monthzero; ?>" <?php echo get_selected($is_year ? 'all' : $month, $monthzero); ?>><?php echo $monthzero; ?></option>
                <?php }?>
            </select>
            <button type="button" class="cal_nav_arrow" onclick="calMonthMove(1);" aria-label="익월">&gt;</button>
        </div>
    </div>
    <?php if($is_year){ ?><span class="cal_year_badge">연간 보기</span><?php } ?>
    <input type="text" id="cal_building_search" placeholder="검색창"
        style="padding:5px 10px; border:2px solid #1976d2; border-radius:4px; font-size:14px; width:180px; height:34px; box-sizing:border-box;">
    <button type="button" onclick="doCalSearch()"
        style="padding:5px 18px; background:#1976d2; color:#fff; border:2px solid #1976d2; border-radius:4px; font-size:14px; cursor:pointer; height:34px;">검색</button>
    <span id="cal_search_label" style="color:#1976d2; font-size:13px; font-weight:bold;"></span>
</div>

<section class="cal_tr cal_head">
	<?php for( $i = 0; $i < count( $doms ); $i++ ) { ?>
	<div class="cal_div cal_th<?php echo $i==0?' cal_sun':($i==6?' cal_sat':'');?>"><?php echo $doms[$i]?></div>
	<?php } ?>
</section>

<?php for( $rows = 0; $rows < $setRows; $rows++ ) { ?>
<section class="cal_tr cal_body">
	<?php
		for( $cols = 0; $cols < 7; $cols++ ){
			$cellIndex    = ( 7 * $rows ) + $cols;

			if ( $startDay <= $cellIndex && $nowDayCount <= $days ) {
				$date2 = $year."-".str_pad($month, 2, "0", STR_PAD_LEFT)."-".str_pad($nowDayCount, 2, "0", STR_PAD_LEFT);
	?>
		<div class="cal_div cal_td cal_td_box <?php if($date2 == date("Y-m-d")){?>today <?php }?> <?php if($type == '2'){?>ver2<?php }?><?php echo $cols==0?' cal_sun':($cols==6?' cal_sat':'');?>" data-date="<?php echo $date2; ?>">
			<div class="cal_day_box">
				<?php echo $nowDayCount++?>
                <?php
                $attendance_row = sql_fetch("SELECT * FROM a_attendance WHERE at_st_idx = '{$st_idx}' and at_date = '{$date2}'");
                ?>
                <?php if(in_array($date2, $date_arr)){?>
                <p class="cal_state_dot red"></p>
                <?php }?>
			</div>
		</div>

		<?php } else if ( $cellIndex < $startDay ) {
				$prevDate = $prevYear."-".sprintf("%02d", $prevMonth)."-".sprintf("%02d", $prevDayCount);
		?>
		<div class="cal_div cal_td not_this<?php echo $cols==0?' cal_sun':($cols==6?' cal_sat':'');?>">
			<div class="cal_day_box">
				<?php echo $prevDayCount++?>
			</div>
		</div>

		<?php } else if ( $cellIndex >= $days ) {
				$nextDate = $nextYear."-".sprintf("%02d", $nextMonth)."-".sprintf("%02d", $nextDayCount);
		?>
		<div class="cal_div cal_td not_this<?php echo $cols==0?' cal_sun':($cols==6?' cal_sat':'');?>">
			<div class="cal_day_box">
				<?php echo $nextDayCount++?>
			</div>
		</div>
	<?php }}?>
</section>
<?php }?>
