<?php
require_once './_common.php';
include_once(G5_LIB_PATH.'/approval.lib.php');

/**
 * 결재 승인 처리 (관리자웹)
 *
 * 2026-09: 실제 처리는 lib/approval.lib.php 의 approval_sign_one() 으로 옮겼다.
 *  - 같은 로직이 매니저앱(holiday_reqeust_info_sign_ajax.php)에도 복사돼 있었고,
 *    2026-08 에 이 파일만 고쳐지면서 앱판에 버그가 남아 있었다. 이제 두 곳이 같은 함수를 쓴다.
 *  - 일괄결재(approval_bulk_check.php)도 같은 함수를 반복 호출한다.
 *
 * ※ $_POST['data'](결재 차수)는 더 이상 신뢰하지 않는다.
 *   어느 차수인지, 지금이 이 사람 차례인지는 서버가 DB 를 보고 판정한다.
 *   응답 형식은 기존과 동일하게 유지해 화면 쪽 수정이 필요 없다.
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
