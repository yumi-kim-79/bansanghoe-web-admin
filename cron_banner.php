<?php
include_once(__DIR__.'/common.php');

//배너 기간 만료시 배너 미노출 처리 매일 0시 01분에 체크

$today = date("Y-m-d");
$today2 = date("Y-m-d H:i:s");

$banner_sql = "SELECT * FROM a_banner WHERE is_del = 0 and is_view = 1 and banner_edate != '' ORDER BY banner_id desc";
$banner_res = sql_query($banner_sql);

while($banner_row = sql_fetch_array($banner_res)){

    // echo $banner_row['banner_id'].'<br>';
    $edate = $banner_row['banner_edate'];

    if($today > $edate){
        //배너 미노출 처리
        $update_sql = "UPDATE a_banner SET
                        is_view = 0
                        WHERE banner_id = '{$banner_row['banner_id']}'";
        
        // echo $update_sql.'<br>';
        sql_query($update_sql);
        
        //배너 미노출 처리 로그 남기기
        $insert_log = "INSERT INTO a_cron_log SET
                        cr_type = 'banner',
                        cr_status = 1,
                        cr_chidx = '{$banner_row['banner_id']}',
                        created_at = '{$today2}'";
        sql_query($insert_log);
    }
}