<?php
require_once './_common.php';

//print_r2($_POST);


$today = date("Y-m-d H:i:s");

if($ct_idx == '') alert('오류가 발생하였습니다.');
if($extend_price == '') alert('연장 금액을 입력하세요.');
if($extend_price < 0) alert('연장 금액은 0원 이상 입력하세요.');
if($extend_sdate > $last_date) alert('연장 시작일은 마지막 계약 종료일 이후로 설정해주세요.');

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $extend_sdate.'-'.$extend_edate.'-'.$extend_price.'-'.$ct_idx.'<br>';
    // exit;

    echo "통과<br>";
    // exit;
}

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
//     $extend_sdate_year = date("Y", strtotime($extend_sdate));
// $extend_sdate_month = date("n", strtotime($extend_sdate));

//     $price_history_confirm = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_contract_price_history WHERE ch_date_year = '{$extend_sdate_year}' and ch_date_month = '{$extend_sdate_month}' and ct_idx = '{$ct_idx}'");

//     echo "SELECT *, COUNT(*) as cnt FROM a_contract_price_history WHERE ch_date_year = '{$extend_sdate_year}' and ch_date_month = '{$extend_sdate_month}' and ct_idx = '{$ct_idx}'"."<br>";
//     print_r2($price_history_confirm);
//     exit;
// }

//계약 내역
$contract_history_insert = "INSERT INTO a_contract_history SET
                            ct_idx = '{$ct_idx}',
                            ct_sdate = '{$extend_sdate}',
                            ct_edate = '{$extend_edate}',
                            ct_price = '{$extend_price}',
                            created_at = '{$today}'";
if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $contract_history_insert.'<br>';
    exit;
}else{
    sql_query($contract_history_insert);
    $cth_idx = sql_insert_id();
}



$extend_sdate_year = date("Y", strtotime($extend_sdate));
$extend_sdate_month = date("n", strtotime($extend_sdate));

//비용 히스토리
$price_history_confirm = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_contract_price_history WHERE ch_date_year = '{$extend_sdate_year}' and ch_date_month = '{$extend_sdate_month}' and ct_idx = '{$ct_idx}'");

// if($price_history_confirm['cnt'] > 0){

//     $history_query = "UPDATE a_contract_price_history SET
//                         cth_idx = '{$cth_idx}',
//                         ch_date_year = '{$extend_sdate_year}',
//                         ch_date_month = '{$extend_sdate_month}',
//                         ch_start_date = '{$extend_sdate}',
//                         ch_end_date = '{$extend_edate}',
//                         price = '{$extend_price}'
//                         WHERE cph_idx = '{$price_history_confirm['cph_idx']}'";

// }else{

//     $history_query = "INSERT INTO a_contract_price_history SET
//                         ct_idx = '{$ct_idx}',
//                         cth_idx = '{$cth_idx}',
//                         ch_date_year = '{$extend_sdate_year}',
//                         ch_date_month = '{$extend_sdate_month}',
//                         ch_start_date = '{$extend_sdate}',
//                         ch_end_date = '{$extend_edate}',
//                         price = '{$extend_price}',
//                         created_at = '{$today}'";
// }
$history_query = "INSERT INTO a_contract_price_history SET
                    ct_idx = '{$ct_idx}',
                    cth_idx = '{$cth_idx}',
                    ch_date_year = '{$extend_sdate_year}',
                    ch_date_month = '{$extend_sdate_month}',
                    ch_start_date = '{$extend_sdate}',
                    ch_end_date = '{$extend_edate}',
                    price = '{$extend_price}',
                    created_at = '{$today}'";

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
//     echo $history_query.'<br>';
//     exit;
// }

//echo $history_query.'<br>';
sql_query($history_query);




//비용 및 계약날짜 변경
$update_extract = "UPDATE a_contract SET
                    ct_edate = '{$extend_edate}',
                    ct_price = '{$extend_price}'
                    WHERE ct_idx = '{$ct_idx}'";
sql_query($update_extract);

alert('연장이 완료되었습니다.');