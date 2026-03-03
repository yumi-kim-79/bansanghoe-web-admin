<?php
require_once './_common.php';

//날짜생성
$months = str_pad($month, 2, "0", STR_PAD_LEFT);
$dates = $year.'-'.$months.'-01';
$month_date = $year.'-'.$months;

// echo $month_date.'<br>';
$months2 = str_pad($months, 2, "0", STR_PAD_LEFT); // 월 앞자리 0 붙이기
$month_end2   = date("Y-m-t", strtotime("$year-$months2-01"));  // 2025-07-31

//계약정보
$sql = "SELECT ct.*, building.building_name FROM a_contract as ct
        LEFT JOIN a_building as building on ct.building_id = building.building_id
        WHERE ct.ct_idx = '{$ct_idx}'";
// echo $sql;
$row = sql_fetch($sql);

//히스토리에서 금액
$sql_his = "SELECT * FROM a_contract_history WHERE ct_idx = '{$ct_idx}' and (ct_sdate <= '{$edate}' and ct_edate >= '{$sdate}')";
// echo $sql_his.'<br>';
$row_his = sql_fetch($sql_his);

//계산서 종류
$sql_bill = "SELECT * FROM a_company_bill_type WHERE is_del = 0 and is_use = 1 ORDER BY is_fixed desc, bt_idx asc";
$res_bill = sql_query($sql_bill);

//계산서 정보...
$bill_stop_date = "";
if($row['bill_stop_status']){
    $bill_stop_date = " and bill_eyear <= '{$year}' and bill_emonth <= '{$month}' ";
}
$sql_select_bill = "SELECT * FROM a_contract_company_bill WHERE ct_idx = '{$ct_idx}' and ( bill_syear >= '{$year}' and bill_smonth >= '{$month}' {$bill_stop_date} )";
//echo $sql_select_bill.'<br>';
$row_select_bill = sql_fetch($sql_select_bill);

//계산서 처리 정보
$sql_bill_info = "SELECT *, COUNT(*) as cnt FROM a_company_bill_list WHERE is_cancel = 0 and ct_idx = '{$ct_idx}' and bill_years = '{$year}' and bill_months = '{$months}'";
// echo $sql_bill_info.'<br>';
$row_bill_info = sql_fetch($sql_bill_info);


$bill_company_setting = "SELECT ccb.*, pt.pt_name, bt.bill_name FROM 
a_contract_company_bill as ccb
LEFT JOIN a_payment_type as pt on ccb.payment_type = pt.pt_idx
LEFT JOIN a_company_bill_type as bt on ccb.bill_type = bt.bt_idx
WHERE ct_idx = '{$ct_idx}' and bill_sdate <= '{$month_end2}' ORDER BY idx desc limit 0, 1";
$bill_company_setting_row = sql_fetch($bill_company_setting);
// echo $bill_company_setting.'<br>';

//지급여부
$sql_payment_type = "SELECT * FROM a_payment_type WHERE is_del = 0 and is_use = 1 ORDER BY is_fixed desc, pt_idx asc";
$res_payment_type = sql_query($sql_payment_type);


$sql_payment_list = "SELECT *, COUNT(*) as cnt FROM a_payment_list WHERE is_cancel = 0 and ct_idx = '{$ct_idx}' and bill_years = '{$year}' and bill_months = '{$months}'";

if($_SERVER["REMOTE_ADDR"] == ADMIN_IP){
    echo $sql_payment_list.'<br>';
}
$sql_payment_list_row = sql_fetch($sql_payment_list);



$contract_history_price = "SELECT * FROM a_contract_price_history WHERE ct_idx = '{$row['ct_idx']}'  and (ch_start_date <= '{$month_end2}') and ch_start_date != '' ORDER BY cph_idx desc limit 0, 1";
if($_SERVER["REMOTE_ADDR"] == ADMIN_IP){
    echo $contract_history_price;
 
    // echo $sql_payment_list_row['cnt'].'<br>';
}
$contract_history_price_row = sql_fetch($contract_history_price);

$pn_ct_price = 0;

if($sql_payment_list_row['cnt'] > 0){
    $pn_ct_price = $sql_payment_list_row['is_services'] ? '0 (서비스)' : $sql_payment_list_row['payment_price'];
}else{
    $pn_ct_price = $contract_history_price_row['price'];
}

?>
<form name="fbillpayment" id="fbillpayment" action="./contract_bill_payment_ajax.php" onsubmit="return fbillpayment_submit(this);" method="post">
    <input type="hidden" name="ct_idx" value="<?php echo $ct_idx; ?>">
    <input type="hidden" name="company_idx" value="<?php echo $company_idx; ?>">
    <input type="hidden" name="bill_years" value="<?php echo $year; ?>">
    <input type="hidden" name="bill_months" value="<?php echo $months; ?>">
    <div class="cm_pop_title">
        <!-- <?php echo $year.'년 '.$nowMonth.'월'?>  -->
        <?php echo $row['building_name']; ?> > <?php echo $row['company_name']; ?> > <?php echo $month; ?>월 계약
    </div>
    <div class="personal_pop_price_box">
        <div class="pprice_label">비용</div>
        <div class="pp_ipt_box_wrap">
            <input type="hidden" name="history_idx" value="<?php echo $contract_history_price_row['cph_idx']; ?>">
            <input type="hidden" name="pn_ct_price_or" id="pn_ct_price_or" value="<?php echo $pn_ct_price; ?>" class="bansang_ipt ver2" readonly size="20">
            <input type="text" name="pn_ct_price" id="pn_ct_price" value="<?php echo $pn_ct_price; ?>" class="bansang_ipt ver2" <?php echo $sql_payment_list_row['is_services'] ? 'readonly' : ''; ?> size="20">
            <p>* 계약정보의 비용 금액</p>
        </div>
    </div>
    <?php
    //계산서 발행 저장된 정보가 있는지 확인

    $start_dates = $year.'-'.$months.'-01';

    $bill_type_sql = "SELECT * FROM a_contract_company_bill WHERE ct_idx = '{$ct_idx}' and bill_sdate <= '{$start_dates}' ORDER BY idx desc limit 0, 1";
    $bill_type_row = sql_fetch($bill_type_sql);
    //echo $bill_type_sql.'<br>';

    
    $bill_status = $row_bill_info['cnt'] > 0 ? $row_bill_info['bill_type'] : $bill_company_setting_row['bill_type'];

    // echo $row_bill_info['bill_type'].'<br>';
    // echo $bill_status;
    ?>
    <div class="personal_wrapper">
        <div class="personal_bill_cont personal_cont_box">
            <div class="personal_ipt_box flex_ver">
                <div class="personal_ipt_label">계산서 종류</div>
                <div class="personal_ipt_box">
                    <select name="bill_types_per" id="bill_types_per" class="bansang_sel full">
                        <option value="">선택</option>
                        <?php while($row_bill = sql_fetch_array($res_bill)){?>
                            <option value="<?php echo $row_bill['bt_idx']?>" <?php echo get_selected($bill_status, $row_bill['bt_idx']); ?>><?php echo $row_bill['bill_name']; ?></option>
                        <?php }?>
                    </select>
                </div>
            </div>
            <div class="personal_ipt_box flex_ver">
                <div class="personal_ipt_label">계산서 발행여부</div>
                <div class="personal_ipt_box">
                    <select name="bill_status_per" id="bill_status_per" class="bansang_sel full" onchange="status_changes();">
                        <option value="1" <?php echo get_selected($row_bill_info['bill_statusm'], '1'); ?>>발행전</option>
                        <option value="2" <?php echo get_selected($row_bill_info['bill_statusm'], '2'); ?>>발행</option>
                        <option value="3" <?php echo get_selected($row_bill_info['bill_statusm'], '3'); ?>>특이사항</option>
                    </select>
                    <script>
                        function status_changes(){
                            var statusSelect = document.getElementById("bill_status_per");
                            var statusValue = statusSelect.options[statusSelect.selectedIndex].value;

                            if(statusValue != "1"){
                                $(".personal_ipt_box_bill_date").removeClass("hide");
                               
                                $("#bill_types_per").attr('required', true);

                                if(statusValue == "2"){
                                    $(".personal_ipt_box_bill_date input").attr("required", true);
                                }else{
                                    $(".personal_ipt_box_bill_date input").attr("required", false);
                                }

                            }else{
                                $(".personal_ipt_box_bill_date").addClass("hide");
                                $(".personal_ipt_box_bill_date input").attr("required", false);
                                $("#bill_types_per").attr('required', false);
                            }
                        }
                    </script>
                </div>
            </div>
            <?php
            $bill_date_box_style = "hide";
            if($row_bill_info['bill_statusm'] != "1" && $row_bill_info['cnt'] > 0){
                $bill_date_box_style = "";
            }
            
            ?>
            <div class="personal_ipt_box flex_ver personal_ipt_box_bill_date <?php echo $bill_date_box_style; ?>">
                <div class="personal_ipt_label">계산서</div>
                <div class="personal_ipt_box">
                    <input type="text" name="bill_dates_per" class="bansang_ipt ver2 ipt_date full" value="<?php echo $row_bill_info['bill_dates']; ?>">
                </div>
            </div>
            <div class="personal_ipt_box flex_ver2">
                <div class="personal_ipt_label">계산서 특이사항</div>
                <div class="personal_ipt_box">
                    <textarea name="bills_memo_pre" class="bansang_ipt ver2 ta full"><?php echo $row_bill_info['bills_memo']; ?></textarea>
                </div>
            </div>
        </div>
        <div class="personal_payment_cont personal_cont_box">
            <div class="personal_ipt_box_wrapper">
                <div class="personal_ipt_box flex_ver">
                    <div class="personal_ipt_label">지급처리 여부</div>
                    <div class="personal_ipt_box">
                        <?php
              
                        ?>
                        <select name="payment_status_per" id="payment_status_per" class="bansang_sel full" onchange="pt_status_change();">
                            <option value="">선택</option>
                            <option value="1" <?php echo get_selected($sql_payment_list_row['payment_status'], '1'); ?>>미지급</option>
                            <option value="2" <?php echo get_selected($sql_payment_list_row['payment_status'], '2'); ?>>지급</option>
                            <option value="3" <?php echo get_selected($sql_payment_list_row['payment_status'], '3'); ?>>서비스</option>
                            <option value="4" <?php echo get_selected($sql_payment_list_row['payment_status'], '4'); ?>>특이사항</option>
                        </select>
                        <script>
                        function pt_status_change(){
                            var ptstatusSelect = document.getElementById("payment_status_per");
                            var ptstatusValue = ptstatusSelect.options[ptstatusSelect.selectedIndex].value;

                            let pn_ct_price_or = $("#pn_ct_price_or").val();
                            
                            // ptstatusValue != "1" &&
                            if( ptstatusValue != '3'){

                                if(ptstatusValue == "1"){
                                    $(".personal_ipt_box_pt_date").addClass("hide");
                                    $(".personal_ipt_box_pt_date input").attr("required", false);
                                }else{
                                    $(".personal_ipt_box_pt_date").removeClass("hide");
                                    $(".personal_ipt_box_pt_date input").attr("required", true);
                                }
                                

                                $("#pn_ct_price").val(pn_ct_price_or);

                                $("#pn_ct_price").attr('readonly', false);

                            }else{
                                
                                $(".personal_ipt_box_pt_date").addClass("hide");
                                $(".personal_ipt_box_pt_date input").attr("required", false);

                                

                                if(ptstatusValue == "3"){
                                    $("#pn_ct_price").val("0 (서비스)");
                                    $("#pn_ct_price").attr('readonly', true);
                                }else{
                                    $("#pn_ct_price").attr('readonly', false);
                                }
                            }
                        }
                    </script>
                    </div>
                </div>
                <?php
                $hideClass = "hide";
                if($sql_payment_list_row['payment_status'] != "3" && $sql_payment_list_row['payment_status'] != "1" && $sql_payment_list_row['cnt'] > 0){
                    $hideClass = "";
                }
                ?>
                <div class="personal_ipt_box flex_ver personal_ipt_box_pt_date <?php echo $hideClass; ?>">
                    <div class="personal_ipt_label">지급일</div>
                    <div class="personal_ipt_box">
                        <input type="text" name="payment_date_per" class="bansang_ipt ver2 ipt_date full" value="<?php echo $sql_payment_list_row['payment_date']; ?>">
                    </div>
                </div>
            </div>
            <div class="personal_ipt_box flex_ver2">
                <div class="personal_ipt_label">지급처리 특이사항</div>
                <div class="personal_ipt_box">
                    <textarea name="payment_memo_per" class="bansang_ipt ver2 ta full"><?php echo $sql_payment_list_row['payment_memo']; ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="contract_personal_bottom">
        <div class="contract_status_box ver2">
            <div class="ctt_status_left">
                <div class="ct_status_chk_box">
                    <input type="checkbox" name="ct_status_per" id="ct_status_per" value="1" <?php echo $row['ct_status'] == '1' ? 'checked' : ''; ?> <?php echo $row['ct_status'] == '1' ? 'disabled' : ''; ?>>
                    <label for="ct_status_per">계약해지</label>
                </div>
                <div class="contract_status_txt_box ver2">
                    <p>* 계약 해지 후 해지한 계약건은 다시 계약중 상태로 변경 불가합니다.</p>
                    <p>* 새롭게 계약 추가 하여야 합니다.</p>
                </div>
            </div>
            <div class="ct_status_date_box_wrap ct_status_date_box_wrap2" style="<?php echo $row['ct_status'] == '1' ? 'display:block' : ''; ?>">
                <?php
                $year = date('Y');
                $yearTo = $year + 3;
                ?>
                <div class="ct_status_date_box">
                    <div class="ct_status_date_label">해지 날짜 설정</div>
                    <select name="ct_status_year" id="ct_status_year" class="bansang_sel ct_status_year2" <?php echo $row['ct_status'] == '1' ? 'disabled' : ''; ?>>
                        <option value="">년 선택</option>
                        <?php for($i=$year;$i<=$yearTo;$i++){?>
                            <option value="<?php echo $i;?>" <?php echo $row['ct_status_year'] == $i ? 'selected' : ''; ?>><?php echo $i.'년';?></option>
                        <?php }?>
                    </select>
                    <select name="ct_status_month" id="ct_status_month" class="bansang_sel ct_status_month2" <?php echo $row['ct_status'] == '1' ? 'disabled' : ''; ?>>
                        <option value="">월 선택</option>
                        <?php for($i=1;$i<=12;$i++){
                            $months = str_pad($i, 2, "0", STR_PAD_LEFT);
                            ?>
                            <option value="<?php echo $i; ?>" <?php echo $row['ct_status_month'] == $i ? 'selected' : ''; ?>><?php echo $months.'월'; ?></option>
                        <?php }?>
                    </select>
                </div>
                <p>* 설정한 년/월의 익월부터 계약종료 처리됩니다.</p>
            </div>
            <script>
                //선임자 미사용 체크
                $("#ct_status_per").click(function () {
                    
                    if ($("#ct_status_per").is(":checked")) {
                        console.log('체크');

                        $(".ct_status_date_box_wrap2").css('display', 'block');
                        $(".ct_status_year2").attr({'required': true});
                        $(".ct_status_month2").attr({'required': true});
                    } else {
                        console.log('체크해제');
                        $(".ct_status_date_box_wrap2").css('display', 'none');
                        $(".ct_status_year2").attr({'required': false});
                        $(".ct_status_month2").attr({'required': false});
                    }
            
                });
            </script>
        </div>
        <div class="pesonal_btn_wrap">
            <button type="button" onclick="popClose('contract_personal_prg_pop');">취소</button>
            <button type="submit" class="personal_submit_btn">저장</button>
        </div>
    </div>
</form>
<script>
$(function(){
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
});
</script>