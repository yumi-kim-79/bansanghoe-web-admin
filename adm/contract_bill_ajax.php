<?php
require_once './_common.php';

$today = date("Y-m-d H:i:s");
//print_r2($_POST);

$bill_date_arr = [];

$i = 0;

$arr = array();
foreach ($bill_date_ipt as $key => $dateArray) {
    //echo "키: {$key}, 날짜: {$dateArray[0]}\n";
    if($dateArray[0] != ""){
        $bill_date_arr[$i]['idx'] = $key;
        $bill_date_arr[$i]['date'] = $dateArray[0];

        array_push($arr, $dateArray[0]);

        $i++;
    }
    
  
}

if(count($arr) == 0) die(result_data(false, "세금계산서 처리 할 업체를 선택하여 날짜를 지정해주세요.", []));


// die(result_data(false, $bill_date_arr, []));

$query_arr = array();
for($i=0;$i<count($bill_date_arr);$i++){

    $contract_go = "SELECT * FROM a_contract WHERE ct_idx = '{$bill_date_arr[$i]['idx']}' and is_temp = 0 and ct_status = 0";
    $contract_go_res = sql_query($contract_go);

    for($j=0;$contract_go_row = sql_fetch_array($contract_go_res);$j++){

        $history_chk = sql_fetch("SELECT COUNT(*) as cnt FROM a_contract_history WHERE ct_sdate <= '{$today_data_bill}' and ct_edate >= '{$today_data_bill}' and ct_idx = '{$bill_date_arr[$i]['idx']}'");

        //계약기간이 존재하는 계약들만 계산서처리
        if($history_chk['cnt'] > 0){

            $company_bill_row = sql_fetch("SELECT COUNT(*) as cnt FROM a_company_bill_list WHERE ct_idx = '{$bill_date_arr[$i]['idx']}' and company_idx = '{$contract_go_row['company_idx']}' and is_cancel = 0 and bill_years = '{$bill_years}' and bill_months = '{$bill_months}'");

            if($company_bill_row['cnt'] > 0){
                $insert_payment = "UPDATE a_company_bill_list SET
                                    bill_statusm = 2,
                                    bill_dates = '{$bill_date_arr[$i]['date']}'
                                    WHERE ct_idx = '{$bill_date_arr[$i]['idx']}' and company_idx = '{$contract_go_row['company_idx']}'";
            }else{
                $insert_payment = "INSERT INTO a_company_bill_list SET
                                    ct_idx = '{$bill_date_arr[$i]['idx']}',
                                    company_idx = '{$contract_go_row['company_idx']}',
                                    bill_statusm = 2,
                                    bill_dates = '{$bill_date_arr[$i]['date']}',
                                    bill_years = '{$bill_years}',
                                    bill_months = '{$bill_months}',
                                    created_at = '{$today}'";
            }

           

            sql_query($insert_payment);
            // array_push($query_arr, $insert_payment);
        }
    }
}

// die(result_data(false, $query_arr, []));
echo result_data(true, "세금계산서 처리가 완료되었습니다.", []);