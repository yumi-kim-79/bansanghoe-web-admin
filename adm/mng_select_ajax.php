<?php
require_once "./_common.php";

// $mng_sql = "SELECT mng.*, mng_grade.mg_name FROM
//              a_mng as mng
//              LEFT JOIN a_mng_grade AS mng_grade ON mng.mng_grades = mng_grade.mg_idx
//              WHERE mng.mng_department = '{$departValue}' and mng.is_del = 0 ORDER BY mng.mng_grades desc, mng.mng_idx asc";

$sql_gr = "";
if($group){
    $sql_gr = " GROUP BY mng.mng_id ";
}

// and mng_b.building_id = '{$building_id}'
$mng_sql = "SELECT mng_b.*, mng.mng_status, mng.mng_department, mng.mng_grades, mng.mng_name, mng_gr.mg_name FROM a_mng_building as mng_b
            LEFT JOIN a_mng as mng ON mng_b.mb_id = mng.mng_id
            LEFT JOIN a_mng_grade as mng_gr ON mng_gr.mg_idx = mng.mng_grades
            WHERE mng_b.is_del = 0 and mng.mng_status = 1 and mng.mng_department = '{$departValue}'
            {$sql_gr}
            ORDER BY mng_b.mng_id desc";
// echo $mng_sql;
if($_SERVER["REMOTE_ADDR"] == ADMIN_IP){
    echo $mng_sql;
    // exit;
}

$mng_res = sql_query($mng_sql);
?>
<option value="">선택하세요</option>
<?php while($mng_row = sql_fetch_array($mng_res)){?>
<option value="<?php echo $mng_row['mb_id']; ?>"><?php echo $mng_row['mng_name'].' '.$mng_row['mg_name'];?></option>
<?php }?>