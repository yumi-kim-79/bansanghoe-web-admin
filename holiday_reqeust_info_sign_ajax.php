<?php
require_once "./_common.php";
include_once(G5_LIB_PATH.'/approval.lib.php');

/**
 * 결재 승인 처리 (매니저앱)
 *
 * 2026-09: 관리자웹(adm/approval_form_check.php)과 로직이 두 벌로 복사돼 있었고,
 *  이 앱판에만 아래 두 버그가 남아 있었다.
 *    · 존재 확인은 a_sign_off_mng_sign 에 하고 UPDATE 는 a_sign_off_img 에 하던 오타
 *      → 재서명해도 서명이 갱신되지 않았다
 *    · 저장된 서명 재사용 시 업서트 없이 무조건 INSERT
 *      → 재서명하면 a_sign_off_mng_sign 에 중복 행이 쌓였다
 *  두 화면이 lib/approval.lib.php 의 approval_sign_one() 을 함께 쓰도록 바꿔 해소했다.
 *
 * 응답 형식은 기존과 동일하다 (result / msg).
 */

$sign_id  = isset($_POST['sign_id'])  ? (int)trim($_POST['sign_id']) : 0;
$mb_id    = isset($_POST['mb_id'])    ? trim($_POST['mb_id'])        : '';
$signdata = isset($_POST['signdata']) ? trim($_POST['signdata'])     : '';

$r = approval_sign_one($sign_id, $mb_id, $signdata);

if (empty($r['ok'])) {
    die(result_data(false, $r['msg'], array('code' => $r['code'])));
}

echo result_data(true, $r['msg'], array(
    'sign_status' => $r['sign_status'],
    'required'    => $r['required'],
    'done'        => $r['done'],
    'current'     => $r['current'],
));
exit;
