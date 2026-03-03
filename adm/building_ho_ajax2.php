<?php
require_once './_common.php';

//퇴실상태인 호수만 보여줌
$sql_ho = "SELECT * FROM a_building_ho WHERE dong_id = '{$dong_id}' and is_del = 0 and ho_status = 'N' ORDER BY ho_id asc";
$res_ho = sql_query($sql_ho);

?>
<option value="">호수를 선택해주세요.</option>
<?php
while($row_ho = sql_fetch_array($res_ho)){
?>
<option value="<?php echo $row_ho['ho_id']?>"><?php echo $row_ho['ho_name'];?></option>
<?php }?>