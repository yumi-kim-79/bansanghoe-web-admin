<?php
require_once "./_common.php";

$mng_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM a_mng_building WHERE mb_id = '{$mb_id}' and building_id = '{$building_id}'");

if($mng_confirm['cnt'] == 0) die(result_data(false, "담당 중인 단지만 수정가능합니다.", []));

$update_query = "UPDATE a_building SET
                 building_memo = '{$build_memo}'
                 WHERE building_id = '{$building_id}'";
//die(result_data(false, $update_query, []));
sql_query($update_query);

echo result_data(true, "단지 메모가 수정되었습니다.", []);