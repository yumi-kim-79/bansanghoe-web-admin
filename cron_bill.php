<?php
include_once(__DIR__.'/common.php');
//시간 도래시 고지서 발행 처리 매시간 1분마다 체크

$today = date("Y-m-d");
$today2 = date("Y-m-d H:i:s");

// $total = sql_fetch("SELECT COUNT(*) as cnt FROM a_cron_log WHERE cr_type = 'bill'");

// if($total['cnt'] == 2){
//     //이미 오늘 고지서 발행 처리 로그가 2개가 있다면 중복 실행 방지
//     echo "오늘 고지서 발행 처리 로그가 이미 2개가 있습니다. 중복 실행을 방지합니다.";
//     exit;
// }

$bill_sql = "SELECT * FROM a_bill WHERE is_submit = 'R' ORDER BY bill_id desc";
$bill_res = sql_query($bill_sql);

while($bill_row = sql_fetch_array($bill_res)){
    

    if($today2 > $bill_row['r_submited_at']){
        // echo $bill_row['bill_id'].'<br>';

        //submited_at = '{$bill_row['r_submited_at']}',
        $update = "UPDATE a_bill SET 
                    is_submit = 'Y',
                    updated_at = '{$today2}'
                    WHERE bill_id = '{$bill_row['bill_id']}'";
        //echo $update.'<br>';
        sql_query($update);


        //배너 미노출 처리 로그 남기기
        $insert_log = "INSERT INTO a_cron_log SET
                        cr_type = 'bill',
                        cr_status = 1,
                        cr_chidx = '{$bill_row['bill_id']}',
                        created_at = '{$today2}'";
        // echo $insert_log.'<br>';
        sql_query($insert_log);


        //고지서 발행시 푸시발송
        $bill_info = sql_fetch("SELECT * FROM a_bill WHERE bill_id = '{$bill_row['bill_id']}'");
        $building_id = $bill_info['building_id']; //빌딩 인덱스

        $building_info = get_builiding_info($building_id); //단지정보

        //입주자
        $sql_ho = "SELECT ho.*, mem.mb_token, mem.noti1 FROM a_building_ho as ho
                    LEFT JOIN a_member as mem ON ho.ho_tenant_id = mem.mb_id
                    WHERE ho.building_id = '{$building_id}' and ho.ho_status = 'Y'
                    GROUP BY ho.ho_tenant_id";
        //echo $sql_ho.'<br>';
        $res_ho = sql_query($sql_ho);

      
        $push_content = $building_info['building_name']." ".$bill_info['bill_year']."년 ".$bill_info['bill_month']."월 고지서가 발행되었습니다.";

        // echo '<br>';
        while($row_ho = sql_fetch_array($res_ho)){
            $insert_push = "INSERT INTO a_push SET
                            recv_id_type = 'user',
                            recv_id = '{$row_ho['ho_tenant_id']}',
                            push_title = '[고지서] {$push_content}',
                            push_content = '{$push_content}',
                            wid = '{$bill_info['wid']}',
                            push_type = 'bill',
                            push_idx = '{$bill_row['bill_id']}',
                            created_at = '{$today2}'";
            // echo $insert_push.'<br>';
            sql_query($insert_push);

            if($row_ho['mb_token'] != "" && $row_ho['noti1']){ //토큰이 있는경우 푸시 발송
           
                fcm_send($row_ho['mb_token'], '[고지서] '.$push_title, $push_content, 'bill', "{$bill_row['bill_id']}", "/bill.php?bill_id=");
            }
        }

        
    }
}