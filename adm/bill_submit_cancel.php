<?php
require_once './_common.php';

/**
 * 고지서 발행취소
 *
 * [2026-09] "발행완료였던 고지서가 저장 상태로 바뀌어 있다" 는 신고 대응
 *  - 기존에는 취소 시 is_submit 을 'N'(저장) 으로 되돌렸다.
 *    그래서 목록에서 '한 번도 발행 안 한 건' 과 '발행했다가 취소한 건' 이 구분되지 않았고,
 *    "발행했던 게 저장으로 변했다" 로 보였다.
 *  - 게다가 submited_at 을 NULL 로 지워 발행 이력 자체가 사라졌다.
 *  → 취소는 'C'(발행취소) 로 기록하고, 발행 시각은 남긴다.
 *    ('C' 표시 로직은 adm/bill_list.php 에 이미 있었으나 쓰이지 않고 있었다)
 *  - 누가 언제 취소했는지 추적할 수 있도록 서버 로그를 남긴다.
 */

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    // 주소만으로 실행되지 않도록 (링크 클릭·브라우저 프리페치로 취소되는 사고 방지)
    die(result_data(false, '잘못된 접근입니다.', []));
}

if($bill_id == '') die(result_data(false, '잘못된 접근입니다.', []));

$bill_id = (int)$bill_id;
$today = date("Y-m-d H:i:s");

$bill = sql_fetch("SELECT * FROM a_bill WHERE bill_id = '{$bill_id}'");
if(!$bill || !isset($bill['bill_id'])) die(result_data(false, '고지서를 찾을 수 없습니다.', []));

if($bill['is_submit'] != 'Y' && $bill['is_submit'] != 'R'){
    die(result_data(false, '발행 또는 예약발행 상태에서만 취소할 수 있습니다.', []));
}

// 발행 시각(submited_at)은 지우지 않는다 — 언제 발행됐던 건인지 남겨야 한다.
$update = "UPDATE a_bill SET
            is_submit = 'C',
            r_submited = '',
            r_submited_at = NULL,
            updated_at = '{$today}'
            WHERE bill_id = '{$bill_id}'";
sql_query($update);

error_log("[BILL] 발행취소 bill_id={$bill_id} building_id={$bill['building_id']} "
        . "{$bill['bill_year']}-{$bill['bill_month']} 이전상태={$bill['is_submit']} "
        . "by=".(isset($member['mb_id']) ? $member['mb_id'] : '?')." at={$today}");

echo result_data(true, '고지서 발행이 취소되었습니다.', []);
