<?php
require_once './_common.php';

$sql_where = " WHERE mng_b.building_id = '{$building_id}' and mng_b.is_del = 0 ";
$sql_where2 = "";

switch($calcode){
    case "one_site":
    case "meter_reading":
        $sql_where2 = " and mng.mng_department = 2 or mng.mng_department = 1 ";
    break;
    case "secretary":
        $sql_where2 = " and mng.mng_department = 3 ";
    break;
    case "computation":
    case "move_out_settlement":
        $sql_where2 = " and mng.mng_department = 1 ";
    break;
    default:
        $sql_where2 = "";
    break;
}

$sql_building = "SELECT mng_b.*, mng.mng_name, mng.mng_department, depart.md_name, mng_grade.mg_name FROM
                 a_mng_building as mng_b
                 LEFT JOIN a_mng as mng on mng_b.mb_id = mng.mng_id
                 LEFT JOIN a_mng_department as depart on mng.mng_department = depart.md_idx
                 LEFT JOIN a_mng_grade as mng_grade on mng.mng_grades = mng_grade.mg_idx
                 {$sql_where} {$sql_where2} GROUP BY mng.mng_department ";
//echo $sql_building;
//exit;
$res_building = sql_query($sql_building);
?>
<option value="">부서를 선택해주세요.</option>
<option value="-1">전체</option>
<?php
while($row_building = sql_fetch_array($res_building)){
?>
<option value="<?php echo $row_building['mng_department']?>"><?php echo $row_building['md_name'];?></option>
<?php }?>