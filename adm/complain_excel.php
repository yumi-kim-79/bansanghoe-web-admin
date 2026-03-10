<?php
/**
 * complain_excel.php - 민원 엑셀 다운로드
 * _common.php 의 HTML 출력을 무력화하기 위해 파일로 먼저 생성 후 전송
 */

// output buffering 시작 (common.php HTML 출력 차단)
ob_start();
require_once './_common.php';
ob_end_clean(); // common.php 가 출력한 모든 HTML 버림

$idxList = isset($_POST['idx']) ? $_POST['idx'] : [];
if (empty($idxList)) {
    die('선택된 항목이 없습니다.');
}

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

$result = sql_query($sql);
$rows = [];
while ($row = sql_fetch_array($result)) {
    $rows[] = $row;
}

// 임시 파일에 먼저 CSV 내용 작성
$tmpFile = tempnam(sys_get_temp_dir(), 'complain_excel_');
$fp = fopen($tmpFile, 'w');

// UTF-8 BOM
fwrite($fp, "\xEF\xBB\xBF");

$headers = ['번호','지역','단지명','동','호수','접수날짜','민원인','연락처','작성자','민원 제목','민원 내용','민원 답변','추가 내용','담당자 부서','담당자','완료날짜','상태'];
fputcsv($fp, $headers, "\t");

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
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($row['complain_content'])),
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($row['complain_answer'])),
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($row['complain_memo'])),
        $row['mng_department_name'],
        $row['mng_name'],
        $row['edate'],
        $row['status_name'],
    ];
    fputcsv($fp, $line, "\t");
}
fclose($fp);

$filename = '민원_' . date('Ymd_His') . '.xls';
$filesize = filesize($tmpFile);

// 헤더 전송 후 파일 출력
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . $filesize);
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Expires: 0');

readfile($tmpFile);
unlink($tmpFile);
exit;
