<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if (isset($_POST['editorImage'])) {
    $base64Data = $_POST['editorImage'];

    $base64Data = str_replace('data:image/png;base64,', '', $base64Data);
    $base64Data = str_replace(' ', '+', $base64Data);  // Base64 디코딩을 위한 공백 처리

    // Base64 데이터를 디코딩
    $imageData = base64_decode($base64Data);

    // 파일 이름 생성 (예: 'image_<타임스탬프>.png')
    $fileName = 'bill_'.time().'.png';

    $uploads_dir = G5_DATA_PATH.'/file/billSample';

    if (!file_exists($uploads_dir)) {
        mkdir($uploads_dir, 0777, true);  // 'uploads' 폴더가 없으면 생성
    }

    // 디코딩된 이미지를 파일로 저장
    if (file_put_contents($uploads_dir."/".$fileName, $imageData)) {
        
        echo result_data(true, "저장이 완료 되었습니다.", $fileName);
    }else {
        // 이미지 저장 실패
        echo result_data(false, "저장 중 오류가 발생하였습니다.", []);
    }
}else {
    echo result_data(false, "이미지 파일이 넘어오지 않았습니다.", []);
}