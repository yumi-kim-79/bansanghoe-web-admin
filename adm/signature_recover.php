<?php
/**
 * 서명 이미지 복원 스크립트 (1회성)
 * a_signature 테이블의 signature_data(base64) → PNG 파일 재생성
 * 사용 후 삭제 권장
 */
require_once '../common.php';

header('Content-Type: text/html; charset=utf-8');

$file_path = G5_DATA_PATH.'/file/approval';
@mkdir($file_path, 0755, true);
@chmod($file_path, 0755);

echo "<h2>서명 이미지 복원</h2>";
echo "<p>저장 경로: {$file_path}</p>";
echo "<p>디렉토리 존재: " . (is_dir($file_path) ? '<b style="color:green">YES</b>' : '<b style="color:red">NO</b>') . "</p>";

$sql = "SELECT sg_idx, mb_id, signature_data, fil_name FROM a_signature WHERE fil_name != '' ORDER BY sg_idx DESC";
$res = sql_query($sql);

$recovered = 0;
$skipped = 0;
$failed = 0;

echo "<table border='1' cellpadding='4' style='border-collapse:collapse;font-size:12px;margin-top:10px;'>";
echo "<tr><th>sg_idx</th><th>mb_id</th><th>fil_name</th><th>파일존재</th><th>복원결과</th></tr>";

while($row = sql_fetch_array($res)){
    $full_path = $file_path . '/' . $row['fil_name'];
    $exists = file_exists($full_path);

    if($exists){
        $skipped++;
        echo "<tr><td>{$row['sg_idx']}</td><td>{$row['mb_id']}</td><td>{$row['fil_name']}</td><td style='color:green'>✅존재</td><td>스킵</td></tr>";
        continue;
    }

    // base64 → PNG 복원
    if(!empty($row['signature_data'])){
        $encoded = explode(",", $row['signature_data']);
        $decoded = base64_decode(end($encoded));

        if($decoded && strlen($decoded) > 100){
            if(file_put_contents($full_path, $decoded)){
                @chmod($full_path, 0644);
                $recovered++;
                echo "<tr><td>{$row['sg_idx']}</td><td>{$row['mb_id']}</td><td>{$row['fil_name']}</td><td style='color:red'>❌없음</td><td style='color:blue'>✅복원됨</td></tr>";
            } else {
                $failed++;
                echo "<tr><td>{$row['sg_idx']}</td><td>{$row['mb_id']}</td><td>{$row['fil_name']}</td><td style='color:red'>❌없음</td><td style='color:red'>❌쓰기실패</td></tr>";
            }
        } else {
            $failed++;
            echo "<tr><td>{$row['sg_idx']}</td><td>{$row['mb_id']}</td><td>{$row['fil_name']}</td><td style='color:red'>❌없음</td><td style='color:orange'>⚠️base64 비어있음</td></tr>";
        }
    } else {
        $failed++;
        echo "<tr><td>{$row['sg_idx']}</td><td>{$row['mb_id']}</td><td>{$row['fil_name']}</td><td style='color:red'>❌없음</td><td style='color:orange'>⚠️data 없음</td></tr>";
    }
}

echo "</table>";
echo "<h3>결과: 복원 {$recovered}건 / 스킵 {$skipped}건 / 실패 {$failed}건</h3>";
