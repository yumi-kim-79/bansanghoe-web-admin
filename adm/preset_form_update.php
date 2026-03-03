<?php
require_once './_common.php';



$today = date("Y-m-d H:i:s");

if($pw == 'u'){

    foreach($pr_name as $idx => $value){

        $del_sql = "";
        if($preset_del[$idx]){
            $del_sql = ", 
                        is_del = 1,
                         deleted_at = '{$today}'";
        }

        $update_query = "UPDATE a_bbs_preset SET
                    pr_name = '{$pr_name[$idx]}'
                    {$del_sql}
                    WHERE pr_idx = '{$pr_idx[$idx]}'";

        sql_query($update_query);

       
    }

}else{

    if($bb_id == "") alert('잘못된 접근입니다.');
    if($pr_name == "") alert('프리셋 이름을 입력하세요.');

    $bbs_info = "SELECT * FROM a_building_bbs WHERE bb_id = '{$bb_id}'";
    $bbs_info_row = sql_fetch($bbs_info);

    $key_arr = array();
    $val_arr = array();

    foreach($bbs_info_row as $key => $value){
        array_push($key_arr, $key);
        array_push($val_arr, $value);
    }

    $key_arr_t = implode(",", $key_arr).", pr_name";
    $val_arr_t = "'".implode("','", $val_arr)."', '{$pr_name}'";

    $insert_preset = "INSERT INTO a_bbs_preset ({$key_arr_t}) VALUES ({$val_arr_t})";
    sql_query($insert_preset);
}

if($pw == 'u'){
    alert('프리셋 수정이 완료되었습니다.');
}else{
    alert('프리셋 저장이 완료되었습니다.');
}
?>