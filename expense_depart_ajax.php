<?php
require_once "./_common.php";

//부서변경시
$mng_grade_sql = "SELECT * FROM a_mng_grade WHERE is_del = 0 ORDER BY is_prior asc, mg_idx asc";
$mng_grade_res = sql_query($mng_grade_sql);
?>
<option value="">선택</option>
<?php
while($mng_grade_row = sql_fetch_array($mng_grade_res)){
?>
<option value="<?php echo $mng_grade_row['mg_idx'];?>"><?php echo $mng_grade_row['mg_name'];?></option>
<?php }?>