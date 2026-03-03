<?php
require_once './_common.php';

$bill_type_nfix = "SELECT * FROM a_company_bill_type WHERE is_fixed = 0 and is_del = 0 ORDER BY bt_idx asc";
//echo $industry_nfix;
$bill_type_nfres = sql_query($bill_type_nfix);
?>
<?php for($i=0;$bill_type_nfrow = sql_fetch_array($bill_type_nfres);$i++){?>
    <input type="hidden" name="bt_idx[]" value="<?php echo $bill_type_nfrow['bt_idx']; ?>">
    <div class="indutry_name_box bill_type_name_box ver2">
        <input type="text" name="bill_name[]" class="bansang_ipt ver2" value="<?php echo $bill_type_nfrow['bill_name']; ?>">
        <div class="industry_use_status_box bill_type_use_status_box ver2 industry_use_status_box<?php echo $i + 1;?>">
            <div class="indus_use_radio_box">
                <input type="radio" name="b_is_use<?php echo $i + 1;?>" id="b_is_use<?php echo $i + 1;?>1" class="b_is_use1" value="1" <?php echo $bill_type_nfrow['is_use'] == '1' ? 'checked' : ''; ?>>
                <label class="b_is_use_label1" for="b_is_use<?php echo $i + 1;?>1">사용</label>
            </div>
            <div class="indus_use_radio_box">
                <input type="radio" name="b_is_use<?php echo $i + 1;?>" id="b_is_use<?php echo $i + 1;?>2" class="b_is_use2" value="0" <?php echo $bill_type_nfrow['is_use'] == '0' ? 'checked' : ''; ?>>
                <label class="b_is_use_label2" for="b_is_use<?php echo $i + 1;?>2">미사용</label>
            </div>
            <!-- <div class="indus_del_box">
                <button type="button" onclick="industry_cancel(this, ${indutry_box_length});" class="btn btn_03">취소</button>
            </div> -->
        </div>
    </div>
<?php }?>