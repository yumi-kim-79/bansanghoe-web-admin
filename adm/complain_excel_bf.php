<?php
/**
 * complain_excel_bf.php - 민원(이전자료) 엑셀 다운로드 (.xlsx)
 */
ob_start();
require_once './_common.php';
ob_end_clean();

$idxList = isset($_POST['idx']) ? $_POST['idx'] : [];
if (empty($idxList)) die('선택된 항목이 없습니다.');

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

$result = sql_query($sql);
$rows = [];
while ($row = sql_fetch_array($result)) {
    $rows[] = $row;
}

function get_bf_comments($seq) {
    $sql = "SELECT content FROM question_answer_comment WHERE question_answer = '" . intval($seq) . "' ORDER BY id asc";
    $res = sql_query($sql);
    $comments = [];
    while ($r = sql_fetch_array($res)) { $comments[] = $r['content']; }
    return implode(' / ', $comments);
}

function bf_status_name($s) {
    switch ((string)$s) {
        case "0": return "접수대기";
        case "1": return "완료";
        case "2": return "진행중";
        default:  return $s;
    }
}

function bf_register_type($t) {
    return $t == 'ADMIN' ? '관리자 접수 민원' : '앱 접수 민원';
}

function col_name($idx) {
    $letters = ''; $idx++;
    while ($idx > 0) {
        $idx--;
        $letters = chr(65 + ($idx % 26)) . $letters;
        $idx = intval($idx / 26);
    }
    return $letters;
}

function xe($v) {
    return htmlspecialchars((string)$v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

$headers = ['번호','접수구분','지역','단지명','동','호수','접수날짜','민원인','연락처','작성자','민원 제목','민원 내용','민원 답변','추가 내용','담당자 직급','담당자','완료날짜','상태'];

// 헤더행 XML
$rowsXml = '<row r="1">';
foreach ($headers as $ci => $h) {
    $col = col_name($ci) . '1';
    $rowsXml .= '<c r="'.$col.'" t="inlineStr"><is><t>' . xe($h) . '</t></is></c>';
}
$rowsXml .= '</row>';

// 데이터행 XML
foreach ($rows as $ri => $row) {
    $r = $ri + 2;
    $addr = explode(" ", $row['address']);
    $region = isset($addr[0]) ? $addr[0] : '';
    $comments = get_bf_comments($row['seq']);

    $cells = [
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
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($row['question'])),
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($row['answer'])),
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($comments)),
        $row['duty'],
        $row['complete_name'],
        $row['answer_date'] != '' ? date('Y-m-d', strtotime($row['answer_date'])) : '',
        bf_status_name($row['status']),
    ];

    $rowsXml .= '<row r="'.$r.'">';
    foreach ($cells as $ci => $val) {
        $col = col_name($ci) . $r;
        $rowsXml .= '<c r="'.$col.'" t="inlineStr"><is><t>' . xe($val) . '</t></is></c>';
    }
    $rowsXml .= '</row>';
}

// xlsx zip 생성
$filename = '민원(이전자료)_' . date('Ymd_His') . '.xlsx';
$tmpFile = tempnam(sys_get_temp_dir(), 'complain_bf_xlsx_');

$zip = new ZipArchive();
$zip->open($tmpFile, ZipArchive::OVERWRITE);

$zip->addFromString('[Content_Types].xml',
'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>');

$zip->addFromString('_rels/.rels',
'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

$zip->addFromString('xl/workbook.xml',
'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="민원이전자료" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>');

$zip->addFromString('xl/_rels/workbook.xml.rels',
'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>');

$zip->addFromString('xl/worksheets/sheet1.xml',
'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetData>' . $rowsXml . '</sheetData>
</worksheet>');

$zip->close();

$filesize = filesize($tmpFile);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . $filesize);
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Expires: 0');

readfile($tmpFile);
unlink($tmpFile);
exit;
