<?php
require_once './_common.php';

$company_sql = "SELECT mng_com.*, indus.industry_idx, indus.industry_name FROM a_manage_company as mng_com
                LEFT JOIN a_industry_list as indus ON mng_com.company_industry = indus.industry_idx
                WHERE mng_com.is_del = 0 and mng_com.transaction_status = 'Y' and mng_com.company_name like '%{$company_name}%' ORDER BY company_idx desc";
$company_res = sql_query($company_sql);
$company_total = sql_num_rows($company_res);
//echo $company_sql;

if($company_total > 0){
for($i=0;$company_row = sql_fetch_array($company_res);$i++){
?>
<button type="button" onclick="company_select('<?php echo $company_row['company_idx']; ?>', '<?php echo $company_row['company_name']; ?>', '<?php echo $company_row['industry_idx']?>', '<?php echo $company_row['industry_name']; ?>', '<?php echo $company_row['company_tel']; ?>', '<?php echo $company_row['company_mng_name']; ?>', '<?php echo $company_row['company_mng_tel']; ?>');"><?php echo $company_row['company_name']; ?></button>
<?php }?>
<?php }?>