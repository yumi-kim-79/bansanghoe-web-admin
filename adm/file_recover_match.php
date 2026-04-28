<?php
/**
 * 복구 이미지 ↔ DB 민원 첨부파일 매칭 스크립트
 * 서버에서 1회 실행: php /var/www/html/adm/file_recover_match.php
 * 또는 브라우저: https://smtm2017.com/adm/file_recover_match.php
 * 사용 후 삭제 권장
 */
require_once '../common.php';

// CLI 또는 브라우저
$is_cli = (php_sapi_name() === 'cli');
if(!$is_cli) header('Content-Type: text/html; charset=utf-8');

$RECOVER_DIR = '/mnt/recover';
$DATA_DIR = G5_DATA_PATH . '/file';

$bo_tables = ['complain', 'complain_answer', 'complain_add', 'bbs_img', 'bbs_pdf',
              'building', 'inspection', 'approval'];

function out($msg, $is_cli) {
    echo $is_cli ? $msg . "\n" : $msg . "<br>\n";
}

out("=== 복구 이미지 매칭 스크립트 ===", $is_cli);
out("복구 경로: {$RECOVER_DIR}", $is_cli);
out("데이터 경로: {$DATA_DIR}", $is_cli);
out("", $is_cli);

// 1단계: 복구 파일 목록 + sha1 해시 맵 구성
out("[1단계] 복구 파일 sha1 해시 계산 중...", $is_cli);

$recover_files = glob($RECOVER_DIR . '/*.{jpg,jpeg,png,gif,pdf,JPG,JPEG,PNG}', GLOB_BRACE);
$recover_hash_map = []; // sha1 → 복구 파일 경로

foreach($recover_files as $rfile){
    $sha1 = sha1_file($rfile);
    $recover_hash_map[$sha1] = $rfile;
}

out("복구 파일: " . count($recover_files) . "개, 해시 계산 완료", $is_cli);
out("", $is_cli);

// 2단계: DB에서 누락된 첨부파일 조회
out("[2단계] DB 누락 파일 조회 중...", $is_cli);

$matched = 0;
$not_found = 0;
$already_exists = 0;

foreach($bo_tables as $tbl){
    $target_dir = $DATA_DIR . '/' . $tbl;
    @mkdir($target_dir, 0755, true);

    $sql = "SELECT bf_no, bf_file, bf_source, wr_id FROM g5_board_file WHERE bo_table = '{$tbl}' AND bf_file != '' ORDER BY bf_datetime DESC";
    $res = sql_query($sql);

    while($row = sql_fetch_array($res)){
        $target_path = $target_dir . '/' . $row['bf_file'];

        // 이미 존재하면 스킵
        if(file_exists($target_path)){
            $already_exists++;
            continue;
        }

        // 방법 1: bf_file에서 sha1 추출 (파일명에 포함된 경우)
        // 예: 37ab7362a6e5c4d6bd7320670dbb8a03_UE9FzQIP_5a016a969e4fed245b56939b1db316e4324dd220.jpg
        $bf_sha1 = '';
        if(preg_match('/([a-f0-9]{40})\.[a-z]+$/i', $row['bf_file'], $m)){
            $bf_sha1 = $m[1];
        }

        $found = false;

        // 방법 1: 파일명의 sha1로 매칭
        if($bf_sha1 && isset($recover_hash_map[$bf_sha1])){
            $src = $recover_hash_map[$bf_sha1];
            if(copy($src, $target_path)){
                @chmod($target_path, 0644);
                $matched++;
                $found = true;
                out("  ✅ [{$tbl}] wr_id={$row['wr_id']} → sha1 매칭: " . basename($src), $is_cli);
            }
        }

        // 방법 2: sha1 매칭 안 되면, 복구 파일의 해시와 비교
        if(!$found){
            // 모든 복구 파일 해시는 이미 $recover_hash_map에 있으므로
            // bf_file의 sha1 부분이 없는 경우 매칭 불가
            $not_found++;
            out("  ❌ [{$tbl}] wr_id={$row['wr_id']} bf_file={$row['bf_file']} → 매칭 실패", $is_cli);
        }
    }
}

out("", $is_cli);
out("=== 결과 ===", $is_cli);
out("매칭 복원: {$matched}건", $is_cli);
out("이미 존재: {$already_exists}건", $is_cli);
out("매칭 실패: {$not_found}건", $is_cli);
out("복구 파일 총: " . count($recover_files) . "개", $is_cli);
