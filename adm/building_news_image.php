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

    if ($imageData === false) {
        die(result_data(false, "이미지 디코딩에 실패하였습니다.", []));
    }

    // 파일 이름 생성 (예: 'image_<타임스탬프>.png')
    $fileName = 'image_' . time() . '.png';

    // 파일 디렉토리 확인 및 생성

    $uploads_dir = $_SERVER['DOCUMENT_ROOT'].'/data/building';

    if (!file_exists($uploads_dir)) {
        mkdir($uploads_dir, 0777, true);  // 'uploads' 폴더가 없으면 생성
    }

    // die(result_data(false, $base64Data, file_put_contents($uploads_dir."/".$fileName, $imageData)));

    // 디코딩된 이미지를 파일로 저장
    if (file_put_contents($uploads_dir."/".$fileName, $imageData)) {

        resizeImage($uploads_dir."/".$fileName, 794, 1123);

        $img_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_bbs_img WHERE bb_id = '{$bb_idx}'");

        //DB에 저장
        if($img_confirm['cnt'] > 0){

            //기존 이미지 삭제
            $del_imgs = sql_fetch("SELECT img_name FROM a_building_bbs_img WHERE bb_id = '{$bb_idx}'");
            $del_path = $uploads_dir.'/'.$del_imgs['img_name'];

            if( file_exists($del_path) ){
                @unlink($del_path);
            }

            //사진 데이터 수정
            $img_query = "UPDATE a_building_bbs_img SET
                            img_name = '{$fileName}'
                            WHERE bb_id = '{$bb_idx}'";
            sql_query($img_query);

            echo result_data(true, "수정이 완료 되었습니다.", ['status' => 'success', 'message' => '이미지 저장 성공', 'file' => $fileName]);

        }else{

            //이미지 데이터 추가
            $img_query = "INSERT INTO a_building_bbs_img SET
                            bb_id = '{$bb_idx}',
                            img_name = '{$fileName}',
                            created_at = '{$today}'";
            sql_query($img_query);

            // 이미지 저장 성공
            echo result_data(true, "저장이 완료 되었습니다.", ['status' => 'success', 'message' => '이미지 저장 성공', 'file' => $fileName]);
        }
        
    } else {
        // 이미지 저장 실패
        echo result_data(false, "저장 중 오류가 발생하였습니다.", []);
    }
} else {
    echo result_data(false, "이미지 파일이 넘어오지 않았습니다.", []);
}

?>