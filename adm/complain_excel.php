<?php
/**
 * complain_excel.php - 민원 엑셀 다운로드 (.xlsx)
 * idx를 콤마 구분 문자열로 받아 max_input_vars 제한 우회
 */
ob_start();
require_once './_common.php';
ob_end_clean();

$idx_str = isset($_POST['idx_str']) ? $_POST['idx_str'] : '';
if (empty($idx_str)) die('선택된 항목이 없습니다.');

$idxList = array_filter(array_map('intval', explode(',', $idx_str)));
if (empty($idxList)) die('유효한 항목이 없습니다.');

$data_rows = [];
foreach (array_chunk($idxList, 500) as $chunk) {
    $inClause = implode(',', $chunk);
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
                cs.cs_name as status_name
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

    $qr = sql_query($sql);
    while ($row = sql_fetch_array($qr)) { $data_rows[] = $row; }
}

function col_name($idx) {
    $letters = ''; $idx++;
    while ($idx > 0) { $idx--; $letters = chr(65 + ($idx % 26)) . $letters; $idx = intval($idx / 26); }
    return $letters;
}
function xe($v) { return htmlspecialchars((string)$v, ENT_XML1 | ENT_QUOTES, 'UTF-8'); }

$excel_headers = ['번호','지역','단지명','동','호수','접수날짜','민원인','연락처','작성자','민원 제목','민원 내용','민원 답변','추가 내용','담당자 부서','담당자','완료날짜','상태'];

$rowsXml = '<row r="1">';
foreach ($excel_headers as $ci => $h) {
    $rowsXml .= '<c r="'.col_name($ci).'1" t="inlineStr"><is><t>' . xe($h) . '</t></is></c>';
}
$rowsXml .= '</row>';

foreach ($data_rows as $ri => $row) {
    $r = $ri + 2;
    $cells = [
        $row['complain_idx'], $row['post_name'], $row['building_name'],
        $row['dong_name'] != '' ? $row['dong_name'].'동' : '',
        $row['ho_name'] != '' ? $row['ho_name'].'호' : '',
        $row['wdate'], $row['complain_name'], $row['complain_hp'], $row['wname'],
        $row['complain_title'],
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($row['complain_content'])),
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($row['complain_answer'])),
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($row['complain_memo'])),
        $row['mng_department_name'], $row['mng_name'], $row['edate'], $row['status_name'],
    ];
    $rowsXml .= '<row r="'.$r.'">';
    foreach ($cells as $ci => $val) {
        $rowsXml .= '<c r="'.col_name($ci).$r.'" t="inlineStr"><is><t>' . xe($val) . '</t></is></c>';
    }
    $rowsXml .= '</row>';
}

$excel_filename = '민원_' . date('Ymd_His') . '.xlsx';
$tmpFile = tempnam(sys_get_temp_dir(), 'complain_xlsx_');
$zip = new ZipArchive();
$zip->open($tmpFile, ZipArchive::OVERWRITE);
$zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
$zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
$zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="민원목록" sheetId="1" r:id="rId1"/></sheets></workbook>');
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
