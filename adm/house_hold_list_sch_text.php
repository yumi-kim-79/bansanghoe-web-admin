<?php
require_once './_common.php';

//검색 필터 추가
$sql_where = "";
$group_sql = "";

if($sch_category == "building_name"){
    $sql_where .= " and building.building_name like '%{$sch_text}%' ";
    $group_sql .= " group by building.building_name "; 
}


$sql = "select ho.*, building.building_name, dong.dong_name, post.post_name from a_building_ho as ho 
        left join a_building_dong as dong on ho.dong_id = dong.dong_id left join a_building as building on ho.building_id = building.building_id left join a_post_addr as post on ho.post_id = post.post_idx where (1) and ho.is_del = '0' {$sql_where} {$group_sql} order by ho.ho_id desc";
//echo $sql;
$res = sql_query($sql);
?>
<?php while($row = sql_fetch_array($res)){?>
<button type="button" onclick="sch_handler('<?php echo $row['building_name']; ?>')"><?php echo $row['building_name']; ?></button>
<?php }?>