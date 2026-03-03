<?php
include_once(__DIR__.'/common.php');

// echo __DIR__;

$today = date("Y-m-d");
$today2 = date("Y-m-d H:i:s");

$visit_car_sql = "SELECT * FROM a_building_visit_car WHERE is_del = 0 and out_status = 'N' ORDER BY car_id desc";
$visit_car_res = sql_query($visit_car_sql);

while($visit_car_row = sql_fetch_array($visit_car_res)){

    $visit_day = $visit_car_row['visit_day'] - 1;
    $visit_end_date = date("Y-m-d", strtotime($visit_car_row['visit_date'].'+ '.$visit_day.' days'));

    // echo $visit_end_date.'<br>';

    if($today > $visit_end_date){
        //출차 처리
        $update_sql = "UPDATE a_building_visit_car SET
                        out_status = 'Y',
                        out_at = '{$today2}'
                        WHERE car_id = '{$visit_car_row['car_id']}'";

        //echo $update_sql.'<br>';
        sql_query($update_sql);
    }


    //출차 처리 로그 남기기
    $insert_log = "INSERT INTO a_cron_log SET
                    cr_type = 'visit_car',
                    cr_status = 1,
                    cr_chidx = '{$visit_car_row['car_id']}',
                    created_at = '{$today2}'";
    sql_query($insert_log);

}

echo "완료";
?>