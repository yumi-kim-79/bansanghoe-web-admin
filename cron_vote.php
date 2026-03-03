<?php
include_once(__DIR__.'/common.php');

//투표 기간 만료시 종료 처리 매일 0시 01분에 체크

$today = date("Y-m-d");
$today2 = date("Y-m-d H:i:s");

$vote_sql = "SELECT * FROM a_online_vote WHERE is_del = 0 and vt_period_type = 'period' and vt_status != 2 ORDER BY vt_id desc";
// echo $vote_sql.'<br>';
$vote_res = sql_query($vote_sql);

while($vote_row = sql_fetch_array($vote_res)){

    if($today > $vt_edate){
        //투표 종료 처리
        $update_sql = "UPDATE a_online_vote SET
                        vt_status = 2
                        WHERE vt_id = '{$vote_row['vt_id']}'";
        // echo $update_sql.'<br>';
        sql_query($update_sql);

        //투표 종료 처리 로그 남기기
        $insert_log = "INSERT INTO a_cron_log SET
                        cr_type = 'online_vote',
                        cr_status = 1,
                        cr_chidx = '{$vote_row['vt_id']}',
                        created_at = '{$today2}'";
        sql_query($insert_log);
    }
}

echo "투표종료 처리완료";