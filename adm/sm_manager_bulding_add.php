<?php
require_once "./_common.php";

//관리중인 단지로 추가

//echo $select_chk.'<br>';

//이미 관리중인 단지정보 가져오기
$mng_building_sql = "SELECT * FROM a_mng_building WHERE mb_id = '{$mb_id}'";
$mng_building_res = sql_query($mng_building_sql);

$mng_building_idx_arr = array(); //관리중인 단지 idx 값 저장

while($mng_building_row = sql_fetch_array($mng_building_res)){
    array_push($mng_building_idx_arr, $mng_building_row['building_id']);
}

$mng_building_idx_arr_t = implode(",", $mng_building_idx_arr);

if($mng_building_idx_arr_t != ""){
    
    $select_chk = $mng_building_idx_arr_t.",".$select_chk;
}

//체크된 값에 해당하는 단지리스트
$building_sql = "SELECT * FROM a_building WHERE is_del = 0 and is_use = 1 and building_id IN ($select_chk) ORDER BY building_id desc";
// echo $building_sql;
$building_res = sql_query($building_sql);

//print_r($building_arr);
for($i=0;$building_row = sql_fetch_array($building_res);$i++){
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