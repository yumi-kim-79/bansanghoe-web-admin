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


$contract_sql = "SELECT ch.ct_sdate as ct_sdate2, ch.ct_edate as ct_edate2, ct.*, building.is_use, building.building_name, mc.company_bank_name, mc.company_account_number, mc.company_account_name FROM a_contract_history as ch 
LEFT JOIN a_contract as ct on ch.ct_idx = ct.ct_idx
LEFT JOIN a_building as building on ct.building_id = building.building_id
LEFT JOIN a_manage_company as mc on mc.company_idx = ct.company_idx
WHERE ct.ct_idx IN ($ct_idx_arr) and ct.is_del = 0 and ct.is_temp = 0 and (ch.ct_sdate <= '{$month_end2}' and ch.ct_edate >= '{$month_start2}') {$sql_where} GROUP BY ct.ct_idx ORDER BY ct.company_name asc, building.building_name asc, ct.ct_idx desc";
//echo $contract_sql;
// and (ct.ct_sdate <= '{$today}' and ct.ct_edate >= '{$today}')
$is_admin = false;
if($_SERVER["REMOTE_ADDR"] == ADMIN_IP){                         
    // echo $contract_sql.'<br>';

    $is_admin = true;
    
}

// if($is_admin){
//     $contract_res2 = sql_query($contract_sql);

// }else{
//     $contract_res = sql_query($contract_sql);
//     $contract_totals = sql_num_rows($contract_res);
// }

$contract_res2 = sql_query($contract_sql);

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


// if($is_admin){
    
//     foreach($ct_arr_data as $row){

//         // print_r2($row);
//         $contract_count[$row['company_idx']] = isset($contract_count[$row['company_idx']]) ? $contract_count[$row['company_idx']] + 1 : 1;
    
    
//         $contract_ct[$row['company_idx']][] = $row['ct_idx'];
//         // echo $row['company_idx'].'<br>';
    
//     }

//     $contract_totals = count($ct_arr_data);

// }else{
//     foreach($contract_res as $row2){
//         $contract_count[$row2['company_idx']] = isset($contract_count[$row2['company_idx']]) ? $contract_count[$row2['company_idx']] + 1 : 1;
     
     
//         $contract_ct[$row2['company_idx']][] = $row2['ct_idx'];
//      }
// }
foreach($ct_arr_data as $row){

    // print_r2($row);
    $contract_count[$row['company_idx']] = isset($contract_count[$row['company_idx']]) ? $contract_count[$row['company_idx']] + 1 : 1;


    $contract_ct[$row['company_idx']][] = $row['ct_idx'];
    // echo $row['company_idx'].'<br>';

}

$contract_totals = count($ct_arr_data);

$bill_months = str_pad($nowMonth, 2, "0", STR_PAD_LEFT); // 월 앞자리 0 붙이기

?>
<form name="fbilldate" id="fbilldate" action="./contract_bill_ajax.php" onsubmit="return fbilldate_submit(this);" method="post">
    <input type="hidden" name="bill_years" value="<?php echo $year; ?>">
    <input type="hidden" name="bill_months" value="<?php echo $months; ?>">
    <input type="hidden" name="today_data_bill" value="<?php echo date("Y-m-d"); ?>">
    <input type="hidden" name="bill_submit_end" id="bill_submit_end" value="">
    <div class="cm_pop_title">
        <!-- <?php echo $year.'년 '.$nowMonth.'월'?>  -->
        세금계산서 처리

        <button type="button" onclick="ct_bill_excel_download();" class="ctn_btn ver4">엑셀 다운로드</button>
    </div>
    <table class="payment_prg_table">
        <thead>
            <tr>
                <th>
                    <input type="checkbox" name="bill_chk_all" id="bill_chk_all">
                </th>
                <th>업종</th>
                <th>업체명</th>
                <th>현장명</th>
                <th>비용 (<?php echo $month.'월';?>)</th>
                <th>계</th>
                <th>세금계산서 처리</th>
                <th>은행</th>
                <th>계좌번호</th>
                <th>예금주</th>
                <th>특이사항</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $printed_names = [];

            // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
            //     $gogo = $ct_arr_data;
            // }else{
            //     $gogo = $contract_res;
            // }

            $gogo = $ct_arr_data;
            foreach($gogo as $row){
               
                    // echo $row['ct_status'];
                    // echo $row['ct_status_year'].'-'.$row['ct_status_month'].'<br>';

                if($row['ct_status'] == 1){
                    $ct_status_date = $row['ct_status_year'].'-'.$row['ct_status_month'].'-01';
                    $ct_status_date = date("Y-m-t", strtotime($ct_status_date));

                    // echo $month_end2.'<br>';
                    // echo $ct_status_date.'<br>';

                    if($ct_status_date < $month_end2){
                        $contract_totals = $contract_totals - 1;
                        // continue;
                    }
                }
                if($_SERVER["REMOTE_ADDR"] == ADMIN_IP){  
                }    
            ?>
                <tr>
                <?php if(!isset($printed_names[$row['company_idx']])){?>
                    <td rowspan="<?php echo $contract_count[$row['company_idx']]?>">
                        <?php
                        $nowYearMonth = date('Y-m');

                        $ct_idx_arr2 = "'".implode("','", $contract_ct[$row['company_idx']])."'";


                        $c_bill_rows = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_company_bill_list WHERE ct_idx IN ($ct_idx_arr2) and company_idx = '{$row['company_idx']}' and is_cancel = 0 and bill_years = '{$year}' and bill_months = '{$months}' and bill_dates != ''");

                        // echo "SELECT *, COUNT(*) as cnt FROM a_company_bill_list WHERE ct_idx IN ($ct_idx_arr2) and company_idx = '{$row['company_idx']}' and is_cancel = 0 and bill_years = '{$year}' and bill_months = '{$months}' and bill_dates != ''"."<br>";
                        // echo $contract_count[$row['company_idx']].'<br>';
                        // echo $c_bill_rows['cnt'].'<br>';
                        ?>
                        <input type="checkbox" name="bill_chk" value="<?php echo $row['company_idx']; ?>" class="bill_chk bill_chk2" <?php echo $c_bill_rows['cnt'] == $contract_count[$row['company_idx']] ? 'disabled' : '';?>>
                        
                    </td>
                <?php }?>
                <td>
                    <?php
                    $pl_sql = "SELECT * FROM a_company_bill_list WHERE ct_idx = '{$row['ct_idx']}' and bill_years = '{$year}' and bill_months = '{$months}'";
                    
                    if($_SERVER["REMOTE_ADDR"] == '59.16.155.80'){
                        //  echo $pl_sql;
                    }
                    $pl_row = sql_fetch($pl_sql);
                    
                    if($pl_row['bill_dates'] == ''){
                    ?>
                    <input type="hidden" name="bill_date_ipt[<?php echo $row['ct_idx']; ?>][]" class="bill_date_ipt<?php echo $row['company_idx']; ?>" value="">
                    <?php }?>
                    <?php echo $row['industry_name']; ?>
                </td>
                <td><?php echo $row['company_name']; ?></td>
                <td><?php echo $row['building_name']; ?></td>
                <td>
                <?php
                                        
                    $pl_sql = "SELECT *, COUNT(*) as cnt FROM a_payment_list WHERE ct_idx = '{$row['ct_idx']}' and bill_years = '{$year}' and bill_months = '{$months}'";
                    // echo $pl_sql.'<br>';
                    $pl_row = sql_fetch($pl_sql);


                    $contract_now_sql = "SELECT ch.*, c.ct_status, c.ct_status_year, c.ct_status_month FROM a_contract_history as ch
                        LEFT JOIN a_contract as c ON ch.ct_idx = c.ct_idx
                        WHERE ch.ct_idx = '{$row['ct_idx']}' and ch.ct_sdate <= '{$month_end2}' and ch.ct_edate >= '{$month_start2}' and ch.is_del = 0";
                    // echo $contract_now_sql.'<br>';
                    $contract_now_rows = sql_fetch($contract_now_sql);


                    $history_price = "SELECT * FROM a_contract_price_history WHERE ch_start_date <= '{$month_end2}' and ct_idx = '{$row['ct_idx']}' ORDER BY cph_idx desc limit 0, 1";
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

                    echo $month_price;


                    ?>
                </td>
                <?php if(!isset($printed_names[$row['company_idx']])){?>
                    <td rowspan="<?php echo $contract_count[$row['company_idx']]?>">
                    <?php
                        
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
                     
                     $c_bills = "SELECT * FROM a_company_bill_list WHERE ct_idx = '{$row['ct_idx']}' and is_cancel = 0 and bill_years = '{$year}' and bill_months = '{$months}' and bill_dates != ''";
                     // echo $c_bills.'<br>';
                     $c_bills_rows = sql_fetch($c_bills);
                    
                    ?>
                    <div class="bill_date_chk_all <?php echo $c_bills_rows['bill_dates'] == '' ? 'bill_date_chk'.$row['company_idx'] : '' ?>">
                        <?php 
                       
                        //echo $c_bills;
                        echo $c_bills_rows['bill_dates'];
                        ?>
                    </div>
                </td>
                <td><?php echo $row['company_bank_name']; ?></td>
                <td><?php echo $row['company_account_number']; ?></td>
                <td><?php echo $row['company_account_name']; ?></td>
                <td>
                <?php echo nl2br($c_bills_rows['bills_memo']);?>
                </td>
                </tr>
            <?php }?>
            <?php if($contract_totals == 0){?>
            <tr>
                <td colspan='11'>리스트가 없습니다.</td>
            </tr>
            <?php }?>
        </tbody>
    </table>
    <?php 
    $contract_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM a_contract WHERE is_del = 0 and is_temp = 0 and ct_status = 0");
    // echo "SELECT COUNT(*) as cnt FROM a_contract WHERE is_del = 0 and is_temp = 0 and ct_status = 0 and (ct_sdate <= '{$today}' and ct_edate >= '{$today}')";

    $de_date = date("Y-m");
    $bills_cnt = sql_fetch("SELECT COUNT(DISTINCT ct_idx) as cnt FROM a_company_bill_list WHERE is_cancel = 0 and bill_years = '{$year}' and bill_months = '{$months}' and bill_dates != '' and ct_idx IN ($ct_idx_arr) {$sql_where2}");


    // echo "SELECT COUNT(DISTINCT ct_idx) as cnt FROM a_company_bill_list WHERE is_cancel = 0 and bill_years = '{$year}' and bill_months = '{$months}' and bill_dates != '' and ct_idx IN ($ct_idx_arr) {$sql_where2}";

    // echo "SELECT COUNT(*) as cnt FROM a_company_bill_list WHERE  is_cancel = 0 and bill_years = '{$year}' and bill_months = '{$months}' and bill_dates != '' and ct_idx IN ($ct_idx_arr)";
    ?>
    <table class="payment_total_table">
        <tr>
            <th>계약건수</th>
            <td><?php echo $contract_totals; ?></td>
            <th>계산서 처리 업체 수</th>
            <td><?php echo $bills_cnt['cnt']; ?></td>
            <th>계산서 미처리 업체 수</th>
            <td><?php echo $contract_totals - $bills_cnt['cnt']; ?></td>
        </tr>
    </table>
    <div class="contract_btn_wraps mgt20">
        <button type="button" name="save_type" class="ct_btns03" onclick="bill_prg_pop_open();">세금계산서 처리</button>
        <button type="button" name="save_type" class="ct_btns02" onclick="bill_save();">최종저장</button>
        <button type="button" name="save_type" class="ct_btns01" onclick="bill_pop_close_handler();">닫기</button>
    </div>
    <div class="payment_date_box_wrap payment_date_box_wrap2 mgt20">
        <div class="payment_date_box">
            <div class="ct_status_date_label">세금계산서 날짜 설정</div>
            <input type="text" name="bill_dates" id="bill_dates" class="bansang_ipt ver2 ipt_date">
            <button type="button" onclick="bills_prg_handler();">세금 계산서 처리 반영</button>
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

//세금계산서 리스트 전체선택
$("#bill_chk_all").click(function() {
	if($("#bill_chk_all").is(":checked")){
		$(".bill_chk").prop("checked", true);
	}else{
		$(".bill_chk").prop("checked", false);
	}
	$(".bill_chk").change();
});
$(".bill_chk").click(function() {
	var total = $(".bill_chk").length;
	var checked = $(".bill_chk:checked").length;

	if(total != checked) $("#bill_chk_all").prop("checked", false);
	else $("#bill_chk_all").prop("checked", true); 
});


function ct_bill_excel_download(){
    console.log(currentYear, currentMonth, viewAll);

    let ct_idx_arr = "<?php echo $ct_idx_arr; ?>";

    let params = new URLSearchParams(window.location.search);
    params = params + "&year=" + currentYear + "&month=" + currentMonth + "&viewAll=" + (viewAll ? 1 : 0) + "&ct_idx_arr=" + ct_idx_arr;

    console.log(params.toString());

    window.location.href = `./contract_bill_list_excel_download.php?${params.toString()}`;
}


</script>