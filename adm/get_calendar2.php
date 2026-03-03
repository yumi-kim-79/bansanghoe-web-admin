<?php
include "../inc/_common.php";

/********** 사용자 설정값 **********/
$startYear        = date( "Y" );
$endYear        = date( "Y" ) + 4;

/********** 입력값 **********/
$year            = ( $toYear )? $toYear : date( "Y" );
$month            = ( $toMonth )? $toMonth : date( "m" );
$doms            = array( "일", "월", "화", "수", "목", "금", "토" );
//$doms            = array( "SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT" );

/********** 계산값 **********/
$mktime            = mktime( 0, 0, 0, $month, 1, $year );      // 입력된 값으로 년-월-01을 만든다
$days            = date( "t", $mktime );                        // 현재의 year와 month로 현재 달의 일수 구해오기
$startDay        = date( "w", $mktime );                        // 시작요일 알아내기

// 지난달 일수 구하기
$prevDayCount    = date( "t", mktime( 0, 0, 0, $month, 0, $year ) ) - $startDay + 1;

$nowDayCount    = 1;                                            // 이번달 일자 카운팅
$nextDayCount    = 1;                                          // 다음달 일자 카운팅

// 이전, 다음 만들기
$prevYear        = ( $month == 1 )? ( $year - 1 ) : $year;
$prevMonth        = ( $month == 1 )? 12 : ( $month - 1 );
$nextYear        = ( $month == 12 )? ( $year + 1 ) : $year;
$nextMonth        = ( $month == 12 )? 1 : ( $month + 1 );

// 출력행 계산
$setRows	= ceil( ( $startDay + $days ) / 7 );

//echo $calcode;
$now_month = $year.'-'.sprintf('%02d', $month);

$startDate = $now_month.'-01';
$endDate = $year.'-'.sprintf('%02d', $month).'-'.sprintf('%02d', $days);

$sql_where = " and is_del = 0 and cal_date like '{$now_month}%' ";
if($calcode != "schedule"){
    $sql_where .= " and is_del = 0 and cal_code = '{$calcode}'  ";

    $sql_where1 = " and cal_code = '{$calcode}' ";
}

$sql = "SELECT * FROM a_calendar WHERE (1) {$sql_where} ORDER BY cal_idx desc";

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $startDate.'<br>';
    // echo $endDate.'<br>';

}
$res = sql_query($sql);

$date_arr = array();

while($row = sql_fetch_array($res)){
    array_push($date_arr, $row['cal_date']);
}

$yearD = date("Y") + 10;

//추가 반복일정 가져오기
$sql2 = "SELECT * FROM a_calendar WHERE is_del = 0 and noti_repeat != 'N' {$sql_where1} ";
$res2 = sql_query($sql2);

$month_arr = array();
$year_arr = array();

$def_year = date("Y", strtotime($now_month));
$def_date = date("Y-m", strtotime($now_month));

foreach ($res2 as $r) {
    if($r['noti_repeat'] == "MONTH"){
        
        $date_month = $def_date.'-'.date("d", strtotime($r['cal_date']));

       
        // if($r['cal_date'] > $startDate) continue;
        if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
            // echo $r['cal_date'].' / '.$startDate.'<br>';
        }
        
        if($date_month <= $endDate && $r['cal_date'] <= $startDate){
            // echo $endDate.'/'.$date_month.'<br>';
            // array_push($month_arr, $r);
            array_push($date_arr, $date_month);
        }

        
    }

    if($r['noti_repeat'] == "YEAR"){
        
        $date_year = $def_year.'-'.date("m-d", strtotime($r['cal_date']));

        if($date_year <= $endDate){
            array_push($date_arr, $date_year);
        }
    }
}

$res_date = array_values(array_unique($date_arr));

sort($res_date);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $sql.'<br>';
    // echo $sql2.'<br>';
    // print_r2($res_date);
}
?>
<!-- <section class="cal_header">
    <button type="button" onClick="moveCal('<?php echo $prevYear?>', '<?php echo $prevMonth?>', '<?php echo $type; ?>', '<?php echo $calcode; ?>');">
        <img src="/images/icon_cal_prev.svg" alt="">
    </button>
    <p><?php echo $year?>년 <?php echo sprintf('%02d', $month)?>월</p>
    <button type="button" onClick="moveCal('<?php echo $nextYear?>', '<?php echo $nextMonth?>', '<?php echo $type; ?>', '<?php echo $calcode; ?>');">
        <img src="/images/icon_cal_next.svg" alt="">
    </button>
</section> -->

<div class="cal_header_new">
    <div class="cal_header_select_box">
        <div class="cal_header_label">년도</div>
        <select name="cal_year" id="cal_year" class="bansang_sel" onchange="cal_year_change();">
            <?php for($y = 2024;$y<=$yearD;$y++){?>
            <option value="<?php echo $y; ?>" <?php echo get_selected($year, $y); ?>><?php echo $y; ?></option>
            <?php }?>
        </select>
    </div>
    <div class="cal_header_select_box">
        <div class="cal_header_label">월</div>
        <select name="cal_month" id="cal_month" class="bansang_sel" onchange="cal_month_change();">
            <?php for($i=1;$i<=12;$i++){
                 $monthzero = str_pad($i, 2, "0", STR_PAD_LEFT);    
            ?>
            <option value="<?php echo $monthzero; ?>" <?php echo get_selected($month, $monthzero); ?>><?php echo $monthzero; ?></option>
            <?php }?>
        </select>
    </div>
</div>

<section class="cal_tr cal_head">
	<?php for( $i = 0; $i < count( $doms ); $i++ ) { ?>
	<div class="cal_div cal_th"><?php echo $doms[$i]?></div>
	<?php } ?>
</section>

<?php for( $rows = 0; $rows < $setRows; $rows++ ) { ?>
<section class="cal_tr cal_body">
	<?php
		for( $cols = 0; $cols < 7; $cols++ ){
			// 셀 인덱스 만들자
			$cellIndex    = ( 7 * $rows ) + $cols;

			// 이번달이라면
			if ( $startDay <= $cellIndex && $nowDayCount <= $days ) {
				$date2 = $year."-".str_pad($month, 2, "0", STR_PAD_LEFT)."-".str_pad($nowDayCount, 2, "0", STR_PAD_LEFT);
	?>
        <!-- select_dates -->
		<div class="cal_div cal_td cal_td_box <?php if($date2 == date("Y-m-d")){?>today <?php }?> <?php if($type == '2'){?>ver2<?php }?>" data-date="<?php echo $date2; ?>">
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

		<?php } else if ( $cellIndex < $startDay ) {  // 이전달이라면 
				$prevDate = $prevYear."-".sprintf("%02d", $prevMonth)."-".sprintf("%02d", $prevDayCount);
		?>
		<div class="cal_div cal_td not_this">
			<div class="cal_day_box">
				<?php echo $prevDayCount++?>
			</div>
		</div>

		<?php } else if ( $cellIndex >= $days ) {  // 다음달 이라면 
				$nextDate = $nextYear."-".sprintf("%02d", $nextMonth)."-".sprintf("%02d", $nextDayCount);
		?>
		<div class="cal_div cal_td not_this">
			<div class="cal_day_box">
				<?php echo $nextDayCount++?>
			</div>
		</div>
	<?php }}?>
</section>
<?php }?>