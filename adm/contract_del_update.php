<?php
/**
 * 용역업체 계약 삭제 (2026-08 신규)
 *
 * ★어드민(최고관리자) 전용. 화면에서 버튼을 숨기는 것만으로는 막을 수 없으므로
 *   서버에서도 반드시 권한을 검사한다.
 *
 * ★소프트 삭제(is_del = 1)로 처리한다.
 *   목록·SM매니저앱·지급처리·계산서 등 모든 조회가 `is_del = 0` 을 거르므로
 *   이 한 줄로 전 화면에서 사라진다. 행 자체는 남겨 두어
 *   지급/계산서 이력 추적이 가능하고, 잘못 지웠을 때 DB에서 `is_del = 0` 으로 되돌릴 수 있다.
 */
require_once "./_common.php";

header('Content-Type: application/json; charset=utf-8');

// ─── 권한 ────────────────────────────────────────────────
if ($is_admin != 'super') {
    echo json_encode(['result' => false, 'msg' => '삭제 권한이 없습니다. (관리자 계정만 가능)'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── 입력 검증 ────────────────────────────────────────────
$ct_idx = isset($_POST['ct_idx']) ? (int)$_POST['ct_idx'] : 0;
if ($ct_idx < 1) {
    echo json_encode(['result' => false, 'msg' => '삭제할 계약이 지정되지 않았습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$row = sql_fetch("SELECT ct_idx, company_name, building_id, is_del FROM a_contract WHERE ct_idx = '{$ct_idx}'");
if (!$row || !isset($row['ct_idx']) || $row['ct_idx'] == '') {
    echo json_encode(['result' => false, 'msg' => '존재하지 않는 계약입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($row['is_del']) {
    echo json_encode(['result' => false, 'msg' => '이미 삭제된 계약입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$building = sql_fetch("SELECT building_name FROM a_building WHERE building_id = '{$row['building_id']}'");
$label = trim(($building['building_name'] ?? '') . ' / ' . $row['company_name']);

// ─── 삭제 ────────────────────────────────────────────────
sql_query("UPDATE a_contract SET is_del = 1 WHERE ct_idx = '{$ct_idx}'");

$after = sql_fetch("SELECT is_del FROM a_contract WHERE ct_idx = '{$ct_idx}'");
if (!$after || $after['is_del'] != 1) {
    echo json_encode(['result' => false, 'msg' => '삭제에 실패했습니다. 다시 시도해 주세요.'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'result' => true,
    'msg'    => $label . ' 계약을 삭제했습니다.',
    'ct_idx' => $ct_idx,
], JSON_UNESCAPED_UNICODE);
