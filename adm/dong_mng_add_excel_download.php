<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

$dong_sql = "SELECT dong.*, building.building_name FROM a_building_dong as dong
             LEFT JOIN a_building as building ON dong.building_id = building.building_id
             WHERE dong.dong_id = '{$dong_id}'";
$dong_row = sql_fetch($dong_sql);

$building_name = $dong_row['building_name'].' '.$dong_row['dong_name'].'동';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

if (!defined('_GNUBOARD_')) exit;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$styleArray = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN, // 또는 BORDER_MEDIUM, BORDER_THICK 등
            'color' => ['argb' => '666666'],
        ],
    ],
];

$sheet->getStyle('A:AC')->getFont()->setSize(14);// 폰트사이즈 설정
$sheet->mergeCells('A1:AC1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', $building_name);
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);
$sheet->mergeCells('A2:AC2');
$sheet->setCellValue('A2', '호수는 동일한 호수가 있는 경우 덮어쓰기 됩니다.');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->mergeCells('A3:AC3');
$sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A3', '면적은 숫자만 입력(소수점 포함 4자리까지 가능) 합니다.');


$sheet->mergeCells('D5:E5');
$sheet->setCellValue('D5', '소유자');

$sheet->mergeCells('F5:H5');
$sheet->setCellValue('F5', '입주자');

$sheet->mergeCells('I5:J5');
$sheet->setCellValue('I5', '1차량');

$sheet->mergeCells('K5:L5');
$sheet->setCellValue('K5', '2차량');

$sheet->mergeCells('M5:N5');
$sheet->setCellValue('M5', '3차량');

$sheet->mergeCells('O5:Q5');
$sheet->setCellValue('O5', '1구성원');

$sheet->mergeCells('R5:T5');
$sheet->setCellValue('R5', '2구성원');

$sheet->mergeCells('U5:W5');
$sheet->setCellValue('U5', '3구성원');

$sheet->mergeCells('X5:Z5');
$sheet->setCellValue('X5', '4구성원');

$sheet->mergeCells('AA5:AC5');
$sheet->setCellValue('AA5', '5구성원');

$sheet->getStyle('A5:AC5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A5:AC5')->getFont()->setSize(14)->setBold(true);// 폰트사이즈 설정

$sheet->setCellValue('A6', '호수');
$sheet->setCellValue('B6', '면적');
$sheet->setCellValue('C6', '(입주,퇴실)');
$sheet->getColumnDimension('C')->setWidth(15);

$sheet->setCellValue('D6', '이름');
$sheet->setCellValue('E6', '연락처');
$sheet->getColumnDimension('E')->setWidth(17);

$sheet->setCellValue('F6', '이름');
$sheet->setCellValue('G6', '연락처');
$sheet->getColumnDimension('G')->setWidth(17);

$sheet->setCellValue('H6', '입주일');
$sheet->getColumnDimension('H')->setWidth(17);

$sheet->setCellValue('I6', '차종');
$sheet->getColumnDimension('I')->setWidth(15);
$sheet->setCellValue('J6', '차번');
$sheet->getColumnDimension('J')->setWidth(15);

$sheet->setCellValue('K6', '차종');
$sheet->getColumnDimension('K')->setWidth(15);
$sheet->setCellValue('L6', '차번');
$sheet->getColumnDimension('L')->setWidth(15);

$sheet->setCellValue('M6', '차종');
$sheet->getColumnDimension('M')->setWidth(15);
$sheet->setCellValue('N6', '차번');
$sheet->getColumnDimension('N')->setWidth(15);

$sheet->setCellValue('O6', '관계');
$sheet->setCellValue('P6', '이름');
$sheet->setCellValue('Q6', '연락처');
$sheet->getColumnDimension('Q')->setWidth(15);

$sheet->setCellValue('R6', '관계');
$sheet->setCellValue('S6', '이름');
$sheet->setCellValue('T6', '연락처');
$sheet->getColumnDimension('T')->setWidth(15);

$sheet->setCellValue('U6', '관계');
$sheet->setCellValue('V6', '이름');
$sheet->setCellValue('W6', '연락처');
$sheet->getColumnDimension('W')->setWidth(15);

$sheet->setCellValue('X6', '관계');
$sheet->setCellValue('Y6', '이름');
$sheet->setCellValue('Z6', '연락처');
$sheet->getColumnDimension('Z')->setWidth(15);

$sheet->setCellValue('AA6', '관계');
$sheet->setCellValue('AB6', '이름');
$sheet->setCellValue('AC6', '연락처');
$sheet->getColumnDimension('AC')->setWidth(15);

$sheet->getStyle('A6:AC50')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A6:AC6')->getFont()->setSize(14)->setBold(true);// 폰트사이즈 설정

$sheet->getStyle('A5:AC6')->applyFromArray($styleArray);

$filename = $building_name."_입주자정보_업로드_" . date('Ymd') . ".xlsx";
$encoded_filename = rawurlencode($filename);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
if (preg_match("/MSIE|Trident|Edge/", $_SERVER['HTTP_USER_AGENT'])) {
    header("Content-Disposition: attachment; filename=\"$encoded_filename\"");
} else {
    header("Content-Disposition: attachment; filename*=UTF-8''" . $encoded_filename);
}
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

exit;