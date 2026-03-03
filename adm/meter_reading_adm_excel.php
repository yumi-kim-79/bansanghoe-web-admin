<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

if($_SERVER['REMOTE_ADDR'] != "59.16.155.80"){
    echo "작업 중입니다.";
    exit;
}

$sql_common = " from a_meter_building as mt_b
                left join a_building as b on mt_b.building_id = b.building_id 
                left join a_post_addr as post on b.post_id = post.post_idx
                left join a_mng_department as dept on mt_b.mr_department = dept.md_idx
                left join a_mng as mng on mt_b.wid = mng.mng_id ";

$sql_search = " where (1) and mt_b.is_del = 0 ";

$sql_order = " order by mt_b.mr_idx desc ";

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
        case 'mng_name':
            $sql_search .= " (mng.{$sfl} like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($mr_year){
    $sql_search .= " and mt_b.mr_year = '{$mr_year}' ";

    $qstr .= '&mr_year='.$mr_year;
}

if($mr_month){
    $sql_search .= " and mt_b.mr_month = '{$mr_month}' ";

    $qstr .= '&mr_month='.$mr_month;
}

if($building_name){
    $sql_search .= " and b.building_name like '%{$building_name}%' ";

    $qstr .= '&building_name='.$building_name;
}

$sql = " select mt_b.*, b.building_name, post.post_name, dept.md_name, mng.mng_name {$sql_common} {$sql_search} {$sql_search2} {$sql_order} ";
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
$sheet->setTitle('검침 리스트');

$sheet->getStyle('A:L')->getFont()->setSize(13);// 폰트사이즈 설정
$sheet->mergeCells('A1:L1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', '신반상회 '.$date.' 검침 리스트');
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);
$sheet->setCellValue('A3', '지역');
$sheet->setCellValue('B3', '단지명');
$sheet->setCellValue('C3', '년도');
$sheet->setCellValue('D3', '월');
$sheet->setCellValue('E3', '부서');
$sheet->setCellValue('F3', '작성자');
$sheet->setCellValue('G3', '전기 검침날짜');
$sheet->setCellValue('H3', '수도 검침날짜');
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(40);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(20);
$sheet->getColumnDimension('G')->setWidth(40);
$sheet->getColumnDimension('H')->setWidth(40);
$sheet->getStyle("A3:H3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A3:H3")->getFont()->setSize(14)->setBold(true);

$cell = 4;
foreach($res as $row){

    $sheet->setCellValue('A'.$cell, $row['post_name']);
    $sheet->setCellValue('B'.$cell, $row['building_name']);
    $sheet->setCellValue('C'.$cell, $row['mr_year']);
    $sheet->setCellValue('D'.$cell, $row['mr_month']);
    $sheet->setCellValue('E'.$cell, $row['md_name']);
    $sheet->setCellValue('F'.$cell, $row['mng_name']);
    $sheet->setCellValue('G'.$cell, $row['electro_date']);
    $sheet->setCellValue('H'.$cell, $row['water_date']);
    $sheet->getStyle("A".$cell.":H".$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $cell++;
}

$filename = "[신반상회] 검침 리스트_" . date('Ymd') . ".xlsx";
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