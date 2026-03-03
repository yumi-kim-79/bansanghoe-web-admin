<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if (isset($_POST['editorImage'])) {
    $base64Data = $_POST['editorImage'];

    // Base64에서 'data:image/png;base64,' 부분을 제거
    $base64Data = str_replace('data:image/png;base64,', '', $base64Data);
    $base64Data = str_replace(' ', '+', $base64Data);  // Base64 디코딩을 위한 공백 처리

    // Base64 데이터를 디코딩
    $imageData = base64_decode($base64Data);

    $plusData = uniqid();

    // 파일 이름 생성 (예: 'image_<타임스탬프>.png')
    $fileName = 'image_' . $plusData . '.png';

    // 파일 디렉토리 확인 및 생성

    $uploads_dir = $_SERVER['DOCUMENT_ROOT'].'/data/file/signOffSample';

    if (!file_exists($uploads_dir)) {
        mkdir($uploads_dir, 0777, true);  // 'uploads' 폴더가 없으면 생성
    }

    // 디코딩된 이미지를 파일로 저장
    if (file_put_contents($uploads_dir."/".$fileName, $imageData)) {

        $sample_confirm = "SELECT *, COUNT(*) as cnt FROM a_sign_off_sample WHERE sign_id = '{$sign_id}'";
        $sample_confirm_row = sql_fetch($sample_confirm);

        //이미지 파일명 저장 이미 있는 경우 삭제하고 저장
        if($sample_confirm_row['cnt'] > 0){

            $delete_file = G5_DATA_PATH."/file/signOffSample/".$sample_confirm_row['sample_img'];

            if( file_exists($delete_file) ){
                // @unlink($delete_file);
            }

            $update_img = "UPDATE a_sign_off_sample SET
                            sample_img = '{$fileName}'
                            WHERE sign_id = '{$sign_id}'";
            sql_query($update_img);
        }else{
            //샘플이미지 저장
            $inser_img = "INSERT INTO a_sign_off_sample SET
                            sign_id = '{$sign_id}',
                            sample_img = '{$fileName}',
                            created_at = '{$today}'";
            sql_query($inser_img);
        }
        


        echo result_data(true, "저장이 완료 되었습니다.", ['status' => 'success', 'message' => '이미지 저장 성공', 'file' => $fileName]);
    }else {
        // 이미지 저장 실패
        echo result_data(false, "저장 중 오류가 발생하였습니다.", []);
    }
}else {
    echo result_data(false, "이미지 파일이 넘어오지 않았습니다.", []);
}

?>