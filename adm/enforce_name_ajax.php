<?php
require_once './_common.php';

$sql = "SELECT mng.*, grade.mg_name FROM a_mng as mng
        LEFT JOIN a_mng_grade as grade on mng.mng_grades = grade.mg_idx
        WHERE mng.mng_department = '{$departValue}' and mng.mng_grades = '{$gradeValue}'  ORDER BY mng.mng_grades desc, mng.mng_idx desc";
//echo $sql;
$res = sql_query($sql);
?>
<option value="">시행자를 선택하세요.</option>
<?php for($i=0;$row = sql_fetch_array($res);$i++){?>
<option value="<?php echo $row['mng_id']; ?>"><?php echo $row['mng_name'].' '.$row['mg_name']; ?></option>
<?php }?>