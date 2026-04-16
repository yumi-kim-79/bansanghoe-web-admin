<?php
/**
 * 네이버 클라우드 SENS SMS 단체 발송 API
 * POST JSON: { recipients: ["010..."], message: "내용", building_id: N }
 */
header('Content-Type: application/json; charset=utf-8');

try {
    // 설정 파일 로드
    $configPath = dirname(__DIR__) . '/config/ncloud_config.php';
    if(!file_exists($configPath)){
        echo json_encode(['success' => false, 'message' => 'NCloud 설정 파일이 없습니다. config/ncloud_config.php를 생성해주세요.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $config = require $configPath;

    // 환경별 DB 연결
    $host = 'localhost';
    $user = 'sm_user1';
    $pass = 'sm2025@@';
    $dbname = (strpos($_SERVER['HTTP_HOST'] ?? '', 'test.') !== false) ? 'bansanghoe' : 'sinbansang';

    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset('utf8');
    if($conn->connect_error){
        echo json_encode(['success' => false, 'message' => 'DB 연결 실패'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 요청 파싱
    $input = json_decode(file_get_contents('php://input'), true);
    $recipients = $input['recipients'] ?? [];
    $message = trim($input['message'] ?? '');
    $building_id = intval($input['building_id'] ?? 0);

    if(empty($recipients)){
        echo json_encode(['success' => false, 'message' => '수신번호를 입력해주세요.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if(empty($message)){
        echo json_encode(['success' => false, 'message' => '문자 내용을 입력해주세요.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 중복 제거 + 전화번호 정리
    $phones = array_values(array_unique(array_map(function($p){
        return preg_replace('/[\s\-]/', '', trim($p));
    }, $recipients)));
    $phones = array_filter($phones, function($p){ return strlen($p) >= 10; });
    $phones = array_values($phones);

    if(empty($phones)){
        echo json_encode(['success' => false, 'message' => '유효한 전화번호가 없습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // SMS/LMS 구분
    $msgLen = mb_strlen($message, 'UTF-8');
    $msgType = ($msgLen <= 45) ? 'SMS' : 'LMS'; // EUC-KR 기준 90byte ≈ UTF-8 45자
    $costPerMsg = ($msgType === 'SMS') ? $config['cost_per_sms'] : $config['cost_per_lms'];

    // SENS API 호출
    $serviceId = $config['service_id'];
    $accessKey = $config['access_key'];
    $secretKey = $config['secret_key'];
    $sender = preg_replace('/[\s\-]/', '', $config['sender']);

    $timestamp = round(microtime(true) * 1000);
    $url = "https://sens.apigw.ntruss.com/sms/v2/services/{$serviceId}/messages";

    // HMAC 서명 생성
    $method = 'POST';
    $uri = "/sms/v2/services/{$serviceId}/messages";
    $signStr = "{$method} {$uri}\n{$timestamp}\n{$accessKey}";
    $signature = base64_encode(hash_hmac('sha256', $signStr, $secretKey, true));

    // 수신자 배열 생성
    $msgRecipients = array_map(function($phone) {
        return ['to' => $phone];
    }, $phones);

    // 요청 바디
    $body = [
        'type' => $msgType,
        'from' => $sender,
        'content' => $message,
        'messages' => $msgRecipients,
    ];

    if($msgType === 'LMS'){
        $body['subject'] = mb_substr($message, 0, 20, 'UTF-8');
    }

    // cURL 호출
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'x-ncp-apigw-timestamp: ' . $timestamp,
        'x-ncp-iam-access-key: ' . $accessKey,
        'x-ncp-apigw-signature-v2: ' . $signature,
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if($curlError){
        echo json_encode(['success' => false, 'message' => 'API 통신 오류: ' . $curlError], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = json_decode($response, true);

    // 결과 판정
    $isSuccess = ($httpCode == 202 && isset($result['requestId']));
    $successCount = $isSuccess ? count($phones) : 0;
    $failCount = $isSuccess ? 0 : count($phones);
    $totalCost = $successCount * $costPerMsg;

    // 세션에서 발송자 ID 가져오기 (가능한 경우)
    $createdBy = '';
    if(session_status() === PHP_SESSION_NONE) @session_start();
    if(!empty($_SESSION['ss_mb_id'])) $createdBy = $_SESSION['ss_mb_id'];

    // 발송 이력 저장
    $recipientsJson = $conn->real_escape_string(json_encode($phones, JSON_UNESCAPED_UNICODE));
    $messageEsc = $conn->real_escape_string($message);
    $responseEsc = $conn->real_escape_string($response);
    $createdByEsc = $conn->real_escape_string($createdBy);
    $senderEsc = $conn->real_escape_string($config['sender']);

    $conn->query("INSERT INTO a_sms_history SET
        sh_building_id = {$building_id},
        sh_sender = '{$senderEsc}',
        sh_recipients = '{$recipientsJson}',
        sh_message = '{$messageEsc}',
        sh_total_count = " . count($phones) . ",
        sh_success_count = {$successCount},
        sh_fail_count = {$failCount},
        sh_cost = {$totalCost},
        sh_api_response = '{$responseEsc}',
        sh_created_by = '{$createdByEsc}'
    ");

    $conn->close();

    echo json_encode([
        'success' => $isSuccess,
        'message' => $isSuccess ? '발송 완료' : ('발송 실패: ' . ($result['error']['message'] ?? $response)),
        'success_count' => $successCount,
        'fail_count' => $failCount,
        'cost' => $totalCost,
        'type' => $msgType,
        'request_id' => $result['requestId'] ?? null,
    ], JSON_UNESCAPED_UNICODE);

} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
