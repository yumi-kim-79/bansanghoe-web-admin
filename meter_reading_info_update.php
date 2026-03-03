<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");
$ip_info = $_SERVER['REMOTE_ADDR'];

// if($_SERVER['REMOTE_ADDR'] != '59.16.155.80'){
//     die(result_data(false, "현재 수정 중입니다.", []));
// }
//검침 날짜 필수입력
if($meter_date == "") die(result_data(false, "검침날짜를 입력해주세요.", []));

$meter_building_confirm = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$mr_year}' and mr_month = '{$mr_month}'");

if($meter_building_confirm['cnt'] > 0){
    
    $mr_idx = $meter_building_confirm['mr_idx'];

    if($mr_type == 'electro'){
        $mb_date_sql = " electro_date = '{$meter_date}', ";
        $mb_total_val = " total_electro = '{$total_val}', ";
    }else{
        $mb_date_sql = " water_date = '{$meter_date}', ";
        $mb_total_val = " total_water = '{$total_val}', ";
    }

    $update_query = "UPDATE a_meter_building SET
                    {$mb_date_sql}
                    {$mb_total_val}
                    updated_at = '{$today}'
                    WHERE mr_idx = '{$mr_idx}'";
    sql_query($update_query);
    //die(result_data(false, $update_query, $_POST));
}else{

    if($mr_type == 'electro'){
        $mb_date_sql = " electro_date = '{$meter_date}', ";
        $mb_total_val = " total_electro = '{$total_val}', ";
    }else{
        $mb_date_sql = " water_date = '{$meter_date}', ";
        $mb_total_val = " total_water = '{$total_val}', ";
    }

    $insert_query = "INSERT INTO a_meter_building SET
                    building_id = '{$building_id}',
                    mr_department = '{$mr_department}',
                    wid = '{$wid}',
                    mr_year = '{$mr_year}',
                    mr_month = '{$mr_month}',
                    {$mb_date_sql}
                    {$mb_total_val}
                    created_at = '{$today}'";
    // die(result_data(false, $insert_query, $_POST));
    sql_query($insert_query);
    $mr_id = sql_insert_id(); 
}

//검침 값 변경
// $query_arr = array();
$val_total = 0;
foreach($mr_val as $idx => $val){

    $mt_val = $val == '' ? 0 : $val;

    //동 idx
    $ho_row = sql_fetch("SELECT dong_id, ho_id FROM a_building_ho WHERE building_id = '{$building_id}' and ho_id = '{$ho_id[$idx]}'");

    $meter_confirm = "SELECT mr_id, COUNT(*) as cnt FROM a_meter_reading WHERE ho_id = '{$ho_id[$idx]}' and mr_idx = '{$mr_id}' and mr_type = '{$mr_type}' and is_del = 0";
    $meter_confirm_row = sql_fetch($meter_confirm);

    if($meter_confirm_row['cnt'] > 0){
        $sql = "UPDATE a_meter_reading SET
                        dong_id = '{$ho_row['dong_id']}',
                        ho_id = '{$ho_id[$idx]}',
                        mr_val = '{$mt_val}'
                        WHERE mr_id = '{$meter_confirm_row['mr_id']}'";
    }else{
        $sql = "INSERT INTO a_meter_reading SET
                    mr_idx = '{$mr_id}',
                    building_id = '{$building_id}',
                    dong_id = '{$ho_row['dong_id']}',
                    ho_id = '{$ho_id[$idx]}',
                    mr_type = '{$mr_type}',
                    mr_val = '{$mt_val}',
                    created_at = '{$today}',
                    mr_ip = '{$ip_info}'";
    }

    sql_query($sql);

    $val_total += $mt_val; //검침 값 합계
    //array_push($query_arr, $sql);
}


// if($mr_type == 'electro'){
//     $mb_sql = " total_electro = '{$val_total}' ";
// }else{
//     $mb_sql = " total_water = '{$val_total}' ";
// }

// $update_total = "UPDATE a_meter_building SET
//                     {$mb_sql}
//                     WHERE mr_idx = '{$mr_id}'";
// sql_query($update_total);
//die(result_data(false, $update_total, $query_arr));

//die(result_data(false, $update_mt_building, []));
echo result_data(true, '검침 정보가 수정되었습니다.', $mr_type);