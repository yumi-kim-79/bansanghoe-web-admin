<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if($w == "u"){

    if($electro_date != ""){
        $electro_date_sql = " electro_date = '{$electro_date}', ";
    }

    if($water_date != ""){
        $water_date_sql = " water_date = '{$water_date}', ";
    }

    //빌딩 검침 내용 수정
    $meter_building_update = "UPDATE a_meter_building SET
                                {$electro_date_sql}
                                {$water_date_sql}
                                total_electro = '{$total_electro}',
                                total_water = '{$total_water}'
                                WHERE mr_idx = '{$mr_idx}'";

    sql_query($meter_building_update);

    //전기 값 수정
    foreach($ho_id_e as $idx => $ho_id_e_row){

        $update_elec = "UPDATE a_meter_reading SET
                        mr_val = '{$mr_val_e[$idx]}'
                        WHERE ho_id = '{$ho_id_e_row}' and mr_idx = '{$mr_idx}' and mr_type = 'electro'";
        //echo $update_elec.'<br>';
        sql_query($update_elec);
    }

     //수도 값 수정
     foreach($ho_id_w as $idx => $ho_id_wrow){

        $update_water = "UPDATE a_meter_reading SET
                        mr_val = '{$mr_val_w[$idx]}'
                        WHERE ho_id = '{$ho_id_wrow}' and mr_idx = '{$mr_idx}' and mr_type = 'water'";
        //echo $update_water.'<br>';
        sql_query($update_water);
    }

}else{

}

alert('검침 값이 수정되었습니다.');