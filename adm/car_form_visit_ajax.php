<?php
require_once './_common.php';

$year            = ( $toYear )? $toYear : date( "Y" );
$month            = ( $toMonth )? $toMonth : date( "m" );

$prevYear        = ( $month == 1 )? ( $year - 1 ) : $year;
$prevMonth        = ( $month == 1 )? 12 : ( $month - 1 );
$nextYear        = ( $month == 12 )? ( $year + 1 ) : $year;
$nextMonth        = ( $month == 12 )? 1 : ( $month + 1 );


$months = str_pad($month, 2, "0", STR_PAD_LEFT);

$qstr .= '&ho_id='.$ho_id.'&year='.$year.'&month='.$months;

$visit_month = $year.'-'.$months;
$visit_month_where = " and visit_date like '{$visit_month}%' ";

$sql = " select count(*)  as cnt from a_building_visit_car WHERE is_del = 0 and ho_id = '{$ho_id}' {$visit_month_where} ORDER BY visit_date desc, car_id desc ";
//echo $sql.'<br>';
$row = sql_fetch($sql);
$total_count = $row['cnt'];

//echo $total_count;

$rows = 20;
//$rows = 5;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$from_record = ($page - 1) * $rows; // 시작 열을 구함



//echo $visit_month_where.'<Br>';

$visit_car_sql = "SELECT *, DATE_ADD(visit_date, INTERVAL (visit_day - 1) DAY) as end_day FROM a_building_visit_car WHERE is_del = 0 and ho_id = '{$ho_id}' {$visit_month_where} ORDER BY visit_date desc, car_id desc limit {$from_record}, {$rows}";

$visit_car_res = sql_query($visit_car_sql);


?>
<section class="cal_header mgb15">
    <button type="button" onClick="visit_car_list('<?php echo $prevYear?>', '<?php echo $prevMonth?>', '');">
        <img src="/images/icon_cal_prev.svg" alt="">
    </button>
    <p><?php echo $year?>년 <?php echo sprintf('%02d', $month)?>월</p>
    <button type="button" onClick="visit_car_list('<?php echo $nextYear?>', '<?php echo $nextMonth?>', '');">
        <img src="/images/icon_cal_next.svg" alt="">
    </button>
</section>
<table class="sub_table">
    <thead>
        <tr>
            <th>방문 기간</th>
            <th>방문자 연락처</th>
            <th>차량번호</th>
            <th>출차 시간</th>
        </tr>
    </thead>
    <tbody>
        <?php for($i=0;$visit_car_row = sql_fetch_array($visit_car_res);$i++){?>
        <tr>
            <td>
                <?php
                $endDayT = $visit_day > 1 ? ' ~ '.$visit_car_row['end_day'] : '';
                ?>
                <?php echo $visit_car_row['visit_date'].''.$endDayT;?>
            </td>
            <td><?php echo $visit_car_row['visit_hp']; ?></td>
            <td><?php echo $visit_car_row['visit_car_number']; ?></td>
            <td>
                <?php
                $dates = date("Y-m-d");
                if($visit_car_row['out_status'] == 'N'){
                        
                    if($visit_car_row['end_day'] < $dates){
                        echo date("Y.m.d", strtotime($visit_car_row['end_day']));
                    }else{
                        echo "-";
                    }
                }else{
                    echo date("Y.m.d H:i", strtotime($visit_car_row['out_at']));
                }
                ?>
            </td>
        </tr>
        <?php }?>
        <?php if($i==0){?>
        <tr>
            <td colspan='4'>등록된 방문차량이 없습니다.</td>
        </tr>
        <?php }?>
    </tbody>
</table>
<?php echo get_paging(5, $page, $total_page, '?' . $qstr . '&amp;page='); ?>