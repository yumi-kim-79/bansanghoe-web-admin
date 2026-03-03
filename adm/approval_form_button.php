<?php
require_once './_common.php';
//echo $selectValue;

$sign_row = sql_fetch("SELECT * FROM a_sign_off WHERE sign_id = '{$sign_id}'");

// echo $mng_certi;

$page_arr = ["daily_paid", "onsite_expenses", "personal_signoff", "expenditure_plan", "builder_statement", "building_account", "mng_adjustment", "bill_payment", "household_refund"];
?>
<a href="./approval_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
<?php if($sign_status == "N" || $w == ''){?>
    <?php if(in_array($selectValue, $page_arr)){?>
        <?php if($sign_row['mng_id'] == $mb_id){?>
        <button type="button" onclick="signOffDel();" class="btn btn_02">삭제</button>
        <?php }?>
        <?php if($w == 'u'){?>
            <?php if($sign_row['mng_id'] == $mb_id){?>
                <button type="button" onclick="approval_submit();" class="btn btn_01">저장</button>
            <?php }?>
        <?php }else{?>
        <button type="button" onclick="approval_submit();" class="btn btn_01">저장</button>
        <?php }?>
    <?php }else{ ?>
        <?php if($w == 'u'){
            
          ?>
            <?php if($sign_row['mng_id'] == $mb_id){?>
            <button type="button" onclick="signOffDel();" class="btn btn_02">삭제</button>
            <?php }?>
            <?php if($sign_row['mng_id'] == $mb_id){?>
                <input type="submit" value="저장" class="btn_submit btn" accesskey='s'>
            <?php }?>
        <?php }else{ ?>
            <input type="submit" value="저장" class="btn_submit btn" accesskey='s'>
        <?php }?>
    <?php }?>
<?php }?>
<?php if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){?>
    <!-- <button type="button" onclick="approval_submit();" class="btn btn_01">저장</button> -->
    <!-- <input type="submit" value="저장" class="btn_submit btn" accesskey='s'> -->
<?php }?>