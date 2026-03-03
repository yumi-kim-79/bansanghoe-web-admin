<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

$sql_common = " from a_manage_company as company left join a_industry_list as industry on industry.industry_idx = company.company_industry ";

$sql_search = " where (1) and company.is_del = '0' ";

$sql_order = " order by company.company_idx desc ";

if($transaction_status == 'Y'){
    $sql_search .= " and company.transaction_status = 'Y' ";
}else{
    $sql_search .= "";
}

if($industry_idx){
    $sql_search .= " and company.company_industry = '{$industry_idx}' ";
}

if($company_idx){
    $sql_search .= " and company.company_idx = '{$company_idx}' ";
}

$sql = " select company.*, industry.industry_name {$sql_common} {$sql_search} {$sql_order}";
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
$sheet->setTitle('업체 리스트');

$sheet->getStyle('A:L')->getFont()->setSize(13);// 폰트사이즈 설정
$sheet->mergeCells('A1:L1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', '신반상회 '.$date.' 업체 리스트');
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);
$sheet->setCellValue('A3', '업종');
$sheet->setCellValue('B3', '업체명');
$sheet->setCellValue('C3', '사업자 등록번호');
$sheet->setCellValue('D3', '담당자');
$sheet->setCellValue('E3', '대표번호');
$sheet->setCellValue('F3', '연락처');
$sheet->setCellValue('G3', '입금은행');
$sheet->setCellValue('H3', '계좌번호');
$sheet->setCellValue('I3', '예금주');
$sheet->setCellValue('J3', '상태');
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(40);
$sheet->getColumnDimension('C')->setWidth(40);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(25);
$sheet->getColumnDimension('F')->setWidth(25);
$sheet->getColumnDimension('G')->setWidth(20);
$sheet->getColumnDimension('H')->setWidth(25);
$sheet->getColumnDimension('I')->setWidth(20);
$sheet->getColumnDimension('J')->setWidth(20);
$sheet->getStyle("A3:J3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A3:J3")->getFont()->setSize(14)->setBold(true);

$cell = 4;

foreach($res as $row){

    $transaction_status = $row['transaction_status'] == 'Y' ? "거래활성화" : "거래중지";
   

    $sheet->setCellValue('A'.$cell, $row['industry_name']);
    $sheet->setCellValue('B'.$cell, $row['company_name']);
    $sheet->setCellValue('C'.$cell, $row['company_number']);
    $sheet->setCellValue('D'.$cell, $row['company_mng_name']);
    $sheet->setCellValue('E'.$cell, $row['company_tel']);
    $sheet->setCellValue('F'.$cell, $row['company_mng_tel']);
    $sheet->setCellValue('G'.$cell, $row['company_bank_name']);
    $sheet->setCellValue('H'.$cell, $row['company_account_number']);
    $sheet->setCellValue('I'.$cell, $row['company_account_name']);
    $sheet->setCellValue('J'.$cell, $transaction_status);
    $sheet->getStyle("A".$cell.":J".$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $cell++;
}

$filename = "[신반상회] 업체 리스트_" . date('Ymd') . ".xlsx";
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