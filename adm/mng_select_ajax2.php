<?php
require_once "./_common.php";

$mng_sql = "SELECT mng.*, mng_grade.mg_name FROM
            a_mng as mng
            LEFT JOIN a_mng_grade AS mng_grade ON mng.mng_grades = mng_grade.mg_idx
            WHERE mng.mng_department = '{$departValue}' and mng.is_del = 0 and mng.mng_status = 1 ORDER BY mng.mng_certi desc, mng.mng_grades desc, mng.mng_idx asc";
if($_SERVER["REMOTE_ADDR"] == ADMIN_IP){
    echo $mng_sql;
    // exit;
}
$mng_res = sql_query($mng_sql);
?>
<option value="">선택하세요</option>
<?php while($mng_row = sql_fetch_array($mng_res)){?>
<option value="<?php echo $mng_row['mng_id']; ?>"><?php echo $mng_row['mng_name'].' '.$mng_row['mg_name'];?></option>
<?php }?>