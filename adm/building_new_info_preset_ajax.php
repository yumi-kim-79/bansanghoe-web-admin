<?php
require_once './_common.php';

if($pr_idx == "") die(result_data(false, "프리셋을 선택해주세요.", []));

$sql = "SELECT * FROM a_bbs_preset WHERE pr_idx = '{$pr_idx}'";
$row = sql_fetch($sql) or die(result_data(false, "잘못된 접근입니다.", []));

// $bbs_sql = "SELECT * FROM a_building_bbs WHERE bb_id = '{$row['bb_id']}'";
// $bbs_row = sql_fetch($bbs_sql) or die(result_data(false, "프리셋으로 등록된 안내문이 없습니다.", []));

echo result_data(true, "프리셋 정보", $row);