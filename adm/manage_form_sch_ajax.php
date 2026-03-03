<?php
require_once './_common.php';

$post_sql = "";
if($post_id != ""){
    $post_sql = " and post_id = '{$post_id}' ";
}

$sql_building = "SELECT * FROM a_building WHERE is_use = 1 and building_name like '%{$building_name}%' {$post_sql} ORDER BY building_name asc";

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $sql_building;
$res_building = sql_query($sql_building);

$total_building = sql_num_rows($res_building);

if($total_building > 0){
for($i=0;$row_building = sql_fetch_array($res_building);$i++){
?>
<button type="button" onclick="building_select('<?php echo $row_building['building_id']; ?>', '<?php echo $row_building['building_name']; ?>');"><?php echo $row_building['building_name']; ?></button>
<?php }?>
<?php }?>