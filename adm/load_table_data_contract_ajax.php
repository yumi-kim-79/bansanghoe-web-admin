<?php
include_once('./_common.php');

// print_r2($_POST);

$year = intval($_POST['year']);
$month = intval($_POST['month']);
$viewAll = isset($_POST['viewAll']) && $_POST['viewAll'] == 1;

$range = $viewAll ? 12 : 3; // 전체보기는 12개월, 일반은 기준 ±1개월
$startOffset = $viewAll ? 0 : -1; // 전체보기는 0부터 시작 (1월부터), 일반은 기준월 -1로 시작

$sql_where = " WHERE (1) ";


//월 시작일, 마지막일
$base_year = $_POST['year'];
$base_month = $_POST['month'];

$month_start = date("Y-m-01", strtotime("$base_year-$base_month-01")); // 2025-07-01
$month_end   = date("Y-m-t", strtotime("$base_year-$base_month-01"));  // 2025-07-31


$sql_where .= " and ct_h.ct_sdate <= '{$month_end}' and ct_h.ct_edate >= '{$month_start}' ";
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

$sql = "SELECT ct_h.ct_hidx, ct_h.ct_sdate, ct_h.ct_edate, cts.* FROM a_contract_list_history as ct_h
        LEFT JOIN a_contract_list as cts on ct_h.ct_idx = cts.ct_idx
        {$sql_where}
        ORDER BY cts.is_temp desc, cts.building_name asc, cts.company_name asc, cts.ct_idx desc";
if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    // echo $sql.'<br>';
}

$res = sql_query($sql);
?>
<div class="left-table-container">
    <table class="fixed-table">
        <!-- 헤더 -->
        <thead>
            <tr>
                <th>업종</th>
                <th>업체명</th>
                <th>현장명</th>
            </tr>
        </thead>
        <tbody id="fixedTableBody">
            <!-- AJAX로 데이터 동기 삽입 -->
            <?php
            // 좌측 고정 테이블
            foreach ($res as $idx => $row) {
                $temp_class = $row['is_temp'] ? 'temp' : ''; //임시저장 class
                $onclick_f = "company_form_pop_open('".$row['ct_idx']."', '".$row['ct_hidx']."')"; //계약수정팝업
            ?>
            <tr class="<?php echo $temp_class; ?>">
                <td onclick="<?php echo $onclick_f; ?>"><?php echo $row['industry_name'];?></td>
                <td onclick="<?php echo $onclick_f; ?>"><?php echo $row['company_name'];?></td>
                <td onclick="<?php echo $onclick_f; ?>"><?php echo $row['building_name'];?></td>
            </tr>
            <?php }?>
        </tbody>
    </table>
</div>
<div class="right-table-container">
    <div class="table-scroll-x">
        <table class="scroll-table">
            <thead id="scrollTableHead">
            <!-- 월별 헤더 (3월 계산서 / 3월 지급일 ...) -->
                <tr>
                <?php 
                if($viewAll){
                    for($i=1;$i<=12;$i++){ ?>
                    <th><?php echo $i; ?>월</th><th>계산서</th><th>지급일</th>
                    <?php }
                }else{
                    for ($offset_hd = -1; $offset_hd <= 1; $offset_hd++) {
                        $month2 = $month - 1 + $offset_hd;

                        $month2 = str_pad($month2, 2, "0", STR_PAD_LEFT);
                        $dates = $year."-".$month2."-01";

                        $mm = date("n", strtotime($dates."+31 day"));
                    ?>
                    <th><?php echo $month2 == -1 ? 12 : $mm; ?>월</th><th>계산서</th><th>지급일</th>
                <?php
                    }
                }
                ?>
                </tr>
            </thead>
            <tbody id="scrollTableBody">
            <!-- AJAX로 데이터 동기 삽입 -->
            <?php
            foreach ($res as $idx => $row) {
                $temp_class = $row['is_temp'] ? 'temp' : ''; //임시저장 class
                
            ?>
            <tr class="<?php echo $temp_class; ?> <?php echo $idx; ?>">
                <?php 
                if($viewAll){
                    for($i=1;$i<=12;$i++){ ?>
                    <td><?php echo $i; ?>월</td><td>계산서</td><td>지급일</td>
                    <?php }
                }else{
                    for ($offset_hd = -1; $offset_hd <= 1; $offset_hd++) {
                        $month2 = $month - 1 + $offset_hd;

                        $month2 = str_pad($month2, 2, "0", STR_PAD_LEFT);
                        $dates = $year."-".$month2."-01";

                        $mm = date("n", strtotime($dates."+31 day"));

                        $mm_d = $month2 == -1 ? 12 : $mm;


                        $base_year2 = $_POST['year'];
                        $base_month2 = $mm_d;

                        $month_start2 = date("Y-m-01", strtotime("$base_year2-$base_month2-01")); // 2025-07-01
                        $month_end2   = date("Y-m-t", strtotime("$base_year2-$base_month2-01"));  // 2025-07-31

                        //price_history
                        $month_prices = 0;
                        $price_history = "SELECT * FROM a_contract_list_price_history WHERE ct_idx = '{$row['ct_idx']}' and pedate <= '{$month_end2}' ";
                        echo $price_history.'<br>';
                        $price_history_row = sql_fetch($price_history);


                        $price_history_cnt = "SELECT COUNT(*) as cnt FROM a_contract_list_price_history WHERE ct_idx = '{$row['ct_idx']}' and psdate >= '{$month_start2}' ";
                        $price_history_cnt_row = sql_fetch($price_history_cnt);

                        if($price_history_row['ch_status']){
                            $price_history = "SELECT * FROM a_contract_list_price_history WHERE ct_idx = '{$row['ct_idx']}' and psdate >= '{$month_start2}' and pedate <= '{$month_end2}' and ch_status = 1";
                            $price_history_row = sql_fetch($price_history);

                            $month_prices = $price_history_row['ct_price'];
                        }else{
                            $month_prices = $price_history_row['ct_price'];
                        }
                    ?>
                    <td><?php echo number_format($month_prices); ?></td><td>계산서</td><td>지급일</td>
                <?php
                    }
                }
                ?>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<script>
updateHeader(year, month, viewAll); // 오른쪽 헤더 생성

function updateHeader(year, month, viewAll) {
  const scrollHead = document.getElementById("scrollTableHead");
  let headerHtml = '<tr>';

  if (viewAll) {
    // — 전체보기 모드: 1월부터 12월까지
    for (let m = 1; m <= 12; m++) {
      headerHtml += `<th>${m}월</th><th>계산서</th><th>지급일</th>`;
    }
  } else {
    // — 일반 모드: 기준월 기준으로 −1, 0, +1
    for (let offset = -1; offset <= 1; offset++) {
      const date = new Date(year, month - 1 + offset);
      const m = date.getMonth() + 1; // 1~12
      headerHtml += `<th>${m}월</th><th>계산서</th><th>지급일</th>`;
    }
  }

  headerHtml += '</tr>';

  console.log('headerHtml', headerHtml);
  scrollHead.innerHTML = headerHtml;

  document.getElementById("currentMonth").textContent = viewAll
    ? `전체보기 (${year}년)`
    : `${year}년 ${month}월`;
}
</script>
