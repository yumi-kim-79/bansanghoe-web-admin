<?php
require_once "./_common.php";

//관리중이지 않은 단지 검색하기

$building_where = " WHERE is_del = 0 and is_use = 1 ";
$building_order = " ORDER BY building_id desc ";

//관리중인 단지 검색하여 해당 내용은 검색되지 않도록 배열에 담음
$af_mng_building_sql = "SELECT * FROM a_mng_building WHERE mb_id = '{$mng_id}' ORDER BY building_id asc";
$af_mng_buliding_res = sql_query($af_mng_building_sql);

$af_mng_building_arr = array();

while($af_mng_buliding_row = sql_fetch_array($af_mng_buliding_res)){
    array_push($af_mng_building_arr, $af_mng_buliding_row['building_id']);
}

$af_mng_building_arr_t = implode(",", $af_mng_building_arr);

if($af_mng_building_arr_t != ""){
    $building_where .= " and building_id NOT IN ({$af_mng_building_arr_t}) ";
}

//검색어 필터 추가
if($schText != ""){
    $building_where .= " and ( building_name like '%{$schText}%' OR building_addr like '%{$schText}%' ) ";
}

$building_sql = "SELECT * FROM a_building {$building_where} {$building_order}";
// echo $building_sql.'<br>';
$building_res = sql_query($building_sql);

for($i=0;$building_row = sql_fetch_array($building_res);$i++){
?>
<div class="mng_building_list_box_wrap">
    <div class="mng_building_list_box1">
        <input type="checkbox" name="bf_mng_chk" id="bf_mng_chk<?php echo $i + 1; ?>" class="bf_mng_chk" value="<?php echo $building_row['building_id']; ?>">
    </div>
    <div class="mng_building_list_box2">
        <div class="mng_building_list_box">
            <label for="bf_mng_chk<?php echo $i + 1;?>">
            <?php echo $building_row['building_name']; ?>
            </label>
        </div>
        <div class="mng_building_list_box"><?php echo $building_row['building_addr']; ?></div>
    </div>
</div>
<?php }?>