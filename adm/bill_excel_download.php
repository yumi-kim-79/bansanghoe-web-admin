<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

//고지서 정보
$bill_info = sql_fetch("SELECT * FROM a_bill WHERE bill_id = '{$bill_id}'");

//빌딩 정보
$building_info = sql_fetch("SELECT * FROM a_building WHERE building_id = '{$bill_info['building_id']}'");

$excel_title = $building_info['building_name'].' '.$bill_info['bill_year'].'년'.$bill_info['bill_month'].'월 고지서';


//총 동수
$bill_item_sql = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' GROUP BY dong_name ORDER BY bi_idx asc";
// echo $bill_item_sql;
$bill_item_res = sql_query($bill_item_sql);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

if (!defined('_GNUBOARD_')) exit;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle($excel_title);

$sheet->getStyle('A:L')->getFont()->setSize(13);// 폰트사이즈 설정
$sheet->mergeCells('A1:L1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', $excel_title);
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);

$cell = 3;

foreach($bill_item_res as $row){

    $bill_it_list = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' and dong_name = '{$row['dong_name']}' ORDER BY bi_idx asc";
    $bill_it_list_res = sql_query($bill_it_list);

    foreach($bill_it_list_row = sql_fetch_array($bill_it_list_res)){
        $bill_opts = explode("|", $bill_it_list_row['bi_option']);

        $sheet->setCellValue('A'.$cell, $bill_it_list_row['bi_name']);

        foreach($bill_opts as $opt_row){
            $sheet->setCellValue('B'.$cell, $bill_it_list_row['bi_name']);
        }
    }
}

$filename = $excel_title.".xlsx";
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