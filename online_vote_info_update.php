<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
//     die(result_data(false, "1", $_POST));
// }

if($w == "u"){

    //투표했던 사항으로 또 투표하려고 하면
    $vote_check = "SELECT COUNT(*) as cnt FROM a_online_vote_result WHERE mb_id = '{$mb_id}' and vt_id = '{$vt_id}' and vtq_id = '{$vtq_id}'";
    $vote_check_row = sql_fetch($vote_check);
    
    if($vote_check_row['cnt'] > 0) die(result_data(false, "이미 투표가 완료되었습니다.", []));

    $update_query = "UPDATE a_online_vote_result SET
                        vtq_id = '{$vtq_id}'
                        WHERE mb_id = '{$mb_id}' and vt_id = '{$vt_id}'";
    sql_query($update_query);

    echo result_data(true, "투표가 수정되었습니다.", []);

}else{

    $vt_row = sql_fetch("SELECT * FROM a_online_vote WHERE vt_id = '{$vt_id}'");

    //투표한 사람 추가
    $vt_cnt_p = "UPDATE a_online_vote SET
                    vt_cnt2 = vt_cnt2 + 1
                    WHERE vt_id = '{$vt_id}'";
    sql_query($vt_cnt_p);

    $insert_query = "INSERT INTO a_online_vote_result SET
                        mb_id = '{$mb_id}',
                        ho_id = '{$ho_id}',
                        vt_id = '{$vt_id}',
                        vtq_id = '{$vtq_id}',
                        created_at = '{$today}'";
    sql_query($insert_query);


    $vt_status_w = '1';
    //투표 유형 인원수 마감이면
    if($vt_row['vt_period_type'] == 'personnel'){

        $vt_row2 = sql_fetch("SELECT * FROM a_online_vote WHERE vt_id = '{$vt_id}'");

        if($vt_row['vt_cnt'] == $vt_row2['vt_cnt2']){
            $update_query = "UPDATE a_online_vote SET
                                vt_status = '2'
                                WHERE vt_id = '{$vt_id}'";
            sql_query($update_query);

            $vt_status_w = '2';
        }
    }

    echo result_data(true, "투표가 완료되었습니다.", $vt_status_w);
}