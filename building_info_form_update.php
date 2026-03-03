<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

// if($building_owner == "") die(result_data(false, "건축주를 입력해주세요.", "building_owner"));
// if($building_estate == "") die(result_data(false, "분양 사무실을 입력해주세요.", "building_estate"));
// if($building_company == "") die(result_data(false, "시공사를 입력해주세요.", "building_company"));

$update_query = "UPDATE a_building SET
                    open_password = '{$open_password}',
                    cctv_password = '{$cctv_password}',
                    building_owner = '{$building_owner}',
                    building_estate = '{$building_estate}',
                    building_company = '{$building_company}',
                    building_bigo = '{$building_bigo}'
                    WHERE building_id = '{$building_id}'";
sql_query($update_query);

echo result_data(true, "단지 정보가 수정되었습니다.", $building_id);