<?php
require_once "./_common.php";

//echo $select_chk;

//$select_chk_arr = explode(",", $select_chk);

$mng_building = "SELECT * FROM a_building WHERE is_del = 0 and is_use = 1 and building_id IN ({$select_chk}) ORDER BY building_id desc ";
// echo $mng_building;
$mng_res = sql_query($mng_building);

//print_r($building_arr);
for($i=0;$mng_row = sql_fetch_array($mng_res);$i++){
?>
<div class="mng_building_list_box_wrap">
    <div class="mng_building_list_box1">
        <input type="checkbox" name="bf_mng_chk" class="bf_mng_chk" id="bf_mng_chk<?php echo $i + 1;?>" value="<?php echo $mng_row['building_id']; ?>">
    </div>
    <div class="mng_building_list_box2">
        <div class="mng_building_list_box">
            <label for="bf_mng_chk<?php echo $i + 1;?>">
                <?php echo $mng_row['building_name']; ?>
            </label>
        </div>
        <div class="mng_building_list_box"><?php echo $mng_row['building_addr']; ?></div>
    </div>
</div>
<?php }?>