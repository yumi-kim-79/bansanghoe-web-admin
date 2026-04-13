<?php
/**
 * 첨부파일 진단 도구 (관리자 전용)
 * 사용 후 삭제 권장
 */
$sub_menu = "100100";
require_once './_common.php';
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = "첨부파일 진단";
require_once './admin.head.php';

// 최근 첨부파일 10건 조회
$tables = ['complain', 'complain_answer', 'complain_add', 'bbs_img', 'bbs_pdf'];

echo '<h2>첨부파일 진단</h2>';
echo '<p>서버 경로: ' . G5_DATA_PATH . '/file/</p>';
echo '<p>웹 경로: /data/file/</p>';

foreach($tables as $tbl){
    echo '<h3 style="margin-top:20px;">bo_table: ' . $tbl . '</h3>';

    $dir = G5_DATA_PATH . '/file/' . $tbl;
    echo '<p>디렉토리: ' . $dir . ' → ' . (is_dir($dir) ? '<span style="color:green">존재</span>' : '<span style="color:red">없음</span>') . '</p>';

    $sql = "SELECT bf_no, bf_file, bf_source, wr_id, bf_datetime FROM g5_board_file WHERE bo_table = '{$tbl}' AND bf_file != '' ORDER BY bf_datetime DESC LIMIT 5";
    $res = sql_query($sql);
    $cnt = 0;

    echo '<table border="1" cellpadding="5" style="border-collapse:collapse;font-size:12px;">';
    echo '<tr><th>wr_id</th><th>bf_no</th><th>bf_file</th><th>bf_source</th><th>파일존재</th><th>웹경로</th><th>미리보기</th></tr>';

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
        echo '<td style="color:' . ($exists ? 'green' : 'red') . ';">' . ($exists ? '✅ 존재' : '❌ 없음') . '</td>';
        echo '<td><a href="' . $web_path . '" target="_blank">' . $web_path . '</a></td>';
        echo '<td>' . ($exists ? '<img src="' . $web_path . '" style="max-width:60px;max-height:60px;">' : '-') . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    if($cnt == 0){
        echo '<p style="color:#999;">레코드 없음</p>';
    }
}

require_once './admin.tail.php';
