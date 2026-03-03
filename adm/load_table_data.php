<?php
include_once('./_common.php');

// print_r2($_POST);

$year = intval($_POST['year']);
$month = intval($_POST['month']);
$viewAll = isset($_POST['viewAll']) && $_POST['viewAll'] == 1;

$range = $viewAll ? 12 : 3; // 전체보기는 12개월, 일반은 기준 ±1개월
$startOffset = $viewAll ? 0 : -1; // 전체보기는 0부터 시작 (1월부터), 일반은 기준월 -1로 시작

$sql_where = " where (1) and contract.is_del = '0' and building.is_use = 1 ";

// if($transactionStatusValue){
//     $sql_where .= " ";
// }else{
//     $sql_where .= " and contract.ct_status = '0' ";
// }

if($industry_idx_sch){
    $industry_idx_sch_t = "'".implode("','", $industry_idx_sch)."'";
    $sql_where .= " and contract.industry_idx IN ({$industry_idx_sch_t}) ";
}

if($company_idx_sch){
    $company_idx_sch_t = "'".implode("','", $company_idx_sch)."'";
    $sql_where .= " and contract.company_idx IN ({$company_idx_sch_t}) ";
}

if($building_id_sch){
    $building_id_sch_t = "'".implode("','", $building_id_sch)."'";
    $sql_where .= " and contract.building_id IN ({$building_id_sch_t}) ";
}

if($ptIdxValue){

    $sql_where .= " and company_bill.payment_type = '{$ptIdxValue}' ";
}

if($paymentStatusSch){
    $sql_where .= " and IFNULL(payment_list.payment_status, 1) = '{$paymentStatusSch}' ";
}

if($billStatusSch){
    $sql_where .= " and IFNULL(bill_list.bill_statusm, 1) = '{$billStatusSch}' ";
}

if($btIdxSch){
    $sql_where .= " and bill_list.bill_type = '{$btIdxSch}' ";
}


$sql = "select contract.*, building.is_use, building.building_name, manage_company.transaction_status, company_bill.payment_type, IFNULL(payment_list.payment_status, 1) as ps, IFNULL(bill_list.bill_statusm, 1) as bs, bill_list.bill_type from a_contract as contract 
        left join a_building as building on contract.building_id = building.building_id 
        left join a_manage_company as manage_company on contract.company_idx = manage_company.company_idx
        left join a_contract_company_bill as company_bill on contract.ct_idx = company_bill.ct_idx
        left join a_payment_list as payment_list on contract.ct_idx = payment_list.ct_idx
        left join a_company_bill_list as bill_list on contract.ct_idx = bill_list.ct_idx
        {$sql_where} GROUP BY ct_idx
        order by contract.is_temp desc, contract.company_name asc, contract.ct_idx desc";
if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $sql.'<br>';
}
// echo $sql;
$res = sql_query($sql);
$totals = sql_num_rows($res);

if ($totals == 0) {
    http_response_code(400);
    echo '요청 값이 부족합니다.';
    exit;
}

// 좌측 고정 테이블
foreach ($res as $idx => $row) {

  $temp_class = $row['is_temp'] ? 'temp' : '';
  $onclick_f = "onclick='company_form_pop_open(\"".$row['ct_idx']."\")'";

  echo "<tr class='".$temp_class."'>";
    echo "<td ".$onclick_f.">{$row['industry_name']}</td>";
    echo "<td ".$onclick_f.">{$row['company_name']}</td>";
    echo "<td ".$onclick_f.">{$row['building_name']}</td>";
  echo "</tr>";
}
echo "<!-- SPLIT -->";

// 오른쪽 스크롤 테이블
foreach ($res as $idx => $row) {
  
  $temp_class = $row['is_temp'] ? 'temp' : '';

  echo "<tr class='".$temp_class."'>";
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
    //ch.ct_sdate <= '{$dates}' or

  
    $contract_now_sql = "SELECT ch.*, c.ct_status, c.ct_status_year, c.ct_status_month FROM a_contract_history as ch
                         LEFT JOIN a_contract as c ON ch.ct_idx = c.ct_idx
                         WHERE ch.ct_idx = '{$row['ct_idx']}' and (ch.ct_sdate <= '{$dates}' and ch.ct_edate >= '{$dates}') and ch.is_del = 0";
    // echo $contract_now_sql.'<br>';
    $contract_now_rows = sql_fetch($contract_now_sql);

//    echo $contract_now_rows['cth_idx'].'<br>';

   

    if(!$transactionStatusValue){

        $de_year = date('Y', strtotime($dates));
        $de_month = date('n', strtotime($dates));
       
      
        if($contract_now_rows['ct_status_year'] == $de_year){

            if($contract_now_rows['ct_status_month'] <= $de_month){
                //echo "1";
            }else{
                //echo "2";

                $classes = 'not_contract';
            }
            //$classes = 'not_contract';
        }
    }else{
        $classes = $contract_now_rows['cth_idx'] != '' ? '' : 'not_contract';
    }


    $clicks = 
    $contract_now_rows['cth_idx'] != '' ?
    "onclick='contract_personal_pop_open(\"".$row['ct_idx']."\", \"".$row['company_idx']."\", ".$year.", ".$m.")'" 
    : '';

    if($contract_now_rows['ct_status'] == '1'){
        
        $nowGo = $year."-".$months.'-01'; //현재 년월

        $months2 = str_pad($contract_now_rows['ct_status_month'], 2, "0", STR_PAD_LEFT); // 월 앞자리 0 붙이기
        $noGo = $contract_now_rows['ct_status_year'].'-'.$months2.'-01'; //계약해지 년월
        
        if($noGo < $nowGo){
            $classes = 'not_contract';
            $clicks = '';
        }
        
    }

    

    $pdates = $year.'-'.$months;
    $payment_list_now = "SELECT payment_date, is_services, COUNT(*) as cnt FROM a_payment_list
                        WHERE is_cancel = 0 and payment_status != 1 and company_idx = '{$row['company_idx']}' and ct_idx = '{$row['ct_idx']}' and bill_years = '{$year}' and bill_months = '{$months}'";
    if($pdates == '2025-05'){
    // echo $payment_list_now.'<br>';

    }
    $payment_list_now_row = sql_fetch($payment_list_now);

    $history_price = "SELECT * FROM a_contract_history WHERE ct_idx = '{$row['ct_idx']}' and (ct_sdate <= '{$month_end2}' and ct_edate >= '{$month_start2}')";
    $history_price_row = sql_fetch($history_price);

    if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
        //echo $history_price.'<br>';
    }

    $bill_list_bf = "SELECT bill_dates, COUNT(*) as cnt FROM a_company_bill_list
                    WHERE is_cancel = 0 and bill_statusm != 1 and ct_idx = '{$row['ct_idx']}' and bill_years = '{$year}' and bill_months = '{$months}'";
    $bill_list_bf_row = sql_fetch($bill_list_bf);

    //첫번째 셀 값
    // $first_price = $payment_list_now_row['is_services'] ? "0 (서비스)" : $payment_list_now_row['cnt'] > 0 ? number_format($history_price_row['ct_price']) : '0';

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
   

    //$value = ($m === 4 && $row[0] === '승강기') ? '495,000' : '0';
    echo "<td ".$clicks." class='".$classes."'>{$first_price}</td>";
    echo "<td ".$clicks." class='".$classes."'>{$secode_date}</td>";
    echo "<td ".$clicks." class='".$classes."'>{$thrid_date}</td>";
  }
  echo "</tr>";
}
?>