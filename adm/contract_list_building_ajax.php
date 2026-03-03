<?php
require_once './_common.php';

$building_sql = "SELECT * FROM a_building WHERE is_del = 0 and is_use = 1 and building_name like '%{$building_name}%' ORDER BY building_id desc";
$building_res = sql_query($building_sql);
$total_building = sql_num_rows($building_res);

if($total_building > 0){
    for($i=0;$row_building = sql_fetch_array($building_res);$i++){
?>
<button type="button" onclick="building_select('<?php echo $row_building['building_id']; ?>', '<?php echo $row_building['building_name']; ?>');"><?php echo $row_building['building_name']; ?></button>
<?php }?>
<?php }?>