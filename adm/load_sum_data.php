<?php
include_once('./_common.php');

// print_r2($_POST);

$year = intval($_POST['year']);
$month = intval($_POST['month']);
$viewAll = isset($_POST['viewAll']) && $_POST['viewAll'] == 1;

$range = $viewAll ? 12 : 3; // 전체보기는 12개월, 일반은 기준 ±1개월
$startOffset = $viewAll ? 0 : -1; // 전체보기는 0부터 시작 (1월부터), 일반은 기준월 -1로 시작

//월 시작일, 마지막일
$base_year = $_POST['year'];
$base_month = $_POST['month'];

$month_start = date("Y-m-01", strtotime("$base_year-$base_month-01")); // 2025-07-01

// echo $month_start.'<br>';
$start_date = date('Y-m-d',strtotime($month_start."-1 month")); 
$end_date = date('Y-m-t',strtotime($month_start."+1 month")); 

//20250718 ban 수정
// $sql_where = " where (1) and ct.is_del = '0' and building.is_use = 1 and ch.ct_sdate <= '{$end_date}' and ch.ct_edate >= '{$start_date}' ";
$sql_where = " where (1) and ct.is_del = '0' and building.is_use >= 0 and ch.ct_sdate <= '{$end_date}' and ch.ct_edate >= '{$start_date}' and ct.is_temp = 0 ";
// ★기본값을 "전체"로 (2026-08 수정)
//   load_table_data2.php 와 **같은 규칙**이어야 한다.
//   예전에는 업종·업체·단지를 하나도 고르지 않으면 이 조건이 남아
//   위 $sql_where 의 `ct.is_temp = 0` 과 정면으로 충돌했다(is_temp 가 0이면서 1일 수는 없다).
//   그래서 업종을 [전체]로 두고 지급방식만 고르면 합계 쿼리가 **항상 0건**이 되어
//   목록은 나오는데 개수·합계금액만 0으로 찍혔다.
$sql_where2 = '';

// ★해지 계약 처리는 아래 PHP 쪽(해지월 이후 제외)에서만 한다 (2026-08 수정)
//   목록(load_table_data2.php)은 여기서 SQL 로 거르지 않고 PHP 로만 판단하는데,
//   합계 쪽만 SQL 로 한 번 더 걸러서 **목록에는 보이는 계약이 개수에는 안 잡히는**
//   불일치가 생겼다. 두 파일의 규칙을 같게 맞춘다.
if($transactionStatusValue){
    $sql_where .= " ";

    // $sql_where2 = "";
}else{
    // 예전: $sql_where .= " and ct.ct_status = '0' ";  ← 목록과 규칙이 달라 제거
}

if($industry_idx_sch){
    $industry_idx_sch_t = "'".implode("','", $industry_idx_sch)."'";
    $sql_where .= " and ct.industry_idx IN ({$industry_idx_sch_t}) ";

    $sql_where2 = "";
}

if($company_idx_sch){
    $company_idx_sch_t = "'".implode("','", $company_idx_sch)."'";
    $sql_where .= " and ct.company_idx IN ({$company_idx_sch_t}) ";

    $sql_where2 = "";
}

if($building_id_sch){
    $building_id_sch_t = "'".implode("','", $building_id_sch)."'";
    $sql_where .= " and ct.building_id IN ({$building_id_sch_t}) ";

    $sql_where2 = "";
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


// $sql = "select contract.*, building.building_name, manage_company.transaction_status, company_bill.payment_type, IFNULL(payment_list.payment_status, 1) as ps, IFNULL(bill_list.bill_statusm, 1) as bs, bill_list.bill_type from a_contract as contract 
//         left join a_building as building on contract.building_id = building.building_id 
//         left join a_manage_company as manage_company on contract.company_idx = manage_company.company_idx
//         left join a_contract_company_bill as company_bill on contract.ct_idx = company_bill.ct_idx
//         left join a_payment_list as payment_list on contract.ct_idx = payment_list.ct_idx
//         left join a_company_bill_list as bill_list on contract.ct_idx = bill_list.ct_idx
//         {$sql_where} GROUP BY ct_idx
//         order by contract.is_temp desc, contract.company_name asc, contract.ct_idx desc";

$sql = "SELECT 
ct.*, 
building.building_name, 
company_bill.payment_type,
IFNULL(bill_list.bill_statusm, 1) as bs, bill_list.bill_type,
IFNULL(payment_list.payment_status, 1) as ps
FROM a_contract_history as ch
LEFT JOIN a_contract as ct on ch.ct_idx = ct.ct_idx 
LEFT JOIN a_building as building on ct.building_id = building.building_id
LEFT JOIN a_manage_company as manage_company on ct.company_idx = manage_company.company_idx
LEFT JOIN a_contract_company_bill as company_bill on ct.ct_idx = company_bill.ct_idx
LEFT JOIN a_company_bill_list as bill_list on ct.ct_idx = bill_list.ct_idx
LEFT JOIN a_payment_list as payment_list on ct.ct_idx = payment_list.ct_idx
{$sql_where} {$sql_where2} GROUP BY ct.ct_idx 
order by ct.is_temp desc, building.building_name asc, ct.company_name asc, ct.ct_idx desc";

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    // echo $sql.'<br>';
}
$res = sql_query($sql);
$total = sql_num_rows($res);


$months = str_pad($month, 2, "0", STR_PAD_LEFT); // 월 앞자리 0 붙이기
$dates = $year.'-'.$months.'-01';

$sum_price = 0;
$ct_arr = array();
foreach ($res as $idx => $row) {


    $ct_arr[$idx]['ct_idx'] = $row['ct_idx'];  //계약
    $ct_arr[$idx]['company_idx'] = $row['company_idx'];  //업체
    $ct_arr[$idx]['company_name'] = $row['company_name'];  //업체명
    $ct_arr[$idx]['industry_name'] = $row['industry_name'];  //업종
    $ct_arr[$idx]['building_name'] = $row['building_name'];  //단지명
    $ct_arr[$idx]['is_temp'] = $row['is_temp'] ? 'temp' : '';
 
    $data_total = 0;
    $price = 0; // ★계약마다 초기화 — 안 하면 앞 계약의 금액이 그대로 남아 합계가 부풀려진다(2026-08)

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
            
            // ★목록(load_table_data2.php)과 **같은 규칙**으로 맞춘다 (2026-08 수정)
            //   ① $year(기준년) 이 아니라 그 칸의 실제 연도 $y 를 쓴다
            //      — 1월 화면에서 -1개월(전년 12월)이 잘못 판정되던 문제
            //   ② 해지월 "말일"과 비교한다 — 해지월 자체는 살아 있는 달로 본다
            $nowGo = $y."-".$months.'-01'; //해당 칸의 년월

            $months2 = str_pad($contract_now_rows['ct_status_month'], 2, "0", STR_PAD_LEFT); // 월 앞자리 0 붙이기
            $noGo = $contract_now_rows['ct_status_year'].'-'.$months2.'-01'; //계약해지 년월
            $noGoLast = date("Y-m-t", strtotime($noGo)); //계약해지 년월 마지막일

            if($noGoLast < $nowGo){
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

        // ★지급 레코드가 없는 달 = 미지급(1) 로 정규화 (2026-08 수정)
        //   load_table_data2.php 와 **같은 규칙**이어야 한다.
        //   이 파일은 목록과 별개로 개수/합계를 계산하는데 정규화가 빠져 있어,
        //   목록에는 행이 나오는데 하단 개수·합계만 0으로 나왔다.
        $pay_status_norm = (isset($payment_list_now_row['payment_status']) && $payment_list_now_row['payment_status'] !== '' && $payment_list_now_row['payment_status'] !== null)
                         ? $payment_list_now_row['payment_status']
                         : 1;

        //세번째 셀 값
        if($payment_list_now_row['is_services']){

            $ct_arr[$idx]['data'][$i]['thrid_date'] = '서비스';
            $ct_arr[$idx]['data'][$i]['payment_status'] = $pay_status_norm;
            $ct_arr[$idx]['data'][$i]['payment_type'] = $company_bill_row['payment_type'];

        }else{
            if($payment_list_now_row['payment_date'] != ""){

                $ct_arr[$idx]['data'][$i]['thrid_date'] = $payment_list_now_row['payment_date'];
                $ct_arr[$idx]['data'][$i]['payment_status'] = $pay_status_norm;
                $ct_arr[$idx]['data'][$i]['payment_type'] = $company_bill_row['payment_type'];
               
            }else{

                $ct_arr[$idx]['data'][$i]['thrid_date'] = "-";
                $ct_arr[$idx]['data'][$i]['payment_status'] = $pay_status_norm;
                $ct_arr[$idx]['data'][$i]['payment_type'] = $company_bill_row['payment_type'];
            }
        }
    
    
        // ★해지 계약의 금액을 "-" 로 지우던 부분 제거 (2026-08 수정)
        //   목록(load_table_data2.php)은 해지 계약이라도 해지월까지는 금액을 그대로 보여준다.
        //   합계 쪽만 여기서 금액을 지워서, 목록에는 금액이 찍히는데 합계금액에는
        //   빠지는 불일치가 있었다. 해지월 이후는 위에서 이미 제외되므로 이 처리는 불필요하다.
        // if($contract_now_rows['ct_status'] == '1'){
        //     $ct_arr[$idx]['data'][$i]['first_price'] = "-";
        // }


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

                        if($billStatusSch == 1){
                            if($ct_arr[$idx]['data'][$i]['bill_status'] == '-' && $ct_arr[$idx]['data'][$i]['bill_type'] == $btIdxSch && $ct_arr[$idx]['data'][$i]['payment_status'] == $paymentStatusSch && $ct_arr[$idx]['data'][$i]['payment_type'] == $ptIdxValue){

                                // echo '000';
                                // $ct_arr[$idx]['data'][$i]['classes'] = '';  
                            }else{
                                $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                            }
                        }
                        
                    }

                    // echo '<br>';
                    
                }else if($billStatusSch && $btIdxSch && $paymentStatusSch && !$ptIdxValue){ //계산서 발행여부, 계산서 종류, 지급여부
                    
                
                    if($ct_arr[$idx]['data'][$i]['bill_status'] == $billStatusSch && $ct_arr[$idx]['data'][$i]['bill_type'] == $btIdxSch && $ct_arr[$idx]['data'][$i]['payment_status'] == $paymentStatusSch){ //


                        $ct_arr[$idx]['data'][$i]['classes'] = '';
                       
                    }else{

                        if($billStatusSch == 1){
                            if($ct_arr[$idx]['data'][$i]['bill_status'] == '-' && $ct_arr[$idx]['data'][$i]['bill_type'] == $btIdxSch && $ct_arr[$idx]['data'][$i]['payment_status'] == $paymentStatusSch){

                                // echo '000';
                                // $ct_arr[$idx]['data'][$i]['classes'] = '';  
                            }else{
                                $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                            }
                        }
                        
                        // $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                    }

                    // echo '<br>';
                    
                }else if($billStatusSch && $btIdxSch && !$paymentStatusSch && !$ptIdxValue){ //계산서 발행여부 계산서 종류 둘다
                    
                    if($ct_arr[$idx]['data'][$i]['bill_status'] == $billStatusSch && $ct_arr[$idx]['data'][$i]['bill_type'] == $btIdxSch){ //


                        // echo '123123';
                        $ct_arr[$idx]['data'][$i]['classes'] = '';
                       
                    }else{

                        if($billStatusSch == 1){
                            if($ct_arr[$idx]['data'][$i]['bill_status'] == '-' && $ct_arr[$idx]['data'][$i]['bill_type'] == $btIdxSch){

                                // echo '000';
                                // $ct_arr[$idx]['data'][$i]['classes'] = '';  
                            }else{
                                $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                            }
                        }
                        
                        //$ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
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

                        if($billStatusSch == 1){
                            if($ct_arr[$idx]['data'][$i]['bill_status'] == '-' && $ct_arr[$idx]['data'][$i]['payment_status'] == $paymentStatusSch){

                                // echo '000';
                                // $ct_arr[$idx]['data'][$i]['classes'] = '';  
                            }else{
                                $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                            }
                        }
                        
                        // echo '456';
                        //$ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
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
                      
                        if($billStatusSch == 1){
                            // echo '123';

                            if($ct_arr[$idx]['data'][$i]['bill_status'] == '-'){

                                // echo '000';
                                // $ct_arr[$idx]['data'][$i]['classes'] = '';  
                            }else{
                                $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                            }
                        }
                    }else{
                        $ct_arr[$idx]['data'][$i]['classes'] = '';
                    }

                }else if(!$billStatusSch && $btIdxSch && !$paymentStatusSch && !$ptIdxValue){ //계산서 종류만
                    if($ct_arr[$idx]['data'][$i]['bill_type'] != $btIdxSch){ //
                        $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                        
                    }else{
                        $ct_arr[$idx]['data'][$i]['classes'] = '';

                        // echo '1';
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

                        if($billStatusSch == 1){
                            if($ct_arr[$idx]['data'][$i]['bill_status'] == '-' && $ct_arr[$idx]['data'][$i]['payment_type'] == $ptIdxValue){

                                // echo '000';
                                // $ct_arr[$idx]['data'][$i]['classes'] = '';  
                            }else{
                                $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                            }
                        }
                        //$ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
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


        // echo $ct_arr[$idx]['data'][$i]['year'].'<br>';
        if($ct_arr[$idx]['data'][$i]['year'] == $year && $ct_arr[$idx]['data'][$i]['month'] == $month){

            // echo '1';
            //print_r2($ct_arr);
            $price = $ct_arr[$idx]['data'][$i]['first_price'] != '-' ? str_replace(',', '', $ct_arr[$idx]['data'][$i]['first_price']) : 0;

            // echo $price.'<br>';
        }

    }

  

    // echo $price.'<br>';

    $ct_arr[$idx]['data_total'] = $data_total;  //카운트

    if($data_total == 0){ //데이터가 없으면 삭제
        unset($ct_arr[$idx]);
        // echo "123123";

        $price = 0;
    }

    $sum_price += $price; //월별 금액 합계
}

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
//     print_r2($ct_arr);
// }


?>
<tr>
    <th>개수</th>
    <td><?php echo number_format(count($ct_arr)); ?></td>
    <th>합계금액</th>
    <td><?php echo number_format($sum_price);?></td>
</tr>