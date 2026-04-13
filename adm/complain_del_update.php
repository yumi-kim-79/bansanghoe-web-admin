<?php
require_once "./_common.php";

header('Content-Type: application/json; charset=utf-8');

// 관리자 권한 체크
if($is_admin != 'super' && $member['mb_level'] < 10){
    echo json_encode(['result' => false, 'msg' => '삭제 권한이 없습니다.']);
    exit;
}

$idx_list = $_POST['idx_list'] ?? '';
if(!$idx_list){
    echo json_encode(['result' => false, 'msg' => '삭제할 항목을 선택해주세요.']);
    exit;
}

$today = date("Y-m-d H:i:s");
$idxArr = array_filter(array_map('intval', explode(',', $idx_list)));

if(count($idxArr) == 0){
    echo json_encode(['result' => false, 'msg' => '유효한 항목이 없습니다.']);
    exit;
}

$deleted = 0;

foreach($idxArr as $complain_idx){
    // 1. 첨부파일 삭제 (complain, complain_answer, complain_add)
    $file_tables = ['complain', 'complain_answer', 'complain_add'];
    foreach($file_tables as $bo_table){
        $file_sql = "SELECT bf_file FROM g5_board_file WHERE bo_table = '{$bo_table}' AND wr_id = '{$complain_idx}' AND bf_file != ''";
        $file_res = sql_query($file_sql);
        while($frow = sql_fetch_array($file_res)){
            $file_path = G5_DATA_PATH . '/file/' . $bo_table . '/' . str_replace('../', '', $frow['bf_file']);
            if(file_exists($file_path)){
                @unlink($file_path);
            }
        }
        // DB 파일 레코드 삭제
        sql_query("DELETE FROM g5_board_file WHERE bo_table = '{$bo_table}' AND wr_id = '{$complain_idx}'");
    }

    // 2. 민원 soft delete
    sql_query("UPDATE a_online_complain SET is_del = 1, deleted_at = '{$today}' WHERE complain_idx = '{$complain_idx}'");

    $deleted++;
}

echo json_encode(['result' => true, 'msg' => $deleted . '건의 민원이 삭제되었습니다.']);
