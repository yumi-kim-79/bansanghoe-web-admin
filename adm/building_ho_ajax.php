<?php
require_once './_common.php';

if($ho_status == 'Y') $ho_status_sql = " and ho_status = 'Y' ";

$sql_ho = "SELECT * FROM a_building_ho WHERE dong_id = '{$dong_id}' and is_del = 0 {$ho_status_sql} ORDER BY ho_name + 1 asc, ho_id desc";

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $sql_ho;
$res_ho = sql_query($sql_ho);

?>
<option value="">호수를 선택해주세요.</option>
<?php if($type == 'complain'){?>
<option value="-1">공용부</option>
<?php }?>
<?php
while($row_ho = sql_fetch_array($res_ho)){
?>
<option value="<?php echo $row_ho['ho_id']?>"><?php echo $row_ho['ho_name'];?>호</option>
<?php }?>