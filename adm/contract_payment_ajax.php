<?php
require_once './_common.php';

$today = date("Y-m-d H:i:s");


//print_r2($_POST);
// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
//     // print_r2($payment_date_arr);
//     // sql_query($insert_payment);
    

    

//     die(result_data(false, "", $_POST));
// }
if(!$payment_date_ipt) die(result_data(false, "잘못된 접근입니다.", []));

$payment_date_arr = [];

$i = 0;

$arr = array();
foreach ($payment_date_ipt as $key => $dateArray) {
    //echo "키: {$key}, 날짜: {$dateArray[0]}\n";
    if($dateArray[0] != ""){
        $payment_date_arr[$i]['idx'] = $key;
        $payment_date_arr[$i]['date'] = $dateArray[0];
        $payment_date_arr[$i]['price'] = $payment_price_ipt[$key][0];

        array_push($arr, $dateArray[0]);

        $i++;
    }
    
  
}

// die(result_data(false, $payment_date_arr, []));

if(count($arr) == 0) die(result_data(false, "지급처리할 업체를 선택하여 날짜를 지정해주세요.", []));

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){

//     for($i=0;$i<count($payment_date_arr);$i++){

//         if($payment_date_arr[$i]['date'] != ''){
    
//             $contract_go = "SELECT * FROM a_contract WHERE ct_idx = '{$payment_date_arr[$i]['idx']}' and is_temp = 0 and ct_status = 0";
//             $contract_go_res = sql_query($contract_go);
//         }
//     }

//     die(result_data(false, "", $payment_date_arr));
// }


$query_arr = array();
for($i=0;$i<count($payment_date_arr);$i++){

    if($payment_date_arr[$i]['date'] != ''){

        //and ct_status = 0
        $contract_go = "SELECT * FROM a_contract WHERE ct_idx = '{$payment_date_arr[$i]['idx']}' and is_temp = 0";
        $contract_go_res = sql_query($contract_go);

        for($j=0;$contract_go_row = sql_fetch_array($contract_go_res);$j++){

            $history_chk = sql_fetch("SELECT COUNT(*) as cnt FROM a_contract_history WHERE ct_sdate <= '{$today_data}' and ct_edate >= '{$today_data}' and ct_idx = '{$contract_go_row['ct_idx']}'");


            $contract_now_sql = "SELECT ch.*, c.ct_status, c.ct_status_year, c.ct_status_month FROM a_contract_history as ch
                         LEFT JOIN a_contract as c ON ch.ct_idx = c.ct_idx
                         WHERE ch.ct_idx = '{$row['ct_idx']}' and ch.ct_sdate <= '{$month_end2}' and ch.ct_edate >= '{$month_start2}' and ch.is_del = 0";
                    // echo $contract_now_sql.'<br>';
                    $contract_now_rows = sql_fetch($contract_now_sql);

            //계약기간이 존재하는 계약들만 지급처리
            if($history_chk['cnt'] > 0){

                // $payment_sql = "SELECT COUNT(*) as cnt FROM a_payment_list WHERE ct_idx = '{$payment_date_arr[$i]['idx']}' and company_idx = '{$contract_go_row['company_idx']}' and is_cancel = 0 and bill_years = '{$bill_years}' and bill_months = '{$bill_months}'";
                $payment_rows = sql_fetch("SELECT COUNT(*) as cnt FROM a_payment_list WHERE ct_idx = '{$payment_date_arr[$i]['idx']}' and company_idx = '{$contract_go_row['company_idx']}' and is_cancel = 0 and bill_years = '{$bill_years}' and bill_months = '{$bill_months}'");

                if($payment_rows['cnt'] > 0){
                    $insert_payment = "UPDATE a_payment_list SET
                                payment_status = 2,
                                payment_price = '{$payment_date_arr[$i]['price']}',
                                payment_date = '{$payment_date_arr[$i]['date']}'
                                WHERE ct_idx = '{$payment_date_arr[$i]['idx']}' and company_idx = '{$contract_go_row['company_idx']}'";
                }else{
                    $insert_payment = "INSERT INTO a_payment_list SET
                                ct_idx = '{$payment_date_arr[$i]['idx']}',
                                company_idx = '{$contract_go_row['company_idx']}',
                                payment_status = 2,
                                payment_price = '{$payment_date_arr[$i]['price']}',
                                payment_date = '{$payment_date_arr[$i]['date']}',
                                bill_years = '{$bill_years}',
                                bill_months = '{$bill_months}',
                                created_at = '{$today}'";
                }

                

                // echo $insert_payment.'<br>';
              
                sql_query($insert_payment);
                // if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
                //     // print_r2($payment_date_arr);
                //     sql_query($insert_payment);
                // }
            }
            
            array_push($query_arr, $insert_payment);
        }
    }

}

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
//     // print_r2($payment_date_arr);
//     // sql_query($insert_payment);
//     die(result_data(false, "", $query_arr));
// }
//exit;

// die(result_data(false, $query_arr, []));
echo result_data(true, "지급처리가 완료되었습니다.", []);