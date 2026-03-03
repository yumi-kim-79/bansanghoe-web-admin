<?php
require_once "./_common.php";

$mng_building = "SELECT * FROM a_mng_building WHERE is_del = 0 and mb_id = '{$mb_id}'";
//echo $mng_building;
$mng_building_res = sql_query($mng_building);

$mng_building_arr = array();

while($mng_building_row = sql_fetch_array($mng_building_res)){
    array_push($mng_building_arr, $mng_building_row['building_id']);
}

//print_r2($mng_building_arr);
$mng_building_arr_t = "'".implode("','", $mng_building_arr)."'";

//echo $mng_building_arr_t;

$sql_sch = "";

if($sortVal == 'up'){
    $sql_order = " ORDER BY building.building_name asc, building.building_id asc ";
}else{
    $sql_order = " ORDER BY building.building_name desc, building.building_id asc ";
}

if($is_use == 1){
    $sql_sch .= " and building.is_use = 1 ";
}else if($is_use == 0){
    $sql_sch .= " and building.is_use = 0 ";
}

if($post_idx != ""){
    $sql_sch .= " and building.post_id = '{$post_idx}' ";
}

if($schText != ""){
    $sql_sch .= " and building_name like '%{$schText}%' ";
}

if($mng_chk == true){
    $sql_sch .= " and building_id IN ({$mng_building_arr_t}) ";
}

$building_sql = "SELECT building.*, post.post_name, (SELECT COUNT(*) FROM a_building_household as hh WHERE building.building_id = hh.building_id) AS cnt FROM a_building as building
                 LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
                 WHERE building.is_del = 0 {$sql_sch} {$sql_order}";
$building_res = sql_query($building_sql);

//echo $building_sql;
for($i=0;$building_row = sql_fetch_array($building_res);$i++){
?>
<li>
    <a href="/building_mng.php?building_id=<?php echo $building_row['building_id']; ?>">
        <div class="building_tits"><?php echo $building_row['post_name']; ?>-<?php echo $building_row['building_name'];?></div>
        <div class="building_addrs"><?php echo $building_row['building_addr'].' '.$building_row['building_addr2'];?></div>
    </a>
</li>
<?php }?>
<?php if($i==0){?>
<li class="empty_li">등록된 단지가 없습니다.</li>
<?php }?>