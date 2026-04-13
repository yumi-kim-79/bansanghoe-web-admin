<?php
/**
 * 첨부파일 진단 도구 (임시 - 세션 체크 없음)
 * 사용 후 삭제 권장
 */
require_once '../common.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html><head><title>첨부파일 진단</title>
<style>body{font-family:sans-serif;padding:20px;font-size:13px;}table{border-collapse:collapse;margin:10px 0;}td,th{border:1px solid #ccc;padding:5px 8px;}th{background:#f4f6f9;}.ok{color:green;}.ng{color:red;}h3{margin-top:25px;}</style>
</head><body>
<h2>첨부파일 진단</h2>
<p>서버 경로: <?php echo G5_DATA_PATH; ?>/file/</p>
<p>웹 경로: /data/file/</p>
<?php
$tables = ['complain', 'complain_answer', 'complain_add', 'bbs_img', 'bbs_pdf'];

foreach($tables as $tbl){
    $dir = G5_DATA_PATH . '/file/' . $tbl;
    echo '<h3>bo_table: ' . $tbl . '</h3>';
    echo '<p>디렉토리: ' . $dir . ' → ' . (is_dir($dir) ? '<span class="ok">존재</span>' : '<span class="ng">없음</span>') . '</p>';

    $sql = "SELECT bf_no, bf_file, bf_source, wr_id, bf_datetime FROM g5_board_file WHERE bo_table = '{$tbl}' AND bf_file != '' ORDER BY bf_datetime DESC LIMIT 5";
    $res = sql_query($sql);
    $cnt = 0;

    echo '<table><tr><th>wr_id</th><th>bf_no</th><th>bf_file</th><th>bf_source</th><th>파일존재</th><th>웹경로</th><th>미리보기</th></tr>';

    while($row = sql_fetch_array($res)){
        $cnt++;
        $full_path = $dir . '/' . $row['bf_file'];
        $exists = file_exists($full_path);
        $web_path = '/data/file/' . $tbl . '/' . $row['bf_file'];

        echo '<tr>';
        echo '<td>' . $row['wr_id'] . '</td>';
        echo '<td>' . $row['bf_no'] . '</td>';
        echo '<td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;">' . htmlspecialchars($row['bf_file']) . '</td>';
        echo '<td>' . htmlspecialchars($row['bf_source']) . '</td>';
        echo '<td class="' . ($exists ? 'ok' : 'ng') . '">' . ($exists ? '✅ 존재' : '❌ 없음') . '</td>';
        echo '<td><a href="' . $web_path . '" target="_blank">' . $web_path . '</a></td>';
        echo '<td>' . ($exists ? '<img src="' . $web_path . '" style="max-width:60px;max-height:60px;">' : '-') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    if($cnt == 0) echo '<p style="color:#999;">레코드 없음</p>';
}
?>
</body></html>
