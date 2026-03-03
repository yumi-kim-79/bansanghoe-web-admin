<?php
require_once "./_common.php";


if($ho_id == '') die(result_data(false, '호수를 선택해주세요.', []));
if($mb_id == '') die(result_data(false, '잘못된 접근입니다.', []));


//호수가 내 소유가 맞는지 확인
$sql_ho = "SELECT * FROM a_building_ho WHERE ho_id = '{$ho_id}'";
$row_ho = sql_fetch($sql_ho);

if($row_ho['ho_tenant_id'] != $mb_id){
    die(result_data(false, '잘못된 접근입니다.', []));
}

$_SESSION['users']['id'] = $mb_id;
$_SESSION['users']['ho_id'] = $row_ho['ho_id'];

// die(result_data(false, 'OK', $_POST));
echo result_data(true, "호수가 변경되었습니다.", []);