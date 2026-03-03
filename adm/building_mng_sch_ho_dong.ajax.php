<?php
require_once './_common.php';

$dong_sql = "SELECT * FROM a_building_dong WHERE building_id = '{$building_id}' and is_del = 0 ORDER BY dong_name asc, dong_id desc";
$dong_res = sql_query($dong_sql);
?>
<option value="">동 선택</option>
<?php
while($dong_row = sql_fetch_array($dong_res)){
?>
<option value="<?php echo $dong_row['dong_id'];?>"><?php echo $dong_row['dong_name']; ?>동</option>
<?php }?>