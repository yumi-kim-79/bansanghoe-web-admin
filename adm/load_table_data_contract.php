<?php
include_once('./_common.php');

// print_r2($_POST);

$year = intval($_POST['year']);
$month = intval($_POST['month']);
$viewAll = isset($_POST['viewAll']) && $_POST['viewAll'] == 1;

$range = $viewAll ? 12 : 3; // 전체보기는 12개월, 일반은 기준 ±1개월
$startOffset = $viewAll ? 0 : -1; // 전체보기는 0부터 시작 (1월부터), 일반은 기준월 -1로 시작

$sql_where = " WHERE (1) ";


// if($transactionStatusValue){
//     $sql_where .= "  ";
// }else{
//     $sql_where .= " and ct_status = '1' ";
// }

if($industry_idx_sch){
    $industry_idx_sch_t = "'".implode("','", $industry_idx_sch)."'";
    $sql_where .= " and contract.industry_idx IN ({$industry_idx_sch_t}) ";
}

if($company_idx_sch){
    $company_idx_sch_t = "'".implode("','", $company_idx_sch)."'";
    $sql_where .= " and contract.company_idx IN ({$company_idx_sch_t}) ";
}

if($building_id_sch){
    $building_id_sch_t = "'".implode("','", $building_id_sch)."'";
    $sql_where .= " and contract.building_id IN ({$building_id_sch_t}) ";
}

if($ptIdxValue){

    $sql_where .= " and company_bill.payment_type = '{$ptIdxValue}' ";
}

if($paymentStatusSch){
    $sql_where .= " and IFNULL(payment_list.payment_status, 1) = '{$paymentStatusSch}' ";
}

if($billStatusSch){
    $sql_where .= " and IFNULL(bill_list.bill_statusm, 1) = '{$billStatusSch}' ";
}

if($btIdxSch){
    $sql_where .= " and bill_list.bill_type = '{$btIdxSch}' ";
}

$sql = "SELECT * FROM a_contract_list {$sql_where} ORDER BY is_temp desc, building_name asc, company_name asc, ct_idx desc";
if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    // echo $sql.'<br>';
}

$res = sql_query($sql);

// 좌측 고정 테이블
foreach ($res as $idx => $row) {
    $temp_class = $row['is_temp'] ? 'temp' : ''; //임시저장 class
    $onclick_f = "company_form_pop_open('".$row['ct_idx']."')"; //계약수정팝업
?>
<tr class="<?php echo $temp_class; ?>">
    <td onclick="<?php echo $onclick_f; ?>"><?php echo $row['industry_name'];?></td>
    <td onclick="<?php echo $onclick_f; ?>"><?php echo $row['company_name'];?></td>
    <td onclick="<?php echo $onclick_f; ?>"><?php echo $row['building_name'];?></td>
</tr>
<?php }?>
<!-- SPLIT --> 
<?php
foreach ($res as $idx => $row) {
    $temp_class = $row['is_temp'] ? 'temp' : ''; //임시저장 class
?>
<tr class="<?php echo $temp_class; ?> <?php echo $idx; ?>">
    <?php for ($i = 0; $i < $range; $i++) { ?>
        <td><?php echo $i; ?></td>
        <td><?php echo $idx; ?></td>
        <td><?php echo $idx; ?></td>
    <?php }?>
</tr>
<?php } ?>