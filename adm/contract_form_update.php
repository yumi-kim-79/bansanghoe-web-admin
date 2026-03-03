<?php
require_once './_common.php';

// print_r2($_POST);
// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
//     print_r2($_POST);
//     exit;
// }

// exit;
$today = date("Y-m-d H:i:s");
$today2 = date("Y-m-d");

$is_temp = $save_type == 'temp' ? '1' : '0';
$ct_status_t = $save_type == 'temp' ? '0' : '1';




$sql_bill = "";
if($bill_status){

    //가장 최근 계산서 발행일자
    $confirm_bills_latest = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_contract_company_bill WHERE ct_idx = '{$ct_idx}' ORDER BY idx desc limit 0, 1");

    if($confirm_bills_latest['cnt'] > 0){
        $billSyearmonth = $confirm_bills_latest['bill_syear'].'-'.$confirm_bills_latest['bill_smonth'].'-01';
        $billSyearmonth2 = $bill_year.'-'.$bill_month.'-01'; //발행 중지 날짜

        if($billSyearmonth >= $billSyearmonth2){
            alert('계산서 발행 날짜는 최근 계산서 발행 날짜 이후로 설정해주세요.');
        }
    }

    $sql_bill = " bill_status = '{$bill_status}', ";
}

if($bill_stop_status){

    //해당 계약 가장 최근에 계산서를 발행하고 중지하지 않은 것 최신 1
    $confirm_bills_latest = sql_fetch("SELECT * FROM a_contract_company_bill WHERE ct_idx = '{$ct_idx}' and bill_eyear = '' and bill_emonth = '' ORDER BY idx desc limit 0, 1");


    $bill_smonth_latest = str_pad($confirm_bills_latest['bill_smonth'], 2, "0", STR_PAD_LEFT);
    $bill_month2_pad = str_pad($bill_month2, 2, "0", STR_PAD_LEFT);

    //계산서 발행날짜
    $billSyearmonth = $confirm_bills_latest['bill_syear'].'-'.$bill_smonth_latest.'-01';
    $billEyearmonth = $bill_year2.'-'.$bill_month2_pad.'-01'; //발행 중지 날짜

    if($billSyearmonth > $billEyearmonth){
    
        alert('계산서 발행 중지 날짜는 계산서 발행 이후로 설정해주세요.');
    }


    $sql_bill = " bill_status = '0', ";
}

$industry_row = sql_fetch("SELECT * FROM a_industry_list WHERE industry_idx = '{$industry_idx}'");

if($w == "u"){

  
    //비용변경 history
    // if($ct_price != "" && $ct_price != $ct_price_or && $ch_date_year2 != "" && $ch_date_month2 != ""  && !$is_temp){

    if($ch_date_year2 != "" && $ch_date_month2 != ""){

        $sql_pr_history_confirm = sql_fetch("SELECT * FROM a_contract_price_history WHERE ct_idx = '{$ct_idx}' and is_del = 0 ORDER BY cph_idx desc limit 0, 1");

        $months = str_pad($sql_pr_history_confirm['ch_date_month'], 2, "0", STR_PAD_LEFT);
        $months2 = str_pad($ch_date_month2, 2, "0", STR_PAD_LEFT);

        $nowMonth = $sql_pr_history_confirm['ch_date_year'].'-'.$months.'-01';
        $addMonth = $ch_date_year2.'-'.$months2.'-01';

        $oneagoDay = date('Y-m-d',strtotime($addMonth."-1 day"));


        //비용변경일이 중간에 들어온 경우 덮어쓰기 251230
        if($sql_pr_history_confirm){
            // echo '현재 변경일 : '.$nowMonth.'<br>';
            // echo '변경일 설정 : '.$addMonth.'<br>';
            // echo '변경일 -1일 : '.$oneagoDay.'<br>';

            // echo $ct_sdate.'<br>';
            // echo $sql_pr_history_confirm['ch_end_date'].'<br>';
            // echo $addMonth.'<br>';

            // print_r2($sql_pr_history_confirm);

            if($addMonth < $ct_sdate){
                alert('비용 변경은 계약 시작일 이후로 설정해주세요.');
            }


            $latest_pr_history = "SELECT * FROM a_contract_price_history WHERE ct_idx = '{$ct_idx}' and ch_end_date > '{$addMonth}' and cph_idx != '{$sql_pr_history_confirm['cph_idx']}' ORDER BY cph_idx desc limit 0, 1";
            $latest_pr_history_row = sql_fetch($latest_pr_history);

            if($latest_pr_history_row){
                $update_bf_price = "UPDATE a_contract_price_history SET
                                    ch_end_date = '{$oneagoDay}'
                                    WHERE cph_idx = '{$latest_pr_history_row['cph_idx']}'";
                sql_query($update_bf_price);
                // echo $update_bf_price.'<br>';


                $update_or_price = "UPDATE a_contract_price_history SET
                                    price = '{$ct_price}',
                                    ch_date_year = '{$ch_date_year2}',
                                    ch_date_month = '{$ch_date_month2}',
                                    ch_start_date = '{$addMonth}'
                                    WHERE cph_idx = '{$sql_pr_history_confirm['cph_idx']}'";
                sql_query($update_or_price);
                // echo $update_or_price.'<br>';
            }else{


                if($addMonth > $sql_pr_history_confirm['ch_end_date']){
                    $date_sql = " ch_start_date = '{$addMonth}', ";
                }else{
                    $date_sql = " ch_start_date = '{$addMonth}',
                                  ch_end_date = '{$sql_pr_history_confirm['ch_end_date']}', ";
                }

                $insert_af_price = "INSERT INTO a_contract_price_history SET
                                    ct_idx = '{$ct_idx}',
                                    price = '{$ct_price}',
                                    ch_date_year = '{$ch_date_year2}',
                                    ch_date_month = '{$ch_date_month2}',
                                    {$date_sql}
                                    created_at = '{$today}'";
                sql_query($insert_af_price);
                // echo $insert_af_price.'<br>';


                //설정 날짜로 덮어쓰기
                $update_or_price = "UPDATE a_contract_price_history SET
                                    ch_end_date = '{$oneagoDay}'
                                    WHERE cph_idx = '{$sql_pr_history_confirm['cph_idx']}'";
                sql_query($update_or_price);
                // echo $update_or_price.'<br>';
            }
           
        }

    }
    
     
   

    $contract_confirm = sql_fetch("SELECT is_temp FROM a_contract WHERE ct_idx = '{$ct_idx}'");

    //계약해지
    $sql_status = "";
    if($ct_status){
        $sql_status = " ct_status = '{$ct_status}',
                        ct_status_year = '{$ct_status_year}',
                        ct_status_month = '{$ct_status_month}', ";
    }

    $sql_price = "";
    if($ct_price != ''){
        $sql_price = " ct_price = '{$ct_price}', ";
    }

    $sql_stop_bill = "";
    if($bill_stop_status){
        $sql_stop_bill = " bill_stop_status = '{$bill_stop_status}',";
    }

   

   
    //계산서 발행 정보 저장

    //계산서 발행 체크
    if($bill_status){

        //해당 년도 월로 계산서 저장한 내역 체크
        $confirm_bills = sql_fetch("SELECT COUNT(*) as cnt FROM a_contract_company_bill WHERE ct_idx = '{$ct_idx}' and bill_syear = '{$bill_year}' and bill_smonth = '{$bill_month}'");


        $bill_month_pad = str_pad($bill_month, 2, "0", STR_PAD_LEFT);
        $billSyearmonth2 = $bill_year.'-'.$bill_month_pad.'-01'; //발행 중지 날짜

        if($confirm_bills['cnt'] > 0){ //있으면 내용 업데이트
            $update_bills = "UPDATE a_contract_company_bill SET
                            bill_type = '{$bill_type}', 
                            payment_type = '{$payment_type}',
                            bill_syear = '{$bill_year}', 
                            bill_smonth = '{$bill_month}',
                            bill_sdate = '{$billSyearmonth2}'
                            WHERE ct_idx = '{$ct_idx}'";
        }else{ //없으면 추가

            //가장 최근 계산서 발행일자
            $confirm_bills_latest = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_contract_company_bill WHERE ct_idx = '{$ct_idx}' ORDER BY idx desc limit 0, 1");

           
            if($confirm_bills_latest['cnt'] > 0){

                $bill_smonth_latest = str_pad($confirm_bills_latest['bill_smonth'], 2, "0", STR_PAD_LEFT);

                $billSyearmonth = $confirm_bills_latest['bill_syear'].'-'.$bill_smonth_latest.'-01';

                if($billSyearmonth > $billSyearmonth2){
                    alert('계산서 발행 날짜는 최근 계산서 발행 날짜 이후로 설정해주세요.');
                }
            }

            $update_bills = "INSERT INTO a_contract_company_bill SET
                            ct_idx = '{$ct_idx}',
                            company_idx = '{$company_idx}',
                            bill_type = '{$bill_type}', 
                            payment_type = '{$payment_type}',
                            bill_syear = '{$bill_year}', 
                            bill_smonth = '{$bill_month}', 
                            bill_sdate = '{$billSyearmonth2}', 
                            created_at = '{$today}'";
        }

        sql_query($update_bills);

    }

    //계산서 발행 중지
    if($bill_stop_status){

        //해당 계약 가장 최근에 계산서를 발행하고 중지하지 않은 것 최신 1
        $confirm_bills_latest = sql_fetch("SELECT * FROM a_contract_company_bill WHERE ct_idx = '{$ct_idx}' and bill_eyear = '' and bill_emonth = '' ORDER BY idx desc limit 0, 1");

        $bill_smonth_latest = str_pad($confirm_bills_latest['bill_smonth'], 2, "0", STR_PAD_LEFT);
        $bill_month2_pad = str_pad($bill_month2, 2, "0", STR_PAD_LEFT);

        //계산서 발행날짜
        $billSyearmonth = $confirm_bills_latest['bill_syear'].'-'.$bill_smonth_latest.'-01';
        $billEyearmonth = $bill_year2.'-'.$bill_month2_pad.'-01'; //발행 중지 날짜


        $last_date = date('t', strtotime($billEyearmonth));
        $billEyearmonth2 = $bill_year2.'-'.$bill_month2_pad.'-'.$last_date; //발행 중지 날짜


        if($billSyearmonth > $billEyearmonth){

      
            alert('계산서 발행 중지 날짜는 계산서 발행 이후로 설정해주세요.');
        }

        $update_bills_stop = "UPDATE a_contract_company_bill SET
                            bill_eyear = '{$bill_year2}',
                            bill_emonth = '{$bill_month2}',
                            bill_edate = '{$billEyearmonth2}'
                            WHERE idx = '{$confirm_bills_latest['idx']}'";

        //echo $update_bills_stop.'<br>';
        //exit;
        sql_query($update_bills_stop);

    }
   
   
    if($is_temp){ //임시 저장일 때
        $insert_history = "UPDATE a_contract_history SET
                            ct_sdate = '{$ct_sdate}',
                            ct_edate = '{$ct_edate}',
                            ct_price = '{$ct_price}'
                            WHERE ct_idx = '{$ct_idx}'";
    
        sql_query($insert_history);


        $update_price = "UPDATE a_contract_price_history SET
                            ch_start_date = '{$ct_sdate}',
                            ch_end_date = '{$ct_edate}',
                            price = '{$ct_price}'
                            WHERE ct_idx = '{$ct_idx}'";
        sql_query($update_price);
    }
    
    // if(!$is_temp && $contract_confirm['is_temp'] == '1'){
    // }

  

     //선임자 정보 미사용 체크 안했을 때
     if(!$sn_use){

        $sn_use_chk = sql_fetch("SELECT COUNT(*) as cnt FROM a_senior WHERE ct_idx = '{$ct_idx}'");

        if($sn_use_chk['cnt'] > 0){

            $s_sql_up = "UPDATE a_senior SET
                        sn_name = '{$sn_name}',
                        sn_hp = '{$sn_hp}',
                        sn_date = '{$sn_date}',
                        sn_sdate = '{$sn_sdate}',
                        sn_edate = '{$sn_edate}',
                        edu_sdate = '{$edu_sdate}',
                        edu_edate = '{$edu_edate}',
                        insurance_name = '{$insurance_name}',
                        insurance_date = '{$insurance_date}',
                        insurance_price = '{$insurance_price}',
                        insurance_mng = '{$insurance_mng}',
                        sn_memo = '{$sn_memo}',
                        not_use = 0
                        WHERE ct_idx = '{$ct_idx}'";
            sql_query($s_sql_up);

        }else{
            $s_sql = "INSERT INTO a_senior SET
                        ct_idx = '{$ct_idx}',
                        sn_name = '{$sn_name}',
                        sn_hp = '{$sn_hp}',
                        sn_date = '{$sn_date}',
                        sn_sdate = '{$sn_sdate}',
                        sn_edate = '{$sn_edate}',
                        edu_sdate = '{$edu_sdate}',
                        edu_edate = '{$edu_edate}',
                        insurance_name = '{$insurance_name}',
                        insurance_date = '{$insurance_date}',
                        insurance_price = '{$insurance_price}',
                        insurance_mng = '{$insurance_mng}',
                        sn_memo = '{$sn_memo}',
                        created_at = '{$today}'";
            //echo $s_sql.'<br>';
            sql_query($s_sql);
        }
    }else{

        //미사용 체크시 선임자 정보 미사용
        $s_sql_up = "UPDATE a_senior SET
                    sn_memo = '{$sn_memo}',
                    not_use = 1
                    WHERE ct_idx = '{$ct_idx}'";
        
        sql_query($s_sql_up);
    }

    if($ct_status_no == 'Y'){
        $update = "UPDATE a_contract SET
                    mng_memo = '{$mng_memo}'
                    WHERE ct_idx = '{$ct_idx}'";
    }else{
        $update = "UPDATE a_contract SET
                    is_temp = '{$is_temp}',
                    {$sql_status}
                    building_id = '{$building_id}',
                    industry_idx = '{$industry_idx}',
                    industry_name = '{$industry_row['industry_name']}',
                    company_idx = '{$company_idx}',
                    company_name = '{$company_name}',
                    company_tel = '{$company_tel}',
                    resident_release = '{$resident_release}',
                    company_recom = '{$company_recom}',
                    {$sql_price}
                    {$sql_bill}
                    {$sql_stop_bill}
                    mng_name1 = '{$mng_name1}',
                    mng_hp1 = '{$mng_hp1}',
                    mng_name2 = '{$mng_name2}',
                    mng_hp2 = '{$mng_hp2}',
                    mng_memo = '{$mng_memo}',
                    sn_use = '{$sn_use}'
                    WHERE ct_idx = '{$ct_idx}'";
    }

    
    

    sql_query($update);

}else{

    //ct_status 0 계약상태
    $contract_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM a_contract WHERE building_id = '{$building_id}' and company_idx = '{$company_idx}' and industry_idx = '{$industry_idx}' and ct_status = '0'");

    // 주석처리 250722
    // if($contract_confirm['cnt'] > 0) alert("이미 동일한 단지, 업체, 업종으로 계약이 등록 되어있습니다.");

    $sql = "INSERT INTO a_contract SET
            mb_id = '{$member['mb_id']}',
            is_temp = '{$is_temp}',
            building_id = '{$building_id}',
            company_idx = '{$company_idx}',
            company_name = '{$company_name}',
            industry_idx = '{$industry_idx}',
            industry_name = '{$industry_row['industry_name']}',
            company_tel = '{$company_tel}',
            resident_release = '{$resident_release}',
            company_recom = '{$company_recom}',
            {$sql_bill}
            ct_sdate = '{$ct_sdate}',
            ct_edate = '{$ct_edate}',
            ct_price = '{$ct_price}',
            mng_name1 = '{$mng_name1}',
            mng_hp1 = '{$mng_hp1}',
            mng_name2 = '{$mng_name2}',
            mng_hp2 = '{$mng_hp2}',
            mng_memo = '{$mng_memo}',
            sn_use = '{$sn_use}',
            created_at = '{$today}'";

    //echo '계약추가 : '.$sql.'<br>';


    //exit;
    sql_query($sql);
    $ct_idx = sql_insert_id();


    //비용변경 history
    $history_date_year = date("Y", strtotime($today));
    $history_date_month = date("n", strtotime($today));

    $ct_sdate_format = date("Y-m-d", strtotime($ct_sdate));
    $ct_edate_format = date("Y-m-d", strtotime($ct_edate));

    $sql_price_history = "INSERT INTO a_contract_price_history SET
                            ct_idx = '{$ct_idx}',
                            ch_date_year = '{$history_date_year}',
                            ch_date_month = '{$history_date_month}',
                            ch_start_date = '{$ct_sdate_format}',
                            ch_end_date = '{$ct_edate_format}',
                            price = '{$ct_price}',
                            created_at = '{$today}'";
    //echo '금액 히스토리 추가 : '.$sql_price_history.'<br>';
    sql_query($sql_price_history);
    // if(!$is_temp){
    // }
  

    //선임자 정보 미사용 체크 안했을 때
    if(!$sn_use){
        $sn_date = $sn_date != '' ? $sn_date : NULL;
        $sn_sdate = $sn_sdate != '' ? $sn_sdate : NULL;
        $sn_edate = $sn_edate != '' ? $sn_edate : NULL;
        $edu_sdate = $edu_sdate != '' ? $edu_sdate : null;
        $edu_edate = $edu_edate != '' ? $edu_edate : NULL;

        $s_sql = "INSERT INTO a_senior SET
                    ct_idx = '{$ct_idx}',
                    sn_name = '{$sn_name}',
                    sn_hp = '{$sn_hp}',
                    sn_date = '{$sn_date}',
                    sn_sdate = '{$sn_sdate}',
                    sn_edate = '{$sn_edate}',
                    edu_sdate = '{$edu_sdate}',
                    edu_edate = '{$edu_edate}',
                    insurance_name = '{$insurance_name}',
                    insurance_date = '{$insurance_date}',
                    insurance_price = '{$insurance_price}',
                    insurance_mng = '{$insurance_mng}',
                    sn_memo = '{$sn_memo}',
                    not_use = 0,
                    created_at = '{$today}'";
        // echo '선임자 정보추가 : '.$s_sql.'<br>';
        sql_query($s_sql);
    }

  

    $insert_history = "INSERT INTO a_contract_history SET
                        ct_idx = '{$ct_idx}',
                        ct_sdate = '{$ct_sdate}',
                        ct_edate = '{$ct_edate}',
                        ct_price = '{$ct_price}',
                        created_at = '{$today}'";
    //echo '계약 히스토리 : '.$insert_history.'<br>';
    sql_query($insert_history);

    // if(!$is_temp){
    //임시저장일 때 내역 계약 기간 리스트 저장 안했다가 수정 250520
    // }

    //계산서 발행 정보 저장

    //계산서 발행 체크
    if($bill_status){

        $update_bills = "INSERT INTO a_contract_company_bill SET
                        ct_idx = '{$ct_idx}',
                        company_idx = '{$company_idx}',
                        bill_type = '{$bill_type}', 
                        payment_type = '{$payment_type}',
                        bill_syear = '{$bill_year}', 
                        bill_smonth = '{$bill_month}', 
                        created_at = '{$today}'";
        

        sql_query($update_bills);

    }
}

//exit;

if($w == 'u'){
    alert('계약정보가 수정되었습니다.');
}else{
    alert('계약이 등록되었습니다.');
}