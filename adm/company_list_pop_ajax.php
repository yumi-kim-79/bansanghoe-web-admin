<?php
require_once "./_common.php";

$industry_nfix = "SELECT * FROM a_industry_list WHERE is_fixed = 0 and is_del = 0 ORDER BY industry_idx asc";
//echo $industry_nfix;
$industry_nres = sql_query($industry_nfix);
?>
<?php for($i=0;$industry_nrow = sql_fetch_array($industry_nres);$i++){?>
    <input type="hidden" name="industry_idx[]" value="<?php echo $industry_nrow['industry_idx']; ?>">
    <div class="indutry_name_box ver2">
        <input type="text" name="industry_name[]" class="bansang_ipt ver2" value="<?php echo $industry_nrow['industry_name']; ?>">
        <div class="industry_use_status_box ver2 industry_use_status_box<?php echo $i + 1;?>">
            <div class="indus_use_radio_box">
                <input type="radio" name="is_use<?php echo $i + 1;?>" id="is_use<?php echo $i + 1;?>1" class="is_use1" value="1" <?php echo $industry_nrow['is_use'] == '1' ? 'checked' : ''; ?>>
                <label class="is_use_label1" for="is_use<?php echo $i + 1;?>1">사용</label>
            </div>
            <div class="indus_use_radio_box">
                <input type="radio" name="is_use<?php echo $i + 1;?>" id="is_use<?php echo $i + 1;?>2" class="is_use2" value="0" <?php echo $industry_nrow['is_use'] == '0' ? 'checked' : ''; ?>>
                <label class="is_use_label2" for="is_use<?php echo $i + 1;?>2">미사용</label>
            </div>
            <!-- <div class="indus_del_box">
                <button type="button" onclick="industry_cancel(this, ${indutry_box_length});" class="btn btn_03">취소</button>
            </div> -->
        </div>
    </div>
<?php }?>