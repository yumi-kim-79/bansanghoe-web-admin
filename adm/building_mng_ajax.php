<?php
require_once './_common.php';

$sql_where = " WHERE mng_b.building_id = '{$building_id}' and mng.mng_department = '{$departmentValue}' and mng_b.is_del = 0 ";
$sql_where2 = "";

$sql_building = "SELECT mng_b.*, mng.mng_name, mng.mng_department, depart.md_name, mng_grade.mg_name FROM
                 a_mng_building as mng_b
                 LEFT JOIN a_mng as mng on mng_b.mb_id = mng.mng_id
                 LEFT JOIN a_mng_department as depart on mng.mng_department = depart.md_idx
                 LEFT JOIN a_mng_grade as mng_grade on mng.mng_grades = mng_grade.mg_idx
                 {$sql_where} {$sql_where2} ";
// echo $sql_building;
// exit;
$res_building = sql_query($sql_building);
?>
<?php if($departmentValue == '-1'){?>
    <option value="-1">전체</option>
<?php }else{?>
    <option value="">담당자를 선택해주세요.</option>
    <?php
    while($row_building = sql_fetch_array($res_building)){
    ?>
    <option value="<?php echo $row_building['mb_id']?>"><?php echo $row_building['mng_name'].' '.$row_building['mg_name'];?></option>
    <?php }?>
<?php }?>