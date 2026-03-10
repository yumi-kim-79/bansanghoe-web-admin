<?php
/**
 * complain_excel.php
 * 민원 선택 엑셀 다운로드
 * - POST로 idx[] 배열을 받아 해당 민원들을 엑셀로 출력
 */
require_once './_common.php';
auth_check_menu($auth, "500100", 'r');

$idxList = isset($_POST['idx']) ? $_POST['idx'] : [];
if (empty($idxList)) {
    die('선택된 항목이 없습니다.');
}

// 정수형 변환 (SQL Injection 방지)
$idxList = array_map('intval', $idxList);
$inClause = implode(',', $idxList);

$sql = "SELECT 
            complain.complain_idx,
            post.post_name,
            building.building_name,
            dong.dong_name,
            ho.ho_name,
            complain.wdate,
            complain.complain_name,
            complain.complain_hp,
            complain.wname,
            complain.complain_title,
            complain.complain_content,
            complain.complain_answer,
            complain.complain_memo,
            dept.md_name as mng_department_name,
            mb.mb_name as mng_name,
            complain.edate,
            cs.cs_name as status_name,
            complain.complain_type,
            complain.register_type
        FROM a_online_complain as complain
        LEFT JOIN a_post_addr as post ON complain.post_id = post.post_idx
        LEFT JOIN a_building as building ON complain.building_id = building.building_id
        LEFT JOIN a_building_dong as dong ON complain.dong_id = dong.dong_id
        LEFT JOIN a_building_ho as ho ON complain.ho_id = ho.ho_id
        LEFT JOIN a_complain_status as cs ON complain.complain_status = cs.cs_code
        LEFT JOIN g5_member as mb ON complain.mng_id = mb.mb_id
        LEFT JOIN a_mng_department as dept ON complain.mng_department = dept.md_idx
        WHERE complain.complain_idx IN ({$inClause})
        ORDER BY complain.complain_idx DESC";

$result = sql_query($sql);
$rows = [];
while ($row = sql_fetch_array($result)) {
    $rows[] = $row;
}

// ===== CSV 방식으로 엑셀 출력 (라이브러리 불필요) =====
$filename = '민원_' . date('Ymd_His') . '.xlsx';

// PhpSpreadsheet 사용 가능 여부 확인
$useSpreadsheet = false;
$spreadsheetPaths = [
    G5_PATH . '/vendor/autoload.php',
    dirname(G5_PATH) . '/vendor/autoload.php',
    $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',
];
foreach ($spreadsheetPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            $useSpreadsheet = true;
            break;
        }
    }
}

if ($useSpreadsheet) {
    // ===== PhpSpreadsheet 방식 =====
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Style\Alignment;
    use PhpOffice\PhpSpreadsheet\Style\Fill;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('민원목록');

    // 헤더 스타일
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F75B6']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ];

    $headers = ['번호','지역','단지명','동','호수','접수날짜','민원인','연락처','작성자','민원 제목','민원 내용','민원 답변','추가 내용','담당자 부서','담당자','완료날짜','상태'];
    foreach ($headers as $col => $header) {
        $cell = $sheet->getCellByColumnAndRow($col + 1, 1);
        $cell->setValue($header);
        $sheet->getStyleByColumnAndRow($col + 1, 1)->applyFromArray($headerStyle);
        $sheet->getColumnDimensionByColumn($col + 1)->setAutoSize(true);
    }

    foreach ($rows as $i => $row) {
        $r = $i + 2;
        $sheet->getCellByColumnAndRow(1, $r)->setValue($row['complain_idx']);
        $sheet->getCellByColumnAndRow(2, $r)->setValue($row['post_name']);
        $sheet->getCellByColumnAndRow(3, $r)->setValue($row['building_name']);
        $sheet->getCellByColumnAndRow(4, $r)->setValue($row['dong_name'] != '' ? $row['dong_name'].'동' : '');
        $sheet->getCellByColumnAndRow(5, $r)->setValue($row['ho_name'] != '' ? $row['ho_name'].'호' : '');
        $sheet->getCellByColumnAndRow(6, $r)->setValue($row['wdate']);
        $sheet->getCellByColumnAndRow(7, $r)->setValue($row['complain_name']);
        $sheet->getCellByColumnAndRow(8, $r)->setValue($row['complain_hp']);
        $sheet->getCellByColumnAndRow(9, $r)->setValue($row['wname']);
        $sheet->getCellByColumnAndRow(10, $r)->setValue($row['complain_title']);
        $sheet->getCellByColumnAndRow(11, $r)->setValue($row['complain_content']);
        $sheet->getCellByColumnAndRow(12, $r)->setValue($row['complain_answer']);
        $sheet->getCellByColumnAndRow(13, $r)->setValue($row['complain_memo']);
        $sheet->getCellByColumnAndRow(14, $r)->setValue($row['mng_department_name']);
        $sheet->getCellByColumnAndRow(15, $r)->setValue($row['mng_name']);
        $sheet->getCellByColumnAndRow(16, $r)->setValue($row['edate']);
        $sheet->getCellByColumnAndRow(17, $r)->setValue($row['status_name']);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

} else {
    // ===== CSV → xls 방식 (라이브러리 없을 때 fallback) =====
    $filename = '민원_' . date('Ymd_His') . '.xls';

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
    header('Cache-Control: max-age=0');

    // UTF-8 BOM
    echo "\xEF\xBB\xBF";

    $headers = ['번호','지역','단지명','동','호수','접수날짜','민원인','연락처','작성자','민원 제목','민원 내용','민원 답변','추가 내용','담당자 부서','담당자','완료날짜','상태'];
    echo implode("\t", $headers) . "\n";

    foreach ($rows as $row) {
        $line = [
            $row['complain_idx'],
            $row['post_name'],
            $row['building_name'],
            $row['dong_name'] != '' ? $row['dong_name'].'동' : '',
            $row['ho_name'] != '' ? $row['ho_name'].'호' : '',
            $row['wdate'],
            $row['complain_name'],
            $row['complain_hp'],
            $row['wname'],
            $row['complain_title'],
            str_replace(["\r\n","\r","\n"], ' ', $row['complain_content']),
            str_replace(["\r\n","\r","\n"], ' ', $row['complain_answer']),
            str_replace(["\r\n","\r","\n"], ' ', $row['complain_memo']),
            $row['mng_department_name'],
            $row['mng_name'],
            $row['edate'],
            $row['status_name'],
        ];
        echo implode("\t", $line) . "\n";
    }
}
exit;
