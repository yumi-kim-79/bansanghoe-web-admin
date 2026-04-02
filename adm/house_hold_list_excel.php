<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

$date = date("Y-m-d");

$sql_common = " from a_building_ho as ho
                left join a_building_dong as dong on ho.dong_id = dong.dong_id
                left join a_building as building on ho.building_id = building.building_id
                left join a_post_addr as post on ho.post_id = post.post_idx ";

$sql_search = " where (1) and ho.is_del = '0' and building.is_use = 1 ";

// 1차 검색: 전체/단지명
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'all':
            $sql_search .= " (building.building_name like '%{$stx}%'
                OR ho.ho_owner like '%{$stx}%'
                OR ho.ho_owner_hp like '%{$stx}%'
                OR ho.ho_tenant like '%{$stx}%'
                OR ho.ho_tenant_hp like '%{$stx}%'
                OR ho.ho_name like '%{$stx}%'
                OR EXISTS (SELECT 1 FROM a_building_car as car WHERE car.ho_id = ho.ho_id AND car.is_del = 0 AND car.car_name like '%{$stx}%')
            ) ";
            break;
        case 'building_name':
            $sql_search .= " (building.building_name like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " (building.building_name like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

// 2차 검색: 소유자명/연락처/입주자명/연락처/호수/차량번호 통합
if ($stx2) {
    $sql_search .= " and (
        ho.ho_owner like '%{$stx2}%'
        OR ho.ho_owner_hp like '%{$stx2}%'
        OR ho.ho_tenant like '%{$stx2}%'
        OR ho.ho_tenant_hp like '%{$stx2}%'
        OR ho.ho_name like '%{$stx2}%'
        OR EXISTS (SELECT 1 FROM a_building_car as car WHERE car.ho_id = ho.ho_id AND car.is_del = 0 AND car.car_name like '%{$stx2}%')
    ) ";
}

if($ho_tenant_at){
    $sql_search .= " and ho.ho_tenant_at = '{$ho_tenant_at}' ";
}

if($ho_status){
    $sql_search .= " and ho.ho_status = '{$ho_status}' ";
}

if($post_id){
    $sql_search .= " and ho.post_id = '{$post_id}' ";
}

if($building_id){
    $sql_search .= " and ho.building_id = '{$building_id}' ";
}

if($dong_id){
    $sql_search .= " and ho.dong_id = '{$dong_id}' ";
}

$sql_order = " order by building.building_name asc, dong.dong_name + 0 asc, (ho.ho_name REGEXP '^[0-9]+$') ASC, CAST(ho.ho_name AS UNSIGNED), ho.ho_name ASC, ho.ho_id desc ";

$sql = " select ho.*, building.building_name, dong.dong_name, post.post_name {$sql_common} {$sql_search} {$sql_order} ";
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
$sheet->setTitle('세대관리 리스트');

$sheet->getStyle('A:N')->getFont()->setSize(13);
$sheet->mergeCells('A1:N1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', '신반상회 '.$date.' 세대관리 리스트');
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);

// 헤더 (세대관리 화면 컬럼 순서)
$sheet->setCellValue('A3', '번호');
$sheet->setCellValue('B3', '지역');
$sheet->setCellValue('C3', '단지명');
$sheet->setCellValue('D3', '동');
$sheet->setCellValue('E3', '호수');
$sheet->setCellValue('F3', '면적(㎡)');
$sheet->setCellValue('G3', '소유자');
$sheet->setCellValue('H3', '소유자 연락처');
$sheet->setCellValue('I3', '입주자');
$sheet->setCellValue('J3', '입주자 연락처');
$sheet->setCellValue('K3', '입주일');
$sheet->setCellValue('L3', '등록차량');
$sheet->setCellValue('M3', '세대구성원');
$sheet->setCellValue('N3', '상태');

$sheet->getColumnDimension('A')->setWidth(10);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(30);
$sheet->getColumnDimension('D')->setWidth(10);
$sheet->getColumnDimension('E')->setWidth(10);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(15);
$sheet->getColumnDimension('H')->setWidth(20);
$sheet->getColumnDimension('I')->setWidth(15);
$sheet->getColumnDimension('J')->setWidth(20);
$sheet->getColumnDimension('K')->setWidth(15);
$sheet->getColumnDimension('L')->setWidth(25);
$sheet->getColumnDimension('M')->setWidth(25);
$sheet->getColumnDimension('N')->setWidth(10);

$sheet->getStyle("A3:N3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A3:N3")->getFont()->setSize(14)->setBold(true);

// 총 건수 (번호 역순용)
$total_rows = [];
foreach($res as $row){ $total_rows[] = $row; }
$total_count = count($total_rows);

$cell = 4;
$num = 1;
foreach($total_rows as $row){

    // 차량 목록 (줄바꿈)
    $car_list_res = sql_query("SELECT car_type, car_name FROM a_building_car WHERE ho_id = '{$row['ho_id']}' and is_del = 0 and car_name != '' ORDER BY car_id asc");
    $car_texts = [];
    while($car_item = sql_fetch_array($car_list_res)){
        $car_text = '';
        if($car_item['car_type']) $car_text .= $car_item['car_type'].' ';
        $car_text .= $car_item['car_name'];
        $car_texts[] = $car_text;
    }

    // 세대구성원 목록 (줄바꿈)
    $hh_list_res = sql_query("SELECT hh_relationship, hh_name FROM a_building_household WHERE ho_id = '{$row['ho_id']}' and is_del = 0 and hh_name != '' ORDER BY hh_id asc");
    $hh_texts = [];
    while($hh_item = sql_fetch_array($hh_list_res)){
        $hh_text = '';
        if($hh_item['hh_relationship']) $hh_text .= '['.$hh_item['hh_relationship'].'] ';
        $hh_text .= $hh_item['hh_name'];
        $hh_texts[] = $hh_text;
    }

    // 상태
    $status = $row['ho_status'] == 'Y' ? '입주' : '퇴실';
    $is_out = $row['ho_status'] == 'N';

    $sheet->setCellValue('A'.$cell, $total_count - $num + 1);
    $sheet->setCellValue('B'.$cell, $row['post_name']);
    $sheet->setCellValue('C'.$cell, $row['building_name']);
    $sheet->setCellValue('D'.$cell, $row['dong_name'].'동');
    $sheet->setCellValue('E'.$cell, $row['ho_name'].'호');
    $sheet->setCellValue('F'.$cell, $row['ho_size'] ? number_format($row['ho_size'], 4) : '-');
    $sheet->setCellValue('G'.$cell, $row['ho_owner']);
    $sheet->setCellValue('H'.$cell, $row['ho_owner_hp']);
    $sheet->setCellValue('I'.$cell, $is_out ? '-' : $row['ho_tenant']);
    $sheet->setCellValue('J'.$cell, $is_out ? '-' : $row['ho_tenant_hp']);
    $sheet->setCellValue('K'.$cell, $is_out ? '-' : $row['ho_tenant_at']);

    // 등록차량: 줄바꿈으로 나열
    if($is_out){
        $sheet->setCellValue('L'.$cell, '-');
    } else {
        $sheet->setCellValue('L'.$cell, count($car_texts) > 0 ? implode("\n", $car_texts) : '-');
        $sheet->getStyle('L'.$cell)->getAlignment()->setWrapText(true);
    }

    // 세대구성원: 줄바꿈으로 나열
    if($is_out){
        $sheet->setCellValue('M'.$cell, '-');
    } else {
        $sheet->setCellValue('M'.$cell, count($hh_texts) > 0 ? implode("\n", $hh_texts) : '-');
        $sheet->getStyle('M'.$cell)->getAlignment()->setWrapText(true);
    }

    $sheet->setCellValue('N'.$cell, $status);
    $sheet->getStyle("A".$cell.":N".$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A".$cell.":N".$cell)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

    $cell++;
    $num++;
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
