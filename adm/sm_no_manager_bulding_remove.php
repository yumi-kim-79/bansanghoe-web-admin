<?php
require_once "./_common.php";

//echo $select_chk;

$select_chk_arr = explode(",", $select_chk);

// $mng_building = "SELECT mng_build.*, building.building_name, building.building_addr FROM 
//                     a_mng_building as mng_build
//                     left join a_building as building on mng_build.building_id = building.building_id
//                     WHERE mng_build.is_del = 0 and mng_build.mb_id = '{$mng_id}' and mng_build.building_id NOT IN ({$af_mng_chk})";

$mng_building = "SELECT * FROM a_building WHERE is_del = 0 and is_use = 1 and building_id IN ({$select_af_chk}) ORDER BY building_id desc ";

// echo $mng_building;
$mng_res = sql_query($mng_building);

$building_arr = array();

for($i=0;$no_mng_row = sql_fetch_array($mng_res);$i++){
?>
<div class="mng_building_list_box_wrap">
    <div class="mng_building_list_box1">
        <input type="checkbox" name="af_mng_chk" id="af_mng_chk<?php echo $i + 1; ?>" class="af_mng_chk" value="<?php echo $no_mng_row['building_id']; ?>">
    </div>
    <div class="mng_building_list_box2">
        <div class="mng_building_list_box">
            <label for="af_mng_chk<?php echo $i + 1;?>">
            <?php echo $no_mng_row['building_name']; ?>
            </label>
        </div>
        <div class="mng_building_list_box"><?php echo $no_mng_row['building_addr']; ?></div>
    </div>
</div>
<?php }?>