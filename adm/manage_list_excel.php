<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

$sql_common = " from a_mng_team as mng
                left join a_post_addr as post on mng.post_id = post.post_idx
                left join a_building as building on mng.build_id = building.building_id
                left join a_building_dong as dong on mng.dong_id = dong.dong_id
                left join a_building_ho as ho on mng.ho_id = ho.ho_id 
                left join a_mng_team_grade as grade on mng.mt_grade = grade.gr_id ";

$sql_search = " where (1) and mng.is_del = '0' ";

$sql_order = " order by mng.mt_id desc ";

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_point':
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_level':
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'building_name':
            $sql_search .= " (building.{$sfl} like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " (mng.{$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($post_id){
    $sql_search .= " and mng.post_id = '{$post_id}' ";

    $qstr .= '&post_id='.$post_id;

    $sql_building = "SELECT * FROM a_building WHERE post_id = '{$post_id}' and is_del = 0";
    $res_building = sql_query($sql_building);
}

if($building_id){
    $sql_search .= " and mng.build_id = '{$building_id}' ";

    $qstr .= '&building_id='.$building_id;

    $sql_dong = "SELECT * FROM a_building_dong WHERE building_id = '{$building_id}' and is_del = 0";
    $res_dong = sql_query($sql_dong);
}

if($dong_id){
    $sql_search .= " and mng.dong_id = '{$dong_id}' ";

    $qstr .= '&dong_id='.$dong_id;
}

$sql = " select mng.*, post.post_name, building.building_name, dong.dong_name, ho.ho_name, grade.gr_name {$sql_common} {$sql_search} {$sql_order}";
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
$sheet->setTitle('관리단 리스트');

$sheet->getStyle('A:L')->getFont()->setSize(13);// 폰트사이즈 설정
$sheet->mergeCells('A1:L1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', '신반상회 '.$date.' 관리단 리스트');
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);
$sheet->setCellValue('A3', '지역');
$sheet->setCellValue('B3', '단지명');
$sheet->setCellValue('C3', '동');
$sheet->setCellValue('D3', '호수');
$sheet->setCellValue('E3', '구분');
$sheet->setCellValue('F3', '이름');
$sheet->setCellValue('G3', '연락처');
$sheet->setCellValue('H3', '직책');
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(40);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(20);
$sheet->getColumnDimension('G')->setWidth(20);
$sheet->getColumnDimension('H')->setWidth(25);
$sheet->getStyle("A3:H3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A3:H3")->getFont()->setSize(14)->setBold(true);

$cell = 4;

foreach($res as $row){

    $mt_type = $row['mt_type'] == "IN" ? "입주민" : "외부인";

    $sheet->setCellValue('A'.$cell, $row['post_name']);
    $sheet->setCellValue('B'.$cell, $row['building_name']);
    $sheet->setCellValue('C'.$cell, $row['dong_name']);
    $sheet->setCellValue('D'.$cell, $row['ho_name']);
    $sheet->setCellValue('E'.$cell, $mt_type);
    $sheet->setCellValue('F'.$cell, $row['mt_name']);
    $sheet->setCellValue('G'.$cell, $row['mt_hp']);
    $sheet->setCellValue('H'.$cell, $row['gr_name']);
    $sheet->getStyle("A".$cell.":H".$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $cell++;
}

$filename = "신반상회_관리단 리스트_" . date('Ymd') . ".xlsx";
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