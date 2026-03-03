<?php
include_once('./_common.php');

// print_r2($_POST);

foreach($ho_id as $key => $ho_row){

    $update = "UPDATE a_building_ho SET
                ho_tenant = '{$ho_tenant[$key]}',
                ho_tenant_hp = '{$ho_tenant_hp[$key]}',
                ho_tenant_at = '{$ho_tenant_at[$key]}'
                WHERE ho_id = '{$ho_id[$key]}'";

    sql_query($update);
}

alert('입주자 정보가 수정되었습니다.');