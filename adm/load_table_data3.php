<?php
include_once('./_common.php');

//print_r2($_POST);

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


//20250718 ban 수정 해지된 단지도 보여주기
// $sql_where = " where (1) and ct.is_del = '0' and building.is_use = 1 and ch.ct_sdate <= '{$end_date}' and ch.ct_edate >= '{$start_date}' ";
$sql_where = " where (1) and ct.is_del = '0' and ch.ct_sdate <= '{$end_date}' and ch.ct_edate >= '{$start_date}' ";

$sql_where2 = ' and ct.is_temp = 1 ';

// if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
//     if($transactionStatusValue){
//         $sql_where .= " ";
//     }else{
//         $sql_where .= " and ct.ct_status = '0' ";
//     }
// }


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

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // print_r2($_REQUEST);
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

// if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
//     $sql_where .= " and ct.building_id = 4 ";
// }



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
}




// echo $sql;
$res = sql_query($sql);

$ct_arr = array();
foreach ($res as $idx => $row) {

    
    $ct_arr[$idx]['ct_idx'] = $row['ct_idx'];  //계약
    $ct_arr[$idx]['company_idx'] = $row['company_idx'];  //업체
    $ct_arr[$idx]['company_name'] = $row['company_name'];  //업체명
    $ct_arr[$idx]['industry_name'] = $row['industry_name'];  //업종
    $ct_arr[$idx]['building_name'] = $row['building_name'];  //단지명
    $ct_arr[$idx]['is_temp'] = $row['is_temp'] ? 'temp' : '';
    $ct_arr[$idx]['ct_status'] = $row['ct_status'];  //계약상태
    $ct_arr[$idx]['ids_name'] = $row['ids_name'];  //업종명
 
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
        // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $contract_now_sql.'<br>';
        $contract_now_rows = sql_fetch($contract_now_sql);

    //    echo $contract_now_rows['cth_idx'].'<br>';

        $ct_arr[$idx]['data'][$i]['classes'] = $contract_now_rows['cth_idx'] != '' ? '' : 'not_contract';  // 클래스로 계약기간내인지

        
        //계약기간 내면 클릭 활성화
        $ct_arr[$idx]['data'][$i]['clicks'] = $contract_now_rows['cth_idx'] != '' ? 'yes' : 'no';
       

        


       
        

        //월별 금액 가져오기
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
        //  echo $bill_list_bf.'<br>';
         $bill_list_bf_row = sql_fetch($bill_list_bf);


        $bill_company_setting = "SELECT ccb.*, pt.pt_name, bt.bill_name FROM 
        a_contract_company_bill as ccb
        LEFT JOIN a_payment_type as pt on ccb.payment_type = pt.pt_idx
        LEFT JOIN a_company_bill_type as bt on ccb.bill_type = bt.bt_idx
        WHERE ct_idx = '{$row['ct_idx']}' and bill_sdate <= '{$month_end2}' ORDER BY idx desc limit 0, 1";
        // echo $bill_company_setting.'<br>';
        $bill_company_setting_row = sql_fetch($bill_company_setting);
       
       

        $ct_arr[$idx]['data'][$i]['secode_date'] = $bill_list_bf_row['cnt'] > 0 && $bill_list_bf_row['bill_dates'] != '' ? $bill_list_bf_row['bill_dates'] : "-";
        $ct_arr[$idx]['data'][$i]['bill_type'] = $bill_list_bf_row['cnt'] > 0 ? $bill_list_bf_row['bill_type'] : $bill_company_setting_row['bill_type'];
        $ct_arr[$idx]['data'][$i]['bill_status'] = $bill_list_bf_row['cnt'] > 0 ? $bill_list_bf_row['bill_statusm'] : "-";


        // echo  $bill_list_bf_row['cnt'].'<br>';



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
    
    
        // if(!$transactionStatusValue){
        //     //해지포함이 아닌 경우
        //     if($contract_now_rows['ct_status'] == '1'){
            
        //         $ct_arr[$idx]['data'][$i]['first_price'] = "-";
                
        //     }
        // }

        // print_r2($contract_now_rows);
       


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


                        // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                        //     if($idx == 2){
                        //     echo $ptIdxValue.'<br>';

                        //     }
                        // }
                        
                        $ct_arr[$idx]['data'][$i]['classes'] = '';

                        // $ct_arr[$idx]['data'][$i]['classes'] = '';
                        
                        // echo '123';
                    }else{
                        // $ct_arr[$idx]['data'][$i]['classes'] = '';
                        $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                        $ct_arr[$idx]['data'][$i]['clicks'] = 'no';
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
                $ct_arr[$idx]['data'][$i]['clicks'] = 'no';
            }


            // echo '=====<br>';
        }

        if(!$transactionStatusValue){
            //계약해지라면
            if($ct_arr[$idx]['ct_status'] == '1'){
                
                $nowGo = $y."-".$months.'-01'; //현재 년월

                $months2 = str_pad($contract_now_rows['ct_status_month'], 2, "0", STR_PAD_LEFT); // 월 앞자리 0 붙이기
                $noGo = $contract_now_rows['ct_status_year'].'-'.$months2.'-01'; //계약해지 년월
                
                $noGoLast = date("Y-m-t", strtotime($noGo)); //계약해지 년월 마지막일

                if($noGoLast < $nowGo){
                    $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                    $ct_arr[$idx]['data'][$i]['clicks'] = 'no';
                }
                
                if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                    // echo 'nowGo'.$nowGo.'<br>';
                    // echo 'noGoLast'.$noGoLast.'<br>';
                }
            }
        }else{
            // if($data_total == 0) 일때와 같이 동작 
            
            if($ct_arr[$idx]['ct_status'] == '1'){
               
                if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                
                    // print_r($ct_arr[0]);
                }
                $nowGo = $y."-".$months.'-01'; //현재 년월

                $months2 = str_pad($contract_now_rows['ct_status_month'], 2, "0", STR_PAD_LEFT); // 월 앞자리 0 붙이기
                $noGo = $contract_now_rows['ct_status_year'].'-'.$months2.'-01'; //계약해지 년월
                
                $noGoLast = date("Y-m-t", strtotime($noGo)); //계약해지 년월 마지막일

                if($noGoLast < $nowGo){
                    $ct_arr[$idx]['data'][$i]['classes'] = 'not_contract';
                    $ct_arr[$idx]['data'][$i]['clicks'] = 'no';
                }
                if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                    // echo 'nowGo'.$nowGo.'<br>';
                    // echo 'noGoLast'.$noGoLast.'<br>';
                }
            }
        }

        if($ct_arr[$idx]['data'][$i]['classes'] == '') $data_total++; //클릭 활성화면 데이터 카운트

    }

    $ct_arr[$idx]['data_total'] = $data_total;  //카운트

    if($data_total == 0){ //데이터가 없으면 삭제
        // unset($ct_arr[$idx]);
        if(!$transactionStatusValue){
            unset($ct_arr[$idx]);
        }else{

            if($ct_arr[$idx]['ct_status'] == '0'){
                unset($ct_arr[$idx]);
            }
        }

        // echo "123123";
    }
}




$ct_idx_arr = array();
foreach($ct_arr as $ct_rows2){
    //echo $ct_rows2['ct_idx'].'<br>';

    if($ct_rows2['is_temp'] == 'temp'){
        // echo '임시저장';
        continue; //임시저장 데이터는 제외
    }
    array_push($ct_idx_arr, $ct_rows2['ct_idx']);
}

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $payment_list_now.'<br>';
    // print_r2($ct_arr[2]);

    
}


$ct_idx_arr_t = "'".implode("','", $ct_idx_arr)."'";

?>
<input type="hidden" name="ct_idx_arr" id="ct_idx_arr" value="<?php echo $ct_idx_arr_t; ?>">
<?php
//print_r2($ct_idx_arr);

// $totals = sql_num_rows($res);
$totals = count($ct_arr);


// echo $totals.'<br>';
if ($totals == 0) {
    http_response_code(400);
    echo '요청 값이 부족합니다.';
    exit;
}

// 좌측 고정 테이블
foreach($ct_arr as $idxc => $ct_rows){

    $temp_class = $ct_arr[$idxc]['is_temp'] ? 'temp' : '';
    $onclick_f = "onclick='company_form_pop_open(\"".$ct_arr[$idxc]['ct_idx']."\", \"".$start_date."\", \"".$end_date."\")'";


    $status_not_class = $ct_arr[$idxc]['ct_status'] == '1' ? "status_not" : '';


    echo "<tr class='".$temp_class." ".$status_not_class."'>";
        echo "<td ".$onclick_f.">{$ct_rows['ids_name']}</td>";
        echo "<td ".$onclick_f.">{$ct_rows['company_name']}</td>";
        echo "<td ".$onclick_f.">{$ct_rows['building_name']}</td>";
    echo "</tr>";
}

echo "<!-- SPLIT -->";

foreach($ct_arr as $idxc => $ct_rows){

    $ct_data = $ct_rows['data'];
    $temp_class = $ct_arr[$idxc]['is_temp'] ? 'temp' : '';
    $status_not_class = $ct_arr[$idxc]['ct_status'] == '1' ? "status_not" : '';

    echo "<tr class='".$temp_class." ".$status_not_class."'>";

    for ($i = 0; $i < count($ct_data); $i++) {


        //클릭
        $clicks = 
        $ct_data[$i]['clicks'] != 'no' ?
        "onclick='contract_personal_pop_open(\"".$ct_rows['ct_idx']."\", \"".$row['company_idx']."\", ".$ct_data[$i]['year'].", ".$ct_data[$i]['month'].", \"".$ct_data[$i]['month_start2']."\", \"".$ct_data[$i]['month_end2']."\")'" 
        : '';


        $classes = $ct_data[$i]['classes']; //클래스


        $first_price = $classes == 'not_contract' ? '-' : $ct_data[$i]['first_price']; //첫번째 셀 값
        $secode_date = $classes == 'not_contract' ? '-' : $ct_data[$i]['secode_date']; //첫번째 셀 값
        $thrid_date = $classes == 'not_contract' ? '-' : $ct_data[$i]['thrid_date']; //첫번째 셀 값

        echo "<td ".$clicks." class='".$classes."'>{$first_price}</td>";
        echo "<td ".$clicks." class='".$classes."'>{$secode_date}</td>";
        echo "<td ".$clicks." class='".$classes."'>{$thrid_date}</td>";
    }
    echo "</tr>";
}

?>