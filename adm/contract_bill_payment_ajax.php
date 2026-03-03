<?php
require_once "./_common.php";

// print_r2($_POST);
$today = date("Y-m-d H:i:s");

// echo $bill_status_per;

//세금계산서 처리한 내용 있는지 확인 업체별로
$bill_dates_ln = date('Y-m', strtotime($bill_dates_per));
// $bill_list_confirm = "SELECT COUNT(*) as cnt FROM a_company_bill_list WHERE company_idx = '{$company_idx}' and bill_dates like '{$bill_dates_ln}%'";
$bill_list_confirm = "SELECT COUNT(*) as cnt FROM a_company_bill_list WHERE company_idx = '{$company_idx}' and ct_idx = '{$ct_idx}' and bill_years = '{$bill_years}' and bill_months = '{$bill_months}'";


$bill_list_confirm_row = sql_fetch($bill_list_confirm);

//이미 있다면 업데이트 없으면 인서트
if($bill_list_confirm_row['cnt'] > 0){

    $bill_list_query = "UPDATE a_company_bill_list SET
                        bill_statusm = '{$bill_status_per}',
                        bill_dates = '{$bill_dates_per}',
                        bill_type = '{$bill_types_per}',
                        bills_memo = '{$bills_memo_pre}'
                        WHERE ct_idx = '{$ct_idx}' and bill_years = '{$bill_years}' and bill_months = '{$bill_months}'";
}else{
    $bill_list_query = "INSERT INTO a_company_bill_list SET
                            ct_idx = '{$ct_idx}',
                            company_idx = '{$company_idx}',
                            bill_statusm = '{$bill_status_per}',
                            bill_dates = '{$bill_dates_per}',
                            bill_years = '{$bill_years}',
                            bill_months = '{$bill_months}',
                            bill_type = '{$bill_types_per}',
                            bills_memo = '{$bills_memo_pre}',
                            created_at = '{$today}'";
}

// echo $bill_list_query.'<br>';
// exit;
sql_query($bill_list_query);
// echo '계산서 처리:::'.$bill_list_query.'<br>';

// 서비스 처리 아닐 때 금액 변경된 경우
if($payment_status_per != '3'){

    if($pn_ct_price_or != $pn_ct_price){
        // $update_query = "UPDATE a_contract_history SET
        //                 ct_price = '{$pn_ct_price}'
        //                 WHERE cth_idx = '{$history_idx}'";
        

        // $update_query = "UPDATE a_contract_price_history SET
        //                 price = '{$pn_ct_price}'
        //                 WHERE cph_idx = '{$history_idx}'";

        // sql_query($update_query);
    }
}

// exit;

//지급처리 여부가 미지급이 아닐 때

//지급처리한 내용 있는지 확인 업체별로
$payment_date_ln = date('Y-m', strtotime($payment_date_per));
$payment_list_confirm = "SELECT COUNT(*) as cnt FROM a_payment_list WHERE ct_idx = '{$ct_idx}' and bill_years = '{$bill_years}' and bill_months = '{$bill_months}'";
$payment_list_confirm_row = sql_fetch($payment_list_confirm);


// echo $payment_list_confirm.'<br>';

//지급처리가 서비스인경우 서비스 체크
$service_sql = "";
if($payment_status_per == '3'){
    $service_sql = " is_services = 1, ";
}else{
    $service_sql = " is_services = 0, ";
}

//지급처리 내용 이미 있다면 업데이트 없으면 인서트
if($payment_list_confirm_row['cnt'] > 0){

    $payment_list_query = "UPDATE a_payment_list SET
                            payment_status = '{$payment_status_per}',
                            payment_price = '{$pn_ct_price}',
                            payment_date = '{$payment_date_per}',
                            {$service_sql}
                            payment_memo = '{$payment_memo_per}'
                            WHERE ct_idx = '{$ct_idx}' and bill_years = '{$bill_years}' and bill_months = '{$bill_months}'";

}else{
    $payment_list_query = "INSERT INTO a_payment_list SET
                            ct_idx = '{$ct_idx}',
                            company_idx = '{$company_idx}',
                            payment_status = '{$payment_status_per}',
                            payment_price = '{$pn_ct_price}',
                            payment_date = '{$payment_date_per}',
                            bill_years = '{$bill_years}',
                            bill_months = '{$bill_months}',
                            {$service_sql}
                            payment_memo = '{$payment_memo_per}',
                            created_at = '{$today}'";
}

// echo '지급처리:::'.$payment_list_query.'<br>';
// exit;
sql_query($payment_list_query);


// exit;


//계약해지
if($ct_status_per){

    $extract_sql = "UPDATE a_contract SET 
                    ct_status = '1',
                    ct_status_year = '{$ct_status_year}',
                    ct_status_month = '{$ct_status_month}'
                    WHERE ct_idx = '{$ct_idx}'";
    sql_query($extract_sql);
}

alert("계산서 처리 및 지급처리가 완료되었습니다.");