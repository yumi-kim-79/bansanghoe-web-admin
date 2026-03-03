<?php
require_once "./_common.php";

$industry_sql = "SELECT * FROM a_industry_list WHERE is_use = 1 and is_del = 0 ORDER BY is_fixed asc, industry_idx asc";
$industry_res = sql_query($industry_sql);
?>
<select name="industry_idx" id="industry_idx" class="bansang_sel full" readonly>
    <option value="">업종을 선택하세요.</option>
    <?php while($industry_row = sql_fetch_array($industry_res)){?>
        <option value="<?php echo $industry_row['industry_idx']?>" <?php echo get_selected($industry_idx, $industry_row['industry_idx']); ?>><?php echo $industry_row['industry_name']; ?></option>
    <?php }?>
</select>