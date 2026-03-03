<?php
require_once './_common.php';

$today = date("Y-m-d H:i:s");

if($push_mem_type == 'user'){
    $ho_sql = "SELECT ho.*, m.mb_id, m.mb_token, m.noti7 FROM a_building_ho as ho
                LEFT JOIN a_member as m ON ho.ho_tenant_hp = m.mb_hp
                WHERE ho.ho_id IN ({$select_push_ho})";
    $ho_res = sql_query($ho_sql);
    $ho_total = sql_num_rows($ho_res);

    //푸시 보낸 내역
    $insert_push_history = "INSERT INTO a_push_send_history SET
                            recv_id_type = '{$push_mem_type}',
                            wid = '{$member['mb_id']}',
                            ph_cnt = '{$ho_total}',
                            push_content = '{$push_content}',
                            created_at = '{$today}'";
    sql_query($insert_push_history);
    $ph_idx = sql_insert_id(); //

    //보낸사람 리스트 저장
    while($ho_row = sql_fetch_array($ho_res)){

        if($ho_row['mb_token'] != "" && $ho_row['noti7']){ //토큰이 있는경우 푸시 발송
            fcm_send($ho_row['mb_token'], $push_title, $push_content);
        }

        $insert_push = "INSERT INTO a_push SET
                        ph_idx = '{$ph_idx}',
                        recv_id_type = '{$push_mem_type}',
                        recv_id = '{$ho_row['mb_id']}',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$member['mb_id']}',
                        push_type = 'admin',
                        created_at = '{$today}'";
        //echo $insert_push.'<br>';

        sql_query($insert_push);
    
    }
}else{

    $select_push_mng = explode(",", $_POST['select_push_mng']);

    $select_push_mng = "'".implode("','", $select_push_mng)."'";

    $mng_sql = "SELECT mng.*, mb.mb_token FROM a_mng as mng
            LEFT JOIN g5_member as mb ON mng.mng_id = mb.mb_id
            WHERE mng.is_del = 0 and mng.mng_id IN ({$select_push_mng}) ORDER BY mng.mng_idx desc";
    $mng_res = sql_query($mng_sql);
    $mng_total = sql_num_rows($mng_res);

    //푸시 보낸 내역
    $insert_push_history = "INSERT INTO a_push_send_history SET
                            recv_id_type = '{$push_mem_type}',
                            wid = '{$member['mb_id']}',
                            ph_cnt = '{$mng_total}',
                            push_content = '{$push_content}',
                            created_at = '{$today}'";
    //echo $insert_push_history.'<br>';
    sql_query($insert_push_history);
    $ph_idx = sql_insert_id(); //

     //보낸사람 리스트 저장
     while($mng_row = sql_fetch_array($mng_res)){

        if($mng_row['mb_token'] != ""){ //토큰이 있는경우 푸시 발송
            fcm_send($mng_row['mb_token'], $push_title, $push_content);
        }

        $insert_push = "INSERT INTO a_push SET
                        ph_idx = '{$ph_idx}',
                        recv_id_type = '{$push_mem_type}',
                        recv_id = '{$mng_row['mng_id']}',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$member['mb_id']}',
                        push_type = 'admin',
                        created_at = '{$today}'";
        sql_query($insert_push);
    }
}

alert('푸시 발송이 완료되었습니다.', G5_ADMIN_URL.'/push_form.php?w=u&push_id='.$ph_idx);
