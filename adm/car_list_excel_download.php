<?php
include_once('./_common.php');

// [단지명 검색 2026-09] 대괄호 등 특수문자가 들어간 단지명도 검색되도록 검색어 원문 복원
//  gnuboard 공통 처리가 [ ] % * = 등을 지워서 '[테스트]-SM프라자' 같은 이름이 검색되지 않았다.
restore_raw_stx();

require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

$date = date("Y-m-d");

$sql_common = " from a_building_ho as ho
                left join a_post_addr as post on ho.post_id = post.post_idx
                left join a_building as building on ho.building_id = building.building_id
                left join a_building_dong as dong on ho.dong_id = dong.dong_id
                left join a_building_car as car on ho.ho_id = car.ho_id and car.is_del = 0
                 ";

$sql_search = " where (1) and ho.is_del = '0' ";
$sql_search2 = "";

if ($stx) {
    if($sfl != "car_type_list" && $sfl != "car_name_list"){
     $sql_search .= " and ( ";
    }
    switch ($sfl) {
        case 'building_name':
            $sql_search .= " (building.{$sfl} like '%{$stx}%') ";
            break;
        case 'car_type_list':
            $sql_search2 .= " and car_type_list like '%{$stx}%' ";
            break;
        case 'car_name_list':
            $sql_search2 .= " and car_name_list like '%{$stx}%' ";
            break;
    }
    if($sfl != "car_type_list" && $sfl != "car_name_list"){
        $sql_search .= " ) ";
    }
}

if($post_id){
    $sql_search .= " and ho.post_id = '{$post_id}' ";
}

// [호수 정렬 2026-09] 하이픈 호수(401-1)가 일반 호수(101)보다 앞에 오던 문제
//  기존: (ho_name REGEXP '^[0-9]+$') ASC → 하이픈 있는 건이 0 이라 전부 앞으로 밀렸다.
//  변경: 앞자리 숫자 → 하이픈 없는 것 먼저 → 뒷자리 숫자 순 (adm/dong_mng_add.php 와 동일 규칙)
$sql_order = " order by building.building_name asc, CAST(SUBSTRING_INDEX(ho.ho_name, '-', 1) AS UNSIGNED) ASC, CASE WHEN ho.ho_name REGEXP '-' THEN 1 ELSE 0 END ASC, CAST(SUBSTRING_INDEX(ho.ho_name, '-', -1) AS UNSIGNED) ASC, ho.ho_name ASC , ho.ho_id desc ";

// $sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_search2} {$sql_order} ";
$sql = " select ho.*, post.post_name, building.building_name, dong.dong_name, GROUP_CONCAT(car.car_name ORDER BY car.car_name SEPARATOR ', ') as car_name_list, GROUP_CONCAT(car.car_type ORDER BY car.car_type SEPARATOR ', ') as car_type_list {$sql_common} {$sql_search} GROUP BY ho.ho_id HAVING
car_name_list IS NOT NULL AND car_name_list != '' {$sql_search2} {$sql_order} ";

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
$sheet->setTitle('차량 리스트');

$sheet->getStyle('A:L')->getFont()->setSize(13);// 폰트사이즈 설정
$sheet->mergeCells('A1:L1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', '신반상회 '.$date.' 차량 리스트');
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);
$sheet->setCellValue('A3', '지역');
$sheet->setCellValue('B3', '단지명');
$sheet->setCellValue('C3', '동');
$sheet->setCellValue('D3', '호수');
$sheet->setCellValue('E3', '입주자');
$sheet->setCellValue('F3', '입주자 연락처');
$sheet->setCellValue('G3', '등록차량');
$sheet->setCellValue('H3', '등록차량');
$sheet->setCellValue('I3', '등록차량');
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(40);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(20);
$sheet->getColumnDimension('G')->setWidth(30);
$sheet->getColumnDimension('H')->setWidth(30);
$sheet->getColumnDimension('I')->setWidth(30);
$sheet->getStyle("A3:I3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A3:I3")->getFont()->setSize(14)->setBold(true);

$cell = 4;
foreach($res as $row){
    // print_r2($row).'<br>';

    $car_type_list = explode(", ",$row['car_type_list']);
    $car_name_list = explode(", ",$row['car_name_list']);

    $car_type1 = $car_type_list[0] != '' && $car_name_list[0] != '' ? '차종 : '.$car_type_list[0]."\n번호 : ".$car_name_list[0] : "";
    $car_type2 = $car_type_list[1] != '' && $car_name_list[1] != '' ? '차종 : '.$car_type_list[1]."\n번호 : ".$car_name_list[1] : "";
    $car_type3 = $car_type_list[2] != '' && $car_name_list[2] != '' ? '차종 : '.$car_type_list[2]."\n번호 : ".$car_name_list[2] : "";

    $sheet->setCellValue('A'.$cell, $row['post_name']);
    $sheet->setCellValue('B'.$cell, $row['building_name']);
    $sheet->setCellValue('C'.$cell, $row['dong_name']);
    $sheet->setCellValue('D'.$cell, $row['ho_name']);
    $sheet->setCellValue('E'.$cell, $row['ho_tenant']);
    $sheet->setCellValue('F'.$cell, $row['ho_tenant_hp']);
    $sheet->setCellValue('G'.$cell, $car_type1);
    $sheet->getStyle('G'.$cell)->getAlignment()->setWrapText(true);
    $sheet->setCellValue('H'.$cell, $car_type2);
    $sheet->getStyle('H'.$cell)->getAlignment()->setWrapText(true);
    $sheet->setCellValue('I'.$cell, $car_type3);
    $sheet->getStyle('I'.$cell)->getAlignment()->setWrapText(true);
    
    $sheet->getStyle("A".$cell.":I".$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A".$cell.":I".$cell)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

    $cell++;
}


$filename = "신반상회_차량 리스트_" . date('Ymd') . ".xlsx";
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