<?php
require_once "./_common.php";

$year  = ( $toYear )? $toYear : date( "Y" );
$month = ( $toMonth )? $toMonth : date( "n" );

// 이전, 다음 만들기
$prevYear        = ( $month == 1 )? ( $year - 1 ) : $year;
$prevMonth        = ( $month == 1 )? 12 : ( $month - 1 );
$nextYear        = ( $month == 12 )? ( $year + 1 ) : $year;
$nextMonth        = ( $month == 12 )? 1 : ( $month + 1 );

//등록된 고지서 확인..
$bill_info = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_bill WHERE is_submit = 'Y' and building_id = '{$building_id}' and bill_year = '{$year}' and bill_month = '{$month}'");


if($bill_info['cnt'] > 0){

    //동호수만 가져옴
    $bill_items_ho = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' and dong_name = '{$dong_name}' and bi_name = '동호'";
    $bill_item_ho_row = sql_fetch($bill_items_ho);

    $bi_option = explode("|", $bill_item_ho_row['bi_option']);

    $bi_opt_new_arr = array();
    foreach($bi_option as $key => $row){
        $opt_re = preg_replace('/[^0-9\-\|]/u', '', $row);
        array_push($bi_opt_new_arr, $opt_re);
    }

    $bi_opt_new_arr = implode("|", $bi_opt_new_arr);
    $bi_option = explode("|", $bi_opt_new_arr);

    $ho_indx = array_search($ho_name, $bi_option);

    //납기내 금액
    $bill_in_price_sql = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' and dong_name = '{$dong_name}' and bi_name = '납기내금액'";
    $bill_in_price_row = sql_fetch($bill_in_price_sql);

    $bill_in_price_arr = explode("|", $bill_in_price_row['bi_option']);

    // print_r2($bill_in_price_arr);


    $bill_in_price = $bill_in_price_arr[$ho_indx];

    //납기연체료
    $bill_items_price = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' and dong_name = '{$dong_name}' and bi_name = '납기후연체료'";

    $bill_items_price_row = sql_fetch($bill_items_price);

    $bill_penulty_arr = explode("|", $bill_items_price_row['bi_option']);

    $bill_penulty = $bill_penulty_arr[$ho_indx];

    $bill_in_price_n = (int) str_replace(',', '', $bill_in_price);
    $bill_penulty_n = (int) str_replace(',', '', $bill_penulty);

    $percent = ($bill_penulty_n / $bill_in_price_n) * 100;
    $percent = number_format($percent, 2);

    //////
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

    $not_item = ['동호', '면적', '성명(상가)', '성명(상호)', '공급가액', '부가세', '비과세', '면세', '당월분합계',  '납기내금액', '납기후연체료', '납기후금액', '자동이체', '입주일자'];

    $not_item_arr = array_diff($item_array, $not_item);


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

        $vat_item_val = $bill_item_arr[$key]['option'][$ho_sch_index];

    
            $vat_item_list[$vcnt]['item_name'] = $row;
            $vat_item_list[$vcnt]['item_val'] = $vat_item_val;
        // if($vat_item_val != ''){
        // }

        $vcnt++;
    }
}

//print_r2($vat_item_list); 
// print_r2($ho_indx);

?>
<div class="cal_header_wrap mgt20">
    <section class="cal_header">
        <button type="button" onClick="moveCal('<?php echo $prevYear?>', '<?php echo $prevMonth?>');">
            <img src="/images/icon_cal_prev.svg" alt="">
        </button>
        <p><?php echo $year; ?>년 <?php echo $month; ?>월</p>
        <button type="button" onClick="moveCal('<?php echo $nextYear?>', '<?php echo $nextMonth?>');">
            <img src="/images/icon_cal_next.svg" alt="">
        </button>
    </section>
</div>
<div class="pop_label_box mgt15">
    <div class="pop_label">관리비 상세 내역</div>
    <?php if($bill_info['vt_add']){?>
    <div class="pop_label_sub">(부가 가치세 포함)</div>
    <?php }?>
</div>
<?php if($bill_info['cnt'] > 0){?>
<div class="pop_bill_wrap mgt10">
    <?php foreach($not_item_list as $key => $val){?>
        <div class="pop_bill_box">
            <div class="pop_bill_label"><?php echo $val['item_name']; ?></div>
            <div class="pop_bill_price">
                <?php echo $val['item_val']?>
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
        </div>
    <?php }?>
</div>
<?php if($bill_info['vt_add']){?>
    <div class="pop_label_box mgt15">
        <div class="pop_label">부가세 내역</div>
    </div>
    <div class="pop_bill_wrap mgt10">
        <?php foreach($vat_item_list as $key => $val){?>
        <div class="pop_bill_box">
            <div class="pop_bill_label"><?php echo $val['item_name']; ?></div>
            <div class="pop_bill_price">
                <?php echo $val['item_val'] == '' ? 0 :  $val['item_val'];?>원
            </div>
        </div>
        <?php }?>
    </div>
<?php }?>
<div class="pop_total_price_box mgt10">
    <div class="pop_label"><?php echo $month; ?>월 총 관리비</div>
    <div class="pop_total_price"><span><?php echo number_format($bill_in_price_n)?></span>원</div>
</div>
<?php }else{ ?>
    <div class="pop_bill_empty">발행된 고지서가 없습니다.</div>
<?php }?>