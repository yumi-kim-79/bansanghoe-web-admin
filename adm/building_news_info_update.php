<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if($type == "") alert('잘못된 접근입니다.');
if($post_id == "") alert("지역을 선택해주세요.");
if($building_id == "") alert("단지를 선택해주세요.");
if($bb_title == "") alert("제목을 입력해주세요.");
if($bb_content == "") alert("내용을 입력해주세요.");

if($w == "u"){

    $update_query = "UPDATE a_building_bbs SET
                    post_id = '{$post_id}',
                    building_id = '{$building_id}',
                    bbs_type = '{$type}',
                    bb_number = '{$bb_number}',
                    bbs_gigan = '{$bbs_gigan}',
                    sdate = '{$sdate}',
                    edate = '{$edate}',
                    bb_title = '{$bb_title}',
                    bb_content = '{$bb_content}'
                    WHERE bb_id = '{$bb_id}'";
    sql_query($update_query);

    //echo $update_query.'<br>';
    
}else{

    
    $insert_query = "INSERT INTO a_building_bbs SET
                    post_id = '{$post_id}',
                    building_id = '{$building_id}',
                    bbs_type = '{$type}',
                    bb_number = '{$bb_number}',
                    bbs_gigan = '{$bbs_gigan}',
                    sdate = '{$sdate}',
                    edate = '{$edate}',
                    bb_title = '{$bb_title}',
                    bb_content = '{$bb_content}',
                    wid = '{$member['mb_id']}',
                    is_submit = 'N',
                    created_at = '{$today}'";
        
    // echo $insert_query.'<br>';
    // exit;
    sql_query($insert_query);
    $bb_id = sql_insert_id(); //팝업 idx


    $insert_bb = "INSERT INTO a_bbs_number SET
                    bb_id = '{$bb_id}',
                    bbs_type = '{$type}',
                    post_id = '{$post_id}',
                    building_id = '{$building_id}',
                    bn_number = '{$bb_num}',
                    created_at = '{$today}'";
    sql_query($insert_bb);
    
    //@include_once(G5_PATH."/pdf_test.php");
}

if($pr_idx != ''){

    $update_preset = "UPDATE a_bbs_preset SET
                    post_id = '{$post_id}',
                    building_id = '{$building_id}',
                    bbs_type = '{$type}',
                    bb_number = '{$bb_number}',
                    bbs_gigan = '{$bbs_gigan}',
                    sdate = '{$sdate}',
                    edate = '{$edate}',
                    bb_title = '{$bb_title}',
                    bb_content = '{$bb_content}'
                    WHERE pr_idx = '{$pr_idx}'";
    // sql_query($update_preset);
}

goto_url('./building_news_sample.php?bb_idx='.$bb_id);

// if($w == 'u'){
//     alert('안내문이 수정되었습니다.');
// }else{
//     //alert('안내문이 등록되었습니다.', './building_news_info_form.php?' . $qstr . '&amp;w=u&amp;bb_id=' . $bb_id);

//     goto_url('./building_news_sample.php?bb_idx='.$bb_id);
// }