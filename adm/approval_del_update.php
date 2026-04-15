<?php
require_once './_common.php';

header('Content-Type: application/json; charset=utf-8');

$today = date("Y-m-d H:i:s");

// 관리자 권한 체크
if($is_admin != 'super' && $member['mb_level'] < 10){
    echo json_encode(['result' => false, 'msg' => '삭제 권한이 없습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$idx_list = isset($_POST['idx_list']) ? $_POST['idx_list'] : '';
if(!$idx_list){
    echo json_encode(['result' => false, 'msg' => '삭제할 항목을 선택해주세요.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$idxArr = array_filter(array_map('intval', explode(',', $idx_list)));

if(count($idxArr) == 0){
    echo json_encode(['result' => false, 'msg' => '유효한 항목이 없습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$deleted = 0;

foreach($idxArr as $sign_id){
    if($sign_id <= 0) continue;

    // 첨부파일 삭제 (g5_board_file - signOff)
    $file_sql = sql_query("SELECT * FROM g5_board_file WHERE bo_table = 'signOff' AND wr_id = '{$sign_id}'");
    while($file_row = sql_fetch_array($file_sql)){
        $file_path = G5_DATA_PATH.'/file/signOff/'.str_replace('../', '', $file_row['bf_file']);
        if(file_exists($file_path)){
            @unlink($file_path);
        }
    }
    sql_query("DELETE FROM g5_board_file WHERE bo_table = 'signOff' AND wr_id = '{$sign_id}'");

    // soft delete
    sql_query("UPDATE a_sign_off SET is_del = 1, deleted_at = '{$today}' WHERE sign_id = '{$sign_id}'");
    $deleted++;
}

echo json_encode(['result' => true, 'msg' => $deleted.'건의 결재서류가 삭제되었습니다.'], JSON_UNESCAPED_UNICODE);
