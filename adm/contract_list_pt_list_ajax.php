<?php
require_once './_common.php';

$payment_nfix = "SELECT * FROM a_payment_type WHERE is_fixed = 0 and is_del = 0 ORDER BY pt_idx asc";
//echo $industry_nfix;
$payment_nres = sql_query($payment_nfix);
?>
<?php for($i=0;$payment_nrow = sql_fetch_array($payment_nres);$i++){?>
    <input type="hidden" name="pt_idx[]" value="<?php echo $payment_nrow['pt_idx']; ?>">
    <div class="indutry_name_box payment_type_name_box ver2">
        <input type="text" name="pt_name[]" class="bansang_ipt ver2" value="<?php echo $payment_nrow['pt_name']; ?>">
        <div class="industry_use_status_box payment_type_use_status_box ver2 industry_use_status_box<?php echo $i + 1;?>">
            <div class="indus_use_radio_box">
                <input type="radio" name="is_use<?php echo $i + 1;?>" id="is_use<?php echo $i + 1;?>1" class="is_use1" value="1" <?php echo $payment_nrow['is_use'] == '1' ? 'checked' : ''; ?>>
                <label class="is_use_label1" for="is_use<?php echo $i + 1;?>1">사용</label>
            </div>
            <div class="indus_use_radio_box">
                <input type="radio" name="is_use<?php echo $i + 1;?>" id="is_use<?php echo $i + 1;?>2" class="is_use2" value="0" <?php echo $payment_nrow['is_use'] == '0' ? 'checked' : ''; ?>>
                <label class="is_use_label2" for="is_use<?php echo $i + 1;?>2">미사용</label>
            </div>
            <!-- <div class="indus_del_box">
                <button type="button" onclick="industry_cancel(this, ${indutry_box_length});" class="btn btn_03">취소</button>
            </div> -->
        </div>
    </div>
<?php }?>