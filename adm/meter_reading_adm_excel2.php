<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

// if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
//     echo "수정 중";
//     exit;
// }

$sql = "SELECT * FROM a_building WHERE building_id = '{$building_id}'";
$row = sql_fetch($sql);




$mb_sql = "SELECT mrb.*, b.building_name, post.post_name, md.md_name, mng.mng_name FROM a_meter_building as mrb
            LEFT JOIN a_building as b on mrb.building_id = b.building_id
            LEFT JOIN a_post_addr as post on b.post_id = post.post_idx
            LEFT JOIN a_mng_department as md on mrb.mr_department = md.md_idx
            LEFT JOIN a_mng as mng on mrb.wid = mng.mng_id
           WHERE mr_idx = '{$mr_idx}'";
$mb_row = sql_fetch($mb_sql);



//전월 값
$month_de = "전월";
$bf_electro_val = 0;

//전월
$bf_years = $mb_row['mr_month'] == 1 ? $mb_row['mr_year'] - 1 : $mb_row['mr_year'];
$bf_months = $mb_row['mr_month'] == 1 ? 12 : $mb_row['mr_month'] - 1;

//전기 전월 검침 값 조회
// and total_electro != ''
// $bf_electro_meter_sql = "SELECT COUNT(*) as cnt FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$bf_years}' and mr_month = '{$bf_months}' and total_electro != '' ";
$bf_electro_meter_sql = "SELECT COUNT(*) as cnt FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$bf_years}' and mr_month = '{$bf_months}' and electro_date != '' ";
// echo $bf_electro_meter_sql.'<br>';
// exit;

$bf_electro_meter_row = sql_fetch($bf_electro_meter_sql);

// echo $bf_electro_meter_sql.'<br>';
//전월
if($bf_electro_meter_row['cnt'] > 0){

    //전월 값 가져오기
    // $bf_electro_meter_sql_total = "SELECT * FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$bf_years}' and mr_month = '{$bf_months}' and total_electro != '' ";
    $bf_electro_meter_sql_total = "SELECT * FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$bf_years}' and mr_month = '{$bf_months}' and electro_date != '' ";
    $bf_elector_meter_total_row = sql_fetch($bf_electro_meter_sql_total);

    // echo $bf_electro_meter_sql_total.'<br>';
    // print_r2($bf_elector_meter_total_row);

    $bf_electro_val = $bf_elector_meter_total_row['total_electro'] != '' ? $bf_elector_meter_total_row['total_electro'] : 0;
    

}else{
    //익월

    //익월로 계산
    $bf_months = $mb_row['mr_month'] == 1 ? 11 : $mb_row['mr_month'] - 2;

    $month_de = "익월";

    // $bf_electro_meter_sql2 = "SELECT COUNT(*) as cnt FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$bf_years}' and mr_month = '{$bf_months}' and total_electro != '' ";
    $bf_electro_meter_sql2 = "SELECT COUNT(*) as cnt FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$bf_years}' and mr_month = '{$bf_months}' and electro_date != '' ";
    // echo $bf_electro_meter_sql2.'<br>';
    // exit;
    $bf_electro_meter_row2 = sql_fetch($bf_electro_meter_sql2);

    

    if($bf_electro_meter_row2['cnt'] > 0){

        //익월의 값을 가져옵니다.
        $bf_electro_meter_sql_total = "SELECT * FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$bf_years}' and mr_month = '{$bf_months}' and electro_date != '' ";
        // echo $bf_electro_meter_sql_total.'<br>';
        $bf_elector_meter_total_row = sql_fetch($bf_electro_meter_sql_total);

        $bf_electro_val = $bf_elector_meter_total_row['total_electro'] != '' ? $bf_elector_meter_total_row['total_electro'] : 0;

    }else{
        $bf_months = $mb_row['mr_month'] == 1 ? 12 : $mb_row['mr_month'] - 1;

        $month_de = "전월";

        $bf_electro_val = 0;
    }

}


// echo $bf_electro_val.'<br>';
// exit;

//수도
$month_de2 = "전월";
$bf_water_val = 0;

//전월
$bf_years_w = $mb_row['mr_month'] == 1 ? $mb_row['mr_year'] - 1 : $mb_row['mr_year'];
$bf_months_w = $mb_row['mr_month'] == 1 ? 12 : $mb_row['mr_month'] - 1;

//전기 전월 검침 값 조회
// and total_water != ''
$bf_water_meter_sql = "SELECT COUNT(*) as cnt FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$bf_years_w}' and mr_month = '{$bf_months_w}' and water_date != '' ";

// echo $bf_water_meter_sql.'<br>';
$bf_water_meter_row = sql_fetch($bf_water_meter_sql);

 //전월
 if($bf_water_meter_row['cnt'] > 0){

    //전월 값 가져오기
    $bf_water_meter_sql_total = "SELECT * FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$bf_years_w}' and mr_month = '{$bf_months_w}' and water_date != '' ";
    // echo $bf_water_meter_sql_total.'<br>';
    $bf_water_meter_total_row = sql_fetch($bf_water_meter_sql_total);

    $bf_water_val = $bf_water_meter_total_row['total_water'] != '' ? $bf_water_meter_total_row['total_water'] : 0;

}else{
    //익월

    //익월로 계산
    $bf_months_w = $mb_row['mr_month'] == 1 ? 11 : $mb_row['mr_month'] - 2;

    $month_de2 = "익월";

    $bf_water_meter_sql2 = "SELECT COUNT(*) as cnt FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$bf_years_w}' and mr_month = '{$bf_months_w}' and water_date != '' ";
    $bf_water_meter_row2 = sql_fetch($bf_water_meter_sql2);

    if($bf_water_meter_row2['cnt'] > 0){

        //익월의 값을 가져옵니다.
        $bf_water_meter_sql_total = "SELECT * FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$bf_years_w}' and mr_month = '{$bf_months_w}' and water_date != '' ";
        $bf_water_meter_total_row = sql_fetch($bf_water_meter_sql_total);

        $bf_water_val = $bf_water_meter_total_row['total_water'] != '' ? $bf_water_meter_total_row['total_water'] : 0;

    }else{
        $bf_months_w = $mb_row['mr_month'] == 1 ? 12 : $mb_row['mr_month'] - 1;

        $month_de2 = "전월";

        $bf_water_val = 0;
    }

}

// echo $bf_water_val;
// print_r2($mb_row);
// exit;

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) {
//    print_r2($mb_row);
//     exit;
// }





use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

// if (!defined('_GNUBOARD_')) exit;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();


// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
//     print_r2($sheet);
//     // print_r2($bf_water_meter_sql);
//     exit;

// }


// 시트 이름 변경
$sheet->setTitle('검침 리스트');


$sheet->getStyle('A:L')->getFont()->setSize(13);// 폰트사이즈 설정
$sheet->mergeCells('A1:L1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', '신반상회 '.$row['building_name'].' '.$mb_row['mr_year'].'년 '.$mb_row['mr_month'].'월 검침');
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);

$sheet->mergeCells('A3:N3');
$sheet->setCellValue('A3', "메인검침");
$sheet->getStyle("A3")->getFont()->setSize(14)->setBold(true);



$sheet->setCellValue('A4', '지역');
$sheet->setCellValue('B4', '단지명');
$sheet->setCellValue('C4', '년도');
$sheet->setCellValue('D4', '월');
$sheet->setCellValue('E4', '부서');
$sheet->setCellValue('F4', '작성자');
$sheet->setCellValue('G4', '전기 검침날짜');
$sheet->setCellValue('H4', '전기 '.$month_de.'('.$bf_months.'월) 값');
$sheet->setCellValue('I4', '전기 당월값');
$sheet->setCellValue('J4', '전기 사용량(kWh)');
$sheet->setCellValue('K4', '수도 검침날짜');
$sheet->setCellValue('L4', '수도 '.$month_de2.'('.$bf_months_w.'월) 값');
$sheet->setCellValue('M4', '수도 당월값');
$sheet->setCellValue('N4', '수도 사용량(t)');
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(40);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(20);
$sheet->getColumnDimension('G')->setWidth(30);
$sheet->getColumnDimension('H')->setWidth(20);
$sheet->getColumnDimension('I')->setWidth(20);
$sheet->getColumnDimension('J')->setWidth(20);
$sheet->getColumnDimension('K')->setWidth(30);
$sheet->getColumnDimension('L')->setWidth(20);
$sheet->getColumnDimension('M')->setWidth(20);
$sheet->getColumnDimension('N')->setWidth(20);
$sheet->getStyle("A4:N4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A4:N4")->getFont()->setSize(14)->setBold(true);



$total_elec_val = $mb_row['total_electro'] ? $mb_row['total_electro'] : 0;
$total_water_val = $mb_row['total_water'] ? $mb_row['total_water'] : 0;

$electro_sum = $total_elec_val - $bf_electro_val;
$water_sum = $total_water_val - $bf_water_val;

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
//     // print_r2($sheet);
//     print_r2($bf_water_meter_sql);
//     exit;

// }

$sheet->setCellValue('A5', $mb_row['post_name']);
$sheet->setCellValue('B5', $mb_row['building_name']);
$sheet->setCellValue('C5', $mb_row['mr_year']);
$sheet->setCellValue('D5', $mb_row['mr_month']);
$sheet->setCellValue('E5', $mb_row['md_name']);
$sheet->setCellValue('F5', $mb_row['mng_name']);
$sheet->setCellValue('G5', $mb_row['electro_date']);
$sheet->setCellValue('H5', $bf_electro_val);
$sheet->setCellValue('I5', $mb_row['total_electro']);
$sheet->setCellValue('J5', $electro_sum);
$sheet->setCellValue('K5', $mb_row['water_date']);
$sheet->setCellValue('L5', $bf_water_val);
$sheet->setCellValue('M5', $mb_row['total_water']);
$sheet->setCellValue('N5', $water_sum);
$sheet->getStyle("A5:N5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A7:N7');
$sheet->setCellValue('A7', "세대 검침");
$sheet->getStyle("A7")->getFont()->setSize(14)->setBold(true);

$sheet->setCellValue('A8', '동');
$sheet->setCellValue('B8', '호수');
$sheet->setCellValue('C8', '전기 '.$month_de.'('.$bf_months.'월) 값');
$sheet->setCellValue('D8', '전기 당월 값');
$sheet->setCellValue('E8', '전기 사용량(kWh)');
$sheet->setCellValue('F8', '수도 '.$month_de2.'('.$bf_months_w.'월) 값');
$sheet->setCellValue('G8', '수도 당월 값');
$sheet->setCellValue('H8', '수도 사용량(t)');
$sheet->getStyle("A8:H8")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A8:H8")->getFont()->setSize(14)->setBold(true);


// $sql_ho = "SELECT * FROM a_building_ho WHERE building_id = '{$building_id}' and ho_status = 'Y' ORDER BY ho_name + 1 asc";
$sql_ho = "SELECT ho.*, dong.dong_id, dong.dong_name FROM a_building_ho as ho
            LEFT JOIN a_building_dong as dong ON ho.dong_id = dong.dong_id
            WHERE ho.is_del = 0 and ho.building_id = '{$building_id}' {$sql_dong} ORDER BY ho.dong_id + 1 asc, ho.ho_name + 1 asc, ho.ho_id asc";


$res_ho = sql_query($sql_ho);


if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $sql_ho.'<br>';
    // print_r2($bf_water_meter_sql);
    // exit;

}




$cell = 9;
foreach($res_ho as $row_ho){

    //전기 전월/익월 값
    $bf_sql_e = "SELECT mr.*, mb.mr_year, mb.mr_month FROM a_meter_reading as mr
                LEFT JOIN a_meter_building as mb on mr.mr_idx = mb.mr_idx
                WHERE mr.building_id = '{$building_id}' and mr.mr_type = 'electro' and mr.ho_id = '{$row_ho['ho_id']}' and mb.mr_year = '{$bf_years}' and mb.mr_month = '{$bf_months}' ";
    // echo $bf_sql.'<br>';
    $bf_electro_val = sql_fetch($bf_sql_e);

    $bf_e_val = $bf_electro_val['mr_val'] == '' ? 0 : $bf_electro_val['mr_val'];

    //전기 당월 값
    $mr_val_sql_e = "SELECT * FROM a_meter_reading WHERE ho_id = '{$row_ho['ho_id']}' and mr_idx = '{$mr_idx}' and mr_type = 'electro'";
    $mr_val_row2 = sql_fetch($mr_val_sql_e);

    // $mr_electro_val = $mr_val_row2 ? $mr_val_row2['mr_val'] : 0;
    $mr_electro_val = 0;

    if($mr_val_row2['mr_val'] != ''){
        $mr_electro_val = $mr_val_row2['mr_val'];
    }

    //합계 - 사용량
    $mr_electro_sum = $mr_electro_val - $bf_e_val;

    //수도 전월/익월 값
    $bf_sql = "SELECT mr.*, mb.mr_year, mb.mr_month FROM a_meter_reading as mr
                LEFT JOIN a_meter_building as mb on mr.mr_idx = mb.mr_idx
                WHERE mr.building_id = '{$building_id}' and mr.mr_type = 'water' and mr.ho_id = '{$row_ho['ho_id']}' and mb.mr_year = '{$bf_years_w}' and mb.mr_month = '{$bf_months_w}' ";
    $bf_water_vals = sql_fetch($bf_sql);

    $bf_w_val = $bf_water_vals['mr_val'] == '' ? 0 : $bf_water_vals['mr_val'];

    //수도 당월 검침값
    $mr_val_sql = "SELECT * FROM a_meter_reading WHERE ho_id = '{$row_ho['ho_id']}' and mr_idx = '{$mr_idx}' and mr_type = 'water'";
    // echo $mr_val_sql.'<br>';
    $mr_val_row = sql_fetch($mr_val_sql);

    $mr_water_val = 0;
    // $mr_water_val = $mr_val_row ? $mr_val_row['mr_val'] : 0;

    if($mr_val_row['mr_val'] != ''){
        $mr_water_val = $mr_val_row['mr_val'];
    }


     //합계 - 사용량
     $mr_water_sum = $mr_water_val - $bf_w_val;

    $sheet->setCellValue('A'.$cell, $row_ho['dong_name'].'동');
    $sheet->setCellValue('B'.$cell, $row_ho['ho_name'].'호');
    $sheet->setCellValue('C'.$cell, $bf_e_val); // 전기 전월,익월 값
    $sheet->setCellValue('D'.$cell, $mr_electro_val); // 전기 당월 값
    $sheet->setCellValue('E'.$cell, $mr_electro_sum); // 전기 전월,익월 - 당월
    $sheet->setCellValue('F'.$cell, $bf_w_val); // 수도 전월, 익월 값
    $sheet->setCellValue('G'.$cell, $mr_water_val); // 수도 당월 값
    $sheet->setCellValue('H'.$cell, $mr_water_sum); // 수도 전월,익월 - 당월
    $sheet->getStyle("A".$cell.":H".$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $cell++;
}

// print_r2($sheet);
// exit;


$filename = "신반상회_".$row['building_name']." ".$mb_row['mr_year']."년 ".$mb_row['mr_month']."월 검침.xlsx";
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