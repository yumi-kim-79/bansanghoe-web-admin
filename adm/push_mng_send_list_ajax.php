<?php
require_once './_common.php';

$select_push_mng = explode(",", $_POST['select_push_mng']);

$select_push_mng = "'".implode("','", $select_push_mng)."'";

$mng_sql = "SELECT mng.*, md.md_name, mg.mg_name FROM a_mng as mng
            LEFT JOIN a_mng_department as md ON mng.mng_department = md.md_idx
            LEFT JOIN a_mng_grade as mg ON mng.mng_grades = mg.mg_idx
            WHERE mng.is_del = 0 and mng.mng_id IN ({$select_push_mng}) ORDER BY mng.mng_idx desc";
//echo $mng_sql;
$mng_res = sql_query($mng_sql);

?>
<?php for($i=0;$mng_row = sql_fetch_array($mng_res);$i++){?>
<div class="push_send_list_tr push_send_list_tbody">
    <div class="push_send_list_lefts">
        <div class="push_send_td">
            <input type="checkbox" name="mng_rm_checked" class="mng_rm_checked" value="<?php echo $mng_row['mng_id']; ?>">
        </div>
    </div>
    <div class="push_send_list_rights ver2">
        <div class="push_send_td"><?php echo $mng_row['md_name']; ?></div>
        <div class="push_send_td"><?php echo $mng_row['mg_name']; ?></div>
        <div class="push_send_td"><?php echo $mng_row['mng_name']; ?></div>
    </div>
</div>
<?php }?>