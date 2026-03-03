<?php
require_once './_common.php';

$sql_ho = "SELECT * FROM a_building_ho WHERE ho_id = '{$ho_id}'";
$row_ho = sql_fetch($sql_ho);

$mb = get_user_hp($row_ho['ho_tenant_hp']);

echo $mb['mb_id'].'|'.$row_ho['ho_tenant'].'|'.$row_ho['ho_tenant_hp'];
?>