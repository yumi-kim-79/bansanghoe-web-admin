<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

$date = date("Y-m-d");

$sql_common = " from a_building as building left join a_post_addr as post on building.post_id = post.post_idx ";

if($type == 'Y'){
    $sql_search = " where (1) and building.is_del = '0' and building.is_use = 1 ";
    //$sql_search2 = " and building.is_use = 1 ";
    
}else{
    $sql_search = " where (1) and building.is_del = '0' and building.is_use = 0 ";
    //$sql_search2 = " and building.is_use = 0 ";
}

$sql_order = " order by building.building_id desc ";

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_point':
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_level':
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'mb_tel':
        case 'pr_name':
            $sql_search .= " (par.{$sfl} like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " (building.{$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($post_id){
    $sql_search .= " and building.post_id = '{$post_id}' ";

    $qstr .= '&post_id='.$post_id;
}

$sql = " select building.*, post.post_name {$sql_common} {$sql_search} {$sql_order} ";
$res = sql_query($sql);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

if (!defined('_GNUBOARD_')) exit;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 시트 이름 변경
$sheet->setTitle('단지 리스트');

$sheet->getStyle('A:L')->getFont()->setSize(13);// 폰트사이즈 설정
$sheet->mergeCells('A1:L1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', '신반상회 '.$date.' 단지 리스트');
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);
$sheet->setCellValue('A3', '지역');
$sheet->setCellValue('B3', '단지명');
$sheet->setCellValue('C3', '주소');
$sheet->setCellValue('D3', '운영여부');
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(40);
$sheet->getColumnDimension('C')->setWidth(50);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getStyle("A3:D3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A3:D3")->getFont()->setSize(14)->setBold(true);

$cell = 4;
foreach($res as $row){

    $addr = $row['building_addr'];

    if($row['building_addr2']){
        $addr .= ' '.$row['building_addr2'];
    }

    $is_use = $row['is_use'] == 1 ? '운영' : '해지';

    $sheet->setCellValue('A'.$cell, $row['post_name']);
    $sheet->setCellValue('B'.$cell, $row['building_name']);
    $sheet->setCellValue('C'.$cell, $addr);
    $sheet->setCellValue('D'.$cell, $is_use);

    $sheet->getStyle("A".$cell.":D".$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $cell++;
}

$filename = "신반상회_단지 리스트_" . date('Ymd') . ".xlsx";
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