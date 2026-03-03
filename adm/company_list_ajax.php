<?php
require_once "./_common.php";

$sql = "SELECT * FROM a_manage_company WHERE company_idx = '{$company_idx}'";
$rows = sql_fetch($sql);
?>
<?php if($company_idx != ''){?>
<input type="hidden" name="w" value="u">
<input type="hidden" name="company_idx" value="<?php echo $company_idx; ?>">
<?php }?>
<ul>
    <li>
        <p>사업자번호</p>
        <div class="ipt_box">
            <input type="text" name="company_number" class="bansang_ipt ver2 full" value="<?php echo $rows['company_number']; ?>" placeholder="사업자 번호를 입력해주세요.">
        </div>
    </li>
    <li>
        <p>업체명 <span>*</span></p>
        <div class="ipt_box ipt_flex">
            <input type="hidden" name="company_name_chk" id="company_name_chk" value="<?php echo $company_idx != '' ? 'Y' : 'N'; ?>">
            <input type="text" name="company_name" id="company_name" class="bansang_ipt <?php echo $company_idx == '' ? 'ver2 ver3' : ''; ?> full" placeholder="업체명을 입력해주세요." required <?php echo $company_idx != '' ? 'readonly' : ''; ?> value="<?php echo $rows['company_name']; ?>">
            <?php if($company_idx == ''){?>
            <button type="button" onclick="companyNameCheckHandler();" class="certify_btn">중복확인</button>
            <?php }?>
            <script> 
                function companyNameCheckHandler(){
                    let company_name = $("#company_name").val();

                    let sendData = {'company_name': company_name};

                    $.ajax({
                        type: "POST",
                        url: "./company_list_name_check.php",
                        data: sendData,
                        cache: false,
                        async: false,
                        dataType: "json",
                        success: function(data) {
                            console.log('data:::', data);

                            if(data.result == false) { 
                                alert(data.msg);
                                //$(".btn_submit").attr('disabled', false);
                                if(data.data != ""){
                                    $("#" + data.data).focus();
                                }
                                return false;
                            }else{
                                alert(data.msg);
                                
                                $(".certify_btn").text("확인완료");
                                $(".certify_btn").attr({"disabled": true});
                                $(".certify_btn").addClass('ver2');
                                $("#company_name_chk").val("Y");
                                $("#company_name").attr({"readonly": true});
                            }
                        },
                    });
                }
                
            </script>
        </div>
    </li>
    <li>
        <p>업종 <span>*</span></p>
        <div class="ipt_box">
        <?php 
        $industry_sql = "SELECT * FROM a_industry_list WHERE is_use = 1 ORDER BY industry_idx asc";
        //echo $industry_sql;
        $industry_res2 = sql_query($industry_sql);
        ?>
            <select name="company_industry" id="company_industry" class="bansang_sel full" required>
                <option value="">선택하세요.</option>
                <?php while($industry_row2 = sql_fetch_array($industry_res2)){?>
                    <option value="<?php echo $industry_row2['industry_idx']; ?>" <?php echo get_selected($rows['company_industry'], $industry_row2['industry_idx']); ?>><?php echo $industry_row2['industry_name']; ?></option>
                <?php }?>
            </select>
        </div>
    </li>
    <li>
        <p>대표번호</p>
        <div class="ipt_box">
            <input type="text" name="company_tel" class="bansang_ipt ver2 full" value="<?php echo $rows['company_tel']; ?>" placeholder="대표번호를 입력해주세요.">
        </div>
    </li>
    <li>
        <p>담당자</p>
        <div class="ipt_box">
            <input type="text" name="company_mng_name" class="bansang_ipt ver2 full" value="<?php echo $rows['company_mng_name']; ?>" placeholder="담당자를 입력해주세요.">
        </div>
    </li>
    <li>
        <p>담당자 연락처</p>
        <div class="ipt_box">
            <input type="text" name="company_mng_tel" class="bansang_ipt ver2 full" value="<?php echo $rows['company_mng_tel']; ?>" placeholder="담당자 연락처를 입력해주세요.">
        </div>
    </li>
    <li>
        <p>은행명</p>
        <div class="ipt_box">
            <input type="text" name="company_bank_name" class="bansang_ipt ver2 full" value="<?php echo $rows['company_bank_name']; ?>" placeholder="은행명을 입력해주세요.">
        </div>
    </li>
    <li>
        <p>계좌번호</p>
        <div class="ipt_box">
            <input type="text" name="company_account_number" class="bansang_ipt ver2 full" value="<?php echo $rows['company_account_number']; ?>" placeholder="계좌번호를 입력해주세요.">
        </div>
    </li>
    <li>
        <p>예금주</p>
        <div class="ipt_box">
            <input type="text" name="company_account_name" class="bansang_ipt ver2 full" value="<?php echo $rows['company_account_name']; ?>" placeholder="예금주를 입력해주세요.">
        </div>
    </li>
    <li>
        <p>비고</p>
        <div class="ipt_box">
            <textarea name="company_memo" id="company_memo" class="bansang_ipt ta ver2 full"><?php echo $rows['company_memo']; ?></textarea>
        </div>
    </li>
</ul>
<?php if($admin_level < 4){?>
<div class="company_add_btn_wrap mgt20">
    <button type="button" class="btn_cancel" onClick="popClose('company_add_pop');">취소</button>
    <?php if($rows['transaction_status'] == 'N'){?>
    <button type="button" onclick="transaction_change('<?php echo $rows['company_name']; ?>','<?php echo $company_idx; ?>');" class="btn btn04">거래 활성화</button>
    <?php }?>
    <button type="submit" class="btn btn_03 btn_submit"><?php echo $company_idx != "" ? "수정" : "저장";?></button>
</div>
<?php }?>