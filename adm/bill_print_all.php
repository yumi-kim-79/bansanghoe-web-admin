<?php
include_once('./_common.php');

$g5['title'] = '고지서 인쇄';
include_once(G5_PATH.'/head.sub.php');


$bill_sql = "SELECT * FROM a_bill WHERE bill_id = '{$bill_id}'";
$bill_info = sql_fetch($bill_sql);

$bill_item_sql = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' and bi_name = '동호'";
$bill_item_res = sql_query($bill_item_sql);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $bill_item_sql;
}

//빌딩 정보
$building_info = get_builiding_info($bill_info['building_id']);

$bill_dong = array();
foreach($bill_item_res as $key => $bill_item_row){
    
    $bi_option = explode('|', $bill_item_row['bi_option']);

    //동합계, 전체합계는 제외
    $bi_opt_new_arr = array();
    foreach($bi_option as $key2 => $row){
        $opt_re = preg_replace('/[^0-9\-\|]/u', '', $row);

        if($opt_re == '') continue;
        array_push($bi_opt_new_arr, $opt_re);
    }

    $bi_opt_new_arr = implode("|", $bi_opt_new_arr);
    $bi_option = explode("|", $bi_opt_new_arr);

    $bill_dong[$bill_item_row['dong_name']] = $bi_option; //동으로 담음
}

$building_name = $building_info['building_name']; //단지 이름
// print_r2($bill_dong); //배열 동
// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) print_r2($bill_dong);
?>
<style>
.building_news_sample_hd {width: 100%;padding:15px;display: flex;justify-content:flex-end;}
.building_news_sample_hd button {padding:10px 15px;border-radius:6px;border:none;background: var(--colorMain);color: #fff;font-size: 14px;}

.print_wrap {
    width: 210mm;
    height: 297mm;
    margin: auto;
    padding: 47px 26px 0 35px;
    position: relative;
    background-image: url('/images/bill_sample3.png');
    background-size: 100% 100%;
    background-color:#fff;
}

.bill_box {font-size: 11px;color: #121212;}

.building_info_addr {font-size: 14px;font-weight: 500;padding-left: 350px;}

.building_info_wrap {display: flex;margin-top: 35px;width: auto;border: none;}
.bill_box_inner {width: 240px;height: 36px;display: flex;align-items:center;justify-content:center;}
.bill_box_inner.ver2 {width: 46px;}

.bill_due_info_wrap {display: flex;margin-top: 9px;gap:0 10px;}
.bill_due_info_wrap > div {width: 57%;min-height: 201px;}
.bill_due_info_wrap > div.bill_due_box {width: 40%;}
.bill_due_info_wrap > div.bill_dear_box {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 15px 0;;
}
.bill_due_box .due_box {height: calc(201px / 6);padding-left: 90px;display: flex;align-items:center;font-size: 10px;}
.bill_dear_box .bill_dates {text-align: center;font-size: 14px;font-weight: 500;}
.bill_dear_box .bill_addr {font-size: 17px;font-weight: 500;line-height: 1.5;padding-right: 15px;display: flex;justify-content: flex-end;word-break: keep-all;text-align: right;}

.bill_page2_wrap {display: flex;margin-top: 14px;gap:0 10px;justify-content: space-between;}
.bill_page2_wrap > div {min-height: 630px;}
.bill_page2_left {width: 39%;}
.bill_page2_right {width: 56%;}

.bpage2_topbottom {height:600px;}
.bpage2_top {height: 503px;padding-top: 35px;}
.bill_item_boxs {display: flex;font-size: 11px;text-align: center;}
.bill_item_boxs + .bill_item_boxs {margin-top: 8px;}
.bill_item_box_l {width: 137px;    white-space: nowrap;text-overflow: ellipsis;overflow: hidden;
padding: 0 10px;}
.bill_item_box_r {width: calc(100% - 137px);}

.bpage2_bottom {min-height: 98px;padding-top: 10px;}
.bill_item_boxs2 {display: flex;font-size: 11px;text-align: center;}
.bill_item_boxs2 + .bill_item_boxs2 {margin-top: 5px;}

.bpage2_no_due {margin-top: 16px;display: flex;font-size: 11px;padding-top: 41px;}
.bpage2_no_due > div {width: 50%;text-align: center;height: 27px;display: flex;align-items:center;justify-content:center;}

.bpage2_no_due_sum_wrap {margin-top: 7px;display: flex;justify-content:flex-end;}
.bpage2_no_due_sum {height: 18px;width: 50%;display: flex;align-items:center;justify-content:center;font-size: 11px;}

.energy_wrap {height: 155px;padding-top: 55px;font-size: 10px;}
.energy_boxs {display: flex;}
.energy_boxs + .energy_boxs {margin-top: 4.5px;}
.energy_boxs > div {width: 50%;text-align: center;}

.building_notice_wrap {margin-top: 13px;height: 197px;padding: 35px 15px 0;}

.bill_right_last_wrap {display: flex;margin-top: 5px;height: 304px;padding-top: 28px;}
.bill_last_left {width: 245px;}
.bill_last_right {width: calc(100% - 245px);padding-top: 5px;}

.bill_last_left_box {height: 31px;display: flex;align-items:center;padding-left: 86px;justify-content:center;}
.bill_last_right_box {height: 42px;padding-top: 11px;display: flex;align-items:center;justify-content:center;font-size: 11px;}
.bill_last_right_box2 {font-size: 10px;padding-left:24px;}
.bill_last_right_box2 span:nth-child(2) {margin-left:10px;margin-right: 8px;}

.red {color:#FA1C1C}
.pages {padding:10px;font-size: 14px;text-align: right;}
</style>
<div class="print_wrapper">
    <div class="building_news_sample_hd">
        <button type="button" onclick="printBuildingNews();">인쇄</button>
    </div>
    <?php foreach($bill_dong as $key2 => $dong_row){

        // print_r2($dong_row);
    ?>
    <div class="print_wrapper_wrap">
    <div class="pages"><?php echo $key2?></div>
   
        <?php foreach($dong_row as $dong_key => $dong_row2){

            // print_r2($dong_row2);
            // echo "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' and bi_name = '성명(상호)' and dong_name = '{$key2}'";

        //성명
        $tenant_row = sql_fetch("SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' and bi_name = '성명(상호)' and dong_name = '{$key2}'");

        // print_r2($tenant_row);
        $item_tenant = explode('|', $tenant_row['bi_option']);
            
        //면적
        $area_row = sql_fetch("SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' and bi_name = '면적' and dong_name = '{$key2}'");
        $item_area = explode('|', $area_row['bi_option']);

        

        //납기내금액
        $due_in_price_row = sql_fetch("SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' and bi_name = '납기내금액' and dong_name = '{$key2}'");
        $item_due_in_price = explode('|', $due_in_price_row['bi_option']);

        $nabginae_price = 0;
        $nabginae_price = $item_due_in_price[$dong_key] != '' ? $item_due_in_price[$dong_key] : 0;

       
     
        

        //납기후금액
        $due_out_price_row = sql_fetch("SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' and bi_name = '납기후금액' and dong_name = '{$key2}'");
        $item_due_out_price = explode('|', $due_out_price_row['bi_option']);

        

        //납기후연체료
        $due_out_price_penulty_row = sql_fetch("SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' and bi_name = '납기후연체료' and dong_name = '{$key2}'");
        $item_due_out_price_penulty = explode('|', $due_out_price_penulty_row['bi_option']);


        
        //항목 가져오기
        //제외할 항목명
        $not_item = ['동호', '면적', '성명(상호)', '전기사용량', '온수사용량', '수도사용량', '난방사용량', '가스사용량', '공급가액', '부가세', '비과세', '면세', '당월분합계', '할인금액', '미납금액', '미납연체료', '납기내금액', '납기후연체료', '납기후금액', '자동이체', '입주일자'];
        $not_item_t = "'".implode("','", $not_item)."'";
        
        $bi_name_sql = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' and dong_name = '{$key2}' and bi_name NOT IN ({$not_item_t})  ORDER BY bi_idx asc";
        // echo $bi_name_sql.'<br>';
        $bi_name_res = sql_query($bi_name_sql);


        // print_r2($bi_name_res);

        $bi_name_arr = array();
        $bcnt = 0;
        foreach($bi_name_res as $name_key => $name_row){

            $bi_option2 = explode('|', $name_row['bi_option']);

            if($bi_option2[$dong_key] != ''){ //금액이 0원이면 제외
                $bi_name_arr[$bcnt]['item_name'] = $name_row['bi_name'];
                $bi_name_arr[$bcnt]['item_val'] = $bi_option2[$dong_key];

                $bcnt++;
            }
        }


        

        //부가세
        $vat_item = ['공급가액', '부가세', '비과세', '면세'];
        $vat_item_t = "'".implode("','", $vat_item)."'";

        $vat_name_sql = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' and dong_name = '{$key2}' and bi_name IN ({$vat_item_t})  ORDER BY bi_idx asc";

        // echo $vat_name_sql.'<br>';
        $vat_name_res = sql_query($vat_name_sql);

        $vat_item_list = array();
        $vcnt = 0;
        foreach($vat_name_res as $key => $row){

            $vat_option2 = explode('|', $row['bi_option']);
        
            $vat_item_list[$vcnt]['item_name'] = $row['bi_name'];
            $vat_item_list[$vcnt]['item_val'] = $vat_option2[$dong_key];

            $vcnt++;
        }

        // print_r2($vat_item_list);

        
        //미납금액
        $no_due_price_row = sql_fetch("SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' and bi_name = '미납금액' and dong_name = '{$key2}'");
        $item_no_due_price = explode('|', $no_due_price_row['bi_option']);
        // echo $vat_name_sql;

        //미납연체료
        $no_due_price_penulty_row = sql_fetch("SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' and bi_name = '미납연체료' and dong_name = '{$key2}'");
        $item_no_due_price_penulty = explode('|', $no_due_price_penulty_row['bi_option']);

        // print_r2($item_no_due_price);

        $item_no_due_price = $item_no_due_price[$dong_key] != '' ? str_replace(',', '', $item_no_due_price[$dong_key]) * 1 : 0;
        $item_no_due_price_penulty = $item_no_due_price_penulty[$dong_key] != '' ? str_replace(',', '', $item_no_due_price_penulty[$dong_key]) * 1 : 0;
        //미납관리비 합계
        $item_no_due_price_sum = $item_no_due_price + $item_no_due_price_penulty;

        // echo $dong_key.'<br>';
        //print_r2($item_no_due_price_penulty);

        //에너지 사용량
        $energy_item = ['전기사용량', '온수사용량', '수도사용량', '난방사용량', '가스사용량'];
        $energy_item_t = "'".implode("','", $energy_item)."'";

        $energy_name_sql = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' and dong_name = '{$key2}' and bi_name IN ({$energy_item_t})  ORDER BY bi_idx asc";
        $energy_name_res = sql_query($energy_name_sql);
        //echo $energy_name_sql.'<br>';

        $energy_item_list = array();
        $ecnt = 0;
        foreach($energy_name_res as $key => $row){

            $energy_option = explode('|', $row['bi_option']);
        
            $energy_item_list[$ecnt]['item_name'] = $row['bi_name'];
            $energy_item_list[$ecnt]['item_val'] = $energy_option[$dong_key];

            $ecnt++;
        }

        //면적
        $due_price_total_row = sql_fetch("SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' and bi_name = '당월분합계' and dong_name = '{$key2}'");
        $item_due_price_total = explode('|', $due_price_total_row['bi_option']);


        
        //print_r2($item_due_price_total);

        // echo '동 index : '.$dong_key.'<br>';
        ?>
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
                        <?php echo $key2; ?>
                        <?php echo $dong_row[$dong_key]; ?>
                    </div>
                    <div class="bill_box_inner ver2">
                        <?php echo $item_area[$dong_key] != '' ? number_format($item_area[$dong_key], 2) : 0; ?>
                    </div>
                </div>

                <!-- 납기정보 -->
                <div class="bill_due_info_wrap">
                    <div class="bill_due_box">
                        <div class="due_box red"><?php echo $nabginae_price; ?></div> <!-- 납기내금액 -->
                        <div class="due_box"><?php echo $bill_info['bill_due_date']; ?></div>
                        <div class="due_box"><?php echo $item_due_out_price[$dong_key] != '' ? $item_due_out_price[$dong_key] : 0; ?></div> <!-- 납기후금액 -->
                        <div class="due_box"><?php echo $building_info['building_bill_account_bank'];?></div>
                        <div class="due_box"><?php echo $building_info['building_bill_account']; ?></div>
                        <div class="due_box"><?php echo $building_info['building_bill_account_name'] == '' ? '(주)에스엠종합관리' : $building_info['building_bill_account_name'];?></div>
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
                            
                            echo $addr.'<br>'.$building_info['building_name'].' '.$key2.' '.$dong_row[$dong_key].'호';
                        ?>
                        </div>
                    </div>
                </div>

                <!-- 접은 페이지 항목 및 기타정보 -->
                <div class="bill_page2_wrap">
                    <div class="bill_page2_left">
                        <div class="bpage2_topbottom">
                            <div class="bpage2_top">
                                <?php foreach($bi_name_arr as $key => $val){?>
                                <div class="bill_item_boxs">
                                    <div class="bill_item_box_l"><?php echo $val['item_name']; ?></div>
                                    <div class="bill_item_box_r"><?php echo $val['item_val'] != '' ? $val['item_val'] :  0; ?></div>
                                </div>
                                <?php }?>
                            </div>
                            <?php if(!$bill_info['vt_add']){?>
                            <div class="bpage2_bottom">
                                <?php foreach($vat_item_list as $key => $val){?>
                                <div class="bill_item_boxs2">
                                    <div class="bill_item_box_l"><?php echo $val['item_name']; ?></div>
                                    <div class="bill_item_box_r"><?php echo $val['item_val'] != '' ? $val['item_val'] :  0; ?></div>
                                </div>
                                <?php }?>
                            </div>
                            <?php }?>
                        </div>
                        <div class="bpage2_no_due">
                            <div class="bpage2_no_due_box"><?php echo $item_no_due_price == 0 ? '' : number_format($item_no_due_price); ?></div>
                            <div class="bpage2_no_due_box"><?php echo $item_no_due_price_penulty == 0 ? '' : number_format($item_no_due_price_penulty); ?></div>
                        </div>
                        <div class="bpage2_no_due_sum_wrap">
                            <div class="bpage2_no_due_sum"><?php echo $item_no_due_price_sum == 0 ? '' : number_format($item_no_due_price_sum); ?></div>
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
                                <div class="bill_last_left_box"><?php echo $item_due_price_total[$dong_key] != '' ? $item_due_price_total[$dong_key] : 0;?></div>
                                <div class="bill_last_left_box"><?php echo $item_no_due_price == 0 ? '0' : number_format($item_no_due_price); ?></div>
                                <div class="bill_last_left_box"><?php echo $item_no_due_price_penulty == 0 ? '0' : number_format($item_no_due_price_penulty); ?></div>
                                <div class="bill_last_left_box red"><?php echo $item_due_in_price[$dong_key] != '' ? $item_due_in_price[$dong_key] : 0; ?></div>
                                <div class="bill_last_left_box"><?php echo $bill_info['bill_due_date']; ?></div>
                                <div class="bill_last_left_box"><?php echo $item_due_out_price_penulty[$dong_key] != '' ? $item_due_out_price_penulty[$dong_key] : 0; ?></div>
                                <div class="bill_last_left_box"><?php echo $item_due_out_price[$dong_key] != '' ? $item_due_out_price[$dong_key] : 0; ?></div>
                                <div class="bill_last_left_box"><?php echo $building_info['building_bill_account_bank'];?></div>
                                <div class="bill_last_left_box"><?php echo $building_info['building_bill_account']; ?></div>
                                <div class="bill_last_left_box"><?php echo $building_info['building_bill_account_name'] == '' ? '(주)에스엠종합관리' : $building_info['building_bill_account_name']; ?></div>
                            </div>
                            <div class="bill_last_right">
                                <div class="bill_last_right_box"><?php echo $item_due_price_total[$dong_key] != '' ? $item_due_price_total[$dong_key] : 0;?></div>
                                <div class="bill_last_right_box"><?php echo $item_no_due_price == 0 ? '0' : number_format($item_no_due_price); ?></div>
                                <div class="bill_last_right_box"><?php echo $item_no_due_price_penulty == 0 ? '0' : number_format($item_no_due_price_penulty); ?></div>
                                <div class="bill_last_right_box"><?php echo $item_due_price_total[$dong_key] != '' ? $item_due_price_total[$dong_key] : 0;?></div>
                                <div class="bill_last_right_box"><?php echo $item_due_out_price_penulty[$dong_key] != '' ? $item_due_out_price_penulty[$dong_key] : 0; ?></div>
                                <div class="bill_last_right_box"><?php echo $item_due_out_price[$dong_key] != '' ? $item_due_out_price[$dong_key] : 0; ?></div>
                                <div class="bill_last_right_box2">
                                    <!-- <span>2025</span> <span>4</span> <span>16</span> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php }?>
    </div>
    <?php } ?>
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
                padding: 47px 26px 0 35px;
                position: relative;
                background-image: url('/images/bill_sample3.png');
                background-size: 100% 100%;
                background-color:#fff;
            }

            .bill_box {font-size: 11px;color: #121212;}

            .building_info_addr {font-size: 14px;font-weight: 500;padding-left: 350px;}

            .building_info_wrap {display: flex;margin-top: 35px;width: auto;border: none;}
            .bill_box_inner {width: 240px;height: 36px;display: flex;align-items:center;justify-content:center;}
            .bill_box_inner.ver2 {width: 46px;}

            .bill_due_info_wrap {display: flex;margin-top: 9px;gap:0 10px;}
            .bill_due_info_wrap > div {width: 57%;min-height: 201px;}
            .bill_due_info_wrap > div.bill_due_box {width: 40%;}
            .bill_due_info_wrap > div.bill_dear_box {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                padding: 15px 0;;
            }
            .bill_due_box .due_box {height: calc(201px / 6);padding-left: 90px;display: flex;align-items:center;font-size: 10px;}
            .bill_dear_box .bill_dates {text-align: center;font-size: 14px;font-weight: 500;}
            .bill_dear_box .bill_addr {font-size: 17px;font-weight: 500;line-height: 1.5;padding-right: 15px;display: flex;justify-content: flex-end;word-break: keep-all;text-align: right;}

            .bill_page2_wrap {display: flex;margin-top: 14px;gap:0 10px;justify-content: space-between;}
            .bill_page2_wrap > div {min-height: 630px;}
            .bill_page2_left {width: 39%;}
            .bill_page2_right {width: 56%;}

            .bpage2_topbottom {height:600px;}
            .bpage2_top {height: 503px;padding-top: 35px;}
            .bill_item_boxs {display: flex;font-size: 11px;text-align: center;}
            .bill_item_boxs + .bill_item_boxs {margin-top: 8px;}
            .bill_item_box_l {width: 137px;    white-space: nowrap;text-overflow: ellipsis;overflow: hidden;
            padding: 0 10px;}
            .bill_item_box_r {width: calc(100% - 137px);}

            .bpage2_bottom {min-height: 98px;padding-top: 10px;}
            .bill_item_boxs2 {display: flex;font-size: 11px;text-align: center;}
            .bill_item_boxs2 + .bill_item_boxs2 {margin-top: 5px;}

            .bpage2_no_due {margin-top: 16px;display: flex;font-size: 11px;padding-top: 41px;}
            .bpage2_no_due > div {width: 50%;text-align: center;height: 27px;display: flex;align-items:center;justify-content:center;}

            .bpage2_no_due_sum_wrap {margin-top: 7px;display: flex;justify-content:flex-end;}
            .bpage2_no_due_sum {height: 18px;width: 50%;display: flex;align-items:center;justify-content:center;font-size: 11px;}

            .energy_wrap {height: 155px;padding-top: 55px;font-size: 10px;}
            .energy_boxs {display: flex;}
            .energy_boxs + .energy_boxs {margin-top: 4.5px;}
            .energy_boxs > div {width: 50%;text-align: center;}

            .building_notice_wrap {margin-top: 13px;height: 197px;padding: 35px 15px 0;}

            .bill_right_last_wrap {display: flex;margin-top: 5px;height: 304px;padding-top: 28px;}
            .bill_last_left {width: 245px;}
            .bill_last_right {width: calc(100% - 245px);padding-top: 5px;}

            .bill_last_left_box {height: 31px;display: flex;align-items:center;padding-left: 86px;justify-content:center;}
            .bill_last_right_box {height: 42px;padding-top: 11px;display: flex;align-items:center;justify-content:center;font-size: 11px;}
            .bill_last_right_box2 {font-size: 10px;padding-left:24px;}
            .bill_last_right_box2 span:nth-child(2) {margin-left:10px;margin-right: 8px;}

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