<?php
include_once(__DIR__.'/common.php');

//시간 도래시 일정 반복 발행 처리 매일 오전 9시 체크

$today = date("Y-m-d");
$today2 = date("Y-m-d H:i:s");

$sql_calendar = "SELECT * FROM a_calendar WHERE is_del = 0 and noti_repeat != 'N' and noti_repeat != '' ORDER BY cal_idx desc";
$res_calendar = sql_query($sql_calendar);

// echo $sql_calendar.'<br>';

while($row_calendar = sql_fetch_array($res_calendar)){

    if($row_calendar['cal_edate'] != ""){
        //종료일이 있다면 종료일이 오늘 날짜보다 이전이면 패스
        if($today > $row_calendar['cal_edate']){
            continue;
        }
    }
    

    //월간
    if($row_calendar['noti_repeat'] == "MONTH"){

        $dd = date("d", strtotime($row_calendar['cal_date']));
        $noti_date = date("Y-m")."-{$dd}";

    }else if($row_calendar['noti_repeat'] == "YEAR"){
    //연간

        $mmdd = date("m-d", strtotime($row_calendar['cal_date']));

        $noti_date = date("Y")."-{$mmdd}";
       
    }

    //알림 날짜가 오늘 날짜와 같다면 알림 발송
    if($today == $noti_date){

        $mem = sql_fetch("SELECT * FROM g5_member WHERE mb_id = '{$row_calendar['mng_id']}'");

        $push_content = "{$row_calendar['cal_title']} - {$row_calendar['cal_content']} 일정 알림";

        $insert_push = "INSERT INTO a_push SET
                        recv_id_type = 'sm',
                        recv_id = '{$row_calendar['mng_id']}',
                        push_title = '[일정] {$push_content}',
                        push_content = '{$push_content}',
                        wid = 'admin',
                        push_type = 'schedule',
                        push_idx = '{$row_calendar['cal_idx']}',
                        created_at = '{$today2}'";
        // echo $insert_push.'<br>';
        sql_query($insert_push);

        //토큰이 있다면
        if($mem['mb_token'] != ""){
            fcm_send($mem['mb_token'], '[일정] '.$push_title, $push_content, 'schedule', "{$row_calendar['cal_idx']}", "/schedule_add.php?w=i&cal_idx=");
        }

         // 크론 처리 로그 남기기
        $insert_log = "INSERT INTO a_cron_log SET
                        cr_type = 'calendar',
                        cr_status = 1,
                        cr_chidx = '{$row_calendar['cal_idx']}',
                        created_at = '{$today2}'";
        // echo $insert_log.'<br>';
        sql_query($insert_log);
    }

   
}