<?php
/**
 * complain_excel.php
 * 민원 선택 엑셀 다운로드
 */
require_once './_common.php';

// POST 데이터 확인
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

$filename = '민원_' . date('Ymd_His') . '.xls';
$headers = ['번호','지역','단지명','동','호수','접수날짜','민원인','연락처','작성자','민원 제목','민원 내용','민원 답변','추가 내용','담당자 부서','담당자','완료날짜','상태'];

// 출력 버퍼 초기화 (헤더 전송 전 HTML 출력 방지)
if (ob_get_length()) ob_end_clean();

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// UTF-8 BOM
echo "\xEF\xBB\xBF";
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
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($row['complain_content'])),
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($row['complain_answer'])),
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($row['complain_memo'])),
        $row['mng_department_name'],
        $row['mng_name'],
        $row['edate'],
        $row['status_name'],
    ];
    echo implode("\t", $line) . "\n";
}
exit;
