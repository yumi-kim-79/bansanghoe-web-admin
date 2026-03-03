<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$ct_info = "SELECT ct.*, mc.company_number, mc.company_mng_name, mc.company_mng_tel, mc.company_bank_name, mc.company_account_number, mc.company_account_name, mc.company_memo, sn.sn_name, sn.sn_date, sn.edu_sdate, sn.edu_edate, sn.insurance_name, sn.insurance_date, sn.sn_memo FROM a_contract as ct 
            LEFT JOIN a_manage_company as mc ON ct.company_idx = mc.company_idx
            LEFT JOIN a_senior as sn ON ct.ct_idx = sn.ct_idx
            WHERE ct.ct_idx = '{$ct_idx}'";

// echo $ct_info;
$ct_info_row = sql_fetch($ct_info);

// print_r2($ct_info_row);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="sm_mng_info_cont">
            <div class="inner">
                <div class="sm_mng_info_cont_wrap">
                    <div class="sm_company_info_box_wrap">
                        <div class="sm_company_label ver2"><?php echo $ct_info_row['company_name']; ?></div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">사업자 번호</div>
                            <div class="sm_company_info_text"><?php echo $ct_info_row['company_number']; ?></div>
                        </div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">업종</div>
                            <div class="sm_company_info_text"><?php echo $ct_info_row['industry_name']; ?></div>
                        </div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">대표번호</div>
                            <div class="sm_company_info_text">
                                <a href="tel:<?php echo $ct_info_row['company_tel']; ?>">
                                    <img src="/images/phone_b.svg" alt="">
                                    <span><?php echo $ct_info_row['company_tel']; ?></span>
                                </a>
                            </div>
                        </div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">담당자</div>
                            <div class="sm_company_info_text"><?php echo $ct_info_row['mng_name1'];?></div>
                        </div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">담당자 연락처</div>
                            <div class="sm_company_info_text">
                                <a href="tel:<?php echo $ct_info_row['mng_hp1']; ?>">
                                    <img src="/images/phone_b.svg" alt="">
                                    <span><?php echo $ct_info_row['mng_hp1']; ?></span>
                                </a>
                            </div>
                        </div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">은행명</div>
                            <div class="sm_company_info_text"><?php echo $ct_info_row['company_bank_name']; ?></div>
                        </div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">예금주</div>
                            <div class="sm_company_info_text"><?php echo $ct_info_row['company_account_name']; ?></div>
                        </div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">계좌번호</div>
                            <div class="sm_company_info_text"><?php echo $ct_info_row['company_account_number']; ?></div>
                        </div>
                        <div class="bigo_btn_wrap">
                            <textarea id="company_memo" style="display:none;"><?php echo $ct_info_row['company_memo']?></textarea>
                            <button type="button" onclick="bigoPopOpen('company_memo', '업체 비고사항')">업체 비고사항</button>
                        </div>
                    </div>
                   
                    
                    <div class="sm_company_info_box_wrap">
                        <div class="sm_company_label">내부 관리 정보</div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">계약 기간</div>
                            <div class="sm_company_info_text"><?php echo date('Y.m.d', strtotime($ct_info_row['ct_sdate']));?> - <?php echo date('Y.m.d', strtotime($ct_info_row['ct_edate']));?></div>
                        </div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">비용</div>
                            <div class="sm_company_info_text"><?php echo number_format($ct_info_row['ct_price']); ?></div>
                        </div>
                        <?php if($ct_info_row['bill_status']){?>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">계산서 발행</div>
                            <div class="sm_company_info_text">
                            <?php 
                            $bill_list = "SELECT cb.*, bt.bill_name FROM a_company_bill_list as cb
                                        LEFT JOIN a_company_bill_type as bt on cb.bill_type = bt.bt_idx
                                        WHERE cb.ct_idx = '{$ct_idx}' ORDER BY cidx desc limit 0, 1";
                            $bill_row = sql_fetch($bill_list);

                            echo $bill_row['bill_name'];
                            ?>
                            </div>
                        </div>
                        <?php }?>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">담당자1</div>
                            <div class="sm_company_info_text"><?php echo $ct_info_row['mng_name1'];?></div>
                        </div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">담당자1 연락처</div>
                            <div class="sm_company_info_text">
                                <a href="tel:<?php echo $ct_info_row['mng_hp1']; ?>">
                                    <img src="/images/phone_b.svg" alt="">
                                    <span><?php echo $ct_info_row['mng_hp1']; ?></span>
                                </a>
                            </div>
                        </div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">담당자2</div>
                            <div class="sm_company_info_text"><?php echo $ct_info_row['mng_name2'] != '' ? $ct_info_row['mng_name2'] : '-'; ?></div>
                        </div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">담당자2 연락처</div>
                            <div class="sm_company_info_text">
                                <?php if($ct_info_row['mng_hp2'] != ''){?>
                                <a href="tel:<?php echo $ct_info_row['mng_hp2']; ?>">
                                    <img src="/images/phone_b.svg" alt="">
                                    <span><?php echo $ct_info_row['mng_hp2']; ?></span>
                                </a>
                                <?php }else{ ?>
                                    -
                                <?php }?>
                            </div>
                        </div>
                        <div class="bigo_btn_wrap">
                            <textarea id="mng_memo" style="display:none;"><?php echo $ct_info_row['mng_memo']?></textarea>
                            <button type="button" onclick="bigoPopOpen('mng_memo', '내부관리 비고사항')">비고사항</button>
                        </div>
                    </div>
                    <?php if($ct_info_row['sn_name'] != ''){?>
                    <div class="sm_company_info_box_wrap">
                        <div class="sm_company_label">선임자 정보</div>
                    
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">선임자명</div>
                            <div class="sm_company_info_text"><?php echo $ct_info_row['sn_name'];?></div>
                        </div>
                     
                        <!-- <div class="sm_company_info_box">
                            <div class="sm_company_info_label">동/호수</div>
                            <div class="sm_company_info_text">1001동 101호</div>
                        </div> -->
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">선임일</div>
                            <div class="sm_company_info_text">
                                <?php echo $ct_info_row['sn_date'] != "" ? date('Y.m.d', strtotime($ct_info_row['sn_date'])) : "-";?>
                            </div>
                        </div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">교육이수일</div>
                            <div class="sm_company_info_text"><?php echo $ct_info_row['edu_sdate'] != "" ? date('Y.m.d', strtotime($ct_info_row['edu_sdate'])) : "-"; ?></div>
                        </div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">교육만료일</div>
                            <div class="sm_company_info_text">
                                <?php echo $ct_info_row['edu_edate'] != "" ? date('Y.m.d', strtotime($ct_info_row['edu_edate'])) : "-"; ?>
                            </div>
                        </div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">보험사</div>
                            <div class="sm_company_info_text">
                                <?php echo $ct_info_row['insurance_name'] != "" ? $ct_info_row['insurance_name'] : "-"; ?>
                            </div>
                        </div>
                        <div class="sm_company_info_box">
                            <div class="sm_company_info_label">보험만료일</div>
                            <div class="sm_company_info_text">
                                <?php echo $ct_info_row['insurance_date'] != "" ? date('Y.m.d', strtotime($ct_info_row['insurance_date'])) : "-"; ?>
                            </div>
                        </div>
                        <div class="bigo_btn_wrap">
                            <textarea id="sn_memo" style="display:none;"><?php echo htmlspecialchars($ct_info_row['sn_memo']);?></textarea>
                            <button type="button" onclick="bigoPopOpen('sn_memo', '선임 비고사항')">비고사항</button>
                        </div>
                    </div>
                    <?php }?>
                    <div class="sm_company_info_box_wrap">
                        <div class="sm_company_label">계약 이력</div>
                        <?php
                        $history_sql = "SELECT * FROM a_contract_history WHERE ct_idx = '{$ct_idx}' ORDER BY cth_idx asc";
                        $history_res = sql_query($history_sql);
                        ?>
                        <div class="sm_company_history">
                            <table>
                                <tr>
                                    <th>계약일</th>
                                    <th>계약 시작일</th>
                                    <th>계약 종료일</th>
                                    <th>계약 금액</th>
                                </tr>
                                <?php while($history_row = sql_fetch_array($history_res)){?>
                                    <tr>
                                        <td><?php echo date("Y-m-d", strtotime($history_row['created_at'])); ?></td>
                                        <td><?php echo $history_row['ct_sdate']; ?></td>
                                        <td><?php echo $history_row['ct_edate']; ?></td>
                                        <td><?php echo number_format($history_row['ct_price']); ?></td>
                                    </tr>
                                <?php }?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="cm_pop" id="bigo_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onclick="popClose('bigo_pop')">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">비고사항</div>
        <div class="cm_pop_bigo_cont"></div>
    </div>
</div>
<script>
function bigoPopOpen(bigoCont, bigoTitle){

    console.log(`${bigoCont}`, $("#" + bigoCont).val());

    let bigoContent = $("#" + bigoCont).val();
    bigoContent = bigoContent.replace(/\n/g, '<br>');
    // console.log(bigoCont);

    $(".cm_pop_title").text(bigoTitle);
    $(".cm_pop_bigo_cont").html(bigoContent);

    popOpen('bigo_pop');
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>