<?php
/**
 * CKEditor 5 SimpleUploadAdapter 전용 업로드 핸들러
 * 기존 cheditor5 업로드 인프라(config, nonce, 이미지 검증) 재사용
 */
require_once("config.php");

if (!function_exists('ft_nonce_is_valid')) {
    include_once('../editor.lib.php');
}

if (!function_exists('che_reprocessImage')) {
    function che_reprocessImage($file_path, $callback)
    {
        $MIME_TYPES_PROCESSORS = array(
            "image/gif" => array("imagecreatefromgif", "imagegif"),
            "image/jpg" => array("imagecreatefromjpeg", "imagejpeg"),
            "image/jpeg" => array("imagecreatefromjpeg", "imagejpeg"),
            "image/png" => array("imagecreatefrompng", "imagepng"),
            "image/webp" => array("imagecreatefromwebp", "imagewebp"),
            "image/bmp" => array("imagecreatefromwbmp", "imagewbmp")
        );

        try {
            $image_info = getimagesize($file_path);
            if ($image_info === null) {
                return false;
            }
            $mime_type = $image_info["mime"];
            if (!array_key_exists($mime_type, $MIME_TYPES_PROCESSORS)) {
                return false;
            }
            $processor = $MIME_TYPES_PROCESSORS[$mime_type];
            $img = $processor[0]($file_path);
            if (!$img) {
                return false;
            }
            $processor[1]($img, $file_path);
            imagedestroy($img);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}

header('Content-Type: application/json');

// nonce 검증
$nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';
if (!$nonce || !ft_nonce_is_valid($nonce, 'cheditor')) {
    echo json_encode(['error' => ['message' => 'Invalid nonce']]);
    exit;
}

// CKEditor 5 SimpleUploadAdapter는 'upload' 필드명 사용
if (!isset($_FILES['upload'])) {
    echo json_encode(['error' => ['message' => 'No file uploaded']]);
    exit;
}

$tempfile = $_FILES['upload']['tmp_name'];
$original_name = $_FILES['upload']['name'];
$type = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

// 허용 확장자 체크
$allowed = array('jpg', 'jpeg', 'gif', 'png', 'webp');
if (!in_array($type, $allowed)) {
    echo json_encode(['error' => ['message' => 'Allowed: jpg, jpeg, gif, png, webp']]);
    exit;
}

// 이미지 파일 검증
$imgsize = getimagesize($tempfile);
if (!$imgsize) {
    echo json_encode(['error' => ['message' => 'Invalid image file']]);
    exit;
}

// 파일명 생성: 년월일시분초_랜덤8자.확장자
$filename = date('YmdHis') . '_' . che_generateRandomString(8) . '.' . $type;
$savefile = SAVE_DIR . '/' . $filename;

move_uploaded_file($tempfile, $savefile);

if (CHE_UPLOAD_IMG_CHECK && !che_reprocessImage($savefile, null)) {
    @unlink($savefile);
    echo json_encode(['error' => ['message' => 'Image security check failed']]);
    exit;
}

try {
    if (defined('G5_FILE_PERMISSION')) {
        chmod($savefile, G5_FILE_PERMISSION);
    }
} catch (Exception $e) {}

$file_url = SAVE_URL . '/' . $filename;

// CKEditor 5 SimpleUploadAdapter 응답 포맷
echo json_encode([
    'url' => $file_url
]);
