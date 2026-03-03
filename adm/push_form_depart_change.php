<?php
require_once './_common.php';

$sql = "SELECT * FROM a_mng WHERE is_del = 0 and mng_department = '{$mng_department}' and mng_status = 1 ORDER BY mng_grades desc, mng_idx asc";

if($_SERVER["REMOTE_ADDR"] == ADMIN_IP){
    // echo $sql;
    // exit;
}
$res = sql_query($sql);

?>
<option value="">인원 전체</option>
<?php
while($row = sql_fetch_array($res)){
?>
<option value="<?php echo $row['mng_id']; ?>"><?php echo $row['mng_name']; ?></option>
<?php }?>