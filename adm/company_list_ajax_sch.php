<?php
require_once "./_common.php";

$sql_where = '';
if($industry_idx != ''){
    $sql_where = "and company_industry = '{$industry_idx}'";
}

$sql = "SELECT * FROM a_manage_company WHERE is_del = 0 {$sql_where} ORDER BY company_name asc, company_idx asc";
$res = sql_query($sql);
?>
<option value="">업체 선택</option>
<?php while($company_row = sql_fetch_array($res)){?>
<option value="<?php echo $company_row['company_idx']; ?>"><?php echo $company_row['company_name']; ?></option>
<?php }?>