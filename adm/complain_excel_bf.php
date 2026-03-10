<?php
/**
 * complain_excel_bf.php
 * 민원(이전자료) 선택 엑셀 다운로드
 * - POST로 idx[] 배열(question_answer.seq)을 받아 해당 민원들을 엑셀로 출력
 */
require_once './_common.php';
auth_check_menu($auth, "500200", 'r');

$idxList = isset($_POST['idx']) ? $_POST['idx'] : [];
if (empty($idxList)) {
    die('선택된 항목이 없습니다.');
}

// 정수형 변환 (SQL Injection 방지)
$idxList = array_map('intval', $idxList);
$inClause = implode(',', $idxList);

$sql = "SELECT 
            qa.seq,
            qa.title as complain_title,
            qa.question,
            qa.answer,
            qa.answer_date,
            qa.create_date,
            qa.status,
            qa.register_type,
            qa.field_comment,
            a1.duty,
            concat(a1.username, '(', a1.nick_name, ')') as complete_name,
            concat(a3.username, '(', a3.nick_name, ')') as sbname,
            e.name as building_name,
            e.address,
            h.dong,
            h.ho,
            u.name as rname,
            u.contact as rhp
        FROM question_answer qa
        LEFT JOIN admin a1 ON qa.admin = a1.seq
        LEFT JOIN admin a3 ON qa.create_admin = a3.seq
        LEFT JOIN estate e ON qa.estate = e.seq
        LEFT JOIN house h ON qa.house = h.seq
        LEFT JOIN user u ON h.tenant = u.seq
        WHERE qa.seq IN ({$inClause})
        ORDER BY qa.seq DESC";

// 추가 내용 조회용 함수
function get_bf_comments($seq) {
    $sql = "SELECT content FROM question_answer_comment WHERE question_answer = '{$seq}' ORDER BY id asc";
    $res = sql_query($sql);
    $comments = [];
    while ($row = sql_fetch_array($res)) {
        $comments[] = $row['content'];
    }
    return implode(' / ', $comments);
}

$result = sql_query($sql);
$rows = [];
while ($row = sql_fetch_array($result)) {
    $rows[] = $row;
}

// ===== 상태 변환 헬퍼 =====
function bf_status_name($status) {
    switch ($status) {
        case "0": return "접수대기";
        case "1": return "완료";
        case "2": return "진행중";
        default:  return $status;
    }
}

// ===== 접수구분 변환 =====
function bf_register_type($type) {
    return $type == 'ADMIN' ? '관리자 접수 민원' : '앱 접수 민원';
}

$filename = '민원(이전자료)_' . date('Ymd_His') . '.xlsx';

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
    $sheet->setTitle('민원이전자료');

    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F75B6']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ];

    $headers = ['번호','접수구분','지역','단지명','동','호수','접수날짜','민원인','연락처','작성자','민원 제목','민원 내용','민원 답변','추가 내용','담당자 직급','담당자','완료날짜','상태'];
    foreach ($headers as $col => $header) {
        $cell = $sheet->getCellByColumnAndRow($col + 1, 1);
        $cell->setValue($header);
        $sheet->getStyleByColumnAndRow($col + 1, 1)->applyFromArray($headerStyle);
        $sheet->getColumnDimensionByColumn($col + 1)->setAutoSize(true);
    }

    foreach ($rows as $i => $row) {
        $r = $i + 2;
        $addr = explode(" ", $row['address']);
        $region = isset($addr[0]) ? $addr[0] : '';
        $comments = get_bf_comments($row['seq']);

        $sheet->getCellByColumnAndRow(1, $r)->setValue($row['seq']);
        $sheet->getCellByColumnAndRow(2, $r)->setValue(bf_register_type($row['register_type']));
        $sheet->getCellByColumnAndRow(3, $r)->setValue($region);
        $sheet->getCellByColumnAndRow(4, $r)->setValue($row['building_name']);
        $sheet->getCellByColumnAndRow(5, $r)->setValue($row['dong'] != '' ? $row['dong'].'동' : '');
        $sheet->getCellByColumnAndRow(6, $r)->setValue($row['ho'] != '' ? $row['ho'].'호' : '');
        $sheet->getCellByColumnAndRow(7, $r)->setValue($row['create_date'] != '' ? date('Y-m-d', strtotime($row['create_date'])) : '');
        $sheet->getCellByColumnAndRow(8, $r)->setValue($row['rname']);
        $sheet->getCellByColumnAndRow(9, $r)->setValue($row['rhp']);
        $sheet->getCellByColumnAndRow(10, $r)->setValue($row['sbname']);
        $sheet->getCellByColumnAndRow(11, $r)->setValue($row['complain_title']);
        $sheet->getCellByColumnAndRow(12, $r)->setValue($row['question']);
        $sheet->getCellByColumnAndRow(13, $r)->setValue($row['answer']);
        $sheet->getCellByColumnAndRow(14, $r)->setValue($comments);
        $sheet->getCellByColumnAndRow(15, $r)->setValue($row['duty']);
        $sheet->getCellByColumnAndRow(16, $r)->setValue($row['complete_name']);
        $sheet->getCellByColumnAndRow(17, $r)->setValue($row['answer_date'] != '' ? date('Y-m-d', strtotime($row['answer_date'])) : '');
        $sheet->getCellByColumnAndRow(18, $r)->setValue(bf_status_name($row['status']));
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

} else {
    // ===== xls(TSV) fallback =====
    $filename = '민원(이전자료)_' . date('Ymd_His') . '.xls';

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
    header('Cache-Control: max-age=0');

    echo "\xEF\xBB\xBF";

    $headers = ['번호','접수구분','지역','단지명','동','호수','접수날짜','민원인','연락처','작성자','민원 제목','민원 내용','민원 답변','추가 내용','담당자 직급','담당자','완료날짜','상태'];
    echo implode("\t", $headers) . "\n";

    foreach ($rows as $row) {
        $addr = explode(" ", $row['address']);
        $region = isset($addr[0]) ? $addr[0] : '';
        $comments = get_bf_comments($row['seq']);

        $line = [
            $row['seq'],
            bf_register_type($row['register_type']),
            $region,
            $row['building_name'],
            $row['dong'] != '' ? $row['dong'].'동' : '',
            $row['ho'] != '' ? $row['ho'].'호' : '',
            $row['create_date'] != '' ? date('Y-m-d', strtotime($row['create_date'])) : '',
            $row['rname'],
            $row['rhp'],
            $row['sbname'],
            $row['complain_title'],
            str_replace(["\r\n","\r","\n"], ' ', $row['question']),
            str_replace(["\r\n","\r","\n"], ' ', $row['answer']),
            str_replace(["\r\n","\r","\n"], ' ', $comments),
            $row['duty'],
            $row['complete_name'],
            $row['answer_date'] != '' ? date('Y-m-d', strtotime($row['answer_date'])) : '',
            bf_status_name($row['status']),
        ];
        echo implode("\t", $line) . "\n";
    }
}
exit;
