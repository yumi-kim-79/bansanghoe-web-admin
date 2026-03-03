<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

// if($_SERVER['REMOTE_ADDR'] != ADMIN_IP) {
//     alert('수정 진행중! 관리자만 접근 가능합니다.');
//     exit;
// }

$type_t = $type == 'electro' ? '전기' : '수도';

$building_row = sql_fetch("SELECT * FROM a_building WHERE building_id = '{$building_id}' and is_del = 0");

$building_name = $building_row['building_name'] != '' ? $building_row['building_name'] : '';
$building_names = preg_replace('/[\\:\\/\\?\\*\\[\\]]/', '', $building_name);

//and ho.ho_status = 'Y' 
$ho_sql = "SELECT ho.*, dong.dong_name FROM a_building_ho as ho
            LEFT JOIN a_building_dong as dong on ho.dong_id = dong.dong_id
            WHERE ho.is_del = 0 and ho.building_id = '{$building_id}'";
// echo $ho_sql.'<br>';


// print_r2($building_row);
// exit;
$res = sql_query($ho_sql);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

// if (!defined('_GNUBOARD_')) exit;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();




$sheet->setTitle($building_names.' 검침');

// print_r2($building_row);
// print_r2($sheet);
// exit;

$sheet->getStyle('A:L')->getFont()->setSize(13);// 폰트사이즈 설정
$sheet->mergeCells('A1:L1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', $building_names.' 검침 업로드');
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);

$sheet->setCellValue('K2', '검침날짜');
$sheet->setCellValue('L2', date('Y-m-d'));
$sheet->getStyle('K2')->getFont()->setBold(true);
$sheet->getColumnDimension('K')->setWidth(15);
$sheet->getColumnDimension('L')->setWidth(15);

$sheet->setCellValue('A3', '동');
$sheet->setCellValue('B3', '호수');
$sheet->setCellValue('C3', $type_t.' 검침 값');
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(30);
$sheet->getStyle("A3:C3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A3:C3")->getFont()->setSize(14)->setBold(true);



$cell = 4;
foreach($res as $row){
    $sheet->setCellValue('A'.$cell, $row['dong_name']);
    $sheet->setCellValue('B'.$cell, $row['ho_name']);
    $sheet->setCellValue('C'.$cell, '검침값입력');
    $sheet->getStyle("A".$cell.":C".$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $cell++;
}



$filename = $building_row['building_name']." 검침 업로드 샘플_" . date('Ym') . ".xlsx";
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