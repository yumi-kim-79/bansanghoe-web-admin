<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

// print_r2($_REQUEST);

$ct_idx_arr = str_replace("\\", "", $ct_idx_arr);

$today = date('Y-m-d');

$months = str_pad($month, 2, "0", STR_PAD_LEFT);
$de_dates = $year.'-'.$months; // 2025-07

$month_start2 = date("Y-m-01", strtotime("$de_dates-01")); // 2025-07-01
$month_end2   = date("Y-m-t", strtotime("$de_dates-01"));  // 2025-07-31

$sql = "SELECT ch.ct_sdate as ct_sdate2, ch.ct_edate as ct_edate2, ct.*, building.is_use, building.building_name, mc.company_bank_name, mc.company_account_number, mc.company_account_name FROM a_contract_history as ch 
LEFT JOIN a_contract as ct on ch.ct_idx = ct.ct_idx
LEFT JOIN a_building as building on ct.building_id = building.building_id
LEFT JOIN a_manage_company as mc on mc.company_idx = ct.company_idx
WHERE ct.ct_idx IN ($ct_idx_arr) and ct.is_del = 0 and ct.is_temp = 0 and ct.ct_status = 0 and (ch.ct_sdate <= '{$month_end2}' and ch.ct_edate >= '{$month_start2}') GROUP BY ct.ct_idx ORDER BY ct.company_name asc, building.building_name asc, ct.ct_idx desc";


// echo $sql;
// exit;

$res = sql_query($sql);

$cell_head = ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'];

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

if (!defined('_GNUBOARD_')) exit;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle('용역업체 세금 계산서 처리');

$sheet->getStyle('A:L')->getFont()->setSize(13);// 폰트사이즈 설정
$sheet->mergeCells('A1:L1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', '신반상회 '.$date.' 세금 계산서 처리 리스트');
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);
$sheet->setCellValue('A3', '업종');
$sheet->setCellValue('B3', '업체명');
$sheet->setCellValue('C3', '현장명');
$sheet->setCellValue('D3', '비용('.$nowMonth.'월)');
// $sheet->setCellValue('E3', '계');
$sheet->setCellValue('E3', '세금계산서 처리');
$sheet->setCellValue('F3', '은행');
$sheet->setCellValue('G3', '계좌번호');
$sheet->setCellValue('H3', '예금주');
$sheet->setCellValue('I3', '특이사항');
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(40);
$sheet->getColumnDimension('C')->setWidth(30);
$sheet->getColumnDimension('D')->setWidth(15);
// $sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('E')->setWidth(30);
$sheet->getColumnDimension('F')->setWidth(20);
$sheet->getColumnDimension('G')->setWidth(25);
$sheet->getColumnDimension('H')->setWidth(25);
$sheet->getColumnDimension('I')->setWidth(40);
$sheet->getStyle("A3:I3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A3:I3")->getFont()->setSize(14)->setBold(true);


$cell = 4;

foreach($res as $row){

    //비용
    $pl_sql = "SELECT *, COUNT(*) as cnt FROM a_payment_list WHERE ct_idx = '{$row['ct_idx']}' and bill_years = '{$year}' and bill_months = '{$months}'";
    // echo $pl_sql.'<br>';
    $pl_row = sql_fetch($pl_sql);


    $contract_now_sql = "SELECT ch.*, c.ct_status, c.ct_status_year, c.ct_status_month FROM a_contract_history as ch
         LEFT JOIN a_contract as c ON ch.ct_idx = c.ct_idx
         WHERE ch.ct_idx = '{$row['ct_idx']}' and ch.ct_sdate <= '{$month_end2}' and ch.ct_edate >= '{$month_start2}' and ch.is_del = 0";
    // echo $contract_now_sql.'<br>';
    $contract_now_rows = sql_fetch($contract_now_sql);
    

    $history_price = "SELECT * FROM a_contract_price_history WHERE ch_start_date <= '{$month_end2}' and ct_idx = '{$row['ct_idx']}' ORDER BY cph_idx desc limit 0, 1";
    // echo $history_price.'<br>';
    $history_price_row = sql_fetch($history_price);

    if($pl_row['cnt'] > 0){
            
        if($pl_row['is_services']){
            //$first_price = '0 (서비스)';

            $month_price = '0 (서비스)';

        }else{

            $month_price = number_format($pl_row['payment_price']);

            
        }
    }else{

        $month_price = $contract_now_rows['cth_idx'] != '' ? number_format($history_price_row['price']) : '-';

        //echo $month_price.'<br>';
    }

    $pl_price = $month_price;


    $c_bills = "SELECT * FROM a_company_bill_list WHERE ct_idx = '{$row['ct_idx']}' and bill_years = '{$year}' and bill_months = '{$months}'";
    // echo $c_bills.'<br>';
    $c_bills_rows = sql_fetch($c_bills);
    //echo $c_bills;
    //echo $c_bills_rows['bill_dates'];

    $c_bills = "SELECT * FROM a_company_bill_list WHERE ct_idx = '{$row['ct_idx']}' and is_cancel = 0 and bill_years = '{$year}' and bill_months = '{$months}'";
    // echo $c_bills.'<br>';
    $c_bills_rows = sql_fetch($c_bills);

    $bill_memos = nl2br($c_bills_rows['bills_memo']);

    $sheet->setCellValue('A'.$cell, $row['industry_name']);
    $sheet->setCellValue('B'.$cell, $row['company_name']);
    $sheet->setCellValue('C'.$cell, $row['building_name']);
    $sheet->setCellValue('D'.$cell, $pl_price);
    //$sheet->setCellValue('E'.$cell, $pl_price);
    $sheet->setCellValue('E'.$cell, $c_bills_rows['bill_dates']);
    $sheet->setCellValue('F'.$cell, $row['company_bank_name']);
    $sheet->setCellValueExplicit('G'.$cell, $row['company_account_number'],  \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('H'.$cell, $row['company_account_name']);
    $sheet->setCellValue('I'.$cell, $bill_memos);
   
    $sheet->getStyle("A".$cell.":J".$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $cell++;
}


$filename = "신반상회_용역업체 세금 계산서 처리_" . date('Ymd') . ".xlsx";
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