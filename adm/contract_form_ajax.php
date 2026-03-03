<?php
require_once './_common.php';

$sql = "select contract.*, building.building_name, ccb.bill_type, cb.bill_name, ccb.payment_type, pt.pt_name, ccb.bill_syear, ccb.bill_smonth, ccb.bill_eyear, ccb.bill_emonth from a_contract as contract left join a_building as building on contract.building_id = building.building_id left join a_contract_company_bill as ccb on ccb.ct_idx = contract.ct_idx left join a_company_bill_type as cb on cb.bt_idx = ccb.bill_type left JOIN a_payment_type as pt on ccb.payment_type = pt.pt_idx where (1) and contract.is_del = '0' and contract.ct_idx = '{$ct_idx}'";

// echo $sql.'<br>';
$row = sql_fetch($sql);

$sn_use = $row['sn_use'] ?? "1";

$sql_sn = "SELECT * FROM a_senior WHERE ct_idx = '{$ct_idx}'";
$row_sn = sql_fetch($sql_sn);

$history_sql = "SELECT * FROM a_contract_history WHERE ct_idx = '{$ct_idx}' and is_del = 0 ORDER BY cth_idx asc";
// echo $history_sql.'<br>';
if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $history_sql.'<br>';
}
$history_res = sql_query($history_sql);

$ct_status_no = $row['ct_status'] ? 'disabeld' : '';
?>
<div class="cm_pop_title">계약정보</div>
<form name="fcontract" id="fcontract" action="./contract_form_update.php" onsubmit="return fcontract_submit(this);" method="post" autocomplete="off">
    <?php if($ct_idx != ''){?>
        <input type="hidden" name="w" value="u">
        <input type="hidden" name="ct_idx" value="<?php echo $ct_idx; ?>">
        <input type="hidden" name="ct_status_no" value="<?php echo $row['ct_status'] ? 'Y' : '';?>">
    <?php }?>
    <div class="contract_form_wrap">
        <div class="contract_resident_wrap">
            <div class="contract_box contract_label_box">입주민 공개용</div>
            <div class="contract_box">
                <div class="contract_box_left">단지명 <span>*</span></div>
                <div class="contract_box_right contract_box_right_build">
                    <div class="sch_result_box sch_result_box_ct sch_result_box1">
                    </div>
                    <input type="hidden" name="building_id" id="building_id" class="bansang_ipt ver2 full" value="<?php echo $row['building_id']; ?>">
                    <input type="text" name="building_name" id="building_name" class="bansang_ipt full <?php echo $ct_idx != '' ? '' : 'ver2 building_name';?>" value="<?php echo $row['building_name']; ?>" required <?php echo $ct_idx != '' ? 'readonly' : '';?>>
                    <?php if(!$row['ct_status']){?>
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
                    <input type="hidden" name="company_idx" id="company_idx" class="bansang_ipt ver2 full" value="<?php echo $row['company_idx']; ?>">
                    <input type="text" name="company_name" id="company_name" class="bansang_ipt <?php echo $ct_idx != '' ? '' : 'ver2 company_name'; ?>  full" value="<?php echo $row['company_name']; ?>" <?php echo $ct_idx != '' ? 'readonly' : ''; ?> required>
                    <?php if($row['is_temp']){  //임시저장일 때 ?>
                    <button type="button" class="rp_building_name rp_building_name4" style="<?php echo $ct_idx != '' ? 'display:block' : '';?>">다시 입력</button>
                    <?php }?>
                </div>
                <script>
                //업체 입력시 ajax
                $(document).on("keyup", ".company_name", function(){
                    let sch_text = this.value;

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
                    $("#company_idx").val(company_idx);
                    $("#company_name").val(company_name);
                    // $("#industry_idx").val(industry_idx);
                    // $("#industry_name").val(industry_name);
                    $("#company_tel").val(company_tel);
                    $("#mng_name1").val(company_mng_name);
                    $("#mng_hp1").val(company_mng_tel);

                    $(".sch_result_box2").hide();

                    let isTemps = "<?php echo $row['is_temp'] ? 'temp' : '';  ?>";

                    if(isTemps){
                        $("#company_name").removeClass('ver2');
                        $("#company_name").removeClass('company_name');
                        $("#company_name").attr('readonly', true);
                        $(".rp_building_name4").show();
                    }

                    $.ajax({

                    url : "./company_industry_ajax.php", //ajax 통신할 파일
                    type : "POST", // 형식
                    data: { "industry_idx":industry_idx}, //파라미터 값
                    success: function(msg){ //성공시 이벤트
                        console.log(msg);
                        $(".contract_box_right_indus").html(msg);
                    }

                    });
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
                    <?php
                    $industry_sql = "SELECT * FROM a_industry_list WHERE is_use = 1 and is_del = 0 ORDER BY is_fixed asc, industry_idx asc";
                    $industry_res = sql_query($industry_sql);
                    ?>
                    <select name="industry_idx" id="industry_idx" class="bansang_sel full" <?php echo $ct_idx != "" ? 'readonly' : '';?>>
                        <option value="">업종을 선택하세요.</option>
                        <?php while($industry_row = sql_fetch_array($industry_res)){?>
                            <option value="<?php echo $industry_row['industry_idx']?>" <?php echo get_selected($row['industry_idx'], $industry_row['industry_idx']); ?>><?php echo $industry_row['industry_name']; ?></option>
                        <?php }?>
                    </select>
                    <!-- <input type="hidden" name="industry_idx" id="industry_idx" class="bansang_ipt ver2 full" value="<?php echo $row['industry_idx']; ?>">
                    <input type="hidden" name="industry_name" id="industry_name" class="bansang_ipt ver2 full" value="<?php echo $row['industry_name']; ?>" required> -->
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">대표번호 <span>*</span></div>
                <div class="contract_box_right">
                    <input type="text" name="company_tel" id="company_tel" class="bansang_ipt ver2 full" value="<?php echo $row['company_tel']; ?>" required <?php echo $row['ct_status'] ? 'disabled' : '';?>>
                </div>
            </div>
            <div class="contract_box column">
                <?php if(!$row['ct_status']){?>
                <div class="contract_resident_chk_wrap">
                    <div class="ct_chk_box">
                        <input type="checkbox" name="resident_release" id="resident_release" value="1" <?php echo $row['resident_release'] == '1' ? 'checked' : ''; ?>>
                        <label for="resident_release">입주민 비공개</label>
                    </div>
                    <div class="ct_chk_box">
                        <input type="checkbox" name="company_recom" id="company_recom" value="1" <?php echo $row['company_recom'] == '1' ? 'checked' : ''; ?>>
                        <label for="company_recom">업체 추천</label>
                    </div>
                </div>
                <?php }?>
                <div class="ct_history_wrap" style="<?php echo $row['ct_status'] ? 'margin-top:20px;' : '';?>">
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
                            <?php 
                            if(!$row['is_temp']){
                            for($i=0;$history_row = sql_fetch_array($history_res);$i++){?>
                            <tr>
                                <td><?php echo date('Y-m-d', strtotime($history_row['created_at']));?></td>
                                <td><?php echo $history_row['ct_sdate'];?></td>
                                <td><?php echo $history_row['ct_edate'];?></td>
                                <td><?php echo number_format($history_row['ct_price']); ?></td>
                            </tr>
                            <?php }?>
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

            $y = date('Y');
            $months = date('m');
            $month_start2 = date("Y-m-01", strtotime("$y-$months-01")); // 2025-07-01
            $month_end2   = date("Y-m-t", strtotime("$y-$months-01"));  // 2025-07-31

            if($ct_idx != ""){
                if($edate != '' && $sdate != ''){
                    $sql_where_dates = " and ct_sdate <= '{$edate}' and ct_edate >= '{$sdate}' ";
                    $sql_limit_dates = "";
                }else{
                    $sql_where_dates = " and ct_edate >= '{$todays}' ";
                    $sql_limit_dates = " limit 0, 1 ";
                }
            }
            $ct_history_go = "SELECT * FROM a_contract_history WHERE ct_idx = '{$ct_idx}' {$sql_where_dates} {$sql_limit_dates}";
            if($_SERVER["REMOTE_ADDR"] == "59.16.155.80"){
                // echo $ct_history_go.'<br>';
            }
            $ct_history_go_row = sql_fetch($ct_history_go);
            // echo $ct_history_go;

            $contract_history_price = "SELECT * FROM a_contract_price_history WHERE ct_idx = '{$row['ct_idx']}'  and (ch_start_date <= '{$todays}') ORDER BY cph_idx desc limit 0, 1";
            $contract_history_price_row = sql_fetch($contract_history_price);

            $ct_price_w = 0;

            if($contract_history_price_row){
                $ct_price_w = $contract_history_price_row['price'];
            }else{
                $ct_price_w = $ct_idx != '' ? $ct_history_go_row['ct_price'] : $row['ct_price'];
            }
            if($_SERVER["REMOTE_ADDR"] == "59.16.155.80"){
                // echo $contract_history_price.'<br>';
            }
            ?>
            <div class="contract_box contract_label_box">내부 관리 정보</div>
            <div class="contract_box">
                <div class="contract_box_left">계약 시작일 <span>*</span></div>
                <div class="contract_box_right">
                    <input type="text" name="ct_sdate" id="ct_sdate" class="bansang_ipt <?php echo $ct_idx != '' && !$row['is_temp'] ? '' : 'ver2 ipt_date2'; ?> full" value="<?php echo $ct_idx != '' ? $ct_history_go_row['ct_sdate'] : $row['ct_sdate']; ?>" <?php echo $ct_idx != '' && !$row['is_temp'] ? 'readonly' : ''; ?>  required>
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">계약 종료일 <span>*</span></div>
                <div class="contract_box_right">
                    <input type="text" name="ct_edate" id="ct_edate" class="bansang_ipt <?php echo $ct_idx != '' && !$row['is_temp'] ? '' : 'ver2 ipt_date2'; ?> full" value="<?php echo $ct_idx != '' ? $ct_history_go_row['ct_edate'] : $row['ct_edate']; ?>" <?php echo $ct_idx != '' && !$row['is_temp'] ? 'readonly' : ''; ?>  required>
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">비용 <span>*</span></div>
                <div class="contract_box_right contract_box_right_build">
                    <input type="hidden" name="ct_price_or" value="<?php echo $ct_price_w; ?>">
                    <input type="number" name="ct_price" id="ct_price" class="bansang_ipt <?php echo $ct_idx != '' && !$row['is_temp'] ? '' : 'ver2'; ?> full" value="<?php echo $ct_price_w; ?>" min="0" <?php echo $ct_idx != '' && !$row['is_temp'] ? 'disabled' : 'ver2'; ?> required>
                   
                    <?php if($ct_idx != '' && !$row['is_temp'] && !$row['ct_status']){?>
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
                    $year = 2017;
                    // $year = date('Y');
                    //$yearTo = $year + 3;
                    $yearTo = $year + 13;
                    ?>
                    <select name="ch_date_year2" id="ch_date_year2" class="bansang_sel">
                        <option value="">연도 선택</option>
                        <?php for($i=$year;$i<=$yearTo;$i++){?>
                            <option value="<?php echo $i;?>"><?php echo $i.'년';?></option>
                        <?php }?>
                    </select>
                    <select name="ch_date_month2" id="ch_date_month2" class="bansang_sel">
                        <option value="">월 선택</option>
                        <?php for($i=1;$i<=12;$i++){
                            $months = str_pad($i, 2, "0", STR_PAD_LEFT);
                            ?>
                            <option value="<?php echo $i; ?>"><?php echo $months.'월'; ?></option>
                        <?php }?>
                    </select>
                </div>
            </div>
            <?php if(!$row['is_temp'] && $ct_idx != ""){ ?>
            <div class="contract_box">
                <div class="contract_box_left">비용 변경내용</div>
                <div class="contract_box_right">
                    <?php
                        $ct_price_his = "SELECT * FROM a_contract_price_history WHERE ct_idx = '{$ct_idx}' and is_del = 0 ORDER BY ch_end_date asc, ch_start_date asc, cph_idx asc";
                        

                        if($_SERVER["REMOTE_ADDR"] == ADMIN_IP){
                            // echo $ct_price_his.'<br>';
                        }
                        $ct_price_his_res = sql_query($ct_price_his);
                        
                        for($i=0;$ct_price_his_row = sql_fetch_array($ct_price_his_res);$i++){

                            $ch_date_month = str_pad($ct_price_his_row['ch_date_month'], 2, "0", STR_PAD_LEFT);

                            $sdates = $ct_price_his_row['ch_start_date'] == '' ? $ct_price_his_row['ch_date_year'].'-'.$ch_date_month :  $ct_price_his_row['ch_start_date'];
                    ?>
                        <div class="price_his_box">
                            <?php echo $sdates; ?> ~ <?php echo $ct_price_his_row['ch_end_date']; ?> : <?php echo number_format($ct_price_his_row['price']);?>
                        </div>
                    <?php }?>
                </div>
            </div>
            <?php }?>
            <script>
                //계산서 발행 체크
                $("#bill_status").click(function () {
                
                    if ($("#bill_status").is(":checked")) {
                        console.log('체크');

                        $(".bill_status_on").show();

                        $(".bill_status_on_sel").attr('disabled', false);
                        $(".bill_status_on_sel").attr('required', true);
                    } else {
                        console.log('체크해제');
                        $(".bill_status_on").hide();
                        $(".bill_status_on_sel").attr('disabled', true);
                        $(".bill_status_on_sel").attr('required', false);
                    }
            
                });

                //계산서 발행 종료 체크
                $("#bill_stop_status").click(function () {
                
                    if ($("#bill_stop_status").is(":checked")) {
                        console.log('체크');

                        $(".bill_status_off").show();
                        $(".bill_status_off_sel").attr('disabled', false);
                        $(".bill_status_off_sel").attr('required', true);
                    } else {
                        console.log('체크해제');
                        $(".bill_status_off").hide();
                        $(".bill_status_off_sel").attr('disabled', true);
                        $(".bill_status_off_sel").attr('required', false);
                    }
            
                });
            </script>
            
            <div class="contract_box column">
                <div class="ct_chk_box ver2">
                    <?php if($row['bill_status']){?>
                    <div class="bill_status_off_wrap">
                        <div class="bill_status_off_box">
                            <!-- <?php echo $row['bill_stop_status'] == '1' ? 'checked' : ''; ?> <?php echo $row['bill_stop_status'] == '1' ? 'disabled' : ''; ?> -->
                            <input type="checkbox" name="bill_stop_status" id="bill_stop_status" value="1" >
                            <label for="bill_stop_status">계산서 발행 중지</label>
                        </div>
                        <div class="bill_status_off">
                            <div class="bill_status_on_box flex_ver">
                                <div class="bill_status_on_label">- 년/월</div>
                                <div class="bill_status_on_select flex_ver">
                                    <?php
                                    $year = date('Y');
                                    $yearTo = $year + 3;
                                    $yearTo2 = 2017;
                                    //$yearTo2 = floor($year / 10) * 10;
                                    ?>
                                    <select name="bill_year2" id="bill_year2" class="bansang_sel bill_status_off_sel" disabled>
                                        <option value="">연도 선택</option>
                                        <?php for($i=$yearTo;$i>=$yearTo2;$i--){?>
                                            <option value="<?php echo $i;?>"><?php echo $i.'년';?></option>
                                        <?php }?>
                                    </select>
                                    <select name="bill_month2" id="bill_month2" class="bansang_sel bill_status_off_sel" disabled>
                                        <option value="">월 선택</option>
                                        <?php for($i=1;$i<=12;$i++){
                                            $months = str_pad($i, 2, "0", STR_PAD_LEFT);
                                            ?>
                                            <option value="<?php echo $i; ?>"><?php echo $months.'월'; ?></option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php }else{?>
                    <!-- 계산서 발행시 -->
                     <div class="bill_status_on_wrap">
                        <div class="bill_status_on_box">
                            <!-- <?php echo $row['bill_status'] == '1' ? 'checked' : ''; ?> <?php echo $row['bill_status'] == '1' ? 'disabled' : ''; ?> -->
                            <input type="checkbox" name="bill_status" id="bill_status" value="1" >
                            <label for="bill_status">계산서 발행</label>
                        </div>
                        <div class="bill_status_on">
                            <div class="bill_status_on_box flex_ver">
                                <div class="bill_status_on_label">- 년/월</div>
                                <div class="bill_status_on_select flex_ver">
                                    <?php
                                    $year = date('Y');
                                    $yearTo = $year + 3;
                                    $yearTo2 = 2017;
                                    //$yearTo2 = floor($year / 10) * 10;

                                    // echo $yearTo2;
                                    ?>
                                    <select name="bill_year" id="bill_year" class="bansang_sel bill_status_on_sel" disabled>
                                        <option value="">연도 선택</option>
                                        <?php for($i=$yearTo;$i>=$yearTo2;$i--){?>
                                            <option value="<?php echo $i;?>"><?php echo $i.'년';?></option>
                                        <?php }?>
                                    </select>
                                    <select name="bill_month" id="bill_month" class="bansang_sel bill_status_on_sel" disabled>
                                        <option value="">월 선택</option>
                                        <?php for($i=1;$i<=12;$i++){
                                            $months = str_pad($i, 2, "0", STR_PAD_LEFT);
                                            ?>
                                            <option value="<?php echo $i; ?>"><?php echo $months.'월'; ?></option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>
                            <div class="bill_status_on_box_flex_wrap">
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
                    </div>
                    <?php }?>
                    <?php
                    $bill_company_list = "SELECT ccb.*, pt.pt_name, bt.bill_name FROM 
                                        a_contract_company_bill as ccb
                                        LEFT JOIN a_payment_type as pt on ccb.payment_type = pt.pt_idx
                                        LEFT JOIN a_company_bill_type as bt on ccb.bill_type = bt.bt_idx
                                        WHERE ct_idx = '{$ct_idx}' ORDER BY idx asc";
                    $bill_company_res = sql_query($bill_company_list);
                    $bill_company_res_total = sql_num_rows($bill_company_res);
                    ?>
                    <div class="bill_info_wrap mgt15" style="<?php echo $bill_company_res_total > 0 ? 'display:block' : 'display:none;';?>">
                        <?php
                        while($bill_company_row = sql_fetch_array($bill_company_res)){

                            $bill_stop = "";
                            if($bill_company_row['bill_eyear'] != '' && $bill_company_row['bill_emonth'] != ''){
                                $bill_stop = $bill_company_row['bill_eyear'].'년 '.$bill_company_row['bill_emonth'].'월';
                            }
                        ?>
                        <div class="bill_info">
                            <div class="bill_info_box">
                            계산서 발행 : <?php echo $bill_company_row['bill_syear']; ?>년 <?php echo $bill_company_row['bill_smonth']; ?>월 ~ <?php echo $bill_stop; ?>
                            </div>
                            <div class="bill_info_box">
                                계산서 종류 : <?php echo $bill_company_row['bill_name']; ?> / <?php echo $bill_company_row['pt_name']; ?>
                            </div>
                        </div>
                        <?php }?>
                    </div>
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">담당자1</div>
                <div class="contract_box_right">
                    <input type="text" name="mng_name1" id="mng_name1" class="bansang_ipt ver2 full" value="<?php echo $row['mng_name1']; ?>" <?php echo $row['ct_status'] ? 'disabled' : '';?>>
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">담당자1 연락처</div>
                <div class="contract_box_right">
                    <input type="text" name="mng_hp1" id="mng_hp1" class="bansang_ipt ver2 full" value="<?php echo $row['mng_hp1']; ?>" <?php echo $row['ct_status'] ? 'disabled' : '';?>>
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">담당자2</div>
                <div class="contract_box_right">
                    <input type="text" name="mng_name2" id="mng_name2" class="bansang_ipt ver2 full" value="<?php echo $row['mng_name2']; ?>" <?php echo $row['ct_status'] ? 'disabled' : '';?>>
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">담당자2 연락처</div>
                <div class="contract_box_right">
                    <input type="text" name="mng_hp2" id="mng_hp2" class="bansang_ipt ver2 full" value="<?php echo $row['mng_hp2']; ?>" <?php echo $row['ct_status'] ? 'disabled' : '';?>>
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">비고</div>
                <div class="contract_box_right">
                    <textarea name="mng_memo" id="mng_memo" class="bansang_ipt ver2 ta full"><?php echo $row['mng_memo']; ?></textarea>
                </div>
            </div>
        </div>
        <div class="contract_resident_wrap">
            <div class="contract_box contract_label_box">
                선임자 정보
                <?php if(!$row['ct_status']){?>
                <div class="contract_label_chk_box">
                    <input type="checkbox" name="sn_use" id="sn_use" value="1" <?php echo $sn_use == '1' ? 'checked' : ''; ?>>
                    <label for="sn_use">미사용</label>
                </div>
                <?php }?>
               
            </div>
            <div class="contract_box">
                <div class="contract_box_left">선임자명</div>
                <div class="contract_box_right">
                    <input type="text" name="sn_name" id="sn_name" class="bansang_ipt ver2 full sn_ipt" value="<?php echo $row_sn['sn_name']; ?>">
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">선임자 연락처</div>
                <div class="contract_box_right">
                    <input type="text" name="sn_hp" id="sn_hp" class="bansang_ipt ver2 full sn_ipt" value="<?php echo $row_sn['sn_hp']; ?>">
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">선임일</div>
                <div class="contract_box_right">
                    <input type="text" name="sn_date" id="sn_date" class="bansang_ipt ver2 full ipt_date sn_ipt" value="<?php echo $row_sn['sn_date']; ?>">
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">선임기간</div>
                <div class="contract_box_right flex_ver">
                    <input type="text" name="sn_sdate" id="sn_sdate" class="bansang_ipt ver2 full ipt_date sn_ipt" value="<?php echo $row_sn['sn_sdate']; ?>"> ~
                    <input type="text" name="sn_edate" id="sn_edate" class="bansang_ipt ver2 full ipt_date sn_ipt" value="<?php echo $row_sn['sn_edate']; ?>">
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">교육이수일</div>
                <div class="contract_box_right">
                    <input type="text" name="edu_sdate" id="edu_sdate" class="bansang_ipt ver2 full ipt_date sn_ipt" value="<?php echo $row_sn['edu_sdate']; ?>">
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">교육만료일</div>
                <div class="contract_box_right">
                    <input type="text" name="edu_edate" id="edu_edate" class="bansang_ipt ver2 full ipt_date sn_ipt" value="<?php echo $row_sn['edu_edate']; ?>">
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">보험정보</div>
                <div class="contract_box_right">
                    <input type="text" name="insurance_name" id="insurance_name" class="bansang_ipt ver2 full sn_ipt" value="<?php echo $row_sn['insurance_name']; ?>">
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">보험기간</div>
                <div class="contract_box_right">
                    <input type="text" name="insurance_date" id="insurance_date" class="bansang_ipt ver2 full sn_ipt" value="<?php echo $row_sn['insurance_date']; ?>">
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">보험 가입금액</div>
                <div class="contract_box_right">
                    <input type="text" name="insurance_price" id="insurance_price" class="bansang_ipt ver2 full sn_ipt" value="<?php echo $row_sn['insurance_price']; ?>">
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">가입담당연락처</div>
                <div class="contract_box_right">
                    <input type="text" name="insurance_mng" id="insurance_mng" class="bansang_ipt ver2 full sn_ipt" value="<?php echo $row_sn['insurance_mng']; ?>">
                </div>
            </div>
            <div class="contract_box">
                <div class="contract_box_left">비고</div>
                <div class="contract_box_right">
                    <textarea name="sn_memo" id="sn_memo" class="bansang_ipt ver2 ta full"><?php echo $row_sn['sn_memo']; ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <?php if($admin_level < 4){?>
    <div class="contract_bottom_wrap">
        <div class="contract_status_box">
            <?php if(!$row['is_temp'] && $ct_idx != ''){?>
            <div class="ct_status_chk_box">
                <input type="checkbox" name="ct_status" id="ct_status" value="1" <?php echo $row['ct_status'] == '1' ? 'checked' : ''; ?> <?php echo $row['ct_status'] == '1' ? 'disabled' : ''; ?>>
                <label for="ct_status">계약해지</label>
            </div>
            <p>* 계약 해지 후 해지한 계약건은 다시 계약중 상태로 변경 불가합니다.</p>
            <p>* 새롭게 계약 추가 하여야 합니다.</p>
            <div class="ct_status_date_box_wrap" style="<?php echo $row['ct_status'] == '1' ? 'display:block' : ''; ?>">
                <div class="ct_status_date_box">
                    <div class="ct_status_date_label">해지 날짜 설정</div>
                    <?php

                    $yearW = date('Y');
                    $bfYearW = 2017;
                    $afYearW = $yearW + 10;
                    ?>
                    <select name="ct_status_year" id="ct_status_year" class="bansang_sel" <?php echo $row['ct_status'] == '1' ? 'disabled' : ''; ?>>
                        <option value="">년 선택</option>
                        <?php for($i=$bfYearW;$i<=$afYearW;$i++){?>
                            <option value="<?php echo $i;?>" <?php echo $row['ct_status_year'] == $i ? 'selected' : ''; ?>><?php echo $i.'년';?></option>
                        <?php }?>
                    </select>
                    <select name="ct_status_month" id="ct_status_month" class="bansang_sel" <?php echo $row['ct_status'] == '1' ? 'disabled' : ''; ?>>
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
            <?php if($row['is_temp'] || $ct_idx == ''){?>
            <button type="submit" name="save_type" class="ct_btns01" value="temp">임시저장</button>
            <?php }?>
            <button type="submit" name="save_type" class="ct_btns02" value="save"><?php echo !$row['is_temp'] && $ct_idx != '' ? '수정' : '저장';?></button>
            <?php if(!$row['is_temp'] && $ct_idx != '' && !$row['ct_status']){?>
                <button type="button" onclick="extend_pop_handler('<?php echo $row['ct_idx']; ?>');">연장하기</button>
            <?php }?>
            <button type="button" name="save_type" value="temp" onclick="company_form_pop_close();">닫기</button>
        </div>
       
    </div>
    <?php }?>
</form>
<script>
document.getElementById("fcontract").addEventListener("keydown", function(event) {
    // console.log('keydown event', event);
  if (event.key === "Enter" && event.target.tagName !== "TEXTAREA") {
    event.preventDefault(); // 기본 동작(submit) 막기
  }
});


$(function(){
    // maxDate: "+365d" 
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99"});
});


var sn_use = "<?php echo $sn_use == '1' ? '1' : '0'; ?>";

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

    return true;
}
</script>