<?php
require_once './_common.php';

//계약정보
$contract_sql = "SELECT * FROM a_contract_list WHERE ct_idx = '{$ct_idx}'";
$contract_row = sql_fetch($contract_sql);

//계약히스토리
$ct_history_sql = "SELECT * FROM a_contract_list_history WHERE is_del = 0 and ct_idx = '{$ct_idx}' ORDER BY created_at asc";
$ct_history_res = sql_query($ct_history_sql);

//계약 선임자 정보
$ct_senior_sql = "SELECT * FROM a_contract_list_senior WHERE ct_idx = '{$ct_idx}'";
$ct_senior_row = sql_fetch($ct_senior_sql);


//계약 해지일 때
if(!$contract_row['ct_status'] && $ct_idx != ''){
    $verCl = "";
    $disabled = "disabled";
    $readonly = "readonly";
}else{
    $verCl = "ver2";
    $disabled = "";
    $readonly = "";
}
echo $ct_history_sql;
?>
<div class="cm_pop_title">계약정보</div>
<form name="fcontract" id="fcontract" action="./contract_formdata_update.php" onsubmit="return fcontract_submit(this);" method="post" autocomplete="off">
    <?php if($ct_idx != ''){?>
        <input type="text" name="w" value="<?php echo $contract_row['is_temp'] ? 't' : 'u'; ?>">
        <input type="hidden" name="ct_idx" value="<?php echo $ct_idx; ?>">
        <input type="hidden" name="ct_status_no" value="<?php echo $contract_row['ct_status'] ? 'Y' : '';?>">
    <?php }?>
    <div class="contract_form_wrap">
        <div class="contract_resident_wrap">
            <div class="contract_box contract_label_box">입주민 공개용</div>
            <div class="contract_box">
                <div class="contract_box_left">단지명 <span>*</span></div>
                <div class="contract_box_right contract_box_right_build">
                    <div class="sch_result_box sch_result_box_ct sch_result_box1">
                    </div>
                    <input type="hidden" name="building_id" id="building_id" class="bansang_ipt ver2 full" value="<?php echo $contract_row['building_id']; ?>">
                    <input type="text" name="building_name" id="building_name" class="bansang_ipt full <?php echo $ct_idx != '' ? '' : 'ver2 building_name';?>" value="<?php echo $contract_row['building_name']; ?>" required <?php echo $ct_idx != '' ? 'readonly' : '';?>>
                    <?php if($contract_row['ct_status'] || $ct_idx == ''){?>
                    <button type="button" class="rp_building_name rp_building_name2" style="<?php echo $ct_idx != '' ? 'display:block' : '';?>">다시 입력</button>
                    <?php }?>
                </div>
                <script>
                //업체 입력시 ajax
                $(document).on("keyup", ".building_name", function(){
                    let sch_text = this.value;

                    $("#building_id").val("");
                    if(sch_text != ""){
                        
                        $(".sch_result_box1").show();

                        $.ajax({

                        url : "./contract_list_building_ajax.php", //ajax 통신할 파일
                        type : "POST", // 형식
                        data: { "building_name":sch_text}, //파라미터 값
                        success: function(msg){ //성공시 이벤트

                            console.log('keyup',msg);
                        
                            $(".sch_result_box1").html(msg); //.select_box2에 html로 나타내라..
                        }

                        });
                    }else{
                        $(".sch_result_box1").html("");
                    }
                });

                function building_select(building_id, building_name){
                    $("#building_id").val(building_id);
                    $("#building_name").val(building_name);

                    $("#building_name").removeClass('ver2');
                    $("#building_name").removeClass('building_name');
                    $("#building_name").attr('readonly', true);
                    $(".sch_result_box1").hide();
                    $(".rp_building_name2").show();
                }

                $(".rp_building_name2").on("click", function(){
                    $("#building_name").addClass('ver2');
                    $("#building_name").addClass('building_name');
                    $("#building_name").attr('readonly', false);
                    $(".rp_building_name2").hide();
                    $("#building_id").val("");
                });
                </script>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">업체명 <span>*</span></div>
                <div class="contract_box_right contract_box_right_build">
                    <div class="sch_result_box sch_result_box_ct sch_result_box2">
                    </div>
                    <input type="hidden" name="company_idx" id="company_idx" class="bansang_ipt ver2 full" value="<?php echo $contract_row['company_idx']; ?>">
                    <input type="text" name="company_name" id="company_name" class="bansang_ipt <?php echo $ct_idx != '' ? '' : 'ver2 company_name'; ?>  full" value="<?php echo $contract_row['company_name']; ?>" <?php echo $ct_idx != '' ? 'readonly' : ''; ?> required>
                    <?php if($contract_row['ct_status'] || $ct_idx == ''){?>
                    <button type="button" class="rp_building_name rp_building_name4" style="<?php echo $ct_idx != '' ? 'display:block' : '';?>">다시 입력</button>
                    <?php }?>
                </div>
                <script>
                //업체 입력시 ajax
                $(document).on("keyup", ".company_name", function(){
                    let sch_text = this.value;

                    console.log('company_name 입력');

                    if(sch_text != ""){

                        $(".sch_result_box2").show();

                        $.ajax({

                        url : "./contract_list_company_ajax.php", //ajax 통신할 파일
                        type : "POST", // 형식
                        data: { "company_name":sch_text}, //파라미터 값
                        success: function(msg){ //성공시 이벤트

                            console.log('keyup',msg);
                        
                            $(".sch_result_box2").html(msg); //.select_box2에 html로 나타내라..
                        }

                        });
                    }else{
                        $(".sch_result_box2").html("");
                    }
                });

                function company_select(company_idx, company_name, industry_idx, industry_name, company_tel, company_mng_name, company_mng_tel){

                    let ct_idx = "<?php echo $ct_idx; ?>";

                    $("#company_idx").val(company_idx);
                    $("#company_name").val(company_name);
                    $("#industry_idx").val(industry_idx);
                    $("#industry_name").val(industry_name);
                    $("#industry_name").removeClass('ver2');
                    $("#industry_name").attr('readonly', true);

                    if(ct_idx == ''){
                        $("#company_number").val(company_tel);
                        $("#ct_mng1").val(company_mng_name);
                        $("#ct_mng1_hp").val(company_mng_tel);
                    }

                    $(".sch_result_box2").hide();

                    $("#company_name").removeClass('ver2');
                    $("#company_name").removeClass('company_name');
                    $("#company_name").attr('readonly', true);
                    $(".rp_building_name4").show();
                }

                $(".rp_building_name4").on("click", function(){
                    $("#company_name").addClass('ver2');
                    $("#company_name").addClass('company_name');
                    $("#company_name").attr('readonly', false);
                    $(".rp_building_name4").hide();
                    $("#company_idx").val("");
                });
                </script>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">업종 <span>*</span></div>
                <div class="contract_box_right contract_box_right_indus">
                    <input type="hidden" name="industry_idx" id="industry_idx" value="<?php echo $contract_row['industry_idx']; ?>">
                    <input type="text" name="industry_name" id="industry_name" class="bansang_ipt <?php echo $ct_idx != "" ? "" : "ver2";?> full" value="<?php echo $contract_row['industry_name']; ?>" readonly>
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">대표번호 <span>*</span></div>
                <div class="contract_box_right">
                    <input type="text" name="company_number" id="company_number" class="bansang_ipt <?php echo $verCl; ?> full" value="<?php echo $contract_row['company_number']; ?>" required <?php echo $readonly; ?>>
                </div>
            </div>
            <div class="contract_box column">
                <div class="contract_resident_chk_wrap">
                    <div class="ct_chk_box">
                        <input type="checkbox" name="resident_release" id="resident_release" value="1" <?php echo $contract_row['resident_release'] == '1' ? 'checked' : ''; ?> <?php echo $disabled; ?>>
                        <label for="resident_release">입주민 비공개</label>
                    </div>
                    <div class="ct_chk_box">
                        <input type="checkbox" name="company_recom" id="company_recom" value="1" <?php echo $contract_row['company_recom'] == '1' ? 'checked' : ''; ?> <?php echo $disabled; ?>>
                        <label for="company_recom">업체 추천</label>
                    </div>
                </div>
                <div class="ct_history_wrap" style="<?php echo $contract_row['ct_status'] ? 'margin-top:20px;' : '';?>">
                    <div class="ct_history_label">- 계약이력</div>
                    <table class="ct_history_table">
                        <thead>
                            <tr>
                                <th>계약일</th>
                                <th>계약 시작일</th>
                                <th>계약 종료일</th>
                                <th>계약 금액</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($i=0;$history_row = sql_fetch_array($ct_history_res);$i++){?>
                            <tr>
                                <td><?php echo date('Y-m-d', strtotime($history_row['created_at']));?></td>
                                <td><?php echo $history_row['ct_sdate'];?></td>
                                <td><?php echo $history_row['ct_edate'];?></td>
                                <td><?php echo number_format($history_row['ct_price']); ?></td>
                            </tr>
                            <?php }?>
                            <!-- <tr>
                                <td class="empty_tables" colspan='4'>등록된 계약이 없습니다.</td>
                            </tr> -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="contract_resident_wrap">
            <?php
            $todays = date('Y-m-d');
            $ct_history_go = "SELECT * FROM a_contract_list_history WHERE ct_hidx = '{$ct_hidx}'";
            $ct_history_go_row = sql_fetch($ct_history_go);

            $ct_hidx = $ct_history_go_row['ct_hidx']; //계약 히스토리 인덱스
            // echo $ct_history_go;
            ?>
            <div class="contract_box contract_label_box">내부 관리 정보</div>
            <div class="contract_box">
                <div class="contract_box_left">계약 시작일 <span>*</span></div>
                <div class="contract_box_right">
                    <input type="text" name="ct_sdate" id="ct_sdate" class="bansang_ipt <?php echo $ct_idx != '' && !$contract_row['is_temp'] ? '' : 'ver2 ipt_date2'; ?> full" value="<?php echo $ct_idx != '' ? $ct_history_go_row['ct_sdate'] : $ct_history_go_row['ct_sdate']; ?>" <?php echo $ct_idx != '' && !$contract_row['is_temp'] ? 'readonly' : ''; ?>  required>
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">계약 종료일 <span>*</span></div>
                <div class="contract_box_right">
                    <input type="text" name="ct_edate" id="ct_edate" class="bansang_ipt <?php echo $ct_idx != '' && $contract_row['is_temp'] ? 'ipt_date ver2' : ''; ?>  full" value="<?php echo $ct_idx != '' ? $ct_history_go_row['ct_edate'] : $ct_history_go_row['ct_edate']; ?>"  readonly required>
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">비용 <span>*</span></div>
                <div class="contract_box_right contract_box_right_build">
                    <?php
                    //비용 변경 연, 월
                    // $ct_sdate = date('Y-m-d', strtotime($ct_history_go_row['ct_sdate']."+1 month"));
                    // $ct_sdate = date('Y-m', strtotime($ct_sdate));
                    // $ct_sdate = $ct_sdate.'-01';

                    $startDate = new DateTime($ct_history_go_row['ct_sdate']);
                    $endDate = new DateTime($ct_history_go_row['ct_edate']);
                    //$startDate = new DateTime('2025-05-01');
                    //$endDate = new DateTime('2026-06-30');

                    $sdate = new DateTime($ct_history_go_row['ct_sdate']);
                    //$sdate->modify('first day of next month');
                    $sdateM = $sdate->format('n'); // 출력: 2025-06-01

                    $edate = new DateTime($ct_history_go_row['ct_edate']);
                    $edateM = $edate->format('n'); // 출력: 2025-06-01

                   // 반복용 변수 생성
                    $current = clone $sdate;

                    $yearsData = [];
                    $monthsData = [];
                    while ($current <= $edate) {
                        $dates = $current->format('n'); 
                        $years = $current->format('Y'); 
                        $current->modify('+1 month');
                        
                        if(!in_array($years, $yearsData)) array_push($yearsData, $years);

                        if(!in_array($dates, $monthsData)) array_push($monthsData, $dates);
                    }

                    // print_r2($yearsData);
                    // print_r2($monthsData);

                    // 연도 리스트 만들기
                    $years = [];
                    
                    for ($date = clone $startDate; $date <= $endDate; $date->modify('first day of next month')) {
                        $year = (int)$date->format('Y');
                        if (!in_array($year, $years)) {
                            $years[] = $year;
                        }
                    }

                    // 월 리스트 만들기
                    $months = [];
                    for ($date = clone $startDate; $date <= $endDate; $date->modify('first day of next month')) {
                        $month = (int)$date->format('n'); // 1~12
                        if (!in_array($month, $months)) {
                            $months[] = $month;
                        }
                    }

                    sort($months);

                    // print_r2($months);
                   
                    $monthCount = count($months);
                    ?>
                    <input type="hidden" name="ct_price_or" value="<?php echo $ct_history_go_row['ct_price']; ?>">
                    <input type="number" name="ct_price" id="ct_price" class="bansang_ipt <?php echo $ct_idx != '' && !$contract_row['is_temp'] ? '' : 'ver2'; ?> full" value="<?php echo $ct_idx != '' ? $ct_history_go_row['ct_price'] : $ct_history_go_row['ct_price']; ?>" min="0" <?php echo $ct_idx != '' && !$contract_row['is_temp'] ? 'disabled' : 'ver2'; ?> required>
                   
                    <?php if($ct_idx != '' && $monthCount > 1 && !$contract_row['is_temp'] && $contract_row['ct_status']){?>
                    <button type="button" class="rp_building_name rp_building_name3" style="<?php echo $ct_idx != '' ? 'display:block' : '';?>">비용변경</button>
                    <script>
                    $(".rp_building_name3").on("click", function(){
                        $("#ct_price").addClass('ver2');
                        $("#ct_price").attr('disabled', false);
                        $(".rp_building_name3").hide();

                        $(".price_ch_box").css('display', 'flex');

                        $("#ch_date_year2").attr('required', true);
                        $("#ch_date_month2").attr('required', true);
                    });
                    </script>
                    <?php }?>
                </div>
            </div>
            <div class="contract_box price_ch_box">
                <div class="contract_box_left">비용변경 년/월</div>
                <div class="contract_box_right flex_ver">
                    <?php
                    $year = date('Y');
                    $yearTo = $year + 3;
                    ?>
                    <input type="hidden" name="ct_hidx" value="<?php echo $ct_hidx; ?>">
                    <select name="ch_date_year2" id="ch_date_year2" class="bansang_sel">
                        <option value="">연도 선택</option>
                        <?php foreach($yearsData as $val){?>
                            <option value="<?php echo $val;?>"><?php echo $val; ?>년</option>
                        <?php }?>
                    </select>
                    <select name="ch_date_month2" id="ch_date_month2" class="bansang_sel">
                        <option value="">월 선택</option>
                        <?php foreach($monthsData as $val){?>
                            <option value="<?php echo $val;?>"><?php echo $val; ?>월</option>
                        <?php }?>
                    </select>
                </div>
            </div>
            <?php if(!$contract_row['is_temp'] && $ct_idx != ""){ ?>
            <div class="contract_box">
                <div class="contract_box_left">비용 변경내용</div>
                <div class="contract_box_right">
                    <?php
                       //and ct_hidx = '{$ct_hidx}'
                        $ct_price_his = "SELECT * FROM a_contract_list_price_history WHERE ct_idx = '{$ct_idx}'  and is_del = 0 ORDER BY psdate asc, ctp_idx asc";
                        
                        $ct_price_his_res = sql_query($ct_price_his);
                        
                        for($i=0;$ct_price_his_row = sql_fetch_array($ct_price_his_res);$i++){

                           //비용변경여부
                           $ch_status = $ct_price_his_row['ch_status'];
                           $ch_date_text = $ct_price_his_row['psdate'] . ' ~ '; //시작일

                           if($ch_status) $ch_date_text .= $ct_price_his_row['pedate']; //비용변경시 종료일

                           $ch_date_text .= " : " . number_format($ct_price_his_row['ct_price']);
                    ?>
                        <div class="price_his_box">
                            <?php echo $ch_date_text; ?>
                        </div>
                    <?php }?>
                </div>
            </div>
            <?php }?>
            <script>
                //계산서 발행 체크
                $("#bill_status").click(function () {
                
                    let bill_status = "<?php echo $contract_row['bill_status']; ?>";

                    if ($("#bill_status").is(":checked")) {
                        console.log('체크');

                        if(bill_status == 'Y'){
                            $(".bill_status_off").hide();
                            $(".bill_status_off_sel").attr('disabled', true);
                            $(".bill_status_off_sel").attr('required', false);
                        }else{
                            $(".bill_status_on").show();

                            $(".bill_status_on_sel").attr('disabled', false);
                            $(".bill_status_on_sel").attr('required', true);
                        }
                    } else {
                        console.log('체크해제');

                        if(bill_status == 'Y'){
                            $(".bill_status_off").show();
                            $(".bill_status_off_sel").attr('disabled', false);
                            $(".bill_status_off_sel").attr('required', true);
                        }else{
                            $(".bill_status_on").hide();
                            $(".bill_status_on_sel").attr('disabled', true);
                            $(".bill_status_on_sel").attr('required', false);
                        }
                       
                    }
            
                });
            </script>
            
            <div class="contract_box column">
                <div class="ct_chk_box ver2">
                    <div class="bill_status_on_wrap">
                        <div class="bill_status_on_box">
                            <input type="checkbox" name="bill_status" id="bill_status" value="1" <?php echo $contract_row['bill_status'] == 'Y' ? 'checked' : '';?> <?php echo $disabled; ?>>
                            <label for="bill_status">계산서 발행</label>
                        </div>
                        <div class="bill_status_on">
                            <div class="bill_status_on_box bill_status_on_box_ym flex_ver">
                                <div class="bill_status_on_label">- 년/월</div>
                                <div class="bill_status_on_select flex_ver">
                                    <?php
                                    $year = date('Y');
                                    $yearTo = $year + 3;
                                    ?>
                                    <select name="bill_year" id="bill_year" class="bansang_sel bill_status_on_sel" disabled>
                                        <option value="">연도 선택</option>
                                        <!-- <?php for($i=$year;$i<=$yearTo;$i++){?>
                                            <option value="<?php echo $i;?>"><?php echo $i.'년';?></option>
                                        <?php }?> -->
                                        <?php foreach ($years as $y): ?>
                                            <option value="<?= $y ?>"><?= $y ?>년</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="bill_month" id="bill_month" class="bansang_sel bill_status_on_sel" disabled>
                                        <option value="">월 선택</option>
                                        <?php foreach ($months as $m): ?>
                                            <option value="<?= $m ?>"><?= $m ?>월</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="bill_status_on_box_flex_wrap bill_status_on_box_bp">
                                <?php
                                $bill_type_sql = "SELECT * FROM a_company_bill_type WHERE is_del = 0 and is_use = 1 ORDER BY bt_idx asc";
                                $bill_type_res = sql_query($bill_type_sql);
                                ?>
                                <div class="bill_status_on_box">
                                    <div class="bill_status_on_label ver2">- 계산서 종류</div>
                                    <div class="bill_status_on_select">
                                        <select name="bill_type" id="bill_type" class="bansang_sel full bill_status_on_sel" disabled>
                                            <option value="">선택</option>
                                            <?php while($bill_type_row = sql_fetch_array($bill_type_res)){?>
                                                <option value="<?php echo $bill_type_row['bt_idx'];?>"><?php echo $bill_type_row['bill_name'];?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                                <?php 
                                $payment_type_sql = "SELECT * FROM a_payment_type WHERE is_del = 0 and is_use = 1 ORDER BY pt_idx asc";
                                $payment_type_res = sql_query($payment_type_sql);
                                ?>
                                <div class="bill_status_on_box">
                                    <div class="bill_status_on_label ver2">- 지급방식</div>
                                    <div class="bill_status_on_select">
                                        <select name="payment_type" id="payment_type" class="bansang_sel full bill_status_on_sel" disabled>
                                            <option value="">선택</option>
                                            <?php while($payment_type_row = sql_fetch_array($payment_type_res)){?>
                                                <option value="<?php echo $payment_type_row['pt_idx'];?>"><?php echo $payment_type_row['pt_name'];?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="bill_status_text">
                                <p>선택한 년/월 부터 계산서 발행 시작됩니다.</p>
                                <p>계산서 발행 년/월 설정 후 저장 시 수정 불가 합니다.</p>
                            </div>
                        </div>
                        <!-- 계산서 발행 중지 -->
                        <div class="bill_status_off">
                            <div class="bill_status_on_box flex_ver">
                                <div class="bill_status_on_label">- 년/월</div>
                                <div class="bill_status_on_select flex_ver">
                                    <?php
                                    $year = date('Y');
                                    $yearTo = $year + 3;
                                    ?>
                                    <select name="bill_eyear" id="bill_eyear" class="bansang_sel bill_status_off_sel" disabled>
                                        <option value="">연도 선택</option>
                                        <?php foreach ($years as $y): ?>
                                            <option value="<?= $y ?>"><?= $y ?>년</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="bill_emonth" id="bill_emonth" class="bansang_sel bill_status_off_sel" disabled>
                                        <option value="">월 선택</option>
                                        <?php foreach ($months as $m): ?>
                                            <option value="<?= $m ?>"><?= $m ?>월</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="bill_status_text mgt10" style="padding-bottom:0;">
                                <p>선택한 월 부터 계산서 발행을 중지합니다. (선택한 월의 전월까지만 계산서 발행을 시행)</p>
                                <p>계산서 발행 년/월 설정 후 저장 시 수정 불가 합니다</p>
                            </div>
                        </div>
                    </div>

                    <?php
                    // 계산서 발행 히스토리
                    $contract_bill_sql = "SELECT bill.ct_idx, bill.bill_year, bill.bill_month, bill.bill_eyear, bill.bill_emonth, bill.bill_type, bt.bill_name, bill.payment_type, pt.pt_name FROM a_contract_list_bill as bill
                                            LEFT JOIN a_company_bill_type as bt on bill.bill_type = bt.bt_idx
                                            LEFT JOIN a_payment_type as pt on bill.payment_type = pt.pt_idx
                                            WHERE bill.ct_idx = '{$ct_idx}' ORDER BY bill.ctb_idx asc";
                    $contract_bill_res = sql_query($contract_bill_sql);
                    $contract_bill_total = sql_num_rows($contract_bill_res);
                    ?>
                    <?php if($contract_bill_total > 0){?>
                        <div class="bill_list_wrapper mgt15">
                        <?php
                        foreach($contract_bill_res as $idx => $val){

                            //계산서 발행일자
                            $bill_month_zero = str_pad($val['bill_month'], 2, "0", STR_PAD_LEFT);
                            $bill_text1 = "계산서 발행 : " .$val['bill_year'] . '-' . $bill_month_zero .  ' ~ ';

                            if(!$val['ctb_status']){
                                $bill_emonth_zero = $val['bill_emonth'] != '' ? '-'.str_pad($val['bill_emonth'], 2, "0", STR_PAD_LEFT) : '';
                                $bill_text1 .= $val['bill_eyear'].$bill_emonth_zero;
                            }


                            //계산서 종류 및 지급방식
                            $bill_text2 = "계산서 종류 : ". $val['bill_name'] . ' / 지급방식 : '.$val['pt_name'];
                        ?>
                            <div class="bill_list_box ">
                                <p><?php echo $bill_text1;?></p>
                                <p class="mgt10"><?php echo $bill_text2;?></p>
                            </div>
                        <?php }?>
                        </div>
                    <?php }?>
                </div>
                
            </div>
            <div class="contract_box">
                <div class="contract_box_left">담당자1</div>
                <div class="contract_box_right">
                    <input type="text" name="ct_mng1" id="ct_mng1" class="bansang_ipt <?php echo $verCl; ?> full" value="<?php echo $contract_row['ct_mng1']; ?>" <?php echo $readonly; ?>>
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">담당자1 연락처</div>
                <div class="contract_box_right">
                    <input type="text" name="ct_mng1_hp" id="ct_mng1_hp" class="bansang_ipt <?php echo $verCl; ?> full" value="<?php echo $contract_row['ct_mng1_hp']; ?>" <?php echo $readonly; ?>>
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">담당자2</div>
                <div class="contract_box_right">
                    <input type="text" name="ct_mng2" id="ct_mng2" class="bansang_ipt <?php echo $verCl; ?> full" value="<?php echo $contract_row['ct_mng2']; ?>" <?php echo $readonly; ?>>
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">담당자2 연락처</div>
                <div class="contract_box_right">
                    <input type="text" name="ct_mng2_hp" id="ct_mng2_hp" class="bansang_ipt <?php echo $verCl; ?> full" value="<?php echo $contract_row['ct_mng2_hp']; ?>" <?php echo $readonly; ?>>
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">비고</div>
                <div class="contract_box_right">
                    <textarea name="ct_bigo" id="ct_bigo" class="bansang_ipt ver2 ta full"><?php echo $contract_row['ct_bigo']; ?></textarea>
                </div>
            </div>
        </div>
        <div class="contract_resident_wrap">
            <div class="contract_box contract_label_box">
                선임자 정보
                <?php if($contract_row['ct_status'] || $ct_idx == ''){?>
                <div class="contract_label_chk_box">
                    <input type="checkbox" name="sn_use" id="sn_use" value="1" <?php echo $ct_senior_row['sn_not_use'] == '1' ? 'checked' : ''; ?>>
                    <label for="sn_use">미사용</label>
                </div>
                <?php }?>
                <script>
                    let sn_use = "<?php echo $ct_senior_row['sn_not_use'] == '1' ? '1' : '0'; ?>";
                    let ct_status = "<?php echo $contract_row['ct_status']; ?>";


                    if(ct_status == '0'){
                        sn_use = '1';
                    }

                    if(sn_use == '1'){
                        $(".sn_ipt").removeClass("ver2");
                        $(".sn_ipt").attr({'disabled': true});
                    }else{
                        $(".sn_ipt").addClass("ver2");
                        $(".sn_ipt").attr({'disabled': false});
                    }

                    //선임자 미사용 체크
                    $("#sn_use").click(function () {
                    
                        if ($("#sn_use").is(":checked")) {
                            console.log('체크');

                            $(".sn_ipt").removeClass("ver2");
                            $(".sn_ipt").attr({'disabled': true});
                        } else {
                            console.log('체크해제');
                            $(".sn_ipt").addClass("ver2");
                            $(".sn_ipt").attr({'disabled': false});
                        }
                
                    });
                </script>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">선임자명</div>
                <div class="contract_box_right">
                    <input type="text" name="sn_name" id="sn_name" class="bansang_ipt ver2 full sn_ipt" value="<?php echo $ct_senior_row['sn_name']; ?>">
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">선임자 연락처</div>
                <div class="contract_box_right">
                    <input type="text" name="sn_hp" id="sn_hp" class="bansang_ipt ver2 full sn_ipt" value="<?php echo $ct_senior_row['sn_hp']; ?>">
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">선임일</div>
                <div class="contract_box_right">
                    <input type="text" name="sn_date" id="sn_date" class="bansang_ipt ver2 full ipt_date sn_ipt" value="<?php echo $ct_senior_row['sn_date']; ?>" <>
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">선임기간</div>
                <div class="contract_box_right flex_ver">
                    <input type="text" name="sn_sdate" id="sn_sdate" class="bansang_ipt ver2 full ipt_date sn_ipt" value="<?php echo $ct_senior_row['sn_sdate']; ?>" > ~
                    <input type="text" name="sn_edate" id="sn_edate" class="bansang_ipt ver2 full ipt_date sn_ipt" value="<?php echo $ct_senior_row['sn_edate']; ?>">
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">교육이수일</div>
                <div class="contract_box_right">
                    <input type="text" name="edu_sdate" id="edu_sdate" class="bansang_ipt ver2 full ipt_date sn_ipt" value="<?php echo $ct_senior_row['edu_sdate']; ?>" >
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">교육만료일</div>
                <div class="contract_box_right">
                    <input type="text" name="edu_edate" id="edu_edate" class="bansang_ipt ver2 full ipt_date sn_ipt" value="<?php echo $ct_senior_row['edu_edate']; ?>" >
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">보험정보</div>
                <div class="contract_box_right">
                    <input type="text" name="insurance_name" id="insurance_name" class="bansang_ipt ver2 full sn_ipt" value="<?php echo $ct_senior_row['insurance_name']; ?>" >
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">보험기간</div>
                <div class="contract_box_right">
                    <input type="text" name="insurance_date" id="insurance_date" class="bansang_ipt ver2 full sn_ipt" value="<?php echo $ct_senior_row['insurance_date']; ?>" >
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">보험 가입금액</div>
                <div class="contract_box_right">
                    <input type="text" name="insurance_price" id="insurance_price" class="bansang_ipt ver2 full sn_ipt" value="<?php echo $ct_senior_row['insurance_price']; ?>" >
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">가입담당연락처</div>
                <div class="contract_box_right">
                    <input type="text" name="insurance_mng" id="insurance_mng" class="bansang_ipt ver2 full sn_ipt" value="<?php echo $ct_senior_row['insurance_mng']; ?>" >
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">비고</div>
                <div class="contract_box_right">
                    <textarea name="sn_memo" id="sn_memo" class="bansang_ipt ver2 ta full"><?php echo $ct_senior_row['sn_memo']; ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="contract_bottom_wrap">
        <div class="contract_status_box">
            <?php if(!$contract_row['is_temp'] && $ct_idx != ''){?>
            <div class="ct_status_chk_box">
                <input type="checkbox" name="ct_status" id="ct_status" value="1" <?php echo $contract_row['ct_status'] == '0' ? 'checked' : ''; ?> <?php echo $contract_row['ct_status'] == '0' ? 'disabled' : ''; ?>>
                <label for="ct_status">계약해지</label>
                
                <?php if($contract_row['ct_status'] == '0'){?>
                    <input type="hidden" name="ct_status" value="1">
                <?php }?>
            </div>
            <p>* 계약 해지 후 해지한 계약건은 다시 계약중 상태로 변경 불가합니다.</p>
            <p>* 새롭게 계약 추가 하여야 합니다.</p>
            <div class="ct_status_date_box_wrap" style="<?php echo $contract_row['ct_status'] == '0' ? 'display:block' : ''; ?>">
                <div class="ct_status_date_box">
                    <div class="ct_status_date_label">해지 날짜 설정</div>
                    <select name="ct_status_year" id="ct_status_year" class="bansang_sel" <?php echo $contract_row['ct_status'] == '0' ? 'readonly' : ''; ?>>
                        <option value="">년 선택</option>
                        <?php for($i=$year;$i<=$yearTo;$i++){?>
                            <option value="<?php echo $i;?>" <?php echo $contract_row['ct_del_year'] == $i ? 'selected' : ''; ?>><?php echo $i.'년';?></option>
                        <?php }?>
                    </select>
                    <select name="ct_status_month" id="ct_status_month" class="bansang_sel" <?php echo $contract_row['ct_status'] == '0' ? 'readonly' : ''; ?>>
                        <option value="">월 선택</option>
                        <?php for($i=1;$i<=12;$i++){
                            $months = str_pad($i, 2, "0", STR_PAD_LEFT);
                            ?>
                            <option value="<?php echo $i; ?>" <?php echo $contract_row['ct_del_month'] == $i ? 'selected' : ''; ?>><?php echo $months.'월'; ?></option>
                        <?php }?>
                    </select>
                </div>
                <p>* 설정한 년/월의 익월부터 계약종료 처리됩니다.</p>
            </div>
            <script>
                //선임자 미사용 체크
                $("#ct_status").click(function () {
                    
                    if ($("#ct_status").is(":checked")) {
                        console.log('체크');

                        $(".ct_status_date_box_wrap").css('display', 'block');
                        $("#ct_status_year").attr({'required': true});
                        $("#ct_status_month").attr({'required': true});
                    } else {
                        console.log('체크해제');
                        $(".ct_status_date_box_wrap").css('display', 'none');
                        $("#ct_status_year").attr({'required': false});
                        $("#ct_status_month").attr({'required': false});
                    }
            
                });
            </script>
            <?php }?>
        </div>
        <div class="contract_btn_wraps">
            <?php if($contract_row['is_temp'] || $ct_idx == ''){?>
            <button type="submit" name="save_type" class="ct_btns01" value="temp">임시저장</button>
            <?php }?>
            <button type="submit" name="save_type" class="ct_btns02" value="save"><?php echo !$contract_row['is_temp'] && $ct_idx != '' ? '수정' : '저장';?></button>
            <?php if(!$contract_row['is_temp'] && $ct_idx != '' && $contract_row['ct_status']){?>
                <button type="button" onclick="extend_pop_handler('<?php echo $contract_row['ct_idx']; ?>');">연장하기</button>
            <?php }?>
            <button type="button" name="save_type" value="temp" onclick="popClose('contract_add_pop');">닫기</button>
        </div>
    </div>
</form>
<script>
$(function(){
    //maxDate: "+365d"
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
});

$(function () {
  $(".ipt_date2").datepicker({
    changeMonth: true, 
    changeYear: true,
    dateFormat: "yy-mm-dd",
    showButtonPanel: true, 
    yearRange: "c-99:c+99",
    //maxDate: "+365d",
    onSelect: function (selectedDate) {
      // 시작일을 선택하면 종료일 활성화
      $("#ct_edate").prop("readonly", false);
      $("#ct_edate").addClass("ver2");
      $("#ct_edate").addClass("ipt_date");

      // 시작일 객체 생성
      var startDate = new Date(selectedDate);

      // 종료일 최소는 시작일, 최대는 1년 후
      var endDate = new Date(startDate);
      endDate.setFullYear(startDate.getFullYear() + 1);

      // ct_edate 설정
      $("#ct_edate").datepicker("destroy"); // 기존 옵션 제거
      $("#ct_edate").datepicker({
        changeMonth: true, 
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true, 
        minDate: startDate,
        // maxDate: endDate,
      });
    },
  });
});

function fcontract_submit(f){
    if(f.building_id.value == ""){
        alert("단지명 입력 후 나온 단지 리스트에서 단지를 선택해주세요.");
        f.building_name.focus();
        return false;
    }
    
    if(f.company_idx.value == ""){
        alert("업체명을 입력 후 나온 업체 리스트에서 업체를 선택해주세요.");
        f.company_name.focus();
        return false;
    }

    // if(f.bill_year.value > f.bill_year2.value){
    //     alert("계산서 발행 중지 날짜가 시작날짜보다 이전일 수 없습니다.");
    //     return false;
    // }

    // if(f.bill_year.value == f.bill_year2.value){
    //     if(f.bill_month.value > f.bill_month2.value){
    //         alert("계산서 발행 중지 날짜가 시작날짜보다 이전일 수 없습니다.");
    //         return false;
    //     }
    // }

    // if(f.ch_date_year2.value != "" && f.ch_date_month2.value != ""){
    //     if(f.ct_price_or.value == f.ct_price.value){
    //         alert('비용이 변경되지 않았습니다.');
    //         return false;
    //     }
    // }

    return true;
}
</script>