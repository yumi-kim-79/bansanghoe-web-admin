<?php
require_once "./_common.php";

$nowYear = date('Y');
// $nowMonth = 7;
$nowMonth = $selectMonth == '' ? date('n') : $selectMonth;

$dong_name = $dong_name.'동';


// print_r2($_POST);

// /$ho_tenant_at_de = "2025-07-01";

//빌딩 정보
$building_info = sql_fetch("SELECT * FROM a_building WHERE building_id = '{$building_id}'");

if($selectMonth == ""){ //선택된 월이 없다면 발행된 고지서 중 최신꺼

    $bill_latest = "SELECT * FROM a_bill WHERE is_del = 0 and is_submit = 'Y' and building_id = '{$building_id}' ORDER BY bill_month + 1 desc limit 0, 1";

    // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $bill_latest.'<br>';
    // echo $bill_latest.'<br>';
    $bill_latest_row = sql_fetch($bill_latest);

    if($bill_latest_row){
        $nowMonth = $bill_latest_row['bill_month'];
    }

    
}else{
   //이번달에 등록된 고지서 확인..
   $bill_now_month_chk = "SELECT COUNT(*) as cnt FROM a_bill WHERE is_del = 0 and is_submit = 'Y' and building_id = '{$building_id}' and bill_year = '{$nowYear}' and bill_month = '{$nowMonth}' and created_at >= '{$ho_tenant_at_de}' ORDER BY bill_month + 1 desc limit 0, 1";
   // echo $bill_now_month_chk;
   if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $bill_now_month_chk.'<br>';
    // exit;
   }
   $bill_now_month_chk_row = sql_fetch($bill_now_month_chk);

   //이번달에 등록된 고지서가 없다면 가장 최근의 고지서 월 가져오기
   if($bill_now_month_chk_row['cnt'] == 0){
       $bill_latest = "SELECT * FROM a_bill WHERE is_del = 0 and is_submit = 'Y' and building_id = '{$building_id}' ORDER BY bill_month + 1 desc limit 0, 1";

       // echo $bill_latest.'<br>';
       $bill_latest_row = sql_fetch($bill_latest);

       $nowMonth = $bill_latest_row['bill_month'];
   }
}


$bfMonth = $nowMonth - 1;
$month00 = str_pad($bfMonth, 2, "0", STR_PAD_LEFT);
$first_date = $nowYear.'-'.$month00.'-01'; //기준의 첫날
$last_date = date('t', strtotime($first_date)); //기준의 마지막날


$ho_tenant_at_year = date('n', strtotime($ho_tenant_at_de));
$ho_tenant_at_month = date('n', strtotime($ho_tenant_at_de));



$bill_list = "SELECT * FROM a_bill WHERE is_del = 0 and is_submit = 'Y' and building_id = '{$building_id}' and STR_TO_DATE(CONCAT(bill_year, '-', LPAD(bill_month, 2, '0'), '-01'), '%Y-%m-%d') >= '{$ho_tenant_at_de}' ORDER BY bill_year + 1 desc, bill_month + 1 desc";
$bill_list_res = sql_query($bill_list);


// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $bill_list.'<br>';
$bill_list_total = sql_num_rows($bill_list_res);




$bill_first_row = "SELECT * FROM a_bill WHERE is_del = 0 and is_submit = 'Y' and building_id = '{$building_id}' and bill_year = '{$nowYear}' and bill_month = '{$nowMonth}' ORDER BY bill_month + 1 desc limit 0, 1";

$bill_first_rows = sql_fetch($bill_first_row);


// echo $bill_first_row;
// exit;


// bill info
// $bill_info = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_bill WHERE is_submit = 'Y' and building_id = '{$building_id}' and bill_year = '{$bill_first_rows['bill_year']}' and bill_month = '{$bill_first_rows['bill_month']}'");
$bill_info = sql_fetch("SELECT * FROM a_bill WHERE is_submit = 'Y' and building_id = '{$building_id}' and bill_month = '{$nowMonth}' and STR_TO_DATE(CONCAT(bill_year, '-', LPAD(bill_month, 2, '0'), '-01'), '%Y-%m-%d') >= '{$ho_tenant_at_de}' ORDER BY bill_year + 1 desc, bill_month + 1 desc limit 0, 1");


// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo "SELECT * FROM a_bill WHERE is_submit = 'Y' and building_id = '{$building_id}' and STR_TO_DATE(CONCAT(bill_year, '-', LPAD(bill_month, 2, '0'), '-01'), '%Y-%m-%d') >= '{$ho_tenant_at_de}' ORDER BY bill_year desc, bill_month desc limit 0, 1"."<br>";

$bill_info_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM a_bill WHERE is_submit = 'Y' and building_id = '{$building_id}' and bill_month = '{$nowMonth}' and STR_TO_DATE(CONCAT(bill_year, '-', LPAD(bill_month, 2, '0'), '-01'), '%Y-%m-%d') >= '{$ho_tenant_at_de}'");

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo "SELECT *, COUNT(*) as cnt FROM a_bill WHERE is_submit = 'Y' and building_id = '{$building_id}' and STR_TO_DATE(CONCAT(bill_year, '-', LPAD(bill_month, 2, '0'), '-01'), '%Y-%m-%d') >= '{$ho_tenant_at_de}' ORDER BY bill_year desc, bill_month desc".'<br>';

// print_r2($bill_info);
$bill_in_price = '';
$bill_penulty = '';

if($bill_info_cnt['cnt'] > 0){
    $bill_items_ho = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' and dong_name = '{$dong_name}' and bi_name = '동호'";
    // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $bill_items_ho.'<br>';
    $bill_item_ho_row = sql_fetch($bill_items_ho);

    $bi_option = explode("|", $bill_item_ho_row['bi_option']);

    $bi_opt_new_arr = array();
    foreach($bi_option as $key => $row){
        // $opt_re = preg_replace('/[^0-9\-\|]/u', '', $row);
        $opt_re = preg_replace('/[^0-9bB\-\|]/u', '', $row);
        array_push($bi_opt_new_arr, $opt_re);
    }

    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        // print_r2($bi_opt_new_arr);
    }

    $bi_opt_new_arr = implode("|", $bi_opt_new_arr);
    $bi_option = explode("|", $bi_opt_new_arr);
    
    $ho_indx = array_search($ho_name, $bi_option);

    

    //납기내 금액
    $bill_in_price_sql = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' and dong_name = '{$dong_name}' and bi_name = '납기내금액'";

    // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $bill_in_price_sql.'<br>';
    $bill_in_price_row = sql_fetch($bill_in_price_sql);

    $bill_in_price_arr = explode("|", $bill_in_price_row['bi_option']);

    // print_r2($bill_in_price_arr);
    // echo $ho_indx;

    // if($_SERVER['REMOTE_ADDR']== ADMIN_IP) echo $ho_indx.'<br>';
    $bill_in_price = $bill_in_price_arr[$ho_indx];

    //납기연체료
    $bill_items_price = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' and dong_name = '{$dong_name}' and bi_name = '납기후연체료'";

    $bill_items_price_row = sql_fetch($bill_items_price);

    $bill_penulty_arr = explode("|", $bill_items_price_row['bi_option']);

    $bill_penulty = $bill_penulty_arr[$ho_indx];
}


$bill_in_price_n = (int) str_replace(',', '', $bill_in_price);
$bill_penulty_n = (int) str_replace(',', '', $bill_penulty);



if($bill_penulty_n != 0 && $bill_in_price_n != 0){
    $percent = ($bill_penulty_n / $bill_in_price_n) * 100;
    $percent = number_format($percent, 2);
}else{
    $percent = 0;
}


//납부기간
$bill_due_date = date("n월 j일", strtotime($bill_info['bill_due_date']));

//아이템 가져오기
$bill_item_list = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' AND dong_name = '{$dong_name}' ORDER BY bi_idx ASC";
$bill_item_list_res = sql_query($bill_item_list);

$item_array = array();
$bill_item_arr = array();

for($i=0;$bill_item_list_row = sql_fetch_array($bill_item_list_res);$i++){
    $item_array[$i] = $bill_item_list_row['bi_name'];

    $bill_item_arr[$i]['item'] = $bill_item_list_row['bi_name'];
    $bill_item_arr[$i]['option'] = array();

    if($bill_item_list_row['bi_name'] == '동호'){

        $bi_option = explode("|", $bill_item_list_row['bi_option']);

        $bi_opt_new_arr = array();
        foreach($bi_option as $key => $row){
            $opt_re = preg_replace('/[^0-9\-\|]/u', '', $row);
            array_push($bi_opt_new_arr, $opt_re);
        }

        $bi_opt_new_arr = implode("|", $bi_opt_new_arr);
        $bi_option = explode("|", $bi_opt_new_arr);

    }else{
        $bi_option = explode("|", $bill_item_list_row['bi_option']);
    }

    foreach($bi_option as $key => $row){
        $bill_item_arr[$i]['option'][$key] = $row;
    }
}

//항목명
// '전기사용량', '온수사용량', '수도사용량', '난방사용량', '가스사용량', '할인금액', '미납금액', '미납연체료',
$not_item = ['동호', '면적', '성명(상가)', '성명(상호)', '공급가액', '부가세', '비과세', '면세', '당월분합계',  '납기내금액', '납기후연체료', '납기후금액', '자동이체', '입주일자'];

$not_item_arr = array_diff($item_array, $not_item);

// print_r2($bill_item_arr);
$not_item_list = array();
$bcnt = 0;
foreach($not_item_arr as $key => $row){

    $not_item_val = $bill_item_arr[$key]['option'][$ho_indx];

    if($not_item_val != ''){
        $not_item_list[$bcnt]['item_name'] = $row;
        $not_item_list[$bcnt]['item_val'] = $not_item_val;
    }

    $bcnt++;
}

$vat_item = ['공급가액', '부가세', '비과세', '면세'];
$vat_item_arr = array_intersect($item_array, $vat_item);


$vat_item_list = array();
$vcnt = 0;

foreach($vat_item_arr as $key => $row){

    $vat_item_val = $bill_item_arr[$key]['option'][$ho_indx];

   

        $vat_item_list[$vcnt]['item_name'] = $row;
        $vat_item_list[$vcnt]['item_val'] = $vat_item_val;
    // if($vat_item_val != ''){
    // }

    $vcnt++;
}

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) {
    // echo $ho_indx.'<br>';
    // print_r2($vat_item_list);
}

?>
<div class="parking_sc parking_sc1">
    <div class="inner">
        <p class="parking_building">
            <?php echo $building_name; ?><br>
            <?php echo $dong_name; ?> <?php echo $ho_name; ?>호 
            <!-- <?php echo $ho_tenant; ?> -->
        </p>
        <div class="bill_top_info">
            <!-- <p class="parking_building2 mgt10"><?php echo $nowMonth; ?>월 요금은 <?php echo $first_date; ?>부터 <?php echo $last_date; ?>일까지 사용한 관리비입니다.</p> -->
            <p class="parking_building2 mgt10">관리비 납부는 아래 계좌정보를 통해 납부 가능합니다.</p>
            <div class="bill_info_box mgt15">
                <div class="bill_info_top">
                    <?php if($bill_list_total > 0){?>
                    <select name="bill_month" id="bill_month" class="bansang_sel bill" onchange="bill_month_change();">
                        <?php while($bill_list_row = sql_fetch_array($bill_list_res)){?>
                        <option value="<?php echo $bill_list_row['bill_month']; ?>" <?php echo $nowMonth == $bill_list_row['bill_month'] ? 'selected' : ''; ?>><?php echo $bill_list_row['bill_month']; ?>월</option>
                        <?php }?>
                    </select>
                    <?php }else{ ?>
                        <select name="bill_month" id="bill_month" class="bansang_sel bill">
                            <option value="">-</option>
                        </select>
                    <?php }?>
                    <div class="bill_info_price_box">
                        <div class="bill_info_price_label">청구 요금</div>
                        <div class="bill_info_price">
                            <span><?php echo number_format($bill_in_price_n);?></span>원
                        </div>
                    </div>
                </div>
                <div class="bill_info_bot mgt10">
                    <div class="bill_info_txt"><?php echo $nowMonth; ?>월 요금은 <?php echo $bill_due_date; ?>까지 입금 부탁드립니다.</div>
                    <div class="bill_info_txt">
                    납부기한 후에는 연체료가 발생하므로 관리사무소에 문의 바랍니다.
                    </div>
                    <!-- <div class="bill_info_txt">입금 연체시 연체가산금(<?php echo $bill_list_total > 0 ? $percent : '0'; ?>%) <?php echo $bill_penulty; ?>원 추가됩니다.</div> -->
                </div>
            </div>
           
        </div>
    </div>
</div>
<div class="bill_payment_info">
    <div class="inner">
        <div class="bill_payment_box">
            <div class="bill_payment_label">납부 기한</div>
            <div class="bill_payment_ct ver2"><?php echo $bill_info['bill_due_date'] != '' ? date("Y.m.d", strtotime($bill_info['bill_due_date'])) : '-'; ?></div>
        </div>
        <div class="bill_payment_box">
            <div class="bill_payment_label">납부 계좌</div>
            <div class="bill_payment_ct">
                <button type="button" onclick="copyToClipboard('<?php echo $building_bill_account; ?>');">
                <?php echo $building_bill_account_bank; ?>
                <?php echo $building_bill_account; ?>
                <?php echo $building_bill_account_name; ?>
                <img src="/images/copy_icons.svg" alt="">
                </button>

                <script>
                    function copyToClipboard(text) {
                        navigator.clipboard.writeText(text).then(() => {
                            showToast("복사되었습니다. 원하는 곳에 붙여넣기하여 주세요.");
                        }).catch(() => {
                            prompt("키보드의 ctrl+C 또는 마우스 오른쪽의 복사하기를 이용해주세요.",text);
                        });
                    };
                </script>
            </div>
        </div>
        <div class="bill_payment_box bill_payment_box_col">
            <div class="bill_payment_label">공지사항</div>
            <div class="bill_payment_ct">
                <div class="bill_notice_box">
                    <?php echo nl2br($building_info['building_bill_notice']); ?>
                </div>
            </div>
        </div>
        <?php if($bill_list_total > 0){?>
        <a href="/bill_download.php?bill_id=<?php echo $bill_info['bill_id'];?>&ho_id=<?php echo $ho_id; ?>" class="bill_downs mgt15">고지서 다운로드</a>
        <?php }?>
    </div>
</div>
<div class="bar ver2"></div>
<?php if($bill_list_total > 0){?>
<div class="mng_bill_info">
    <div class="inner">
        <div class="mng_bill_info_wrap">
            <div class="bill_label">관리비 상세 내역</div>
            <div class="mng_bill_list">
                <ul>
                    <?php foreach($not_item_list as $key => $val){?>
                    <li>
                        <div class="mng_bill_cont"><?php echo $val['item_name']; ?></div>
                        <div class="mng_bill_price">
                            <?php echo $val['item_val']; ?>
                            <?php
                            
                            if($val['item_name'] == '전기사용량'){
                                echo "kWh";
                            }else if($val['item_name'] == '수도사용량' || $val['item_name'] == '온수사용량'){
                                echo "㎥";
                            }else if($val['item_name'] == '가스사용량'){
                                echo "N㎥";
                            }else if($val['item_name'] == '난방사용량'){
                                echo "MWh";
                            }else{
                                echo "원";
                            }
                            ?>
                        </div>
                    </li>
                    <?php }?>
                </ul>
            </div>
        </div>
        <?php if(!$bill_info['vt_add']){?>
        <div class="mng_bill_info_wrap">
            <div class="bill_label">부가세 내역</div>
            <div class="mng_bill_list">
                <ul>
                    <?php foreach($vat_item_list as $key => $val){?>
                    <li>
                        <div class="mng_bill_cont"><?php echo $val['item_name']; ?></div>
                        <div class="mng_bill_price"><?php echo $val['item_val'] == '' ? 0 :  $val['item_val'];?>원</div>
                    </li>
                    <?php }?>
                </ul>
            </div>
        </div>
        <?php }?>
        <div class="mnb_bill_total_price">
            <div class="total_label"><?php echo $nowMonth; ?>월 총 관리비</div>
            <div class="total_price"><span><?php echo number_format($bill_in_price_n);?></span>원</div>
        </div>
    </div>
</div>


<?php }else{ ?>
<div class="bill_empty">
    발행된 고지서가 없습니다.
</div>
<?php }?>