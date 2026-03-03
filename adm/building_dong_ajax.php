<?php
require_once './_common.php';

$sql_building = "SELECT * FROM a_building_dong WHERE building_id = '{$building_id}' and is_del = 0";
$res_building = sql_query($sql_building);

?>
<option value="">동을 선택해주세요.</option>
<?php if($all != ''){?>
    <option value="-1">전체</option>
<?php }?>
<?php
while($row_building = sql_fetch_array($res_building)){
?>
<option value="<?php echo $row_building['dong_id']?>"><?php echo $row_building['dong_name'];?>동</option>
<?php }?>