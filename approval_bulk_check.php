<?php
require_once "./_common.php";
include_once(G5_LIB_PATH.'/approval.lib.php');

/**
 * 결재서류함 일괄결재 — 2026-09 신설
 *
 *  선택한 결재건들을 서명 1개로 한 번에 승인한다.
 *  관리자웹(adm/approval_document_list.php)과 매니저앱(approval_document.php)이 같이 쓴다.
 *
 *  입력: idx_list = "12,34,56"   (a_sign_off.sign_id 목록)
 *        signdata = 서명 base64 data URI (전 건에 동일하게 적용)
 *
 *  처리 정책
 *   - 이 저장소에는 DB 트랜잭션을 쓰는 곳이 한 군데도 없다.
 *     전체 롤백을 흉내 내는 것은 오히려 위험해서, 기존 일괄삭제(adm/approval_del_update.php)와
 *     같이 **건별 독립 처리 + 성공/실패 집계** 방식으로 간다.
 *   - 실패 건은 사유와 함께 돌려주므로 화면에서 선택을 남겨 재시도할 수 있다.
 *   - 권한/차례 검증은 approval_sign_one() 안에서 건마다 다시 한다.
 *     (화면에서 넘어온 목록을 그대로 믿지 않는다)
 */

$mb_id = isset($member['mb_id']) ? trim((string)$member['mb_id']) : '';
if ($mb_id === '') die(result_data(false, "로그인 후 이용해 주세요.", []));

$signdata = isset($_POST['signdata']) ? trim($_POST['signdata']) : '';
$idx_list = isset($_POST['idx_list']) ? trim($_POST['idx_list']) : '';

if ($signdata === '') die(result_data(false, "서명이 없습니다. 서명 후 다시 시도해 주세요.", []));
if ($idx_list === '') die(result_data(false, "결재할 문서를 선택해 주세요.", []));

$ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $idx_list)))));

if (count($ids) === 0)   die(result_data(false, "결재할 문서를 선택해 주세요.", []));
if (count($ids) > 100)   die(result_data(false, "한 번에 최대 100건까지 결재할 수 있습니다.", []));

// 건수가 많으면 서명 저장·상태갱신·푸시가 반복되므로 실행시간을 넉넉히 잡는다
@set_time_limit(300);
@ignore_user_abort(true);

$success = array();
$failed  = array();

foreach ($ids as $sid) {
    $r = approval_sign_one($sid, $mb_id, $signdata);

    if (!empty($r['ok'])) {
        $success[] = array(
            'sign_id'     => (int)$r['sign_id'],
            'sign_status' => $r['sign_status'],   // P = 다음 결재자 대기 / E = 최종 완료
        );
    } else {
        $failed[] = array(
            'sign_id' => (int)$sid,
            'code'    => $r['code'],
            'msg'     => $r['msg'],
        );
    }
}

$ok_cnt   = count($success);
$fail_cnt = count($failed);

if ($ok_cnt === 0) {
    $msg = "결재된 문서가 없습니다.";
    if ($fail_cnt > 0) $msg .= "\n" . $failed[0]['msg'];
    die(result_data(false, $msg, array('success' => $success, 'failed' => $failed)));
}

$msg = $ok_cnt . "건 결재되었습니다.";
if ($fail_cnt > 0) $msg .= " (" . $fail_cnt . "건 실패)";

echo result_data(true, $msg, array(
    'success'     => $success,
    'failed'      => $failed,
    'success_cnt' => $ok_cnt,
    'fail_cnt'    => $fail_cnt,
));
exit;
