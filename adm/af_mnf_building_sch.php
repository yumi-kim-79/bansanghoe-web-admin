<?php
require_once "./_common.php";

//관리중인 단지 검색하기

$building_where = " WHERE mng_b.mb_id = '{$mng_id}' and building.is_use = 1 ";
$building_order = " ORDER BY mng_b.building_id desc ";

//검색어 필터 추가
if($schText != ""){
    $building_where .= " and ( building.building_name like '%{$schText}%' OR building.building_addr like '%{$schText}%' ) ";
}

$mng_building_sql = "SELECT mng_b.*, building.building_name, building.is_use, building.building_addr FROM 
                    a_mng_building as mng_b
                    LEFT JOIN a_building as building on mng_b.building_id = building.building_id
                    {$building_where} {$building_order}";
$mng_building_res = sql_query($mng_building_sql);

for($i=0;$building_row = sql_fetch_array($mng_building_res);$i++){
?>
<div class="mng_building_list_box_wrap">
    <div class="mng_building_list_box1">
        <input type="checkbox" name="af_mng_chk" class="af_mng_chk" id="af_mng_chk<?php echo $i + 1;?>" value="<?php echo $building_row['building_id']; ?>">
    </div>
    <div class="mng_building_list_box2">
        <div class="mng_building_list_box">
            <label for="af_mng_chk<?php echo $i + 1;?>">
                <?php echo $building_row['building_name']; ?>
            </label>
        </div>
        <div class="mng_building_list_box"><?php echo $building_row['building_addr']; ?></div>
    </div>
</div>
<?php }?>