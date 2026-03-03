<?php
require_once "./_common.php";


if($mt_id == '') die(result_data(false, '단지를 선택해주세요.', []));
if($mb_id == '') die(result_data(false, '잘못된 접근입니다.', []));

//내가 관리단으로 등록된 단지가 맞는지 확인
$sql_mng_team = "SELECT * FROM a_mng_team WHERE mt_id = '{$mt_id}'";
$row_mng_team = sql_fetch($sql_mng_team);

if($row_mng_team['is_del']){
    die(result_data(false, '해당 단지의 관리단에서 삭제되었습니다. 다시 시도해주세요.', 'reload'));
}

if($row_mng_team['mb_id'] != $mb_id){
    die(result_data(false, '해당 단지의 관리단이 아닙니다. 다시 시도해주세요.', 'reload'));
}

$_SESSION['users']['id'] = $mb_id;
$_SESSION['users']['mng_building'] = $row_mng_team['mt_id'];

// die(result_data(false, 'OK', $_POST));
echo result_data(true, "단지가 변경되었습니다.", []);