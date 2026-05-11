<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

if (!defined('_GNUBOARD_')) exit;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// ─── 입력 파라미터 검증 ─────────────────────────────────────────────
$cal_code = isset($_GET['cal_code']) ? trim($_GET['cal_code']) : '';
$year     = isset($_GET['year'])     ? (int)$_GET['year']      : (int)date('Y');
$month    = isset($_GET['month'])    ? (int)$_GET['month']     : (int)date('m');

if($cal_code === ''){
    die('cal_code 필수');
}
if($year < 2000 || $year > 2100 || $month < 1 || $month > 12){
    die('잘못된 년/월입니다.');
}

$month_pad = str_pad($month, 2, '0', STR_PAD_LEFT);
$date_prefix = $year.'-'.$month_pad;

// 카테고리명 조회
$cal_setting = sql_fetch("SELECT cal_name FROM a_calendar_setting WHERE cal_code = '{$cal_code}'");
$cal_name = $cal_setting ? $cal_setting['cal_name'] : $cal_code;

// ─── 일정 조회 ─────────────────────────────────────────────────────
$schedule_sql = "SELECT cal.*, mng.mng_name, mng.mng_department AS mng_dept_idx,
                        building.building_name
                 FROM a_calendar AS cal
                 LEFT JOIN a_mng AS mng           ON cal.mng_id = mng.mng_id
                 LEFT JOIN a_building AS building ON cal.building_id = building.building_id
                 WHERE cal.is_del = 0
                   AND cal.cal_code = '{$cal_code}'
                   AND cal.cal_date LIKE '{$date_prefix}%'
                 ORDER BY cal.cal_date ASC, cal.cal_idx DESC";

$res = sql_query($schedule_sql);

// ─── 스프레드시트 작성 ─────────────────────────────────────────────
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($cal_name);

$sheet->getStyle('A:I')->getFont()->setSize(13);
$sheet->mergeCells('A1:I1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', '신반상회 '.$year.'-'.$month_pad.' '.$cal_name);
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);

// 헤더
$sheet->setCellValue('A3', '번호');
$sheet->setCellValue('B3', '날짜');
$sheet->setCellValue('C3', '단지명');
$sheet->setCellValue('D3', '제목');
$sheet->setCellValue('E3', '작성자');
$sheet->setCellValue('F3', '담당자');
$sheet->setCellValue('G3', '처리자');
$sheet->setCellValue('H3', '처리완료');
$sheet->setCellValue('I3', '내용');

$sheet->getColumnDimension('A')->setWidth(8);
$sheet->getColumnDimension('B')->setWidth(14);
$sheet->getColumnDimension('C')->setWidth(28);
$sheet->getColumnDimension('D')->setWidth(40);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(20);
$sheet->getColumnDimension('G')->setWidth(20);
$sheet->getColumnDimension('H')->setWidth(10);
$sheet->getColumnDimension('I')->setWidth(50);

$sheet->getStyle('A3:I3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A3:I3')->getFont()->setSize(14)->setBold(true);

// 작성자/담당자/처리자 표시 헬퍼 — get_schedule.php 와 동일 규칙
function _excel_writer_label($wid){
    if($wid === 'admin' || $wid === '') return $wid === 'admin' ? '신반상회' : '';
    $info = get_manger($wid);
    if(!$info) return '';
    return trim(($info['md_name'] ?? '').' '.($info['mng_name'] ?? ''));
}

$cell = 4;
$num  = 1;
while($row = sql_fetch_array($res)){
    // 작성자: wid → admin 이면 "신반상회", 그 외 매니저 부서+이름
    $writer = _excel_writer_label($row['wid']);

    // 담당자: 부서명(mng_department) + 이름(mng_name JOIN)
    $dept_name = '';
    if(!empty($row['mng_department'])){
        if($row['mng_department'] === '-1') {
            $dept_name = '전체';
        } else {
            $dept_name = get_department_name($row['mng_department']);
        }
    }
    $manager = trim($dept_name.' '.($row['mng_name'] ?? ''));

    // 처리자: 처리완료 시 process_id 의 매니저 부서+이름
    $processor = '';
    if($row['is_process']){
        $processor = _excel_writer_label($row['process_id']);
    }

    $sheet->setCellValue('A'.$cell, $num);
    $sheet->setCellValue('B'.$cell, $row['cal_date']);
    $sheet->setCellValue('C'.$cell, $row['building_name'] ?: '-');
    $sheet->setCellValue('D'.$cell, $row['cal_title']);
    $sheet->setCellValue('E'.$cell, $writer ?: '-');
    $sheet->setCellValue('F'.$cell, $manager ?: '-');
    $sheet->setCellValue('G'.$cell, $processor ?: '-');
    $sheet->setCellValue('H'.$cell, $row['is_process'] ? '완료' : '미처리');

    // 내용: HTML 태그 제거 + 엔티티 디코드 후 셀에 입력 (에디터 HTML 저장 대비)
    $content_plain = trim(html_entity_decode(strip_tags($row['cal_content'] ?? ''), ENT_QUOTES, 'UTF-8'));
    $sheet->setCellValue('I'.$cell, $content_plain);
    $sheet->getStyle('I'.$cell)->getAlignment()->setWrapText(true);

    $sheet->getStyle('A'.$cell.':I'.$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A'.$cell.':I'.$cell)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle('D'.$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('I'.$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

    $cell++;
    $num++;
}

// 데이터 0건이면 안내 행
if($num === 1){
    $sheet->mergeCells('A4:I4');
    $sheet->setCellValue('A4', $year.'-'.$month_pad.' 에 등록된 일정이 없습니다.');
    $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// ─── 다운로드 응답 ─────────────────────────────────────────────────
$filename = '신반상회_'.$cal_name.'_'.$year.$month_pad.'.xlsx';
$encoded_filename = rawurlencode($filename);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
if (preg_match("/MSIE|Trident|Edge/", $_SERVER['HTTP_USER_AGENT'] ?? '')) {
    header("Content-Disposition: attachment; filename=\"$encoded_filename\"");
} else {
    header("Content-Disposition: attachment; filename*=UTF-8''" . $encoded_filename);
}
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
