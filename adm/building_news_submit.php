<?php
require_once "./_common.php";

if($bb_id == "") die(result_data(false, "발행에 실패하였습니다.", []));

$today = date("Y-m-d H:i:s");

$bbs_info = sql_fetch("SELECT * FROM a_building_bbs WHERE bb_id = '{$bb_id}'");

$update_query = "UPDATE a_building_bbs SET
                    is_submit = 'S',
                    submited_at = '{$today}'
                    WHERE bb_id = '{$bb_id}'";
sql_query($update_query);


if($building_id != "-1") {
    $sql_buildings = " and ho.building_id = '{$building_id}' ";
}else{
    $sql_buildings = " and ho.post_id = '{$post_id}' "; //단지 전체로 발송
}

// 단지내 세대에게 푸시발송
$ho_sql = "SELECT ho.*, mem.mb_id, mem.mb_token, mem.noti2 FROM a_building_ho as ho
LEFT JOIN a_member as mem ON ho.ho_tenant_hp = mem.mb_hp
WHERE ho.ho_status = 'Y' {$sql_buildings} ORDER BY ho.ho_id asc";
$ho_res = sql_query($ho_sql);

while($ho_row = sql_fetch_array($ho_res)){

    $push_title = '[안내문] 안내문이 등록되었습니다.';
    $push_content = $bbs_info['bb_title'].' 안내문이 등록되었습니다.';

    //noti2 공문 알림 수신여부 1 수신 0 안함
    if($ho_row['mb_token'] != "" && $ho_row['noti2']){ //토큰이 있는경우 푸시 발송

        fcm_send($ho_row['mb_token'], $push_title, $push_content, 'info', "{$bb_id}", "/building_new_info.php?bb_id=");
    }

    $insert_push = "INSERT INTO a_push SET
                    recv_id_type = 'user',
                    recv_id = '{$ho_row['mb_id']}',
                    push_title = '{$push_title}',
                    push_content = '{$push_content}',
                    wid = '{$member['mb_id']}',
                    push_type = 'info',
                    push_idx = '{$bb_id}',
                    created_at = '{$today}'";
    sql_query($insert_push);
}

echo result_data(true, "발행되었습니다.", []);
?>