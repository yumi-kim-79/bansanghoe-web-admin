<?php
require_once './_common.php';

$post_row = sql_fetch("SELECT * FROM a_post_addr WHERE post_idx = '{$post_id}'");

$building_where = '';
if($building_id != '-1'){
    $building_where = " and building_id = '{$building_id}'  ";
}else{
    $building_where = " and building_id = '-1' and post_id = '{$post_id}' ";
}

$sql_num = "SELECT MAX(bn_number) as max FROM a_bbs_number WHERE bbs_type = '{$bbs_type}' and is_del = 0 {$building_where} ";

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP)  die(result_data(false, $sql_num, []));
$row_num = sql_fetch($sql_num);

$bbs_name = '';

switch($bbs_type){
    case "infomation":
        $bbs_name = '안내문';
        break;
    case "public":
        $bbs_name = '공문';
        break;
    case "event":
        $bbs_name = '이벤트';
        break;
}

if($row_num){

    $bb_numbers = "";

    $nums = $row_num['max'] + 1;

    $nums2 = str_pad($nums, 4, "0", STR_PAD_LEFT);

    if($building_id != '-1'){
        $bb_numbers = $bbs_name.'_'.$nums2;
        // $bb_numbers = $post_row['post_name'].'_'.$nums2;
    }else{
        $bb_numbers = $bbs_name.'_'.$post_row['post_name'].'_'.$nums2;
    }

    echo result_data(true, $bb_numbers, $nums);
}else{

    $bb_numbers = "";

    if($building_id != '-1'){
        $bb_numbers = $bbs_name.'_0001'.$nums2;
        // $bb_numbers = $post_row['post_name'].'_0001';
    }else{
        $bb_numbers = $bbs_name.'_'.$post_row['post_name'].'_0001';
    }

    echo result_data(true, $bb_numbers, $nums2);
}