<?php
/**
 * complain_excel_bf.php - 민원(이전자료) 엑셀 다운로드 (.xlsx)
 * idx를 콤마 구분 문자열로 받아 max_input_vars 제한 우회
 */
ob_start();
require_once './_common.php';
ob_end_clean();

// idx_str: 콤마로 구분된 문자열로 받기 (max_input_vars 1000 제한 우회)
$idx_str = isset($_POST['idx_str']) ? $_POST['idx_str'] : '';
if (empty($idx_str)) die('선택된 항목이 없습니다.');

// 정수만 추출 (보안)
$idxList = array_filter(array_map('intval', explode(',', $idx_str)));
if (empty($idxList)) die('유효한 항목이 없습니다.');

$data_rows = [];
// 500개씩 나눠서 쿼리
foreach (array_chunk($idxList, 500) as $chunk) {
    $inClause = implode(',', $chunk);
    $sql = "SELECT 
                qa.seq,
                (SELECT COUNT(*) FROM question_answer WHERE seq >= qa.seq) as row_num,
                qa.title as complain_title,
                qa.question,
                qa.answer,
                qa.answer_date,
                qa.create_date,
                qa.status,
                qa.register_type,
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

    $qr = sql_query($sql);
    while ($row = sql_fetch_array($qr)) { $data_rows[] = $row; }
}

function get_bf_comments($seq) {
    $s = "SELECT content FROM question_answer_comment WHERE question_answer = '" . intval($seq) . "' ORDER BY id asc";
    $r = sql_query($s); $c = [];
    while ($rr = sql_fetch_array($r)) { $c[] = $rr['content']; }
    return implode(' / ', $c);
}
function bf_status_name($s) {
    switch ((string)$s) { case "0": return "접수대기"; case "1": return "완료"; case "2": return "진행중"; default: return $s; }
}
function bf_register_type($t) { return $t == 'ADMIN' ? '관리자 접수 민원' : '앱 접수 민원'; }
function col_name($idx) {
    $letters = ''; $idx++;
    while ($idx > 0) { $idx--; $letters = chr(65 + ($idx % 26)) . $letters; $idx = intval($idx / 26); }
    return $letters;
}
function xe($v) { return htmlspecialchars((string)$v, ENT_XML1 | ENT_QUOTES, 'UTF-8'); }

$excel_headers = ['번호','접수구분','지역','단지명','동','호수','접수날짜','민원인','연락처','작성자','민원 제목','민원 내용','민원 답변','추가 내용','담당자 직급','담당자','완료날짜','상태'];

$rowsXml = '<row r="1">';
foreach ($excel_headers as $ci => $h) {
    $rowsXml .= '<c r="'.col_name($ci).'1" t="inlineStr"><is><t>' . xe($h) . '</t></is></c>';
}
$rowsXml .= '</row>';

foreach ($data_rows as $ri => $row) {
    $r = $ri + 2;
    $addr = explode(" ", $row['address']);
    $cells = [
        $row['row_num'], bf_register_type($row['register_type']),
        isset($addr[0]) ? $addr[0] : '', $row['building_name'],
        $row['dong'] != '' ? $row['dong'].'동' : '',
        $row['ho'] != '' ? $row['ho'].'호' : '',
        $row['create_date'] != '' ? date('Y-m-d', strtotime($row['create_date'])) : '',
        $row['rname'], $row['rhp'], $row['sbname'], $row['complain_title'],
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($row['question'])),
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($row['answer'])),
        str_replace(["\r\n","\r","\n"], ' ', strip_tags(get_bf_comments($row['seq']))),
        $row['duty'], $row['complete_name'],
        $row['answer_date'] != '' ? date('Y-m-d', strtotime($row['answer_date'])) : '',
        bf_status_name($row['status']),
    ];
    $rowsXml .= '<row r="'.$r.'">';
    foreach ($cells as $ci => $val) {
        $rowsXml .= '<c r="'.col_name($ci).$r.'" t="inlineStr"><is><t>' . xe($val) . '</t></is></c>';
    }
    $rowsXml .= '</row>';
}

$excel_filename = '민원(이전자료)_' . date('Ymd_His') . '.xlsx';
$tmpFile = tempnam(sys_get_temp_dir(), 'complain_bf_xlsx_');
$zip = new ZipArchive();
$zip->open($tmpFile, ZipArchive::OVERWRITE);
$zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
$zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
$zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="민원이전자료" sheetId="1" r:id="rId1"/></sheets></workbook>');
$zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
$zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>' . $rowsXml . '</sheetData></worksheet>');
$zip->close();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $excel_filename . '"');
header('Content-Length: ' . filesize($tmpFile));
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public'); header('Expires: 0');
readfile($tmpFile); unlink($tmpFile);
exit;