<?php
require_once './_common.php';

//a_contract_list 계약
//a_contract_list_history 계약내역
//a_contract_list_price_history 계약 비용내역
//a_contract_list_bill 계약 지급처리 및 계산서발행
//a_contract_list_senior 계약 선임자정보

$today = date("Y-m-d H:i:s"); // 오늘날짜

$sql_temp = "";
if($save_type == 'temp'){
    $sql_temp = " is_temp = 1, ";
}else{
    $sql_temp = " is_temp = 0, ";
}

$bill_status_sql = "";

if($w == "u"){ //계약 수정

    // print_r2($_POST);
    // exit;

    if($bill_eyear != '' && $bill_emonth != ''){
        $bill_status_sql = " bill_status = 'S', "; //계산서 발행 중지
    }

    if($bill_year != '' && $bill_month != ''){
        $bill_status_sql = " bill_status = 'Y', "; //계산서 발행 시작
    }

    //계약해지
    $ct_status_no = $ct_status;

    $ct_status_sql = "";
    if($ct_status_no){

        //넘어온 월에 0을 붙인다.
        $ct_del_month = str_pad($ct_status_month, 2, "0", STR_PAD_LEFT);
        
        //계약해지 날짜계산용
        $ct_not_date = $ct_status_year . '-' . $ct_del_month . '-01'; // 계약해지 날짜 
        $ct_not_date2 = date('Y-m-d', strtotime($ct_not_date."-1 month"));
        $ct_not_date_last = date("t", strtotime($ct_not_date2));

         //계약 종료일
        $ct_deleted_at = date("Y-m", strtotime($ct_not_date2)).'-'.$ct_not_date_last;

        $ct_status_sql = " ct_status = 0, 
                           ct_del_year = '{$ct_status_year}',
                           ct_del_month = '{$ct_status_month}',
                           ct_deleted_at = '{$ct_deleted_at}', ";

        //계약해지시에는 선임자 미사용에 비고만 수정가능하도록
        $sn_use = 1;

    }else{

        //계약해지시에는 비고만 수정가능
        $ct_status_sql = " {$bill_status_sql}
                        building_id = '{$building_id}',
                        building_name = '{$building_name}',
                        industry_name = '{$industry_name}',
                        company_number = '{$company_number}',
                        resident_release = '{$resident_release}',
                        company_recom = '{$company_recom}',
                        ct_mng1 = '{$ct_mng1}',
                        ct_mng1_hp = '{$ct_mng1_hp}',
                        ct_mng2 = '{$ct_mng2}',
                        ct_mng2_hp = '{$ct_mng2_hp}', 
                        {$sql_temp} ";
    }

    // echo $ct_status_no.'<br>';

    //계약정보 수정
    $update_query = "UPDATE a_contract_list SET
                        {$ct_status_sql}
                        ct_bigo = '{$ct_bigo}'
                        WHERE ct_idx = '{$ct_idx}'";
    // echo $update_query.'<br>';
    sql_query($update_query);

    //선임자정보 수정
    $sn_common = "";
    
    //미사용 체크가 아닐 때
    if($sn_use != '1'){
        $sn_common = "sn_name = '{$sn_name}',
                    sn_hp = '{$sn_hp}',
                    sn_date = '{$sn_date}',
                    sn_sdate = '{$sn_sdate}',
                    sn_edate = '{$sn_edate}',
                    edu_sdate = '{$edu_sdate}',
                    edu_edate = '{$edu_edate}',
                    insurance_name = '{$insurance_name}',
                    insurance_date = '{$insurance_date}',
                    insurance_price = '{$insurance_price}',
                    insurance_mng = '{$insurance_mng}',";
    }

    $update_senior_query = "UPDATE a_contract_list_senior SET
                            sn_not_use = '{$sn_use}',
                            {$sn_common}
                            sn_memo = '{$sn_memo}'
                            WHERE ct_idx = '{$ct_idx}'";
    // echo $update_senior_query.'<br>';

    // exit;
    sql_query($update_senior_query);


    //계산서 발행 중지
    if($bill_eyear != '' && $bill_emonth != ''){

        //해당 계약의 계산서 발행중인 값 가져오기
        $bill_row = "SELECT * FROM a_contract_list_bill WHERE ct_idx = '{$ct_idx}' and bill_eyear = '' and bill_emonth = '' ORDER BY ctb_idx desc limit 0, 1";
        $bill_row_check = sql_fetch($bill_row);

        $update_bill_query = "UPDATE a_contract_list_bill SET
                                ctb_status = 0,
                                bill_eyear = '{$bill_eyear}',
                                bill_emonth = '{$bill_emonth}'
                                WHERE ctb_idx = '{$bill_row_check['ctb_idx']}'";
        // echo $update_bill_query;
        sql_query($update_bill_query);
    }

    //계산서 발행 시작
    if($bill_year != '' && $bill_month != ''){
        $insert_bill_query = "INSERT INTO a_contract_list_bill SET
                                ctb_status = 1,
                                ct_idx = '{$ct_idx}',
                                bill_year = '{$bill_year}',
                                bill_month = '{$bill_month}',
                                bill_type = '{$bill_type}',
                                payment_type = '{$payment_type}',
                                mb_id = '{$member['mb_id']}',
                                created_at = '{$today}'";
        // echo $insert_bill_query.'<br>';
        sql_query($insert_bill_query);
    }
   
    // exit;

    //계약 이력 및 비용 변경
    if($save_type == 'temp'){
        //계약내역 안의 비용만 수정
        $update_contract_price_query = "UPDATE a_contract_list_history SET
                                        ct_sdate = '{$ct_sdate}',
                                        ct_edate = '{$ct_edate}',
                                        ct_price = '{$ct_price}'
                                        WHERE ct_hidx = '{$ct_hidx}'";
        sql_query($update_contract_price_query);

        //비용 내역에서 정보가져오기
        $price_row = "SELECT * FROM a_contract_list_price_history WHERE ct_idx = '{$ct_idx}' and ct_hidx = '{$ct_hidx}' and ch_status = 0";
        $price_row_check = sql_fetch($price_row);

        $update_price_query = "UPDATE a_contract_list_price_history SET
                                ct_price = '{$ct_price}'
                                WHERE ctp_idx = '{$price_row_check['ctp_idx']}'";
        // echo $update_price_query.'<br>';
        sql_query($update_price_query);
    }

    //비용 변경
    if($ch_date_year2 != '' && $ch_date_month2 != ''){

        //계약 히스토리 정보 비용 종료일이 계약 기간 내인지 체크
        $history_row = "SELECT * FROM a_contract_list_history WHERE ct_hidx = '{$ct_hidx}'";
        $history_row_check = sql_fetch($history_row);

      

        //넘어온 월에 0을 붙인다.
        $pmonth = str_pad($ch_date_month2, 2, "0", STR_PAD_LEFT);
        
        //비용변경 날짜계산용
        $psdate = $ch_date_year2 . '-' . $pmonth . '-01'; // 비용 새로 시작일
        $ch_dates2 = date('Y-m-d', strtotime($psdate."-1 month"));
        $ch_dates_last = date("t", strtotime($ch_dates2));

        //비용 종료일
        $pedate = date("Y-m", strtotime($ch_dates2)).'-'.$ch_dates_last;


        // echo $psdate.'<br>';
        // echo $pedate.'<br>';
        // echo $history_row_check['ct_sdate'].'<br>';


        //비용 내역에서 정보가져오기 and ch_status = 0
        $price_row = "SELECT * FROM a_contract_list_price_history WHERE ct_idx = '{$ct_idx}' and ct_hidx = '{$ct_hidx}' and psdate < '{$pedate}' ORDER BY psdate desc limit 0, 1";
        // echo $price_row.'<br>';
        $price_row_check = sql_fetch($price_row);
      

        if(!$price_row_check){

            $price_go = "SELECT * FROM a_contract_list_price_history WHERE ct_idx = '{$ct_idx}' and ct_hidx = '{$ct_hidx}' and psdate = '{$psdate}' ORDER BY psdate desc limit 0, 1";
            // echo $price_go.'<br>';

            $price_go_check2 = sql_fetch($price_go);

            if($price_go_check2){
                // echo "수정<br>";

                $update_price_query = "UPDATE a_contract_list_price_history SET
                                            ct_price = '{$ct_price}'
                                            WHERE ctp_idx = '{$price_go_check2['ctp_idx']}'";
                // echo $update_price_query.'<br>';
                sql_query($update_price_query);

            }else{
                //시작일자까지 겹치지 않으면
                $psdate_str = mb_substr($psdate, 0, 7);
                // echo $psdate_str."은 계약 기간에 포함되지 않는 일자입니다.<br>확인 후 다시 변경해주세요.";
                alert($psdate_str."은 계약 기간에 포함되지 않는 일자입니다.\\n확인 후 다시 변경해주세요.");
            }
           
            // alert('비용 변경할 기간이 계약기간 범위가 아닙니다.');
        }else{
            // print_r2($price_row_check);

            if(!$price_row_check['ch_status']){ //비용변경 한적 없다면
                //이전 비용내역에 종료내역 업데이트

                
                $update_price_query = "UPDATE a_contract_list_price_history SET
                            ch_status = 1,
                            pyear = '{$ch_date_year2}',
                            pmonth = '{$ch_date_month2}',
                            pedate = '{$pedate}'
                            WHERE ctp_idx = '{$price_row_check['ctp_idx']}'";

                // echo $update_price_query.'<br>';


                sql_query($update_price_query);


                //새로운 비용내역 추가
                $insert_price_query = "INSERT INTO a_contract_list_price_history SET
                            ct_idx = '{$ct_idx}',
                            ct_hidx = '{$ct_hidx}',
                            ch_status = 0,
                            psdate = '{$psdate}',
                            ct_price = '{$ct_price}',
                            mb_id = '{$member['mb_id']}',
                            created_at = '{$today}'";
                // echo $insert_price_query.'<br>';

                sql_query($insert_price_query);


               

            }else{ //비용변경한적 있다면 금액만 변경

                // echo $pedate.'<br>';
                // echo $price_row_check['pedate'].'<br>';

                if($pedate < $price_row_check['pedate']){

                    
                    // echo "수정<br>";
                    //시작날짜 변경됨
                    $update_price_query = "UPDATE a_contract_list_price_history SET
                                            psdate = '{$psdate}',
                                            ct_price = '{$ct_price}'
                                            WHERE ctp_idx = '{$price_row_check['ctp_idx']}'";

                    // echo $update_price_query.'<br>';
                    sql_query($update_price_query);

                    //새로운 비용내역 추가
                    $insert_price_query = "INSERT INTO a_contract_list_price_history SET
                                            ct_idx = '{$ct_idx}',
                                            ct_hidx = '{$ct_hidx}',
                                            ch_status = 1,
                                            psdate = '{$price_row_check['psdate']}',
                                            pedate = '{$pedate}',
                                            ct_price = '{$price_row_check['ct_price']}',
                                            mb_id = '{$member['mb_id']}',
                                            created_at = '{$today}'";
                    // echo $insert_price_query.'<br>';
                    sql_query($insert_price_query);

                }else{

                    $price_go = "SELECT * FROM a_contract_list_price_history WHERE ct_idx = '{$ct_idx}' and ct_hidx = '{$ct_hidx}' and psdate = '{$psdate}' ORDER BY psdate desc limit 0, 1";
                    // echo $price_go."<br>";

                    $price_go_check = sql_fetch($price_go);

                    $update_price_query = "UPDATE a_contract_list_price_history SET
                                            ct_price = '{$ct_price}'
                                            WHERE ctp_idx = '{$price_go_check['ctp_idx']}'";
                    // echo $update_price_query.'<br>';

                    sql_query($update_price_query);
                  
                }
               
            }
        }
       
    }

    // exit;
    // 가장 최근 변경 값
    $price_history_max = "SELECT * FROM a_contract_list_price_history WHERE ct_idx = '{$ct_idx}' and ct_hidx = '{$ct_hidx}' ORDER BY psdate desc limit 0, 1";
    $price_history_max_price = sql_fetch($price_history_max);

    //계약내역 안의 비용도 수정
    $update_contract_price_query = "UPDATE a_contract_list_history SET
                                    ct_price = '{$price_history_max_price['ct_price']}'
                                    WHERE ct_hidx = '{$ct_hidx}'";
    // echo $update_contract_price_query.'<br>';
    sql_query($update_contract_price_query);

    // exit;

}else if($w == 't'){

  
    //계약 내용만 바꿈
    if($bill_eyear != '' && $bill_emonth != ''){
        $bill_status_sql = " bill_status = 'S', "; //계산서 발행 중지
    }

    if($bill_year != '' && $bill_month != ''){
        $bill_status_sql = " bill_status = 'Y', "; //계산서 발행 시작
    }

    $update_query = "UPDATE a_contract_list SET
                    {$bill_status_sql}
                    building_id = '{$building_id}',
                    building_name = '{$building_name}',
                    company_idx = '{$company_idx}',
                    company_name = '{$company_name}',
                    industry_idx = '{$industry_idx}',
                    industry_name = '{$industry_name}',
                    company_number = '{$company_number}',
                    resident_release = '{$resident_release}',
                    company_recom = '{$company_recom}',
                    ct_mng1 = '{$ct_mng1}',
                    ct_mng1_hp = '{$ct_mng1_hp}',
                    ct_mng2 = '{$ct_mng2}',
                    ct_mng2_hp = '{$ct_mng2_hp}',
                    ct_bigo = '{$ct_bigo}',
                    {$sql_temp}
                    sn_not_use = '{$sn_use}',
                    ct_sdate_all = '{$ct_sdate}',
                    ct_edate_all = '{$ct_edate}'
                    WHERE ct_idx = '{$ct_idx}'";
    //echo "계약정보 수정만".$update_query.'<br>';
    sql_query($update_query);

    // 저장된 계약 히스토리 수정
    $update_ct_history_query = "UPDATE a_contract_list_history SET
                                ct_sdate = '{$ct_sdate}',
                                ct_edate = '{$ct_edate}',
                                ct_price = '{$ct_price}'
                                WHERE ct_idx = '{$ct_idx}'";
    // echo "계약 히스토리 수정".$update_ct_history_query.'<br>';
    sql_query($update_ct_history_query);

    //저장된 금액 히스토리 수정
    $update_ct_price_query = "UPDATE a_contract_list_price_history SET
                            ct_price = '{$ct_price}',
                            psdate = '{$ct_sdate}'
                            WHERE ct_idx = '{$ct_idx}'";
    //echo "금액 히스토리 수정".$update_ct_price_query.'<br>';
    sql_query($update_ct_price_query);

    //계산서 발행
    if($bill_status){

        //계산서 발행 시작
        if($bill_year != '' && $bill_month != ''){
            $insert_bill_query = "INSERT INTO a_contract_list_bill SET
                                    ctb_status = 1,
                                    ct_idx = '{$ct_idx}',
                                    bill_year = '{$bill_year}',
                                    bill_month = '{$bill_month}',
                                    bill_type = '{$bill_type}',
                                    payment_type = '{$payment_type}',
                                    mb_id = '{$member['mb_id']}',
                                    created_at = '{$today}'";
            // echo $insert_bill_query.'<br>';
            sql_query($insert_bill_query);
        }
        
        sql_query($insert_bill_query);
        //echo "계산서 발행 입력::".$insert_bill_query.'<br>';
        //sql_query($insert_bill_query);
    }else{
        if($bill_eyear != '' && $bill_emonth != ''){

            //해당 계약의 계산서 발행중인 값 가져오기
            $bill_row = "SELECT * FROM a_contract_list_bill WHERE ct_idx = '{$ct_idx}' and bill_eyear = '' and bill_emonth = '' ORDER BY ctb_idx desc limit 0, 1";
            $bill_row_check = sql_fetch($bill_row);
    
            $update_bill_query = "UPDATE a_contract_list_bill SET
                                    ctb_status = 0,
                                    bill_eyear = '{$bill_eyear}',
                                    bill_emonth = '{$bill_emonth}'
                                    WHERE ctb_idx = '{$bill_row_check['ctb_idx']}'";
            // echo $update_bill_query;
            sql_query($update_bill_query);
        }
    }

    //선임자정보 수정
    $sn_common = "";

    if($sn_use != '1'){
        $sn_common = "sn_name = '{$sn_name}',
                    sn_hp = '{$sn_hp}',
                    sn_date = '{$sn_date}',
                    sn_sdate = '{$sn_sdate}',
                    sn_edate = '{$sn_edate}',
                    edu_sdate = '{$edu_sdate}',
                    edu_edate = '{$edu_edate}',
                    insurance_name = '{$insurance_name}',
                    insurance_date = '{$insurance_date}',
                    insurance_price = '{$insurance_price}',
                    insurance_mng = '{$insurance_mng}',";
    }

    $update_senior_query = "UPDATE a_contract_list_senior SET
                            sn_not_use = '{$sn_use}',
                            {$sn_common}
                            sn_memo = '{$sn_memo}'
                            WHERE ct_idx = '{$ct_idx}'";
    //echo "선임자 정보 수정".$update_senior_query.'<br>';
    sql_query($update_senior_query);
    // exit;
    
}else{ //계약추가

    // print_r2($_POST);
    $contract_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM a_contract_list WHERE building_id = '{$building_id}' and company_idx = '{$company_idx}' and industry_idx = '{$industry_idx}' and  ct_status = '1'");

    if($contract_confirm['cnt'] > 0) alert("이미 동일한 단지, 업체로 계약이 등록 되어있습니다.");

    if($bill_status){
        $bill_status_sql = " bill_status = 'Y', ";
    }else{
        $bill_status_sql = " bill_status = 'N', ";
    }

    //계약 추가
    $insert_query = "INSERT INTO a_contract_list SET
                        {$bill_status_sql}
                        building_id = '{$building_id}',
                        building_name = '{$building_name}',
                        company_idx = '{$company_idx}',
                        company_name = '{$company_name}',
                        industry_idx = '{$industry_idx}',
                        industry_name = '{$industry_name}',
                        company_number = '{$company_number}',
                        resident_release = '{$resident_release}',
                        company_recom = '{$company_recom}',
                        ct_mng1 = '{$ct_mng1}',
                        ct_mng1_hp = '{$ct_mng1_hp}',
                        ct_mng2 = '{$ct_mng2}',
                        ct_mng2_hp = '{$ct_mng2_hp}',
                        ct_bigo = '{$ct_bigo}',
                        {$sql_temp}
                        sn_not_use = '{$sn_use}',
                        ct_status = '1',
                        ct_sdate_all = '{$ct_sdate}',
                        ct_edate_all = '{$ct_edate}',
                        mb_id = '{$member['mb_id']}',
                        created_at = '{$today}'";

    // echo $insert_query.'<br>';
    sql_query($insert_query);
    $ct_idx = sql_insert_id(); //계약 idx
   

    //처음 추가시에는 임시저장 상관없이 저장
    //계약내역 추가
    $insert_ct_history_query = "INSERT INTO a_contract_list_history SET
                                ct_idx = '{$ct_idx}',
                                ct_sdate = '{$ct_sdate}',
                                ct_edate = '{$ct_edate}',
                                ct_price = '{$ct_price}',
                                mb_id = '{$member['mb_id']}',
                                created_at = '{$today}'";
    // echo $insert_ct_history_query.'<br>';
    sql_query($insert_ct_history_query);
    $ct_hidx = sql_insert_id(); //계약 히스토리 idx

    //비용 변경 내용 히스토리
    //신규 추가때는 pyear와 pmonth 없음    
    $insert_ct_price_query = "INSERT INTO a_contract_list_price_history SET
                                ct_idx = '{$ct_idx}',
                                ct_hidx = '{$ct_hidx}',
                                ct_price = '{$ct_price}',
                                psdate = '{$ct_sdate}',
                                mb_id = '{$member['mb_id']}',
                                created_at = '{$today}'";
    // echo $insert_ct_price_query.'<br>';
    sql_query($insert_ct_price_query);
    //     if($save_type != 'temp'){
    // }
   

    //계약추가시 계산서 발행을 했다면
    if($bill_status){
        $insert_bill_query = "INSERT INTO a_contract_list_bill SET
                                ctb_status = 1,
                                ct_idx = '{$ct_idx}',
                                bill_year = '{$bill_year}',
                                bill_month = '{$bill_month}',
                                bill_type = '{$bill_type}',
                                payment_type = '{$payment_type}',
                                mb_id = '{$member['mb_id']}',
                                created_at = '{$today}'";
        // echo $insert_bill_query.'<br>';
        sql_query($insert_bill_query);
    }


    //선임자 정보 입력 미사용 체크 아닐 때 내용 업데이트
    $sn_common = "";
    
    if($sn_use != '1'){
        $sn_common = "sn_name = '{$sn_name}',
                    sn_hp = '{$sn_hp}',
                    sn_date = '{$sn_date}',
                    sn_sdate = '{$sn_sdate}',
                    sn_edate = '{$sn_edate}',
                    edu_sdate = '{$edu_sdate}',
                    edu_edate = '{$edu_edate}',
                    insurance_name = '{$insurance_name}',
                    insurance_date = '{$insurance_date}',
                    insurance_price = '{$insurance_price}',
                    insurance_mng = '{$insurance_mng}',";
    }

    $insert_senior_query = "INSERT INTO a_contract_list_senior SET
                            sn_not_use = '{$sn_use}',
                            ct_idx = '{$ct_idx}',
                            {$sn_common}
                            sn_memo = '{$sn_memo}',
                            mb_id = '{$member['mb_id']}',
                            created_at = '{$today}'";
    // echo $insert_senior_query.'<br>';
    sql_query($insert_senior_query);
    
}

if($w == 'u'){
    alert('계약정보가 수정되었습니다.');
}else{
    alert('계약이 등록되었습니다.');
}
?>