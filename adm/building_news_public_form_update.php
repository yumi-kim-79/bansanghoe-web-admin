<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if($bbs_type == "") alert('잘못된 접근입니다.');
if($post_id == "") alert("지역을 선택해주세요.");
if($building_id == "") alert("단지를 선택해주세요.");
if($bb_title == "") alert("제목을 입력해주세요.");
if($bb_content == "") alert("내용을 입력해주세요.");


if($w == "u"){

    $update_query = "UPDATE a_building_bbs SET
                    post_id = '{$post_id}',
                    building_id = '{$building_id}',
                    bbs_gigan = 1,
                    bb_title = '{$bb_title}',
                    bb_content = '{$bb_content}',
                    is_view = '{$is_view}'
                    WHERE bb_id = '{$bb_id}'";
    sql_query($update_query);

    //echo $update_query.'<br>';
    
}else{

    
    $insert_query = "INSERT INTO a_building_bbs SET
                    post_id = '{$post_id}',
                    building_id = '{$building_id}',
                    bbs_type = '{$bbs_type}',
                    bbs_gigan = 1,
                    bb_number = '{$bb_number}',
                    bb_title = '{$bb_title}',
                    bb_content = '{$bb_content}',
                    wid = '{$member['mb_id']}',
                    is_view = '{$is_view}',
                    created_at = '{$today}'";
        
    //echo $insert_query.'<br>';
    //exit;
    sql_query($insert_query);
    $bb_id = sql_insert_id(); //팝업 idx


    //문서번호 저장..
    $insert_bb = "INSERT INTO a_bbs_number SET
                    bb_id = '{$bb_id}',
                    bbs_type = '{$bbs_type}',
                    post_id = '{$post_id}',
                    building_id = '{$building_id}',
                    bn_number = '{$bb_num}',
                    created_at = '{$today}'";
    sql_query($insert_bb);


    if($is_view){ //노출로 올렸을 때

        if($building_id != "-1") {
            $sql_buildings = " and ho.building_id = '{$building_id}' ";
        }

        // 단지내 세대에게 푸시발송
        $ho_sql = "SELECT ho.*, mem.mb_id, mem.mb_token, mem.noti2 FROM a_building_ho as ho
                LEFT JOIN a_member as mem ON ho.ho_tenant_hp = mem.mb_hp
                WHERE ho.ho_status = 'Y' {$sql_buildings} ORDER BY ho.ho_id asc";
        $ho_res = sql_query($ho_sql);


        while($ho_row = sql_fetch_array($ho_res)){

            $push_title = '[공문] 공문이 등록되었습니다.';
            $push_content = $bb_title. ' 공문이 등록되었습니다.';

            //noti2 공문 알림 수신여부 1 수신 0 안함
            if($ho_row['mb_token'] != "" && $ho_row['noti2']){ //토큰이 있는경우 푸시 발송

                fcm_send($ho_row['mb_token'], $push_title, $push_content, 'public', "{$bb_id}", "/building_new_info.php?bb_id=");
            }

            $insert_push = "INSERT INTO a_push SET
                            recv_id_type = 'user',
                            recv_id = '{$ho_row['mb_id']}',
                            push_title = '{$push_title}',
                            push_content = '{$push_content}',
                            wid = '{$member['mb_id']}',
                            push_type = 'public',
                            push_idx = '{$bb_id}',
                            created_at = '{$today}'";
            sql_query($insert_push);
        }
    }
    

}

//exit;
goto_url('./building_news_sample.php?bb_idx='.$bb_id.'&type='.$type);

// if($w == 'u'){
//     alert('공문이 수정되었습니다.');
// }else{
//     alert('공문이 등록되었습니다.', './building_news_public_form.php?type='.$type.'&amp;' . $qstr . '&amp;w=u&amp;bb_id=' . $bb_id);
// }
// ?>