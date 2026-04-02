<?php
require_once './_common.php';

$today = date("Y-m-d H:i:s");

$sign_id  = isset($_POST['sign_id']) ? trim($_POST['sign_id']) : '';
$sign_id  = (int)$sign_id;
$mb_id    = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
$signdata = isset($_POST['signdata']) ? trim($_POST['signdata']) : '';
$data     = isset($_POST['data']) ? trim($_POST['data']) : '';

if ($sign_id === 0 || $mb_id === '') {
    die(result_data(false, "잘못된 요청입니다.(필수값 누락)", []));
}
if ($signdata === '') {
    die(result_data(false, "서명을 입력해주세요.", []));
}

$sign_info = sql_fetch("SELECT * FROM a_sign_off WHERE sign_id = '{$sign_id}'");
if (!$sign_info || !isset($sign_info['sign_id'])) {
    die(result_data(false, "결재 정보를 찾을 수 없습니다.", []));
}

$allowed_keys = ['sign_off_mng_id1', 'sign_off_mng_id2', 'sign_off_mng_id3'];
if (!in_array($data, $allowed_keys, true)) {
    if ($sign_info['sign_off_mng_id1'] === $mb_id) $data = 'sign_off_mng_id1';
    else if ($sign_info['sign_off_mng_id2'] === $mb_id) $data = 'sign_off_mng_id2';
    else if ($sign_info['sign_off_mng_id3'] === $mb_id) $data = 'sign_off_mng_id3';
}

if (!in_array($data, $allowed_keys, true)) {
    if (!$sign_info['sign_off_status']) $data = 'sign_off_mng_id1';
    else if (!$sign_info['sign_off_status2']) $data = 'sign_off_mng_id2';
    else if (!$sign_info['sign_off_status3']) $data = 'sign_off_mng_id3';
}

if (!in_array($data, $allowed_keys, true)) {
    die(result_data(false, "결재자 정보가 올바르지 않습니다.", []));
}

$approval_name = approval_category_name($sign_info['sign_off_category']);
$wname = get_member($sign_info['mng_id'])['mb_name'];
$wid = $sign_info['mng_id'];

// -----------------------------------------------------------------------------
// 1) 서명 이미지 저장/재사용 (a_signature)
// -----------------------------------------------------------------------------
$singature_row = sql_fetch("SELECT COUNT(*) as cnt FROM a_signature WHERE mb_id = '{$mb_id}' and signature_data = '{$signdata}'");

$sg_idx = 0;

if ((int)$singature_row['cnt'] === 0) {
    $data_uri = $signdata;
    $encoded_image = explode(",", $signdata);
    if (count($encoded_image) < 2) {
        die(result_data(false, "서명 데이터 형식이 올바르지 않습니다.", []));
    }
    $decoded_image = base64_decode($encoded_image[1]);

    $file_name = md5(uniqid(rand(), TRUE)).".png";
    $file_name = preg_replace("/\.(php|phtm|htm|cgi|pl|exe|jsp|asp|inc)/i", "$0-x", $file_name);

    $file_path = G5_DATA_PATH.'/file/approval';
    @mkdir($file_path, G5_DIR_PERMISSION);
    @chmod($file_path, G5_DIR_PERMISSION);

    $tgt = $file_path.'/'.$file_name;
    file_put_contents($tgt, $decoded_image);

    $signature_insert = "INSERT INTO a_signature SET
                         mb_id = '{$mb_id}',
                         signature_data = '{$data_uri}',
                         fil_name = '{$file_name}',
                         created_at = '{$today}'";
    sql_query($signature_insert);
    $sg_idx = sql_insert_id();
} else {
    $singature_row2 = sql_fetch("SELECT * FROM a_signature WHERE mb_id = '{$mb_id}' and signature_data = '{$signdata}' ORDER BY sg_idx DESC LIMIT 1");
    $sg_idx = isset($singature_row2['sg_idx']) ? (int)$singature_row2['sg_idx'] : 0;
}

if (!$sg_idx) {
    die(result_data(false, "서명 저장에 실패했습니다.", []));
}

// -----------------------------------------------------------------------------
// 2) 결재 문서에 결재자 서명 연결 (a_sign_off_mng_sign) - 업서트
// -----------------------------------------------------------------------------
$img_confirm = sql_fetch("SELECT COUNT(*) as cnt
                          FROM a_sign_off_mng_sign
                          WHERE mng_id = '{$mb_id}' AND sign_mng_data = '{$data}' AND sign_id = '{$sign_id}' AND is_del = 0");

if ((int)$img_confirm['cnt'] > 0) {
    $update_img = "UPDATE a_sign_off_mng_sign SET
                    sg_idx = '{$sg_idx}'
                    WHERE sign_id = '{$sign_id}' AND sign_mng_data = '{$data}' AND mng_id = '{$mb_id}' AND is_del = 0";
    sql_query($update_img);
} else {
    $insert_img = "INSERT INTO a_sign_off_mng_sign SET
            sg_idx = '{$sg_idx}',
            sign_mng_data = '{$data}',
            sign_id = '{$sign_id}',
            mng_id = '{$mb_id}',
            created_at = '{$today}',
            is_del = 0";
    sql_query($insert_img);
}

// -----------------------------------------------------------------------------
// 3) 결재 완료 플래그(1/2/3) 업데이트
// -----------------------------------------------------------------------------
$sign_chk = "";
$current_idx = 0;

switch($data){
    case 'sign_off_mng_id1':
        $sign_chk = 'sign_off_status';
        $current_idx = 1;
        break;
    case 'sign_off_mng_id2':
        $sign_chk = 'sign_off_status2';
        $current_idx = 2;
        break;
    case 'sign_off_mng_id3':
        $sign_chk = 'sign_off_status3';
        $current_idx = 3;
        break;
}

if ($sign_chk === '') {
    die(result_data(false, "결재 상태 업데이트에 실패했습니다.(결재자 식별 실패)", []));
}

$update_sign = "UPDATE a_sign_off SET
                {$sign_chk} = '1'
                WHERE sign_id = '{$sign_id}'";
sql_query($update_sign);

$sign_row = sql_fetch("SELECT * FROM a_sign_off WHERE sign_id = '{$sign_id}'");

// -----------------------------------------------------------------------------
// 4) 결재 상태(sign_status) 계산
// -----------------------------------------------------------------------------
$required = 0;
$done = 0;

$has1 = isset($sign_row['sign_off_mng_id1']) && trim($sign_row['sign_off_mng_id1']) !== '';
$has2 = isset($sign_row['sign_off_mng_id2']) && trim($sign_row['sign_off_mng_id2']) !== '';
$has3 = isset($sign_row['sign_off_mng_id3']) && trim($sign_row['sign_off_mng_id3']) !== '';

if ($has1) $required++;
if ($has2) $required++;
if ($has3) $required++;

$val1 = isset($sign_row['sign_off_status']) ? $sign_row['sign_off_status'] : '';
$val2 = isset($sign_row['sign_off_status2']) ? $sign_row['sign_off_status2'] : '';
$val3 = isset($sign_row['sign_off_status3']) ? $sign_row['sign_off_status3'] : '';

$isDone = function($v){
    $v = is_string($v) ? trim($v) : $v;
    return ($v === 1 || $v === '1' || $v === 'Y' || $v === 'y' || $v === true);
};

if ($has1 && $isDone($val1)) $done++;
if ($has2 && $isDone($val2)) $done++;
if ($has3 && $isDone($val3)) $done++;

if ($done <= 0) $status = 'N';
else if ($required > 0 && $done >= $required) $status = 'E';
else $status = 'P';

sql_query("UPDATE a_sign_off SET sign_status = '{$status}' WHERE sign_id = '{$sign_id}'");

// -----------------------------------------------------------------------------
// 5) 다음 결재자에게 푸시 발송
// -----------------------------------------------------------------------------
if ($status === 'P') {
    // 1차 결재 완료 → 2차 결재자 푸시
    if ($current_idx === 1 && $has2) {
        $nextId = $sign_row['sign_off_mng_id2'];
        $sign_off_id_info = get_member($nextId);

        $push_title = '[결재요청] '.$approval_name." 결재 요청이 있습니다.";
        $push_content = $wname.'님의 '.$approval_name." 결재 요청이 있습니다.";

        if ($sign_off_id_info['mb_token'] != "") {
            try {
                // ✅ 수정: 매니저앱 화면으로 이동
                fcm_send($sign_off_id_info['mb_token'], $push_title, $push_content, "sign_off", "{$sign_id}", "/holiday_reqeust_info.php?mng=Y&sign_id=");
            } catch(Exception $e) {
                // FCM 오류 무시하고 계속 진행
            }
        }

        sql_query("INSERT INTO a_push SET
            recv_id = '{$nextId}',
            recv_id_type = 'sm',
            push_title = '{$push_title}',
            push_content = '{$push_content}',
            wid = '{$wid}',
            push_type = 'sign_off',
            push_idx = '{$sign_id}',
            created_at = '{$today}'");
    }

    // 2차 결재 완료 → 3차 결재자 푸시
    if ($current_idx === 2 && $has3) {
        $nextId = $sign_row['sign_off_mng_id3'];
        $sign_off_id_info = get_member($nextId);

        $push_title = '[결재요청] '.$approval_name." 결재 요청이 있습니다.";
        $push_content = $wname.'님의 '.$approval_name." 결재 요청이 있습니다.";

        if ($sign_off_id_info['mb_token'] != "") {
            try {
                // ✅ 수정: 매니저앱 화면으로 이동
                fcm_send($sign_off_id_info['mb_token'], $push_title, $push_content, "sign_off", "{$sign_id}", "/holiday_reqeust_info.php?mng=Y&sign_id=");
            } catch(Exception $e) {
                // FCM 오류 무시하고 계속 진행
            }
        }

        sql_query("INSERT INTO a_push SET
            recv_id = '{$nextId}',
            recv_id_type = 'sm',
            push_title = '{$push_title}',
            push_content = '{$push_content}',
            wid = '{$wid}',
            push_type = 'sign_off',
            push_idx = '{$sign_id}',
            created_at = '{$today}'");
    }
}

echo result_data(true, "서명이 완료되었습니다.", [
    'sign_status' => $status,
    'required' => $required,
    'done' => $done,
    'current' => $data,
]);
exit;
