<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

$date = date("Y-m-d");

$sql_common = " from a_building_visit_car as vc 
                left join a_building as building on vc.building_id = building.building_id 
                left join a_post_addr as post on building.post_id = post.post_idx
                left join a_building_dong as dong on vc.dong_id = dong.dong_id
                left join a_building_ho as ho on vc.ho_id = ho.ho_id
                ";


$sql_search = " where (1) and vc.is_del = 0 ";


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
            $sql_search .= " (vc.{$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if($status){
    
    if($status == "N"){
        $sql_search .= " and vc.out_status = '{$status}' AND DATE_ADD(vc.visit_date, INTERVAL (vc.visit_day - 1) DAY) >= '{$today}' ";
    }else{
        $sql_search .= " and vc.out_status = '{$status}' OR ( vc.out_status = 'N' and DATE_ADD(vc.visit_date, INTERVAL (vc.visit_day - 1) DAY) < '{$today}' ) ";
    }

    $qstr .= '&status='.$status;
}

if($visit_date != "" && $end_date == ""){
    $sql_search .= " and DATE_ADD(vc.visit_date, INTERVAL (vc.visit_day - 1) DAY) >= '{$visit_date}' ";

    $qstr .= '&visit_date='.$visit_date;
}else if($visit_date == "" && $end_date != ""){
    $sql_search .= " and visit_date <= '{$end_date}' ";

    $qstr .= '&end_date='.$end_date;

}else if($visit_date != "" && $end_date != ""){
    $sql_search .= " and visit_date <= '{$end_date}' and DATE_ADD(vc.visit_date, INTERVAL (vc.visit_day - 1) DAY) >= '{$visit_date}' ";

    $qstr .= '&visit_date='.$visit_date.'&end_date='.$end_date;
}

if($post_id){
    $sql_search .= " and building.post_id = '{$post_id}' ";

    $qstr .= '&post_id='.$post_id;
}

if($building_name){
    $sql_search .= " and building.building_name like '%{$building_name}%' ";

    $qstr .= '&building_name='.$building_name;
}

if($dong_id){
    $sql_search .= " and vc.dong_id = '{$dong_id}' ";

    $qstr .= '&dong_id='.$dong_id;
}

if($ho_id){
    $sql_search .= " and vc.ho_id = '{$ho_id}' ";

    $qstr .= '&ho_id='.$ho_id;
}

$sql_order = " order by vc.visit_date desc, vc.car_id desc ";

$sql = " select vc.*, building.post_id, building.building_name, post.post_name, dong.dong_name, ho.ho_name, DATE_ADD(vc.visit_date, INTERVAL (vc.visit_day - 1) DAY) as end_day {$sql_common} {$sql_search} {$sql_search2} {$sql_order} ";
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
$sheet->setTitle('방문 차량 리스트');

$sheet->getStyle('A:L')->getFont()->setSize(13);// 폰트사이즈 설정
$sheet->mergeCells('A1:L1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', '신반상회 '.$date.' 방문 차량 리스트');
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);
$sheet->setCellValue('A3', '지역');
$sheet->setCellValue('B3', '단지명');
$sheet->setCellValue('C3', '동');
$sheet->setCellValue('D3', '호수');
$sheet->setCellValue('E3', '방문기간');
$sheet->setCellValue('F3', '차량');
$sheet->setCellValue('G3', '연락처');
$sheet->setCellValue('H3', '상태');
$sheet->setCellValue('I3', '출차시간');
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

    //방문기간
    $visit_days = $row['visit_day'] - 1;
    $endDayT = $visit_days > 0 ? ' ~ '.$row['end_day'] : '';
    $visit_date = $row['visit_date'].''.$endDayT;

    //차량정보
    $car_info = "차종 : ".$row['visit_car_name']."\n번호 : ".$row['visit_car_number'];
    
    //방문상태
    $dates = date("Y-m-d");

    if($row['out_status'] == 'N'){
        
        if($row['end_day'] < $dates){
            $visit_status = "출차";
        }else{
            $visit_status = "방문전";
        }
    }else{
        $visit_status = "출차";
    }

    //출차시간
    if($row['out_status'] == 'N'){
        if($row['end_day'] < $dates){
            $out_date = date("Y.m.d", strtotime($row['end_day']));
        }else{
            $out_date = "-";
        }
    }else{
        $out_date = date("Y.m.d H:i", strtotime($row['out_at']));
    }

    $sheet->setCellValue('A'.$cell, $row['post_name']);
    $sheet->setCellValue('B'.$cell, $row['building_name']);
    $sheet->setCellValue('C'.$cell, $row['dong_name']);
    $sheet->setCellValue('D'.$cell, $row['ho_name']);
    $sheet->setCellValue('E'.$cell, $visit_date);
    $sheet->setCellValue('F'.$cell, $car_info);
    $sheet->getStyle('F'.$cell)->getAlignment()->setWrapText(true);
    $sheet->setCellValue('G'.$cell, $row['visit_hp']);
    $sheet->setCellValue('H'.$cell, $visit_status);
    $sheet->setCellValue('I'.$cell, $out_date);

    $sheet->getStyle("A".$cell.":I".$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A".$cell.":I".$cell)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

    $cell++;
}

$filename = "신반상회_방문 차량 리스트_" . date('Ymd') . ".xlsx";
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