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

    if($calendar_code == ""){
        $calendar_code = "schedule";
    }

    if($type == '1'){

        $sql_sch = "";
        if($calendar_code != "schedule"){
            $sql_sch = " and cal_code = '{$calendar_code}' ";
        }

        $schedule_sql = "SELECT * FROM a_calendar WHERE is_del = 0 and cal_date like '{$year}-{$month}%' {$sql_sch} ORDER BY cal_date asc, cal_idx desc";
        $schedule_res = sql_query($schedule_sql);

        // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        //     echo $schedule_sql.'<br>';
        // }
        // echo $schedule_sql;
      

        $schedule_arr = array();

        while($schedule_row = sql_fetch_array($schedule_res)){
            array_push($schedule_arr, $schedule_row['cal_date']);
        }

        $schedule_sql2 = " SELECT * FROM a_calendar WHERE is_del = 0 and noti_repeat != 'N' {$sql_sch} ";
        $res2 = sql_query($schedule_sql2);

        $month_arr = array();
        $year_arr = array();

        $now_month = $year.'-'.sprintf('%02d', $month);
        $endDate = $year.'-'.sprintf('%02d', $month).'-'.sprintf('%02d', $days);

        $def_year = date("Y", strtotime($now_month));
        $def_date = date("Y-m", strtotime($now_month));

        // ★ [2026-08] 예외 레코드 날짜 목록 (반복 일정의 특정 날짜 수정/삭제분)
        //  관리자웹에서 "이 날짜만 삭제"한 일정도 달력에 점이 그대로 남아 목록과 어긋났다.
        $exception_dates = array();
        $exc_sql = "SELECT cal_idx, exception_idx, cal_date FROM a_calendar
                    WHERE exception_idx IS NOT NULL AND exception_idx != '' AND exception_idx != '0' AND exception_idx != 0
                      AND cal_date like '{$now_month}%' {$sql_sch}";
        $exc_res = sql_query($exc_sql);
        while($exc_row = sql_fetch_array($exc_res)){
            $exception_dates[$exc_row['exception_idx'] . '_' . $exc_row['cal_date']] = true;
        }


        // echo $endDate;

        foreach ($res2 as $r) {

            if($r['noti_repeat'] == "MONTH"){

                $date_month = $def_date.'-'.date("d", strtotime($r['cal_date']));

                // ★ [2026-08] 반복 시작일 이전 / 마감일 이후 / 예외(수정·삭제)가 있는 날짜에는 점을 찍지 않는다
                if($date_month < $r['cal_date']) continue;
                if($r['cal_edate'] != '' && $date_month > $r['cal_edate']) continue;
                if(isset($exception_dates[$r['cal_idx'] . '_' . $date_month])) continue;

                if($date_month <= $endDate){
                    array_push($schedule_arr, $date_month);
                }
            }

            if($r['noti_repeat'] == "YEAR"){

                $date_year = $def_year.'-'.date("m-d", strtotime($r['cal_date']));

                // ★ [2026-08] 위와 동일 규칙
                if($date_year < $r['cal_date']) continue;
                if($r['cal_edate'] != '' && $date_year > $r['cal_edate']) continue;
                if(isset($exception_dates[$r['cal_idx'] . '_' . $date_year])) continue;

                if($date_year <= $endDate){
                    array_push($schedule_arr, $date_year);
                }
            }
        }

        $res_date = array_values(array_unique($schedule_arr));

        sort($res_date);
        // echo $schedule_sql.'<br>';
        // echo $schedule_sql2.'<br>';
        // print_r2($res_date);
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
if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $schedule_sql.'<br>';
    // echo $move_sql.'<br>';
    // echo $year.'-'.$month.'<br>';
}
?>
<section class="cal_header">
    <button type="button" onClick="moveCal('<?php echo $prevYear?>', '<?php echo $prevMonth?>', '<?php echo $type; ?>', '<?php echo $calendar_code; ?>');">
        <img src="/images/icon_cal_prev.svg" alt="">
    </button>
    <p><?php echo $year?>년 <?php echo sprintf('%02d', $month)?>월</p>
    <button type="button" onClick="moveCal('<?php echo $nextYear?>', '<?php echo $nextMonth?>', '<?php echo $type; ?>', '<?php echo $calendar_code; ?>');">
        <img src="/images/icon_cal_next.svg" alt="">
    </button>
</section>

<?php
// [공휴일] 해당 월 공휴일 맵 (a_holiday 비어있거나 없으면 빈 배열 → 표시만 미적용, 회귀 없음)
$cal_now_month = $year.'-'.sprintf('%02d', $month);
$holiday_map = [];
$hol_res = sql_query("SELECT holiday_date, holiday_name FROM a_holiday WHERE holiday_date LIKE '{$cal_now_month}%'");
while($hol = sql_fetch_array($hol_res)) $holiday_map[$hol['holiday_date']] = $hol['holiday_name'];
?>
<section class="cal_tr cal_head">
	<?php for( $i = 0; $i < count( $doms ); $i++ ) { ?>
	<div class="cal_div cal_th<?php echo $i==0?' cal_sun':($i==6?' cal_sat':'');?>"><?php echo $doms[$i]?></div>
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
		<div class="cal_div cal_td cal_td_box <?php if($date2 == date("Y-m-d")){?>today <?php }?> <?php if($type == '2'){?>cal_td_box2 ver2<?php }else{?>cal_td_box1<?php }?><?php echo $cols==0?' cal_sun':($cols==6?' cal_sat':'');?><?php echo isset($holiday_map[$date2])?' cal_holiday':''; ?>" data-date="<?php echo $date2; ?>">
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
		<div class="cal_div cal_td not_this<?php echo $cols==0?' cal_sun':($cols==6?' cal_sat':'');?>">
			<div class="cal_day_box">
				<?php echo $prevDayCount++?>
			</div>
		</div>

		<?php } else if ( $cellIndex >= $days ) {  // 다음달 이라면 
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