<?php
include_once('./_common.php');

$g5['title'] = '고지서 인쇄';
include_once(G5_PATH.'/head.sub.php');

$nowYear = date("Y");
$nowMonth = date("n");

$ho_info = sql_fetch("SELECT ho.*, dong.dong_name FROM a_building_ho as ho LEFT JOIN a_building_dong as dong on ho.dong_id = dong.dong_id WHERE ho.ho_id = '{$ho_id}'");

//단지idx
$building_id = $ho_info['building_id'];

//단지정보
$building_info = sql_fetch("SELECT * FROM a_building WHERE building_id = '{$building_id}'");

//고지서 정보
$bill_info = sql_fetch("SELECT * FROM a_bill WHERE building_id = '{$building_id}' AND bill_year = '{$nowYear}' AND bill_month = '{$nowMonth}'");
// print_r2($ho_info);

$bill_item_info = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' AND dong_name = '{$ho_info['dong_name']}' ORDER BY bi_idx ASC";
$bill_item_res = sql_query($bill_item_info);

$bill_item_arr = array();

$item_array = array(); //항목만 담을 배열
for($i=0;$bill_item_row = sql_fetch_array($bill_item_res);$i++){
    $item_array[$i] = $bill_item_row['bi_name'];
    $bill_item_arr[$i]['item'] = $bill_item_row['bi_name'];
    $bill_item_arr[$i]['option'] = array();

    if($bill_item_row['bi_name'] == '동호'){

        $bi_option = explode("|", $bill_item_row['bi_option']);

        $bi_opt_new_arr = array();
        foreach($bi_option as $key => $row){
            $opt_re = preg_replace('/[^0-9\-\|]/u', '', $row);
            array_push($bi_opt_new_arr, $opt_re);
        }

        $bi_opt_new_arr = implode("|", $bi_opt_new_arr);
        $bi_option = explode("|", $bi_opt_new_arr);

    }else{
        $bi_option = explode("|", $bill_item_row['bi_option']);
    }

    foreach($bi_option as $key => $row){
        $bill_item_arr[$i]['option'][$key] = $row;
    }
    //$bill_item_arr[$i]['item'] = $bill_item_row['bi_name'];
}

$ho_arr = $bill_item_arr[0]; //동호수
$area_arr = $bill_item_arr[1]; //면적
$name_arr = $bill_item_arr[2]; //성명

$ho_sch_index = array_search($ho_info['ho_name'], $ho_arr['option']); //동호수 index


$due_in_price_index = array_search('납기내금액', $item_array); //납기내 금액 index
$due_out_price_index = array_search('납기후금액', $item_array); //납기후 금액 index
$due_out_price_penulty_index = array_search('납기후연체료', $item_array); //납기후 연체료 index


$due_in_price = $bill_item_arr[$due_in_price_index]['option'][$ho_sch_index]; //납기내 금액
$due_out_price = $bill_item_arr[$due_out_price_index]['option'][$ho_sch_index]; //납기후 금액
$due_out_price_penulty = $bill_item_arr[$due_out_price_penulty_index]['option'][$ho_sch_index]; //납기후 연체료

//echo $ho_sch_index;
//print_r2($bill_item_arr[4]);

//항목명
$not_item = ['동호', '면적', '성명(상가)', '전기사용량', '온수사용량', '수도사용량', '난방사용량', '가스사용량', '공급가액', '부가세', '비과세', '면세', '당월분합계', '할인금액', '미납금액', '미납연체료', '납기내금액', '납기후연체료', '납기후금액', '자동이체', '입주일자'];

$not_item_arr = array_diff($item_array, $not_item);

$not_item_list = array();
$bcnt = 0;
foreach($not_item_arr as $key => $row){

    $not_item_val = $bill_item_arr[$key]['option'][$ho_sch_index];

    if($not_item_val != ''){
        $not_item_list[$bcnt]['item_name'] = $row;
        $not_item_list[$bcnt]['item_val'] = $not_item_val;
    }

    $bcnt++;
}
//print_r2($item_array);

//부가세영역
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

//미납관리비 및 미납연체료
$noduePrice_index = array_search('미납금액', $item_array); //미납금액 index
$noduePrice_penalty_index = array_search('미납연체료', $item_array); //미납연체료 index

$no_due_price = $bill_item_arr[$noduePrice_index]['option'][$ho_sch_index]; //미납금액
$no_due_price_penulty = $bill_item_arr[$noduePrice_penalty_index]['option'][$ho_sch_index]; //미납연체료 금액
$no_due_price_sum = $no_due_price + $no_due_price_penulty; //미납금액 + 미납연체료 금액


// 에너지 사용량
$energy_item = ['전기사용량', '온수사용량', '수도사용량', '난방사용량', '가스사용량'];
$energy_item_arr = array_intersect($item_array, $energy_item);

$energy_item_list = array();
$ecnt = 0;

foreach($energy_item_arr as $key => $row){

    $energy_item_val = $bill_item_arr[$key]['option'][$ho_sch_index];

   
    $energy_item_list[$ecnt]['item_name'] = $row;
    $energy_item_list[$ecnt]['item_val'] = $energy_item_val;
    // if($energy_item_val != ''){
    // }

    $ecnt++;
}

//print_r2($ho_info);

//당월 합계
$due_price_total_index = array_search('당월분합계', $item_array); //당월분합계 index

$due_price_total = $bill_item_arr[$due_price_total_index]['option'][$ho_sch_index]; //당월분합계

//echo $due_price_total;
?>
<style>
.building_news_sample_hd {width: 100%;padding:15px;display: flex;justify-content:flex-end;}
.building_news_sample_hd button {padding:10px 15px;border-radius:6px;border:none;background: var(--colorMain);color: #fff;font-size: 14px;}

.print_wrap {
    width: 210mm;
    height: 297mm;
    margin: auto;
    padding: 100px 70px 0 90px;
    position: relative;
    background-image: url('/images/bill_sample.png');
    background-size: 100%;
    background-color:#fff;
}

.bill_box {font-size: 11px;color: #121212;}

.building_info_addr {font-size: 14px;font-weight: 500;padding-left: 350px;}

.building_info_wrap {display: flex;margin-top: 30px;}
.bill_box_inner {width: 206px;height: 30px;display: flex;align-items:center;justify-content:center;}
.bill_box_inner.ver2 {width: 42px;}

.bill_due_info_wrap {display: flex;margin-top: 8px;gap:0 10px;}
.bill_due_info_wrap > div {width: 57%;min-height: 173px;}
.bill_due_info_wrap > div.bill_due_box {width: 41%;}
.bill_due_info_wrap > div.bill_dear_box {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 15px 0;;
}
.bill_due_box .due_box {height: calc(173px / 6);padding-left: 80px;display: flex;align-items:center;font-size: 10px;}
.bill_dear_box .bill_dates {text-align: center;font-size: 14px;font-weight: 500;}
.bill_dear_box .bill_addr {font-size: 17px;font-weight: 500;line-height: 1.5;padding-right: 15px;display: flex;justify-content: flex-end;}

.bill_page2_wrap {display: flex;margin-top: 14px;gap:0 10px;justify-content: space-between;}
.bill_page2_wrap > div {min-height: 630px;}
.bill_page2_left {width: 39%;}
.bill_page2_right {width: 56%;}

.bpage2_top {height: 432px;padding-top: 30px;}
.bill_item_boxs {display: flex;font-size: 11px;text-align: center;}
.bill_item_boxs + .bill_item_boxs {margin-top: 8px;}
.bill_item_box_l {width: 137px;    white-space: nowrap;text-overflow: ellipsis;overflow: hidden;
padding: 0 10px;}
.bill_item_box_r {width: calc(100% - 137px);}

.bpage2_bottom {min-height: 87px;padding-top: 10px;}
.bill_item_boxs2 {display: flex;font-size: 11px;text-align: center;}
.bill_item_boxs2 + .bill_item_boxs2 {margin-top: 5px;}

.bpage2_no_due {margin-top: 5px;display: flex;font-size: 11px;padding-top: 41px;}
.bpage2_no_due > div {width: 50%;text-align: center;height: 27px;display: flex;align-items:center;justify-content:center;}

.bpage2_no_due_sum_wrap {margin-top: 2px;display: flex;justify-content:flex-end;}
.bpage2_no_due_sum {height: 18px;width: 50%;display: flex;align-items:center;justify-content:center;font-size: 11px;}

.energy_wrap {height: 133px;padding-top: 47px;font-size: 10px;}
.energy_boxs {display: flex;}
.energy_boxs + .energy_boxs {margin-top: 4.5px;}
.energy_boxs > div {width: 50%;text-align: center;}

.building_notice_wrap {margin-top: 9px;height: 172px;padding: 35px 15px 0;}

.bill_right_last_wrap {display: flex;margin-top: 5px;height: 304px;padding-top: 23px;}
.bill_last_left {width: 215px;}
.bill_last_right {width: calc(100% - 215px);padding-top: 5px;}

.bill_last_left_box {height: 27px;display: flex;align-items:center;padding-left: 75px;justify-content:center;}
.bill_last_right_box {height: 36px;padding-top: 11px;display: flex;align-items:center;justify-content:center;font-size: 11px;}
.bill_last_right_box2 {font-size: 10px;padding-left:24px;margin-top: 2px;}
.bill_last_right_box2 span:nth-child(2) {margin-left:13px;margin-right: 8px;}

.red {color:#FA1C1C}
</style>
<div class="building_news_sample_hd">
    <button type="button" onclick="printBuildingNews();">인쇄</button>
</div>
<div class="print_wrapper">
    <div class="print_wrap">
        <!-- 신반상회 업체 주소 -->
         <div class="building_info_addr">
             인천 부평구 충선로 209번길 13<br>SM프라자 6층 606호
         </div>

        <!-- 관리비 명세서 / 면적 -->
        <div class="building_info_wrap bill_box">
            <div class="bill_box_inner">
                <?php echo $building_info['building_name']; ?>
                <?php echo $ho_info['dong_name']; ?>
                <?php echo $ho_info['ho_name']; ?>
            </div>
            <div class="bill_box_inner ver2">
                <?php echo number_format($area_arr['option'][$ho_sch_index], 2); ?>
            </div>
        </div>

        <!-- 납기정보 -->
        <div class="bill_due_info_wrap">
            <div class="bill_due_box">
                <div class="due_box red"><?php echo $due_in_price; ?></div> <!-- 납기내금액 -->
                <div class="due_box"><?php echo $bill_info['bill_due_date']; ?></div>
                <div class="due_box"><?php echo $due_out_price; ?></div> <!-- 납기후금액 -->
                <div class="due_box"><?php echo $building_info['building_bill_account_bank'];?></div>
                <div class="due_box"><?php echo $building_info['building_bill_account']; ?></div>
                <div class="due_box">(주)에스엠종합관리</div>
            </div>
            <div class="bill_dear_box">
                <!-- 관리비 날짜 -->
                <div class="bill_dates">
                    <?php echo $bill_info['bill_year'].'년 '.$bill_info['bill_month'].'월 관리비 고지서';?>
                </div>
                <!-- 수신 주소 -->
                 <div class="bill_addr">
                 <?php

                    $addr = $building_info['building_addr'];
                    if($building_info['building_addr2'] != ''){
                        $addr .= '<br>'.$building_info['building_addr2'];
                    }
                    echo $addr.'<br>'.$building_info['building_name'].' '.$ho_info['dong_name'].' '.$ho_info['ho_name'].' '.$ho_info['ho_tenant'].' 귀하';
                ?>
                 </div>
            </div>
        </div>

        <!-- 접은 페이지 항목 및 기타정보 -->
        <div class="bill_page2_wrap">
            <div class="bill_page2_left">
                <div class="bpage2_top">
                    <?php foreach($not_item_list as $key => $val){?>
                    <div class="bill_item_boxs">
                        <div class="bill_item_box_l"><?php echo $val['item_name']; ?></div>
                        <div class="bill_item_box_r"><?php echo $val['item_val'] != '' ? $val['item_val'] :  0; ?></div>
                    </div>
                    <?php }?>
                </div>
                <div class="bpage2_bottom">
                    <?php foreach($vat_item_list as $key => $val){?>
                    <div class="bill_item_boxs2">
                        <div class="bill_item_box_l"><?php echo $val['item_name']; ?></div>
                        <div class="bill_item_box_r"><?php echo $val['item_val'] != '' ? $val['item_val'] :  0; ?></div>
                    </div>
                    <?php }?>
                </div>
                <div class="bpage2_no_due">
                    <div class="bpage2_no_due_box"><?php echo $no_due_price; ?></div>
                    <div class="bpage2_no_due_box"><?php echo $no_due_price_penulty; ?></div>
                </div>
                <div class="bpage2_no_due_sum_wrap">
                    <div class="bpage2_no_due_sum"><?php echo $no_due_price_sum == 0 ? '' : $no_due_price_sum; ?></div>
                </div>
            </div>
            <div class="bill_page2_right">
                <!-- 에너지사용량 -->
                <div class="energy_wrap">
                    <?php foreach($energy_item_list as $key => $val){?>
                    <div class="energy_boxs">
                        <div class="energy_box_l"><?php echo $val['item_name']; ?></div>
                        <div class="energy_box_r"><?php echo $val['item_val'] == '' ? 0 : $val['item_val']; ?></div>
                    </div>
                    <?php }?>
                   
                </div>

                <!-- 단지 공지사항 -->
                 <div class="building_notice_wrap">
                    <?php echo nl2br($building_info['building_bill_notice']); ?>
                 </div>

                 <div class="bill_right_last_wrap">
                    <div class="bill_last_left">
                        <div class="bill_last_left_box"><?php echo $due_price_total;?></div>
                        <div class="bill_last_left_box"><?php echo $no_due_price == '' ? 0 : $no_due_price; ?></div>
                        <div class="bill_last_left_box"><?php echo $no_due_price_penulty == '' ? 0 : $no_due_price_penulty; ?></div>
                        <div class="bill_last_left_box red"><?php echo $due_in_price; ?></div>
                        <div class="bill_last_left_box"><?php echo $bill_info['bill_due_date']; ?></div>
                        <div class="bill_last_left_box"><?php echo $due_out_price_penulty; ?></div>
                        <div class="bill_last_left_box"><?php echo $due_out_price; ?></div>
                        <div class="bill_last_left_box"><?php echo $building_info['building_bill_account_bank'];?></div>
                        <div class="bill_last_left_box"><?php echo $building_info['building_bill_account']; ?></div>
                        <div class="bill_last_left_box">(주)에스엠종합관리</div>
                    </div>
                    <div class="bill_last_right">
                        <div class="bill_last_right_box"><?php echo $due_in_price;?></div>
                        <div class="bill_last_right_box"><?php echo $no_due_price == '' ? 0 : $no_due_price; ?></div>
                        <div class="bill_last_right_box"><?php echo $no_due_price_penulty == '' ? 0 : $no_due_price_penulty; ?></div>
                        <div class="bill_last_right_box"><?php echo $due_in_price;?></div>
                        <div class="bill_last_right_box"><?php echo $due_out_price_penulty;?></div>
                        <div class="bill_last_right_box"><?php echo $due_out_price; ?></div>
                        <div class="bill_last_right_box2">
                            <span>2025</span> <span>4</span> <span>16</span>
                        </div>
                    </div>
                 </div>
            </div>
        </div>
    </div>
</div>
<script>
function printBuildingNews() {
    var printContent = document.querySelector(".print_wrapper").cloneNode(true);
    var originalContent = document.body.innerHTML;

    // 인쇄 전용 스타일 추가
    var printStyle = document.createElement("style");
    
    // -webkit-print-color-adjust: exact; 인쇄 배경 고정
    printStyle.innerHTML = `

        @page {
            size: A4 portrait; /* 세로 방향으로 고정 */
            margin: 0;
        }

        @media print {
            body { margin: 0; padding: 0; background: none; }
            .building_news_sample_hd { display: none !important; } /* 인쇄 버튼 숨김 */
            .pages {display:none !important;}

            
        .print_wrap {
            width: 210mm;
            height: 297mm;
            margin: auto;
            padding: 100px 70px 0 90px;
            position: relative;
            background-image: url('/images/bill_sample.png');
            background-size: 100%;
            background-color:#fff;
        }

        .bill_box {font-size: 11px;color: #121212;}

        .building_info_addr {font-size: 14px;font-weight: 500;padding-left: 350px;}

        .building_info_wrap {display: flex;margin-top: 30px;}
        .bill_box_inner {width: 206px;height: 30px;display: flex;align-items:center;justify-content:center;}
        .bill_box_inner.ver2 {width: 42px;}

        .bill_due_info_wrap {display: flex;margin-top: 8px;gap:0 10px;}
        .bill_due_info_wrap > div {width: 57%;min-height: 173px;}
        .bill_due_info_wrap > div.bill_due_box {width: 41%;}
        .bill_due_info_wrap > div.bill_dear_box {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 15px 0;;
        }
        .bill_due_box .due_box {height: calc(173px / 6);padding-left: 80px;display: flex;align-items:center;font-size: 10px;}
        .bill_dear_box .bill_dates {text-align: center;font-size: 14px;font-weight: 500;}
        .bill_dear_box .bill_addr {font-size: 17px;font-weight: 500;line-height: 1.5;padding-right: 15px;display: flex;justify-content: flex-end;}

        .bill_page2_wrap {display: flex;margin-top: 14px;gap:0 10px;justify-content: space-between;}
        .bill_page2_wrap > div {min-height: 630px;}
        .bill_page2_left {width: 39%;}
        .bill_page2_right {width: 56%;}

        .bpage2_top {height: 432px;padding-top: 30px;}
        .bill_item_boxs {display: flex;font-size: 11px;text-align: center;}
        .bill_item_boxs + .bill_item_boxs {margin-top: 8px;}
        .bill_item_box_l {width: 137px;    white-space: nowrap;text-overflow: ellipsis;overflow: hidden;
        padding: 0 10px;}
        .bill_item_box_r {width: calc(100% - 137px);}

        .bpage2_bottom {min-height: 87px;padding-top: 10px;}
        .bill_item_boxs2 {display: flex;font-size: 11px;text-align: center;}
        .bill_item_boxs2 + .bill_item_boxs2 {margin-top: 5px;}

        .bpage2_no_due {margin-top: 5px;display: flex;font-size: 11px;padding-top: 41px;}
        .bpage2_no_due > div {width: 50%;text-align: center;height: 27px;display: flex;align-items:center;justify-content:center;}

        .bpage2_no_due_sum_wrap {margin-top: 2px;display: flex;justify-content:flex-end;}
        .bpage2_no_due_sum {height: 18px;width: 50%;display: flex;align-items:center;justify-content:center;font-size: 11px;}

        .energy_wrap {height: 133px;padding-top: 47px;font-size: 10px;}
        .energy_boxs {display: flex;}
        .energy_boxs + .energy_boxs {margin-top: 4.5px;}
        .energy_boxs > div {width: 50%;text-align: center;}

        .building_notice_wrap {margin-top: 9px;height: 172px;padding: 35px 15px 0;}

        .bill_right_last_wrap {display: flex;margin-top: 5px;height: 304px;padding-top: 23px;}
        .bill_last_left {width: 215px;}
        .bill_last_right {width: calc(100% - 215px);padding-top: 5px;}

        .bill_last_left_box {height: 27px;display: flex;align-items:center;padding-left: 75px;justify-content:center;}
        .bill_last_right_box {height: 36px;padding-top: 11px;display: flex;align-items:center;justify-content:center;font-size: 11px;}
        .bill_last_right_box2 {font-size: 10px;padding-left:24px;margin-top: 2px;}
        .bill_last_right_box2 span:nth-child(2) {margin-left:13px;margin-right: 8px;}

        .red {color:#FA1C1C}
        }
    `;

    document.head.appendChild(printStyle); // 스타일 적용
    document.body.innerHTML = "";
    document.body.appendChild(printContent);

    window.print(); // 인쇄 실행

    // 원래 페이지 복원
    document.body.innerHTML = originalContent;
    location.reload();
}
</script>