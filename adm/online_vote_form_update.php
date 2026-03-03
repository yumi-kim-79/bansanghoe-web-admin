<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");
$dates = date("Y-m-d");
$ip_info = $_SERVER['REMOTE_ADDR'];

//print_r2($_POST);

//수정
if($w == "u"){

    $update_query = "UPDATE a_online_vote SET
                        post_id = '{$post_id}',
                        building_id = '{$building_id}',
                        dong_id = '{$dong_id}',
                        vt_title = '{$vt_title}',
                        vt_period_type = '{$vt_period_type}',
                        vt_sdate = '{$vt_sdate}',
                        vt_edate = '{$vt_edate}',
                        vt_status = '{$vt_status}',
                        vt_content = '{$vt_content}'
                        WHERE vt_id = '{$vt_id}'";

    //echo $update_query.'<br>';
    sql_query($update_query);

    for($i=0;$i<count($vtq_name);$i++){

        if($vtq_id[$i] != ""){

            $del_sql = "";
            if($vtq_del[$i]){
                $del_sql = " ,
                            is_del = 1,
                             deleted_at = '{$today}' ";
            }

            $update_question = "UPDATE a_online_vote_question SET
                                vtq_name = '{$vtq_name[$i]}'
                                {$del_sql}
                                WHERE vtq_id = '{$vtq_id[$i]}'";

            //echo $update_question.'<br>';
            sql_query($update_question);

        }else{
            $insert_question = "INSERT INTO a_online_vote_question SET
                                vt_id = '{$vt_id}',
                                vtq_name = '{$vtq_name[$i]}',
                                wid = '{$member['mb_id']}',
                                created_at = '{$today}'";

            //echo '<br>문항추가----<br>';
            //echo $insert_question.'<br>';
            sql_query($insert_question);
        }
    }

}else{

    $vt_status = 0;

    //기간설정인 경우 기간에 오늘이 포함될 떄 푸시발송
    if($vt_period_type == 'period'){
        if($dates >= $vt_sdate && $dates <= $vt_edate){
            $vt_status = 1;
        }
    }else{
        $vt_status = 1;
    }


    // 단지 인원 수
    $sql_ho_cnt = "SELECT COUNT(*) as cnt FROM a_building_ho WHERE dong_id = '{$dong_id}' and ho_status = 'Y'";
    $sql_ho_cnt_row = sql_fetch($sql_ho_cnt);


    // if($_SERVER['REMOTE_ADDR'] == '59.16.155.80'){
    //     echo $sql_ho_cnt.'<br>';
    //     exit;
    // }

     //호수 입력
     $insert_query = "INSERT INTO a_online_vote SET
                        post_id = '{$post_id}',
                        building_id = '{$building_id}',
                        dong_id = '{$dong_id}',
                        vt_cnt = '{$sql_ho_cnt_row['cnt']}',
                        vt_title = '{$vt_title}',
                        vt_period_type = '{$vt_period_type}',
                        vt_sdate = '{$vt_sdate}',
                        vt_edate = '{$vt_edate}',
                        vt_status = '{$vt_status}',
                        vt_content = '{$vt_content}',
                        wid = '{$member['mb_id']}',
                        created_at = '{$today}'";

    // echo $insert_query.'<br>';
    // exit;
    sql_query($insert_query);
    $vt_id = sql_insert_id();


    for($i=0;$i<count($vtq_name);$i++){

        $insert_question = "INSERT INTO a_online_vote_question SET
                                vt_id = '{$vt_id}',
                                vtq_name = '{$vtq_name[$i]}',
                                wid = '{$member['mb_id']}',
                                created_at = '{$today}'";

        //echo '<br>문항추가----<br>';
        //echo $insert_question.'<br>';
        sql_query($insert_question);
    }

    
   

    //기간설정 안함이나 무기한일 때 상태가 진행중일 때

    if($dong_id != "-1") {
        $sql_buildings = " and ho.dong_id = '{$dong_id}' ";
    }else{
        $sql_buildings = " and ho.building_id = '{$building_id}' ";
    }
   
    // 단지내 세대에게 푸시발송
    $ho_sql = "SELECT ho.*, mem.mb_id, mem.mb_token, mem.noti5 FROM a_building_ho as ho
                LEFT JOIN a_member as mem ON ho.ho_tenant_hp = mem.mb_hp
                WHERE ho.ho_status = 'Y' {$sql_buildings} ORDER BY ho.ho_id asc";
    $ho_res = sql_query($ho_sql);

    while($ho_row = sql_fetch_array($ho_res)){

        $push_title = '[온라인 투표] 온라인 투표가 등록되었습니다.';
        $push_content = '투표 제목 : ' . $vt_title;

        //noti5 온라인투표 알림 수신여부 1 수신 0 안함
        if($ho_row['mb_token'] != "" && $ho_row['noti5']){ //토큰이 있는경우 푸시 발송

            fcm_send($ho_row['mb_token'], $push_title, $push_content, 'vote', "{$vt_id}", "/online_vote_info.php?vote=prg&vt_id=");
        }

        $insert_push = "INSERT INTO a_push SET
                recv_id_type = 'user',
                recv_id = '{$ho_row['mb_id']}',
                push_title = '{$push_title}',
                push_content = '{$push_content}',
                wid = '{$member['mb_id']}',
                push_type = 'vote',
                push_idx = '{$vt_id}',
                created_at = '{$today}'";
        sql_query($insert_push);
    }
  
}

//exit;

if($w == 'u'){
    
    alert('온라인투표가 수정되었습니다.');
    
}else{
    alert('온라인투표가 등록되었습니다.', './online_vote_form.php?'. $qstr . '&amp;w=u&amp;vt_id=' . $vt_id);
}
?>