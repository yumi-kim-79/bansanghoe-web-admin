<?php
require_once "./_common.php";

header('Content-Type: application/json; charset=utf-8');

// 관리자 권한 체크
if($is_admin != 'super' && $member['mb_level'] < 10){
    echo json_encode(['result' => false, 'msg' => '삭제 권한이 없습니다.']);
    exit;
}

$idx_list = $_POST['idx_list'] ?? '';
$bbs_code = $_POST['bbs_code'] ?? '';

if(!$idx_list || !$bbs_code){
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

foreach($idxArr as $bbs_idx){
    // 1. 이미지 첨부파일 삭제 (bbs_img)
    $file_sql = "SELECT bf_file FROM g5_board_file WHERE bo_table = 'bbs_img' AND wr_id = '{$bbs_idx}' AND bf_file != ''";
    $file_res = sql_query($file_sql);
    while($frow = sql_fetch_array($file_res)){
        $file_path = G5_DATA_PATH . '/file/bbs_img/' . str_replace('../', '', $frow['bf_file']);
        if(file_exists($file_path)) @unlink($file_path);
    }
    sql_query("DELETE FROM g5_board_file WHERE bo_table = 'bbs_img' AND wr_id = '{$bbs_idx}'");

    // 2. PDF 첨부파일 삭제 (bbs_pdf)
    $pdf_sql = "SELECT bf_file FROM g5_board_file WHERE bo_table = 'bbs_pdf' AND wr_id = '{$bbs_idx}' AND bf_file != ''";
    $pdf_res = sql_query($pdf_sql);
    while($prow = sql_fetch_array($pdf_res)){
        $pdf_path = G5_DATA_PATH . '/file/bbs_pdf/' . str_replace('../', '', $prow['bf_file']);
        if(file_exists($pdf_path)) @unlink($pdf_path);
    }
    sql_query("DELETE FROM g5_board_file WHERE bo_table = 'bbs_pdf' AND wr_id = '{$bbs_idx}'");

    // 3. 게시글 soft delete
    sql_query("UPDATE a_bbs SET is_del = 1, deleted_at = '{$today}' WHERE bbs_idx = '{$bbs_idx}' AND bbs_code = '{$bbs_code}'");

    $deleted++;
}

echo json_encode(['result' => true, 'msg' => $deleted . '건의 게시글이 삭제되었습니다.']);
