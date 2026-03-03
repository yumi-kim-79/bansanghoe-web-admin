<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

$date = date("Y-m-d");

$sql_common = " from a_building_household as hh
                LEFT JOIN a_building_ho as ho on hh.ho_id = ho.ho_id 
                LEFT JOIN a_building as building on hh.building_id = building.building_id 
                LEFT JOIN a_building_dong as dong on hh.dong_id = dong.dong_id 
                LEFT JOIN a_post_addr as post on hh.post_id = post.post_idx ";

$sql_search = " where (1) and hh.is_del = '0' and ho.ho_status = 'Y' ";

$sql_order = " order by building.building_name asc, dong.dong_name asc, ho.ho_name asc, ho.ho_id desc ";

if($building_id_sch){
    $building_id_sch_t = implode(',', $building_id_sch);
    $sql_search .= " and ho.building_id in ({$building_id_sch_t}) ";

    foreach($building_id_sch as $key => $val){
        $qstr .= '&building_id_sch[]='.$val;
    }
}

$sql = " select hh.*, ho.ho_name, ho.ho_status, ho.ho_tenant, ho.ho_tenant_hp, ho.ho_tenant_at, building.building_name, dong.dong_name, post.post_name {$sql_common} {$sql_search} {$sql_order} ";
$res = sql_query($sql);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 시트 이름 변경
$sheet->setTitle('세대 구성원 리스트');

$sheet->getStyle('A:L')->getFont()->setSize(13);// 폰트사이즈 설정
$sheet->mergeCells('A1:L1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', '신반상회 '.$date.' 세대 구성원 리스트');
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);
$sheet->setCellValue('A3', '지역');
$sheet->setCellValue('B3', '단지명');
$sheet->setCellValue('C3', '동');
$sheet->setCellValue('D3', '호수');
$sheet->setCellValue('E3', '입주자');
$sheet->setCellValue('F3', '입주자 연락처');
$sheet->setCellValue('G3', '입주일');
$sheet->setCellValue('H3', '관계');
$sheet->setCellValue('I3', '이름');
$sheet->setCellValue('J3', '연락처');
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(40);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(20);
$sheet->getColumnDimension('G')->setWidth(25);
$sheet->getColumnDimension('H')->setWidth(25);
$sheet->getColumnDimension('I')->setWidth(25);
$sheet->getColumnDimension('J')->setWidth(25);
$sheet->getStyle("A3:J3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A3:J3")->getFont()->setSize(14)->setBold(true);

$cell = 4;
foreach($res as $row){
    //print_r2($row).'<br>';

    $sheet->setCellValue('A'.$cell, $row['post_name']);
    $sheet->setCellValue('B'.$cell, $row['building_name']);
    $sheet->setCellValue('C'.$cell, $row['dong_name']);
    $sheet->setCellValue('D'.$cell, $row['ho_name']);
    $sheet->setCellValue('E'.$cell, $row['ho_tenant']);
    $sheet->setCellValue('F'.$cell, $row['ho_tenant_hp']);
    $sheet->setCellValue('G'.$cell, $row['ho_tenant_at']);
    $sheet->setCellValue('H'.$cell, $row['hh_relationship']);
    $sheet->setCellValue('I'.$cell, $row['hh_name']);
    $sheet->setCellValue('J'.$cell, $row['hh_hp']);
    $sheet->getStyle("A".$cell.":L".$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $cell++;
}


$filename = "신반상회_세대 구성원 리스트_" . date('Ymd') . ".xlsx";
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