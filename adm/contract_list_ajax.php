<?php
include_once('./_common.php');

$year = $year ? $year : date("Y");
$month = $month ? $month : date("n");

$nowMonth = $month;
$bfMonth = $nowMonth - 1;
$afMonth = $nowMonth + 1;

$monthArr = [$bfMonth, $nowMonth, $afMonth];

//echo $bfMonth;
//print_r2($monthArr);
//echo count($monthArr) + 1;

$contract_sql = "SELECT ct.*, building.building_name FROM a_contract as ct
                LEFT JOIN a_building as building on ct.building_id = building.building_id
                WHERE ct.is_del = 0 ORDER BY ct.is_temp desc, ct.company_name asc, building.building_name asc, ct.ct_idx desc";
//echo $contract_sql;
$contract_res = sql_query($contract_sql);

$contract_arr = array();

while($contract_row = sql_fetch_array($contract_res)){
    array_push($contract_arr, $contract_row);
}
?>
<div class="table_wrap_inner">
    <div class="table_fixed">
        <div class="table_fixed_left">
            <table class="contract_table contract_table2">
                <thead>
                    <tr>
                        <th>업종</th>
                        <th>업체명</th>
                        <th>현장명</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for($i=0;$i<count($contract_arr);$i++){?>
                        <tr>
                        <td style="cursor:pointer;" onclick="company_form_pop_open('<?php echo $contract_arr[$i]['ct_idx']; ?>');"><?php echo $contract_arr[$i]['industry_name']; ?></td>
                        <td style="cursor:pointer;" onclick="company_form_pop_open('<?php echo $contract_arr[$i]['ct_idx']; ?>');"><?php echo $contract_arr[$i]['company_name']; ?></td>
                        <td style="cursor:pointer;" onclick="company_form_pop_open('<?php echo $contract_arr[$i]['ct_idx']; ?>');"><?php echo $contract_arr[$i]['building_name']; ?></td>
                        </tr>
                    <?php }?>
                </tbody>
            </table>
        </div>
        <div class="table_fixed_right">
            <?php
                for($i=$bfMonth;$i<=$bfMonth + 2;$i++){

                    $months = str_pad($i, 2, "0", STR_PAD_LEFT);
                    $dates = $year.'-'.$months.'-01';

                    //echo $dates;
            ?>
            <table class="contract_table">
                <thead>
                    <tr>
                        <th><?php echo $i.'월'?></th>
                        <th>계산서</th>
                        <th>지급일</th>
                    </tr>
                </thead>
                <tbody>
                     <?php for($j=0;$j<count($contract_arr);$j++){
                        // print_r2($contract_arr);
                        $contract_bf_sql2 = "SELECT COUNT(*) as cnt FROM a_contract_history WHERE ct_idx = '{$contract_arr[$j]['ct_idx']}' and (ct_sdate <= '{$dates}' and ct_edate >= '{$dates}') and is_del = 0";
                        $contract_bf_row2 = sql_fetch($contract_bf_sql2);

                        // 계약이 없을 때 비활성화 class
                        $classes = $contract_bf_row2['cnt'] > 0 ? '' : 'not_contract';

                        // 계약이 있을 때 클릭이벤트
                        $clicks = 
                        $contract_bf_row2['cnt'] > 0 ?
                        "onclick='contract_personal_pop_open(\"".$contract_arr[$j]['ct_idx']."\", \"".$contract_arr[$j]['company_idx']."\", '".$year."', '".$i."')'" 
                        : '';

                        // 계약이 있을 때 클릭이벤트용 스타일
                        $styles = $contract_bf_row2['cnt'] > 0 ? "style='cursor:pointer'" : "";

                        //지급 확인
                        //지급 확인용 날짜
                        $pdates = $year.'-'.$months;

                        //지급 확인
                        $payment_list_sql = "SELECT payment_date, is_services, COUNT(*) as cnt FROM a_payment_list
                                             WHERE is_cancel = 0 and company_idx = '{$contract_arr[$j]['company_idx']}' and created_at like '{$pdates}%'";
                        $payment_list_row = sql_fetch($payment_list_sql);

                        //계약 히스토리 확인하기
                        $history_price_sql = "SELECT * FROM a_contract_history WHERE ct_idx = '{$contract_arr[$j]['ct_idx']}' and (ct_sdate <= '{$dates}' and ct_edate >= '{$dates}')";
                        $history_price_row = sql_fetch($history_price);

                        $bill_list_bf = "SELECT bill_dates, COUNT(*) as cnt FROM a_company_bill_list
                                             WHERE is_cancel = 0 and ct_idx = '{$contract_arr[$j]['ct_idx']}' and created_at like '{$pdates}%'";
                        echo $bill_list_bf.'<br>';
                    ?>
                        <tr>
                            <td>0</td>
                            <td>2025-04-10</td>
                            <td>2025-04-10</td>
                        </tr>
                    <?php }?> 
                </tbody>
            </table>
            <?php }?>
        </div>
    </div>
</div>