<?php
// 네이버 클라우드 SENS 설정 예시
// 이 파일을 복사하여 ncloud_config.php로 저장 후 실제 값 입력
// ncloud_config.php는 .gitignore 처리됨

return [
    'service_id' => 'ncp:sms:kr:xxxxxxxxx:xxxxxx', // SENS 프로젝트 서비스 ID
    'access_key' => 'YOUR_ACCESS_KEY',             // Access Key
    'secret_key' => 'YOUR_SECRET_KEY',             // Secret Key
    'sender'     => '02-0000-0000',                // 발신번호 (등록된 번호)
    'cost_per_sms' => 9,                           // SMS 단가 (원)
    'cost_per_lms' => 29,                          // LMS 단가 (원)
];
