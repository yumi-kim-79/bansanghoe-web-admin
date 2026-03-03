<?php
require_once './_common.php';


$sql_where = '';
if($building_id != ''){
    $sql_where .= " and ho.building_id = '{$building_id}' ";
}

if($dong_id != ''){
    $sql_where .= " and ho.dong_id = '{$dong_id}' ";
}

if($ho_id != ''){
    $sql_where .= " and ho.ho_id = '{$ho_id}' ";
}

if($select_push_ho != ''){
    $sql_where .= " and ho.ho_id NOT IN ({$select_push_ho}) ";
}

$ho_sql = "SELECT ho.*, bu.building_name, do.dong_name FROM a_building_ho as ho
            LEFT JOIN a_building as bu ON ho.building_id = bu.building_id
            LEFT JOIN a_building_dong as do ON ho.dong_id = do.dong_id
            WHERE ho.is_del = 0 and ho.ho_status = 'Y' {$sql_where} ORDER BY bu.building_name asc, ho.ho_id asc limit 0, 10";

$ho_res = sql_query($ho_sql);
?>
<?php for($i=0;$ho_row = sql_fetch_array($ho_res);$i++){?>
<div class="push_send_list_tr push_send_list_tbody">
    <div class="push_send_list_lefts">
        <div class="push_send_td">
            <input type="checkbox" name="ho_checked" class="ho_checked" value="<?php echo $ho_row['ho_id']; ?>">
        </div>
    </div>
    <div class="push_send_list_rights">
        <div class="push_send_td"><?php echo $ho_row['building_name'];?></div>
        <div class="push_send_td"><?php echo $ho_row['dong_name'];?>동</div>
        <div class="push_send_td"><?php echo $ho_row['ho_name'];?>호</div>
        <div class="push_send_td"><?php echo $ho_row['ho_tenant'];?></div>
    </div>
</div>
<?php }?>