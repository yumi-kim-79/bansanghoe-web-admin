<?php
/**
 * complain_excel_bf.php
 * 민원(이전자료) 선택 엑셀 다운로드
 */
require_once './_common.php';

$idxList = isset($_POST['idx']) ? $_POST['idx'] : [];
if (empty($idxList)) {
    die('선택된 항목이 없습니다.');
}

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

// 추가 내용 조회
function get_bf_comments($seq) {
    $sql = "SELECT content FROM question_answer_comment WHERE question_answer = '" . intval($seq) . "' ORDER BY id asc";
    $res = sql_query($sql);
    $comments = [];
    while ($row = sql_fetch_array($res)) {
        $comments[] = $row['content'];
    }
    return implode(' / ', $comments);
}

function bf_status_name($status) {
    switch ((string)$status) {
        case "0": return "접수대기";
        case "1": return "완료";
        case "2": return "진행중";
        default:  return $status;
    }
}

function bf_register_type($type) {
    return $type == 'ADMIN' ? '관리자 접수 민원' : '앱 접수 민원';
}

$filename = '민원(이전자료)_' . date('Ymd_His') . '.xls';
$headers = ['번호','접수구분','지역','단지명','동','호수','접수날짜','민원인','연락처','작성자','민원 제목','민원 내용','민원 답변','추가 내용','담당자 직급','담당자','완료날짜','상태'];

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
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($row['question'])),
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($row['answer'])),
        str_replace(["\r\n","\r","\n"], ' ', strip_tags($comments)),
        $row['duty'],
        $row['complete_name'],
        $row['answer_date'] != '' ? date('Y-m-d', strtotime($row['answer_date'])) : '',
        bf_status_name($row['status']),
    ];
    echo implode("\t", $line) . "\n";
}
exit;
