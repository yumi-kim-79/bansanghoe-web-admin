<?php
require_once "./_common.php";

$sql_wheres = " and mng_department = '{$departId}' and mng_grades = '{$gradeId}' ";

$mng_sql = "SELECT * FROM a_mng WHERE is_del = 0 {$sql_wheres} ORDER BY mng_idx desc";
echo $sql_wheres;
$mng_res = sql_query($mng_sql);
?>
<option value="">선택</option>
<?php while($mng_row = sql_fetch_array($mng_res)){?>
<option value="<?php echo $mng_row['mng_id'];?>"><?php echo $mng_row['mng_name'];?></option>
<?php }?>