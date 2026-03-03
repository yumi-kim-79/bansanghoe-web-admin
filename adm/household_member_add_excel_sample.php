<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

$dong_sql = "SELECT ho.*, building.building_name, dong.dong_name FROM a_building_ho as ho
             LEFT JOIN a_building as building ON ho.building_id = building.building_id
             LEFT JOIN a_building_dong as dong ON ho.dong_id = dong.dong_id
             WHERE ho.ho_id = '{$ho_id}'";
//echo $dong_sql;
$dong_row = sql_fetch($dong_sql);

$building_name = $dong_row['building_name'].' '.$dong_row['dong_name'];

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

if (!defined('_GNUBOARD_')) exit;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->getStyle('A:I')->getFont()->setSize(13);// 폰트사이즈 설정
$sheet->mergeCells('A1:I1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', $building_name.' 세대구성원 등록');
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);
$sheet->setCellValue('A3', '관계');
$sheet->setCellValue('B3', '이름');
$sheet->setCellValue('C3', '연락처');
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(25);
$sheet->getStyle("A3:C3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$filename = $building_name."_세대구성원정보_업로드_" . date('Ymd') . ".xlsx";
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