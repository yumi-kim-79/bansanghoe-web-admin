<?php
require_once './_common.php';

$select_ho = "SELECT ho.*, b.building_name, d.dong_name FROM a_building_ho as ho
                LEFT JOIN a_building as b ON ho.building_id = b.building_id   
                LEFT JOIN a_building_dong as d ON ho.dong_id = d.dong_id
                WHERE ho.ho_id IN ({$select_push_ho})
                ORDER BY b.building_name asc, ho.ho_id asc";
$select_res = sql_query($select_ho);

for($i=0;$select_row = sql_fetch_array($select_res);$i++){
?>
<div class="push_send_list_tr push_send_list_tbody ">
    <div class="push_send_list_lefts">
        <div class="push_send_td">
            <input type="checkbox" name="ho_checked_rm" class="ho_checked_rm" value="<?php echo $select_row['ho_id']; ?>">
        </div>
    </div>
    <div class="push_send_list_rights">
        <div class="push_send_td"><?php echo $select_row['building_name']; ?></div>
        <div class="push_send_td"><?php echo $select_row['dong_name']; ?>동</div>
        <div class="push_send_td"><?php echo $select_row['ho_name']; ?>호</div>
        <div class="push_send_td"><?php echo $select_row['ho_tenant']; ?></div>
    </div>
</div>
<?php }?>