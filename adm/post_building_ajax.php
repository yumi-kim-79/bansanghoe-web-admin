<?php
require_once './_common.php';

$sql_building = "SELECT * FROM a_building WHERE post_id = '{$post_id}' and is_del = 0 and is_use = 1 ORDER BY building_name asc, building_id desc";
$res_building = sql_query($sql_building);

?>
<option value="">단지를 선택해주세요.</option>
<?php if($all == "Y"){?>
<option value="-1">전체</option>
<?php }?>
<?php
while($row_building = sql_fetch_array($res_building)){
?>
<option value="<?php echo $row_building['building_id']?>"><?php echo $row_building['building_name'];?></option>
<?php }?>