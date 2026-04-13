<?php
include_once('./_common.php');

// 개발 중 — 접근 차단
echo "<script>alert('개발 중입니다.'); history.back();</script>";
exit;

// 토스페이먼츠 빌링키 발급 성공 콜백
// GET: authKey, customerKey, ho_id

$ho_id = $_GET['ho_id'];
$authKey = $_GET['authKey'];
$customerKey = $_GET['customerKey'];

if(!$ho_id || !$authKey){
    echo "<script>alert('잘못된 접근입니다.');location.replace('/card_register.php');</script>";
    exit;
}

// 토스페이먼츠 API로 빌링키 확정 요청
$secretKey = 'test_sk_placeholder'; // 나중에 실제 시크릿키로 교체
$auth = base64_encode($secretKey . ':');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.tosspayments.com/v1/billing/authorizations/issue');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . $auth,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'authKey' => $authKey,
    'customerKey' => $customerKey
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if($httpCode == 200 && isset($result['billingKey'])){
    $billing_key = $result['billingKey'];
    $card_company = $result['card']['company'] ?? '';
    $card_number = $result['card']['number'] ?? '';

    $today = date("Y-m-d H:i:s");

    // 기존 카드 soft delete
    sql_query("UPDATE a_billing_card SET is_del = 1, deleted_at = '{$today}' WHERE ho_id = '{$ho_id}' AND is_del = 0");

    // 새 카드 등록
    sql_query("INSERT INTO a_billing_card SET
        ho_id = '{$ho_id}',
        billing_key = '{$billing_key}',
        customer_key = '{$customerKey}',
        card_company = '{$card_company}',
        card_number = '{$card_number}',
        created_at = '{$today}'");

    echo "<script>alert('카드가 등록되었습니다.');location.replace('/card_register.php');</script>";
} else {
    $errMsg = $result['message'] ?? '카드 등록에 실패했습니다.';
    echo "<script>alert('" . addslashes($errMsg) . "');location.replace('/card_register.php');</script>";
}
