<?php
require_once "./_common.php";

$sql_where = " WHERE mng.mng_department = '{$departmentValue}' and mng_b.is_del = 0 ";
$sql_where2 = "";

$sql_building = "SELECT mng.*, mng_gr.mg_name FROM a_mng as mng
                 LEFT JOIN a_mng_grade as mng_gr on mng_gr.mg_idx = mng.mng_grades
                 WHERE mng.mng_department = '{$departmentValue}' ORDER BY mng.mng_idx desc ";
//echo $sql_building;
// exit;
$res_building = sql_query($sql_building);
?>
<?php if($departmentValue != '-1'){?>
<option value="">담당자를 선택해주세요.</option>
<?php
while($row_building = sql_fetch_array($res_building)){
?>
<option value="<?php echo $row_building['mng_id']?>"><?php echo $row_building['mng_name'].' '.$row_building['mg_name'];?></option>
<?php }?>
<?php }else{ ?>
    <option value="-1">전체</option>
<?php }?>