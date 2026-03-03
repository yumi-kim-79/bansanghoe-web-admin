<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

// print_r2($_REQUEST);

$year = intval($_GET['year']);
$month = intval($_GET['month']);
$viewAll = isset($_GET['viewAll']) && $_GET['viewAll'] == 1;

$range = $viewAll ? 12 : 3; // 전체보기는 12개월, 일반은 기준 ±1개월
$startOffset = $viewAll ? 0 : -1; // 전체보기는 0부터 시작 (1월부터), 일반은 기준월 -1로 시작

//월 시작일, 마지막일
$base_year = $year;
$base_month = $month;

$month_start = date("Y-m-01", strtotime("$base_year-$base_month-01")); // 2025-07-01

// echo $month_start.'<br>';
$start_date = date('Y-m-d',strtotime($month_start."-1 month")); 
$end_date = date('Y-m-t',strtotime($month_start."+1 month")); 

// $sql_where = " where (1) and contract.is_del = '0' ";
$sql_where = " where (1) and ct.is_del = '0' and ch.ct_sdate <= '{$end_date}' and ch.ct_edate >= '{$start_date}' ";

if($transactionStatusValue){
    $sql_where .= " ";
}else{
    $sql_where .= " and ct.ct_status = '0' ";
}


if($industry_idx_sch){
    $industry_idx_sch_t = "'".implode("','", $industry_idx_sch)."'";
    $sql_where .= " and ct.industry_idx IN ({$industry_idx_sch_t}) ";
}

if($company_idx_sch){
    $company_idx_sch_t = "'".implode("','", $company_idx_sch)."'";
    $sql_where .= " and ct.company_idx IN ({$company_idx_sch_t}) ";
}

if($building_id_sch){
    $building_id_sch_t = "'".implode("','", $building_id_sch)."'";
    $sql_where .= " and ct.building_id IN ({$building_id_sch_t}) ";
}

// if($ptIdxValue){

//     $sql_where .= " and company_bill.payment_type = '{$ptIdxValue}' ";
// }

// if($paymentStatusSch){
//     $sql_where .= " and IFNULL(payment_list.payment_status, 1) = '{$paymentStatusSch}' ";
// }

// if($billStatusSch){
//     $sql_where .= " and IFNULL(bill_list.bill_statusm, 1) = '{$billStatusSch}' ";
// }

// if($btIdxSch){
//     $sql_where .= " and bill_list.bill_type = '{$btIdxSch}' ";
// }

// $sql = "SELECT 
//         ct.*, 
//         building.is_use,
//         building.building_name, 
//         company_bill.payment_type,
//         IFNULL(bill_list.bill_statusm, 1) as bs, bill_list.bill_type,
//         IFNULL(payment_list.payment_status, 1) as ps
//         FROM a_contract_history as ch
//         LEFT JOIN a_contract as ct on ch.ct_idx = ct.ct_idx 
//         LEFT JOIN a_building as building on ct.building_id = building.building_id
//         LEFT JOIN a_manage_company as manage_company on ct.company_idx = manage_company.company_idx
//         LEFT JOIN a_contract_company_bill as company_bill on ct.ct_idx = company_bill.ct_idx
//         LEFT JOIN a_company_bill_list as bill_list on ct.ct_idx = bill_list.ct_idx
//         LEFT JOIN a_payment_list as payment_list on ct.ct_idx = payment_list.ct_idx
//         {$sql_where} GROUP BY ct.ct_idx 
//         order by ct.is_temp desc, building.building_name asc, ct.company_name asc, ct.ct_idx desc";

$sql = "SELECT 
        ct.*, 
        building.is_use,
        building.building_name, 
        company_bill.payment_type,
        indus.industry_name as ids_name,
        IFNULL(bill_list.bill_statusm, 1) as bs, bill_list.bill_type,
        IFNULL(payment_list.payment_status, 1) as ps
        FROM a_contract_history as ch
        LEFT JOIN a_contract as ct on ch.ct_idx = ct.ct_idx 
        LEFT JOIN a_building as building on ct.building_id = building.building_id
        LEFT JOIN a_manage_company as manage_company on ct.company_idx = manage_company.company_idx
        LEFT JOIN a_contract_company_bill as company_bill on ct.ct_idx = company_bill.ct_idx
        LEFT JOIN a_company_bill_list as bill_list on ct.ct_idx = bill_list.ct_idx
        LEFT JOIN a_payment_list as payment_list on ct.ct_idx = payment_list.ct_idx
        LEFT JOIN a_industry_list as indus on ct.industry_idx = indus.industry_idx
        {$sql_where} {$sql_where2} GROUP BY ct.ct_idx 
        order by ct.is_temp desc, ct.company_name asc, building.building_name asc, ct.ct_idx desc";


if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $sql.'<br>';
    // exit;
}
// print_r2($_GET);
// echo $sql.'<br>';
// // echo $transaction_status.'aaa<br>';
// exit;
$res = sql_query($sql);


$ct_arr = array();
foreach ($res as $idx => $row) {


    $ct_arr[$idx]['ct_idx'] = $row['ct_idx'];  //계약
    $ct_arr[$idx]['company_idx'] = $row['company_idx'];  //업체
    $ct_arr[$idx]['company_name'] = $row['company_name'];  //업체명
    $ct_arr[$idx]['industry_name'] = $row['industry_name'];  //업종
    $ct_arr[$idx]['building_name'] = $row['building_name'];  //단지명
    $ct_arr[$idx]['is_temp'] = $row['is_temp'] ? 'temp' : '';
 
    $data_total = 0;

    for ($i = 0; $i < $range; $i++) {

        $offset = $startOffset + $i;

        // 기준이 되는 달을 설정하고, offset을 적용하여 날짜를 계산
        $baseMonth = $viewAll ? 1 : $month; // 전체보기일 경우 1월로 고정, 그 외에는 현재 월 사용
        $date = (new DateTime())->setDate($year, $baseMonth, 1)->modify("{$offset} month");

        $y = $date->format('Y');
        $m = $date->format('n'); // 1~12로 월 계산
        $months = str_pad($m, 2, "0", STR_PAD_LEFT); // 월 앞자리 0 붙이기
        $dates = $y.'-'.$months.'-01';

        $dates2 = $y.'-'.$months;


        $month_start2 = date("Y-m-01", strtotime("$y-$months-01")); // 2025-07-01
        $month_end2   = date("Y-m-t", strtotime("$y-$months-01"));  // 2025-07-31
       
        $ct_arr[$idx]['data'][$i]['year'] = $y;  //년도
        $ct_arr[$idx]['data'][$i]['month'] = $m;  //년도
        $ct_arr[$idx]['data'][$i]['month_start2'] = $month_start2;  //시작 기준 날짜
        $ct_arr[$idx]['data'][$i]['month_end2'] = $month_end2;  //종료 기준 날짜

        //기간내에 계약이 존재하면 클래스로 색상
        $contract_now_sql = "SELECT ch.*, c.ct_status, c.ct_status_year, c.ct_status_month FROM a_contract_history as ch
                         LEFT JOIN a_contract as c ON ch.ct_idx = c.ct_idx
                         WHERE ch.ct_idx = '{$row['ct_idx']}' and ch.ct_sdate <= '{$month_end2}' and ch.ct_edate >= '{$month_start2}' and ch.is_del = 0";
        // echo $contract_now_sql.'<br>';
        $contract_now_rows = sql_fetch($contract_now_sql);

    //    echo $contract_now_rows['cth_idx'].'<br>';

        $ct_arr[$idx]['data'][$i]['classes'] = $contract_now_rows['cth_idx'] != '' ? '' : 'not_contract';  // 클래스로 계약기간내인지

        
        //계약기간 내면 클릭 활성화
        $ct_arr[$idx]['data'][$i]['clicks'] = $contract_now_rows['cth_idx'] != '' ? 'yes' : 'no';
       
        //계약해지라면
        if($contract_now_rows['ct_status'] == '1'){
            
            $nowGo = $year."-".$months.'-01'; //현재 년월

            $months2 = str_pad($contract_now_rows['ct_status_month'], 2, "0", STR_PAD_LEFT); // 월 앞자리 0 붙이기
            $noGo = $contract_now_rows['ct_status_year'].'-'.$months2.'-01'; //계약해지 년월
            
            if($noGo < $nowGo){
                $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                $ct_arr[$idx]['data'][$i]['clicks'] = 'no';
            }
            
        }


       
        

        //월별 금액 가져오기
        // $payment_list_now = "SELECT payment_status, payment_price, payment_date, is_services, COUNT(*) as cnt FROM a_payment_list
        // WHERE is_cancel = 0 and  company_idx = '{$row['company_idx']}' and ct_idx = '{$row['ct_idx']}' and bill_years = '{$year}' and bill_months = '{$months}'";
        // if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
        // // echo $payment_list_now.'<br>';

        // }
        // $payment_list_now_row = sql_fetch($payment_list_now);

        // $contract_history_price = "SELECT * FROM a_contract_price_history WHERE ct_idx = '{$row['ct_idx']}'  and (ch_start_date <= '{$month_end2}') ORDER BY cph_idx desc limit 0, 1";
        // $contract_history_price_row = sql_fetch($contract_history_price);

        // //$first_price = '-';
        // if($payment_list_now_row['cnt'] > 0){
            
        //     if($payment_list_now_row['is_services']){
        //         //$first_price = '0 (서비스)';

        //         $ct_arr[$idx]['data'][$i]['first_price'] = '0 (서비스)';

        //     }else{

        //         $ct_arr[$idx]['data'][$i]['first_price'] = number_format($payment_list_now_row['payment_price']);
        //     }
        // }else{

        //     $ct_arr[$idx]['data'][$i]['first_price'] = $contract_now_rows['cth_idx'] != '' ? number_format($contract_history_price_row['price']) : '-';
        // }

        $payment_list_now = "SELECT *, COUNT(*) as cnt FROM a_payment_list
        WHERE is_cancel = 0 and ct_idx = '{$row['ct_idx']}' and bill_years = '{$year}' and bill_months = '{$months}'";
        if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
        // echo $payment_list_now.'<br>';

        }
        $payment_list_now_row = sql_fetch($payment_list_now);

        $contract_history_price = "SELECT * FROM a_contract_price_history WHERE ct_idx = '{$row['ct_idx']}'  and (ch_start_date <= '{$month_end2}') and ch_start_date != '' ORDER BY cph_idx desc limit 0, 1";

        // echo $contract_history_price.'<br>';
        $contract_history_price_row = sql_fetch($contract_history_price);

        //$first_price = '-';
        if($payment_list_now_row['cnt'] > 0){
            
            if($payment_list_now_row['is_services']){
                //$first_price = '0 (서비스)';

                $ct_arr[$idx]['data'][$i]['first_price'] = '0 (서비스)';

            }else{

                $ct_arr[$idx]['data'][$i]['first_price'] = number_format($payment_list_now_row['payment_price']);
            }
        }else{

            $ct_arr[$idx]['data'][$i]['first_price'] = $contract_now_rows['cth_idx'] != '' ? number_format($contract_history_price_row['price']) : '-';
        }


        //두번째 셀 값
        //bill_statusm != 1 and
        $bill_list_bf = "SELECT *, COUNT(*) as cnt FROM a_company_bill_list
        WHERE is_cancel = 0 and ct_idx = '{$row['ct_idx']}' and bill_years = '{$year}' and bill_months = '{$months}'";
        // echo $bill_list_bf.'<br>';
        $bill_list_bf_row = sql_fetch($bill_list_bf);


        $ct_arr[$idx]['data'][$i]['secode_date'] = $bill_list_bf_row['cnt'] > 0 && $bill_list_bf_row['bill_dates'] != '' ? $bill_list_bf_row['bill_dates'] : "-";
        $ct_arr[$idx]['data'][$i]['bill_type'] = $bill_list_bf_row['cnt'] > 0 ? $bill_list_bf_row['bill_type'] : "-";
        $ct_arr[$idx]['data'][$i]['bill_status'] = $bill_list_bf_row['cnt'] > 0 ? $bill_list_bf_row['bill_statusm'] : "-";



        $company_bill_sql = "SELECT * FROM a_contract_company_bill WHERE ct_idx = '{$row['ct_idx']}' and bill_sdate <= '{$month_end2}' order by idx desc limit 0, 1";
        $company_bill_row = sql_fetch($company_bill_sql);
        // echo $company_bill_sql.'<br>';

        //세번째 셀 값
        if($payment_list_now_row['is_services']){

            $ct_arr[$idx]['data'][$i]['thrid_date'] = '서비스';
            $ct_arr[$idx]['data'][$i]['payment_status'] = $payment_list_now_row['payment_status'];
            $ct_arr[$idx]['data'][$i]['payment_type'] = $company_bill_row['payment_type'];

        }else{
            if($payment_list_now_row['payment_date'] != ""){

                $ct_arr[$idx]['data'][$i]['thrid_date'] = $payment_list_now_row['payment_date'];
                $ct_arr[$idx]['data'][$i]['payment_status'] = $payment_list_now_row['payment_status'];
                $ct_arr[$idx]['data'][$i]['payment_type'] = $company_bill_row['payment_type'];
               
            }else{

                $ct_arr[$idx]['data'][$i]['thrid_date'] = "-";
                $ct_arr[$idx]['data'][$i]['payment_status'] = $payment_list_now_row['payment_status'];
                $ct_arr[$idx]['data'][$i]['payment_type'] = $company_bill_row['payment_type'];
            }
        }
    
    
        if($contract_now_rows['ct_status'] == '1'){
            
            $ct_arr[$idx]['data'][$i]['first_price'] = "-";
            
        }


        //계산서 발행여부 검색시
        if($billStatusSch || $btIdxSch || $paymentStatusSch || $ptIdxValue){ 
       
            if($ct_arr[$idx]['data'][$i]['year'] == $year && $ct_arr[$idx]['data'][$i]['month'] == $month){ //년도가 같은 데이터만 조회
                
                // echo 'billStatusSch' . $billStatusSch.'<br>';
                // echo 'btIdxSch'. $btIdxSch.'<br>';
                // echo 'paymentStatusSch '.$paymentStatusSch.'<br>';
                // echo 'ptIdxValue'.$ptIdxValue.'<br>';
                
                //계산서 발행여부, 계산서 종류 둘다
                if($billStatusSch && $btIdxSch && $paymentStatusSch && $ptIdxValue){ //계산서 발행여부, 계산서 종류, 지급여부, 지급방식
                    
                
                    if($ct_arr[$idx]['data'][$i]['bill_status'] == $billStatusSch && $ct_arr[$idx]['data'][$i]['bill_type'] == $btIdxSch && $ct_arr[$idx]['data'][$i]['payment_status'] == $paymentStatusSch && $ct_arr[$idx]['data'][$i]['payment_type'] == $ptIdxValue){ //


                        $ct_arr[$idx]['data'][$i]['classes'] = '';
                       
                    }else{
                        
                        $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                    }

                    // echo '<br>';
                    
                }else if($billStatusSch && $btIdxSch && $paymentStatusSch && !$ptIdxValue){ //계산서 발행여부, 계산서 종류, 지급여부
                    
                
                    if($ct_arr[$idx]['data'][$i]['bill_status'] == $billStatusSch && $ct_arr[$idx]['data'][$i]['bill_type'] == $btIdxSch && $ct_arr[$idx]['data'][$i]['payment_status'] == $paymentStatusSch){ //


                        $ct_arr[$idx]['data'][$i]['classes'] = '';
                       
                    }else{
                        
                        $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                    }

                    // echo '<br>';
                    
                }else if($billStatusSch && $btIdxSch && !$paymentStatusSch && !$ptIdxValue){ //계산서 발행여부 계산서 종류 둘다
                    
                    if($ct_arr[$idx]['data'][$i]['bill_status'] == $billStatusSch && $ct_arr[$idx]['data'][$i]['bill_type'] == $btIdxSch){ //


                        // echo '123123';
                        $ct_arr[$idx]['data'][$i]['classes'] = '';
                       
                    }else{
                        
                        $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                    }

                    // echo '<br>';
                    
                }else if(!$billStatusSch && !$btIdxSch && $paymentStatusSch && $ptIdxValue){ //지급여부 지급방식 둘다
                    
                    if($ct_arr[$idx]['data'][$i]['payment_status'] == $paymentStatusSch && $ct_arr[$idx]['data'][$i]['payment_type'] == $ptIdxValue){ //


                        $ct_arr[$idx]['data'][$i]['classes'] = '';
                       
                    }else{
                        
                        $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                    }

                    // echo '<br>';
                    
                }else if($billStatusSch && !$btIdxSch && $paymentStatusSch && !$ptIdxValue){ //계산서 발행여부, 지급여부 둘다
                    
                  
                    if($ct_arr[$idx]['data'][$i]['payment_status'] == $paymentStatusSch && $ct_arr[$idx]['data'][$i]['bill_status'] == $billStatusSch){ //


                        //echo '123';
                        $ct_arr[$idx]['data'][$i]['classes'] = '';
                       
                    }else{
                        
                        // echo '456';
                        $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                    }

                    // echo '<br>';
                    
                }else if(!$billStatusSch && $btIdxSch && !$paymentStatusSch && $ptIdxValue){ //계산서 종류, 지급여부 둘다
                    
                    if($ct_arr[$idx]['data'][$i]['payment_type'] == $ptIdxValue && $ct_arr[$idx]['data'][$i]['bill_type'] == $btIdxSch){ //


                        $ct_arr[$idx]['data'][$i]['classes'] = '';
                       
                    }else{
                        
                        $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                    }

                    // echo '<br>';
                    
                }else if($billStatusSch && !$btIdxSch && !$paymentStatusSch && !$ptIdxValue){ //계산서 발행여부만
                    if($ct_arr[$idx]['data'][$i]['bill_status'] != $billStatusSch){ //
                        $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                    }else{
                        $ct_arr[$idx]['data'][$i]['classes'] = '';
                    }

                }else if(!$billStatusSch && $btIdxSch && !$paymentStatusSch && !$ptIdxValue){ //계산서 종류만
                    if($ct_arr[$idx]['data'][$i]['bill_type'] != $btIdxSch){ //
                        $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                    }else{
                        $ct_arr[$idx]['data'][$i]['classes'] = '';
                    }
                }else if($paymentStatusSch && !$billStatusSch && !$btIdxSch && !$ptIdxValue){ //지급여부만

                    if($ct_arr[$idx]['data'][$i]['payment_status'] != $paymentStatusSch){ //
                        $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                    }else{
                        $ct_arr[$idx]['data'][$i]['classes'] = '';
                    }

                }else if(!$paymentStatusSch && !$billStatusSch && !$btIdxSch && $ptIdxValue){ //지급방식만

                    // echo '지급방식만'.$ptIdxValue.'<br>';
                    if($ct_arr[$idx]['data'][$i]['payment_type'] == $ptIdxValue){ //
                        $ct_arr[$idx]['data'][$i]['classes'] = '';

                        // echo '123';
                    }else{
                        // $ct_arr[$idx]['data'][$i]['classes'] = '';
                        $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                        // echo '456';
                    }

                }else if($paymentStatusSch && !$billStatusSch && $btIdxSch && !$ptIdxValue){ //지급여부, 계산서 종류

                    // echo '지급방식만'.$ptIdxValue.'<br>';
                    if($ct_arr[$idx]['data'][$i]['payment_status'] == $paymentStatusSch && $ct_arr[$idx]['data'][$i]['bill_type'] == $btIdxSch){ //
                        $ct_arr[$idx]['data'][$i]['classes'] = '';

                        // echo $paymentStatusSch.'<br>';
                        // echo '123';
                    }else{
                        // $ct_arr[$idx]['data'][$i]['classes'] = '';
                        $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                        // echo '456';
                    }

                }else if(!$paymentStatusSch && $billStatusSch && !$btIdxSch && $ptIdxValue){ //지급방식, 계산서 상태

                    // echo '지급방식만'.$ptIdxValue.'<br>';
                    if($ct_arr[$idx]['data'][$i]['payment_type'] == $ptIdxValue && $ct_arr[$idx]['data'][$i]['bill_status'] == $billStatusSch){ //
                        $ct_arr[$idx]['data'][$i]['classes'] = '';

                        // echo $ptIdxValue.'<br>';
                        // echo '123';
                    }else{
                        // $ct_arr[$idx]['data'][$i]['classes'] = '';
                        $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                        // echo '456';
                    }

                }else{
                    $ct_arr[$idx]['data'][$i]['classes'] = '';
                }

            }else{ //나머지 데이터는 비활성화
                $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
            }


            // echo '=====<br>';
        }

        if($ct_arr[$idx]['data'][$i]['classes'] == '') $data_total++; //클릭 활성화면 데이터 카운트

    }

    $ct_arr[$idx]['data_total'] = $data_total;  //카운트

    if($data_total == 0){ //데이터가 없으면 삭제
        unset($ct_arr[$idx]);

        // echo "123123";
    }
}


// print_r2($ct_arr);

// exit;

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


$sdate = date('Y-m', strtotime($start_date));
$edate = date('Y-m', strtotime($end_date));

$sheet->setTitle('용역업체 리스트');

$sheet->getStyle('A:L')->getFont()->setSize(13);// 폰트사이즈 설정
$sheet->mergeCells('A1:L1');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A1', '신반상회 '.$sdate.' ~ '.$edate.' 용역업체 리스트');
$sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);
$sheet->setCellValue('A3', '업종');
$sheet->setCellValue('B3', '업체명');
$sheet->setCellValue('C3', '현장명');

$startColIndex = 4; // A=1, B=2, C=3, D=4부터 시작
$row = 3;

for ($i = 0; $i < $range; $i++) {
    $offset = $startOffset + $i;

    // 기준이 되는 달을 설정하고, offset을 적용하여 날짜를 계산
    $baseMonth = $viewAll ? 1 : $month; // 전체보기일 경우 1월로 고정, 그 외에는 현재 월 사용
    $date = (new DateTime())->setDate($year, $baseMonth, 1)->modify("{$offset} month");

    $y = $date->format('Y');
    $m = $date->format('n'); // 1~12로 월 계산
    $months = str_pad($m, 2, "0", STR_PAD_LEFT); // 월 앞자리 0 붙이기
    $dates = $y.'-'.$months.'-01';

    // 열 계산
    $col1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex + ($i * 3));
    $col2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex + ($i * 3) + 1);
    $col3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex + ($i * 3) + 2);

    // 셀 입력
    $sheet->setCellValue($col1.$row, $months.'월'); // ex) D3, G3, J3
    $sheet->setCellValue($col2.$row, '계산서');     // ex) E3, H3, K3
    $sheet->setCellValue($col3.$row, '지급일');     // ex) F3, I3, L3
}

$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(40);
$sheet->getColumnDimension('C')->setWidth(40);
$sheet->getColumnDimension('D')->setWidth(20);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(20);
$sheet->getColumnDimension('G')->setWidth(20);
$sheet->getColumnDimension('H')->setWidth(20);
$sheet->getColumnDimension('I')->setWidth(20);
$sheet->getColumnDimension('J')->setWidth(20);
$sheet->getColumnDimension('K')->setWidth(20);
$sheet->getColumnDimension('L')->setWidth(20);
$sheet->getStyle("A3:L3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A3:L3")->getFont()->setSize(14)->setBold(true);

$startColIndex2 = 4; // A=1, B=2, C=3, D=4부터 시작
$cell = 4;

foreach($ct_arr as $idxc => $ct_rows){

    $sheet->setCellValue('A'.$cell, $ct_rows['industry_name']);
    $sheet->setCellValue('B'.$cell, $ct_rows['company_name']);
    $sheet->setCellValue('C'.$cell, $ct_rows['building_name']);


    $ct_data = $ct_rows['data'];

    for ($i = 0; $i < count($ct_data); $i++) {

        $first_price = $classes == 'not_contract' ? '-' : $ct_data[$i]['first_price']; //첫번째 셀 값
        $secode_date = $classes == 'not_contract' ? '-' : $ct_data[$i]['secode_date']; //첫번째 셀 값
        $thrid_date = $classes == 'not_contract' ? '-' : $ct_data[$i]['thrid_date']; //첫번째 셀 값

         // 열 계산
         $col1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex2 + ($i * 3));
         $col2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex2 + ($i * 3) + 1);
         $col3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex2 + ($i * 3) + 2);
 
         // 셀 입력
         $sheet->setCellValue($col1.$cell, $first_price); // ex) D3, G3, J3
         $sheet->setCellValue($col2.$cell, $secode_date);     // ex) E3, H3, K3
         $sheet->setCellValue($col3.$cell, $thrid_date);     // ex) F3, I3, L3
    }


    $sheet->getStyle("A".$cell.":L".$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $cell++;

}

$filename = "신반상회_용역업체 리스트_" . date('Ymd') . ".xlsx";
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
foreach ($res as $idx => $row) {

    $sheet->setCellValue('A'.$cell, $row['industry_name']);
    $sheet->setCellValue('B'.$cell, $row['company_name']);
    $sheet->setCellValue('C'.$cell, $row['building_name']);

    for ($i = 0; $i < $range; $i++) {

        $offset = $startOffset + $i;

        // 기준이 되는 달을 설정하고, offset을 적용하여 날짜를 계산
        $baseMonth = $viewAll ? 1 : $month; // 전체보기일 경우 1월로 고정, 그 외에는 현재 월 사용
        $date = (new DateTime())->setDate($year, $baseMonth, 1)->modify("{$offset} month");

        $y = $date->format('Y');
        $m = $date->format('n'); // 1~12로 월 계산
        $months = str_pad($m, 2, "0", STR_PAD_LEFT); // 월 앞자리 0 붙이기
        $dates = $y.'-'.$months.'-01';

        $contract_now_sql = "SELECT COUNT(*) as cnt FROM a_contract_history WHERE ct_idx = '{$row['ct_idx']}' and (ct_sdate <= '{$dates}' and ct_edate >= '{$dates}') and is_del = 0";
        $contract_now_rows = sql_fetch($contract_now_sql);

        $pdates = $year.'-'.$months;
        $payment_list_now = "SELECT payment_date, is_services, COUNT(*) as cnt FROM a_payment_list
                            WHERE is_cancel = 0 and payment_status != 1 and company_idx = '{$row['company_idx']}' and created_at like '{$pdates}%'";
        if($pdates == '2025-04'){
        // echo $payment_list_now.'<br>';

        }
        $payment_list_now_row = sql_fetch($payment_list_now);

        $history_price = "SELECT * FROM a_contract_history WHERE ct_idx = '{$row['ct_idx']}' and (ct_sdate <= '{$dates}' and ct_edate >= '{$dates}')";
        $history_price_row = sql_fetch($history_price);

        $bill_list_bf = "SELECT bill_dates, COUNT(*) as cnt FROM a_company_bill_list
                        WHERE is_cancel = 0 and bill_statusm != 1 and ct_idx = '{$row['ct_idx']}' and created_at like '{$pdates}%'";
        $bill_list_bf_row = sql_fetch($bill_list_bf);

        if($payment_list_now_row['is_services']){
            $first_price = '0 (서비스)';
        }else{

            $first_price = number_format($history_price_row['ct_price']);
            // if($payment_list_now_row['cnt'] > 0){
            //     $first_price = number_format($history_price_row['ct_price']);
            // }else{
            //     $first_price = '0';
            // }
        }
    
        //두번째 셀 값
        $secode_date = $bill_list_bf_row['cnt'] > 0 ? $bill_list_bf_row['bill_dates'] : "-";
    
        // $thrid_date = $payment_list_now_row['is_services'] ? "서비스" : $payment_list_now_row['payment_date'] != "" ? $payment_list_now_row['payment_date'] : "-";
        if($payment_list_now_row['is_services']){
            $thrid_date = '서비스';
        }else{
            if($payment_list_now_row['payment_date'] != ""){
                $thrid_date = $payment_list_now_row['payment_date'];
            }else{
                $thrid_date = '-';
            }
        }

        // 열 계산
        $col1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex2 + ($i * 3));
        $col2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex2 + ($i * 3) + 1);
        $col3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex2 + ($i * 3) + 2);

        // 셀 입력
        $sheet->setCellValue($col1.$cell, $first_price); // ex) D3, G3, J3
        $sheet->setCellValue($col2.$cell, $secode_date);     // ex) E3, H3, K3
        $sheet->setCellValue($col3.$cell, $thrid_date);     // ex) F3, I3, L3
       
    }

    $sheet->getStyle("A".$cell.":L".$cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $cell++;
}

$filename = "신반상회_용역업체 리스트_" . date('Ymd') . ".xlsx";
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