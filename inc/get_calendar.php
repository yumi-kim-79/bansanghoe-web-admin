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

    //echo $type;
    $mng_building = get_mng_building($mb_id);
    $mng_building_t = "'".implode("','", $mng_building)."'";

    $month = str_pad($month, 2, "0", STR_PAD_LEFT);

    if($type == '1'){

        $sql_sch = "";
        if($calendar_code != "schedule"){
            $sql_sch = " and cal_code = '{$calendar_code}' ";
        }

        $schedule_sql = "SELECT * FROM a_calendar WHERE is_del = 0 and cal_date like '{$year}-{$month}%' {$sql_sch} ORDER BY cal_date asc, cal_idx desc";
        // echo $schedule_sql;
        $schedule_res = sql_query($schedule_sql);

        $schedule_arr = array();

        while($schedule_row = sql_fetch_array($schedule_res)){
            array_push($schedule_arr, $schedule_row['cal_date']);
        }
        //print_r2($schedule_arr);
    }

    if($type == '2'){
        $move_sql = "SELECT mr.*, b.is_use FROM a_move_request as mr
                    LEFT JOIN a_building as b ON mr.building_id = b.building_id
                    WHERE b.is_use = 1 and mr.building_id IN ({$mng_building_t}) and mr.mv_date like '{$year}-{$month}%' ORDER BY mr.mv_date asc, mr.mv_idx desc";
        // echo $move_sql;
        $move_res = sql_query($move_sql);

        $move_arr = array();

        while($move_row = sql_fetch_array($move_res)){
            array_push($move_arr, $move_row['mv_date']);
        }

        //print_r2($move_arr);
    }
?>
<section class="cal_header">
    <button type="button" onClick="moveCal('<?php echo $prevYear?>', '<?php echo $prevMonth?>', '<?php echo $type; ?>');">
        <img src="/images/icon_cal_prev.svg" alt="">
    </button>
    <p><?php echo $year?>년 <?php echo sprintf('%02d', $month)?>월</p>
    <button type="button" onClick="moveCal('<?php echo $nextYear?>', '<?php echo $nextMonth?>', '<?php echo $type; ?>');">
        <img src="/images/icon_cal_next.svg" alt="">
    </button>
</section>

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
		<div class="cal_div cal_td cal_td_box <?php if($date2 == date("Y-m-d")){?>today <?php }?> <?php if($type == '2'){?>cal_td_box2 ver2<?php }else{?>cal_td_box1<?php }?>" data-date="<?php echo $date2; ?>">
			<div class="cal_day_box">
				<?php echo $nowDayCount++?>
                <?php
                //$attendance_row = sql_fetch("SELECT * FROM a_attendance WHERE at_st_idx = '{$st_idx}' and at_date = '{$date2}'");
                ?>

                <?php if($type == '2' && in_array($date2, $move_arr)){?>
                    <p class="cal_state_dot green"></p>
                <?php }else if($type == '1' && in_array($date2, $schedule_arr)){ ?>
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