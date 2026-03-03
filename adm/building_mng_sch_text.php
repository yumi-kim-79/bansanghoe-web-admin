<?php
require_once './_common.php';

//검색 필터 추가
$sql_where = "";
if($type == "Y"){
    $sql_where = " and building.is_use = 1 ";
}else{
    $sql_where = " and building.is_use = 0 ";
}

if($sch_category == "building_name"){
    $sql_where .= " and building.building_name like '%{$sch_text}%' ";
}else{
    $sql_where .= " and building.building_addr like '%{$sch_text}%' ";
}

if($post_id){
    $sql_where .= " and building.post_id = '{$post_id}' ";
}

$sql = "SELECT building.*, post.post_name FROM a_building as building
        LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
        WHERE building.is_del = 0  {$sql_where} ORDER BY building.building_name asc, building.building_id desc";
//echo $sql;
$res = sql_query($sql);

while($row = sql_fetch_array($res)){

    $sch_names = $sch_category == "building_name" ? $row['building_name'] : $row['building_addr'];
?>
<button type="button" onclick="sch_handler<?php echo $numbers; ?>('<?php echo $sch_names; ?>', '<?php echo $row['building_id']?>')"><?php echo $sch_names; ?></button>
<?php }?>
