<?php
/**
 * parking_move_request_ajax.php
 * 이동주차 요청 → FCM 푸시 직접 발송
 */
include_once('./_common.php');

header('Content-Type: application/json');

$target_ho_id    = trim($_POST['target_ho_id']);
$car_number      = trim($_POST['car_number']);
$requester_ho_id = trim($_POST['requester_ho_id']);

if (!$target_ho_id || !$car_number || !$requester_ho_id) {
    echo json_encode(['result' => false, 'msg' => '필수 값이 누락되었습니다.']);
    exit;
}

// ── 요청자 호실 이름 조회 ─────────────────────────────────────────────────
$req_ho = sql_fetch("SELECT ho.ho_name, dong.dong_name
                     FROM a_building_ho as ho
                     LEFT JOIN a_building_dong as dong ON dong.dong_id = ho.dong_id
                     WHERE ho.ho_id = '{$requester_ho_id}'");
$requester_name = $req_ho['dong_name'] . '동 ' . $req_ho['ho_name'] . '호';

// ── 대상 호실 입주민 정보 + FCM 토큰 조회 ────────────────────────────────
$target_ho = sql_fetch("SELECT ho.ho_tenant_id, mem.mb_token
                        FROM a_building_ho as ho
                        LEFT JOIN a_member as mem ON ho.ho_tenant_id = mem.mb_id
                        WHERE ho.ho_id = '{$target_ho_id}'");

if (!$target_ho['ho_tenant_id']) {
    echo json_encode(['result' => false, 'msg' => '해당 호실 입주민을 찾을 수 없습니다.']);
    exit;
}

$recv_id      = $target_ho['ho_tenant_id'];
$mb_token     = $target_ho['mb_token'];
$today        = date("Y-m-d H:i:s");
$push_title   = '이동 주차 요청';
$push_content = "{$requester_name}에서 이동 주차를 요청했습니다. (차량번호: {$car_number})";

// ── a_push 테이블 INSERT ──────────────────────────────────────────────────
$insert_push = "INSERT INTO a_push SET
                recv_id      = '{$recv_id}',
                recv_id_type = 'user',
                push_title   = '{$push_title}',
                push_content = '{$push_content}',
                push_type    = 'parking_move',
                push_idx     = '{$target_ho_id}',
                is_send      = 0,
                created_at   = '{$today}'";
sql_query($insert_push);

// ── FCM 직접 발송 (토큰 있는 경우) ───────────────────────────────────────
if ($mb_token != '') {
    fcm_send($mb_token, $push_title, $push_content, 'parking_move', "{$target_ho_id}", "/parking_manage.php?");
}

echo json_encode(['result' => true, 'msg' => '이동 주차 요청을 전송했습니다.']);
exit;