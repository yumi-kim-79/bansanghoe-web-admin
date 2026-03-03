<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

// echo $ho_status;
// print_r2($_REQUEST);


$date = date("Y-m-d");

$sql_common = " from a_building_ho as ho 
                left join a_building_dong as dong on ho.dong_id = dong.dong_id
                left join a_building as building on ho.building_id = building.building_id 
                left join a_post_addr as post on ho.post_id = post.post_idx ";

$sql_search = " where (1) and ho.is_del = '0' and building.is_use = 1 ";

$sql_order = " order by ho.ho_id desc ";

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_point':
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_level':
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'ho_name':
            $sql_search .= " (ho.{$sfl} like '%{$stx}%') ";
            break;
        case 'building_name':
            $sql_search .= " (building.{$sfl} like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " (ho.{$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($ho_tenant_at){
    $sql_search .= " and ho.ho_tenant_at = '{$ho_tenant_at}' ";

    $qstr .= '&ho_tenant_at='.$ho_tenant_at;
}


if($ho_status){
    $sql_search .= " and ho.ho_status = '{$ho_status}' ";

    $qstr .= '&ho_status='.$ho_status;
}


if($post_id){
    $sql_search .= " and ho.post_id = '{$post_id}' ";

    $qstr .= '&post_id='.$post_id;

    $sql_building = "SELECT * FROM a_building WHERE post_id = '{$post_id}' and is_del = 0";
    $res_building = sql_query($sql_building);
}

if($building_id){
    $sql_search .= " and ho.building_id = '{$building_id}' ";

    $qstr .= '&building_id='.$building_id;

    $sql_dong = "SELECT * FROM a_building_dong WHERE building_id = '{$building_id}' and is_del = 0";
    $res_dong = sql_query($sql_dong);
}

if($dong_id){
    $sql_search .= " and ho.dong_id = '{$dong_id}' ";

    $qstr .= '&dong_id='.$dong_id;
}

$sql = " select ho.*, building.building_name, dong.dong_name, post.post_name {$sql_common} {$sql_search} {$sql_order} ";
$res = sql_query($sql);
// echo $sql;
// exit;

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
$sheet->setTitle('세대관리 리스트');

$sheet->getStyle('A:L')->getFont()->setSize(13);// 폰트사이즈 설정
$sheet->mergeCells('A1:L1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', '신반상회 '.$date.' 세대관리 리스트');
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);
$sheet->setCellValue('A3', '지역');
$sheet->setCellValue('B3', '단지명');
$sheet->setCellValue('C3', '동');
$sheet->setCellValue('D3', '호수');
$sheet->setCellValue('E3', '소유자');
$sheet->setCellValue('F3', '소유자 연락처');
$sheet->setCellValue('G3', '입주자');
$sheet->setCellValue('H3', '입주자 연락처');
$sheet->setCellValue('I3', '입주일');
$sheet->setCellValue('J3', '등록차량 수');
$sheet->setCellValue('K3', '세대구성원 수');
$sheet->setCellValue('L3', '상태');
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(40);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(20);
$sheet->getColumnDimension('G')->setWidth(25);
$sheet->getColumnDimension('H')->setWidth(25);
$sheet->getColumnDimension('I')->setWidth(25);
$sheet->getColumnDimension('J')->setWidth(15);
$sheet->getColumnDimension('K')->setWidth(20);
$sheet->getColumnDimension('L')->setWidth(15);
$sheet->getStyle("A3:L3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A3:L3")->getFont()->setSize(14)->setBold(true);


$cell = 4;
foreach($res as $row){
    //print_r2($row).'<br>';

    //차량 수
    $car_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_car WHERE ho_id = '{$row['ho_id']}' and is_del = 0");

    //세대구성원 수
    $hh_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_household WHERE ho_id = '{$row['ho_id']}' and is_del = 0");

    //상태
    $status = $row['ho_status'] == 'Y' ? '입주' : '퇴실';

    $sheet->setCellValue('A'.$cell, $row['post_name']);
    $sheet->setCellValue('B'.$cell, $row['building_name']);
    $sheet->setCellValue('C'.$cell, $row['dong_name']);
    $sheet->setCellValue('D'.$cell, $row['ho_name']);
    $sheet->setCellValue('E'.$cell, $row['ho_owner']);
    $sheet->setCellValue('F'.$cell, $row['ho_owner_hp']);
    $sheet->setCellValue('G'.$cell, $row['ho_tenant']);
    $sheet->setCellValue('H'.$cell, $row['ho_tenant_hp']);
    $sheet->setCellValue('I'.$cell, $row['ho_tenant_at']);
    $sheet->setCellValue('J'.$cell, $car_cnt['cnt']);
    $sheet->setCellValue('K'.$cell, $hh_cnt['cnt']);
    $sheet->setCellValue('L'.$cell, $status);
    $sheet->getStyle("A".$cell.":L".$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $cell++;
}



$filename = "신반상회_세대관리 리스트_" .date("Ymd").".xlsx";
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