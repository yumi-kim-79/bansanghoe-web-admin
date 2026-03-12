<?php
/**
 * parking_move_request_ajax.php
 * 
 * 이동주차 요청 처리 → a_push 테이블 insert → cron_push.php가 FCM 발송
 * 
 * POST 파라미터:
 *   - target_ho_id    : 요청 대상 호실 ID
 *   - car_number      : 차량번호
 *   - requester_ho_id : 요청자 호실 ID (로그인 사용자)
 */
include_once('./_common.php');

header('Content-Type: application/json');

$result   = false;
$msg      = '';

$target_ho_id    = trim($_POST['target_ho_id']);
$car_number      = trim($_POST['car_number']);
$requester_ho_id = trim($_POST['requester_ho_id']);

if (!$target_ho_id || !$car_number || !$requester_ho_id) {
    echo json_encode(['result' => false, 'msg' => '필수 값이 누락되었습니다.']);
    exit;
}

// ── 요청자 호실 이름 조회 (동/호 텍스트) ──────────────────────────────────
$req_ho_sql = "SELECT h.ho_name, d.dong_name 
               FROM a_ho h 
               JOIN a_dong d ON d.dong_id = h.dong_id 
               WHERE h.ho_id = '{$requester_ho_id}'";
$req_ho = sql_fetch($req_ho_sql);
$requester_name = $req_ho['dong_name'] . ' ' . $req_ho['ho_name'];

// ── 대상 호실 입주민 user ID 조회 ─────────────────────────────────────────
$target_user_sql = "SELECT mb_id FROM a_user_building 
                    WHERE ho_id = '{$target_ho_id}' 
                    AND is_main = 1 
                    LIMIT 1";
$target_user = sql_fetch($target_user_sql);

if (!$target_user['mb_id']) {
    echo json_encode(['result' => false, 'msg' => '해당 호실 입주민을 찾을 수 없습니다.']);
    exit;
}

$recv_id   = $target_user['mb_id'];
$today     = date("Y-m-d H:i:s");

// ── a_push 테이블에 INSERT → cron_push.php가 자동 발송 ───────────────────
$push_title   = '🚗 이동 주차 요청';
$push_content = "{$requester_name}에서 이동 주차를 요청했습니다. (차량번호: {$car_number})";
$push_type    = 'parking_move';

$insert_push = "INSERT INTO a_push SET
                recv_id      = '{$recv_id}',
                recv_id_type = 'user',
                push_title   = '{$push_title}',
                push_content = '{$push_content}',
                push_type    = '{$push_type}',
                push_idx     = '{$target_ho_id}',
                is_send      = 0,
                created_at   = '{$today}'";

$push_res = sql_query($insert_push);

if ($push_res) {
    // ── 이동주차 요청 이력 저장 (선택사항) ───────────────────────────────
    // 기존 a_move_request 테이블이 이사 전용이므로 별도 테이블 또는 컬럼 추가 권장
    // 우선 push insert 성공 시 result true 반환
    echo json_encode(['result' => true, 'msg' => '이동 주차 요청을 전송했습니다.']);
} else {
    echo json_encode(['result' => false, 'msg' => '요청 처리 중 오류가 발생했습니다.']);
}
exit;
