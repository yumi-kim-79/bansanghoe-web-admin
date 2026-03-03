<?php
require_once './_common.php';

// print_r2($_POST);

$today = date("Y-m-d");
//$de_dates = date("Y-m");
$months = str_pad($month, 2, "0", STR_PAD_LEFT);
$de_dates = $year.'-'.$months; // 2025-07

$month_start2 = date("Y-m-01", strtotime("$de_dates-01")); // 2025-07-01
$month_end2   = date("Y-m-t", strtotime("$de_dates-01"));  // 2025-07-31

$sql_where = '';
$sql_where2 = '';

if($company_idx){
    $company_idx_sch_t = "'".implode("','", $company_idx)."'";
    $sql_where .= " and ct.company_idx IN ({$company_idx_sch_t}) ";

    $sql_where2 = " and company_idx IN ({$company_idx_sch_t}) ";
    // echo $sql_where.'<br>';
}


$ct_idx_arr = str_replace("\\", "", $ct_idx_arr);



// if($_SERVER["REMOTE_ADDR"] == ADMIN_IP){                         
//     if($transactionStatusValue){
//         $sql_where .= " ";
//     }else{
//         $sql_where .= "  ";
//     }
//     // echo $contract_sql.'<br>';
    
// }else{
//     if($transactionStatusValue){
//         $sql_where .= " ";
//     }else{
//         $sql_where .= " and ct.ct_status = '0' ";
//     }
// }

if($transactionStatusValue){
    $sql_where .= " ";
}else{
    $sql_where .= "  ";
}


// print_r($ct_idx_arr);

$contract_sql = "SELECT ch.ct_sdate as ct_sdate2, ch.ct_edate as ct_edate2, ct.*, building.is_use, building.building_name, mc.company_bank_name, mc.company_account_number, mc.company_account_name FROM a_contract_history as ch 
                    LEFT JOIN a_contract as ct on ch.ct_idx = ct.ct_idx
                    LEFT JOIN a_building as building on ct.building_id = building.building_id
                    LEFT JOIN a_manage_company as mc on mc.company_idx = ct.company_idx
                    WHERE ct.ct_idx IN ($ct_idx_arr) and ct.is_del = 0 and ct.is_temp = 0 and  (ch.ct_sdate <= '{$month_end2}' and ch.ct_edate >= '{$month_start2}') {$sql_where} GROUP BY ct.ct_idx order by ct.company_name asc, building.building_name asc, ct.ct_idx desc";
    if($_SERVER["REMOTE_ADDR"] == ADMIN_IP){                         
        // echo $contract_sql.'<br>';
        // exit;
    }

// $contract_res = sql_query($contract_sql);
$contract_res2 = sql_query($contract_sql);
// $contract_total = sql_num_rows($contract_res);

// print_r2($contract_arr);


$contract_count = [];
$contract_ct = [];

$ct_arr_data = [];



foreach($contract_res2 as $row){

    if($row['ct_status'] == 1){
        $ct_status_date = $row['ct_status_year'].'-'.$row['ct_status_month'].'-01';
        $ct_status_date = date("Y-m-t", strtotime($ct_status_date));

        if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
           
            // echo $ct_status_date.'<br>';
            // echo $month_end2.'<br>';
        }
        if($ct_status_date >= $month_end2){
            // $contract_total = $contract_total - 1;
            array_push($ct_arr_data, $row);
            if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                // echo '작음<br>';
            }
        }
    }else{

        if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
            // print_r2($row);
        }
        array_push($ct_arr_data, $row);
    }
    

    
}


if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo '종료된 계약서 제외 리스트<br>';
    // print_r2($ct_arr_data);
    // exit;
    // print_r2($contract_count2);
}


foreach($ct_arr_data as $row){

    // print_r2($row);
    $contract_count[$row['company_idx']] = isset($contract_count[$row['company_idx']]) ? $contract_count[$row['company_idx']] + 1 : 1;


    $contract_ct[$row['company_idx']][] = $row['ct_idx'];
    // echo $row['company_idx'].'<br>';

}


// print_r2($contract_ct);

$bill_months = str_pad($nowMonth, 2, "0", STR_PAD_LEFT); // 월 앞자리 0 붙이기
?>
<form name="fpayment" id="fpayment" action="./contract_payment_ajax.php" onsubmit="return fpayment_submit(this);" method="post">
    <input type="hidden" name="today_data" value="<?php echo date("Y-m-d"); ?>">
    <input type="hidden" name="submit_end" id="submit_end" value="">

    <input type="hidden" name="bill_years" value="<?php echo $year; ?>">
    <input type="hidden" name="bill_months" value="<?php echo $months; ?>">
    <div class="cm_pop_title">
        <!-- <?php echo $year.'년 '.$nowMonth.'월'?>  -->
        지급처리
        <button type="button" onclick="ct_payment_excel_download();" class="ctn_btn ver4">엑셀 다운로드</button>
    </div>
    <table class="payment_prg_table">
        <thead>
            <tr>
                <th>
                    <input type="checkbox" name="payment_chk_all" id="payment_chk_all">
                </th>
                <th>업종</th>
                <th>업체명</th>
                <th>현장명</th>
                <th>비용 (<?php echo $month.'월';?>)</th>
                <th>계</th>
                <th>지급여부</th>
                <th>은행</th>
                <th>계좌번호</th>
                <th>예금주</th>
                <th>특이사항</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $printed_names = [];
            $goDate = date('Y-m');

            // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
            //     $gogo = $ct_arr_data;
            // }else{
            //     $gogo = $contract_res;
            // }
            $gogo = $ct_arr_data;

            foreach($gogo as $row){                
                ?>
                <tr>
                <?php if(!isset($printed_names[$row['company_idx']])){

                    
                    ?>
                    <td rowspan="<?php echo $contract_count[$row['company_idx']]?>">
                        <?php

                        

                        $ct_idx_arr2 = "'".implode("','", $contract_ct[$row['company_idx']])."'";

                        // echo $ct_idx_arr2.'<br>';

                        $payment_rows = sql_fetch("SELECT COUNT(*) as cnt FROM a_payment_list WHERE ct_idx IN ($ct_idx_arr2) and payment_status NOT IN (0, 1) and company_idx = '{$row['company_idx']}' and is_cancel = 0 and bill_years = '{$year}' and bill_months = '{$months}'");

                        // echo "SELECT COUNT(*) as cnt FROM a_payment_list WHERE ct_idx IN ($ct_idx_arr2) and  payment_status != 1 and company_idx = '{$row['company_idx']}' and is_cancel = 0 and bill_years = '{$year}' and bill_months = '{$months}'";

                        if($_SERVER["REMOTE_ADDR"] == ADMIN_IP){

                            // echo "SELECT COUNT(*) as cnt FROM a_payment_list WHERE ct_idx IN ($ct_idx_arr2) and payment_status NOT IN (0, 1) and company_idx = '{$row['company_idx']}' and is_cancel = 0 and bill_years = '{$year}' and bill_months = '{$months}'";
                            // echo "SELECT COUNT(*) as cnt FROM a_payment_list WHERE ct_idx IN ($ct_idx_arr) and company_idx = '{$row['company_idx']}' and is_cancel = 0 and bill_years = '{$year}' and bill_months = '{$months}'".'<br>';
                            // echo $payment_rows['cnt'].'<br>';
                            // echo $contract_count[$row['company_idx']].'<br>';
                            // echo $contract_count[$row['company_idx']];
                            // echo "SELECT *, COUNT(*) as cnt FROM a_payment_list WHERE company_idx = '{$row['company_idx']}' and is_cancel = 0 and bill_years = '{$year}' and bill_months = '{$bill_months}'";
                        }
                        ?>
                        <input type="checkbox" name="payment_chk" value="<?php echo $row['company_idx']; ?>" class="payment_chk payment_chk2" <?php echo $payment_rows['cnt'] == $contract_count[$row['company_idx']] ? 'disabled' : '';?>>
                      
                    </td>
                <?php }?>
                <td>
                    <?php
                    $pl_sql = "SELECT * FROM a_payment_list WHERE ct_idx = '{$row['ct_idx']}' and bill_years = '{$year}' and bill_months = '{$months}'";
                    
                    if($_SERVER["REMOTE_ADDR"] == '59.16.155.80'){
                        //  echo $pl_sql;
                    }
                    $pl_row = sql_fetch($pl_sql);
                    
                    if($pl_row['payment_date'] == ''){
                    ?>
                    <input type="hidden" name="payment_date_ipt[<?php echo $row['ct_idx']; ?>][]" class="payment_date_ipt<?php echo $row['company_idx']; ?>" value="">
                    <?php }?>
                    <?php echo $row['industry_name']; ?>
                </td>
                <td><?php echo $row['company_name']; ?></td>
                <td><?php echo $row['building_name']; ?></td>
                <td>
                    
                    <?php

                    
                    $pl_sql = "SELECT *, COUNT(*) as cnt FROM a_payment_list WHERE ct_idx = '{$row['ct_idx']}' and bill_years = '{$year}' and bill_months = '{$months}' and is_cancel = 0";
                    // echo $pl_sql.'<br>';
                    $pl_row = sql_fetch($pl_sql);


                    $contract_now_sql = "SELECT ch.*, c.ct_status, c.ct_status_year, c.ct_status_month FROM a_contract_history as ch
                         LEFT JOIN a_contract as c ON ch.ct_idx = c.ct_idx
                         WHERE ch.ct_idx = '{$row['ct_idx']}' and ch.ct_sdate <= '{$month_end2}' and ch.ct_edate >= '{$month_start2}' and ch.is_del = 0";
                    // echo $contract_now_sql.'<br>';
                    $contract_now_rows = sql_fetch($contract_now_sql);
                    

                    $history_price = "SELECT * FROM a_contract_price_history WHERE ch_start_date <= '{$month_end2}' and ct_idx = '{$row['ct_idx']}' ORDER BY cph_idx desc limit 0, 1";
                    // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $history_price.'<br>';
                    $history_price_row = sql_fetch($history_price);


                    if($pl_row['cnt'] > 0){
                        
                        if($pl_row['is_services']){
                            //$first_price = '0 (서비스)';
            
                            $month_price = '0 (서비스)';
                            $value_price = 0;
            
                        }else{
            
                            $month_price = number_format($pl_row['payment_price']);

                            $value_price = $pl_row['payment_price'];
                        }
                    }else{
            
                        $month_price = $contract_now_rows['cth_idx'] != '' ? number_format($history_price_row['price']) : '-';

                        $value_price = $contract_now_rows['cth_idx'] != '' ? $history_price_row['price'] : 0;
                        //echo $month_price.'<br>';
                    }

                    echo $month_price;
                    
                    
                    ?>
                    <input type="hidden" name="payment_price_ipt[<?php echo $row['ct_idx']; ?>][]" class="payment_price_ipt<?php echo $row['company_idx']; ?>" value="<?php echo $value_price; ?>">
                </td>
                <?php 
                
                //print_r2($contract_count);
                if(!isset($printed_names[$row['company_idx']])){?>
                    <td rowspan="<?php echo $contract_count[$row['company_idx']]?>">
                        <?php
                        
                        //and ct.ct_status = 0
                        // if($_SERVER["REMOTE_ADDR"] == ADMIN_IP){
                        //     //  echo $history_price2;
                        //     $history_where = "";
                        // }else{
                        //     $history_where = " and ct.ct_status = 0 ";
                        // }

                        $history_where = "";

                        $history_price2 = "SELECT ch.*, ct.company_idx, ct.ct_status, ct.is_temp FROM a_contract_history as ch
                                            LEFT JOIN a_contract as ct on ch.ct_idx = ct.ct_idx
                                            WHERE ch.ct_sdate <= '{$month_end2}' and ch.ct_edate >= '{$month_start2}'
                                            and ct.ct_idx IN ($ct_idx_arr) and ct.company_idx = '{$row['company_idx']}' {$history_where} and ct.is_temp = 0 ORDER BY ct_idx desc";
                        $history_price_res2 = sql_query($history_price2);

                        // echo $history_price2.'<br>';

                        if($_SERVER["REMOTE_ADDR"] == ADMIN_IP){
                            //  echo $history_price2;
                        }
                        $total_prices = 0;


                        while($history_price_row2 = sql_fetch_array($history_price_res2)){

                            $pl_sql = "SELECT *, COUNT(*) as cnt FROM a_payment_list WHERE ct_idx = '{$history_price_row2['ct_idx']}' and bill_years = '{$year}' and bill_months = '{$months}'";
                            // echo $pl_sql.'<br>';
                            $pl_row = sql_fetch($pl_sql);


                            $contract_now_sql = "SELECT ch.*, c.ct_status, c.ct_status_year, c.ct_status_month FROM a_contract_history as ch
                            LEFT JOIN a_contract as c ON ch.ct_idx = c.ct_idx
                            WHERE ch.ct_idx = '{$history_price_row2['ct_idx']}' and ch.ct_sdate <= '{$month_end2}' and ch.ct_edate >= '{$month_start2}' and ch.is_del = 0";
                            // echo $contract_now_sql.'<br>';
                            $contract_now_rows = sql_fetch($contract_now_sql);


                            $history_price = "SELECT * FROM a_contract_price_history WHERE ch_start_date <= '{$month_end2}' and ct_idx = '{$history_price_row2['ct_idx']}' ORDER BY cph_idx desc limit 0, 1";
                            // echo $history_price.'<br>';
                            $history_price_row = sql_fetch($history_price);

                            if($pl_row['cnt'] > 0){
            
                                if($pl_row['is_services']){
                                    //$first_price = '0 (서비스)';
                    
                                    $month_price = '0 (서비스)';
                    
                                }else{
                    
                                    $month_price = number_format($pl_row['payment_price']);
        
                                    
                                }
                            }else{
                    
                                $month_price = $contract_now_rows['cth_idx'] != '' ? number_format($history_price_row['price']) : '-';
        
                                //echo $month_price.'<br>';
                            }

                            $total_prices += $month_price != '-' ? (int)str_replace(',', '', $month_price) : 0;

                            // echo $month_price.'<br>';
                        }

                       

                        

                        echo number_format($total_prices);

                        
                        
                        ?>
                    </td>
                    <?php $printed_names[$row['company_idx']] = true;?>
                <?php }?>
                <td>
                    <?php
                    $pl_sql = "SELECT * FROM a_payment_list WHERE ct_idx = '{$row['ct_idx']}' and bill_years = '{$year}' and payment_status != 1 and bill_months = '{$months}'";
                        
                    if($_SERVER["REMOTE_ADDR"] == '59.16.155.80'){
                        //  echo $pl_sql;
                    }
                    $pl_row = sql_fetch($pl_sql);
                    
                    ?>
                    <div class="payment_date_chk_all <?php echo $pl_row['payment_date'] == '' ? 'payment_date_chk'.$row['company_idx'] : '' ?>">
                        <?php 

                        

                        echo $pl_row['is_services'] ? '서비스' : $pl_row['payment_date'];
                        ?>
                    </div>
                </td>
                <td><?php echo $row['company_bank_name']; ?></td>
                <td><?php echo $row['company_account_number']; ?></td>
                <td><?php echo $row['company_account_name']; ?></td>
                <td>
                    <?php echo $pl_row['payment_status'] == '4' ? nl2br($pl_row['payment_memo']) : '';?>
                </td>
                </tr>
            <?php }?>
            <?php if(count($ct_arr_data) == 0){?>
            <tr>
                <td colspan='11'>리스트가 없습니다.</td>
            </tr>
            <?php }?>
        </tbody>
    </table>
    <?php 
    $contract_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM a_contract WHERE is_del = 0 and is_temp = 0");
    

    $p_date = date("Y-m");
    $payment_cnt = sql_fetch("SELECT COUNT(DISTINCT ct_idx) as cnt FROM a_payment_list WHERE payment_status != 1 and is_cancel = 0 and bill_years = '{$year}' and bill_months = '{$months}' and payment_date != '' and ct_idx IN ($ct_idx_arr) {$sql_where2}");

    // echo "SELECT COUNT(DISTINCT ct_idx) as cnt FROM a_payment_list WHERE payment_status != 1 and is_cancel = 0 and bill_years = '{$year}' and bill_months = '{$months}' and payment_date != '' and ct_idx IN ($ct_idx_arr) {$sql_where2}";
    
    ?>

    <table class="payment_total_table">
        <tr>
            <th>계약건수</th>
            <td><?php echo count($ct_arr_data); ?></td>
            <th>지급 업체 수</th>
            <td><?php echo $payment_cnt['cnt']; ?></td>
            <th>미지급 업체 수</th>
            <td><?php echo count($ct_arr_data) - $payment_cnt['cnt']; ?></td>
        </tr>
    </table>
    <div class="contract_btn_wraps mgt20">
        <button type="button" name="save_type" class="ct_btns03" onclick="payment_prg_pop_open();">지급처리</button>
        <button type="button" name="save_type" class="ct_btns02" onclick="payment_save();">최종저장</button> 
        <!-- <button type="submit" name="save_type" class="ct_btns02" onclick="payment_save();">최종저장</button> -->
        <button type="button" name="save_type" class="ct_btns01" onclick="payment_pop_close_handler();">닫기</button>
    </div>
    <div class="payment_date_box_wrap payment_date_box_wrap1 mgt20">
        <div class="payment_date_box">
            <div class="ct_status_date_label">지급 날짜 설정</div>
            <input type="text" name="payment_date" id="payment_date" class="bansang_ipt ver2 ipt_date">
            <button type="button" onclick="payment_prg_handler();">지급 처리 반영</button>
        </div>
        <p>* 지급 날짜 설정 후 지급 처리 반영 버튼을 클릭해야만 지급처리 내역에 반영됩니다.</p>
        <p>* 지급 처리 반영은 임시저장의 개념이며 최종 저장 해야지만 지급 처리 완료 됩니다.</p>
    </div>
</form>
<script>
$(function(){
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
});

document.querySelectorAll('.ipt_date').forEach(function(input) {
    input.setAttribute('maxlength', '10');
});

document.querySelectorAll('.ipt_date').forEach(function (input) {
    input.addEventListener('input', function () {
        let val = this.value.replace(/\D/g, '').substring(0, 8);
        if (val.length >= 5) {
            val = val.replace(/(\d{4})(\d{2})(\d{0,2})/, '$1-$2-$3');
        } else if (val.length >= 3) {
            val = val.replace(/(\d{4})(\d{0,2})/, '$1-$2');
        }
        this.value = val;
    });
});

//지급리스트 전체선택
$("#payment_chk_all").click(function() {
	if($("#payment_chk_all").is(":checked")){
		$(".payment_chk").prop("checked", true);
	}else{
		$(".payment_chk").prop("checked", false);
	}
	$(".payment_chk").change();
});
$(".payment_chk").click(function() {
	var total = $(".payment_chk").length;
	var checked = $(".payment_chk:checked").length;

	if(total != checked) $("#payment_chk_all").prop("checked", false);
	else $("#payment_chk_all").prop("checked", true); 
});


//지급처리 엑셀
function ct_payment_excel_download(){
    console.log(currentYear, currentMonth, viewAll);

    let ct_idx_arr = "<?php echo $ct_idx_arr; ?>";

    let params = new URLSearchParams(window.location.search);
    params = params + "&year=" + currentYear + "&month=" + currentMonth + "&viewAll=" + (viewAll ? 1 : 0) + "&ct_idx_arr=" + ct_idx_arr;

    // console.log(params.toString());

    window.location.href = `./contract_payment_list_excel_download.php?${params.toString()}`;
}

</script>