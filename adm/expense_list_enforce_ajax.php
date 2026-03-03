<?php
require_once './_common.php';

//덩
$dong_sql = "SELECT dong.*, building.building_name FROM a_building_dong as dong
             LEFT JOIN a_building as building on dong.building_id = building.building_id
             WHERE dong_id = '{$dong_id}'";
$dong_row = sql_fetch($dong_sql);

$ex_report_sql = "SELECT ex.*, depart.md_name, grade.mg_name, mng.mng_name FROM a_expense_report as ex
                    LEFT JOIN a_mng_department as depart on depart.md_idx = ex.enforce_deaprt
                    LEFT JOIN a_mng_grade as grade on grade.mg_idx = ex.enforce_grade
                    LEFT JOIN a_mng as mng on mng.mng_id = ex.enforce_id
                    WHERE ex.is_del = 0 and ex.ex_id = '{$ex_id}'";
$ex_report_row = sql_fetch($ex_report_sql);
?>
<div class="cm_pop_desc4 ver2 mgt10"><?php echo $dong_row['building_name']; ?> - <?php echo $dong_row['dong_name']; ?></div>
<div class="expense_mng_wrap mgt10">
    <div class="expense_mng_box_wrap">
        <div class="expense_mng_box">
            <div class="expense_mng_box_left">부서</div>
            <div class="expense_mng_box_right"><?php echo $ex_report_row['md_name']; ?></div>
        </div>
        <div class="expense_mng_box">
            <div class="expense_mng_box_left">직급</div>
            <div class="expense_mng_box_right"><?php echo $ex_report_row['mg_name']; ?></div>
        </div>
        <div class="expense_mng_box">
            <div class="expense_mng_box_left">시행자</div>
            <div class="expense_mng_box_right"><?php echo $ex_report_row['mng_name']; ?></div>
        </div>
    </div>
</div>