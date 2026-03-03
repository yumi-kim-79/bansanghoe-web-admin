<?php
include_once('./common.php');

if($_SERVER['REMOTE_ADDR'] != ADMIN_IP) {
    echo '접근불가';
    exit;
}

$today = date("Y-m-d H:i:s");

$sql = "SELECT push.*, ho.post_id FROM `a_push` as push left join a_building_ho as ho on push.recv_id = ho.ho_tenant_id WHERE push.push_type = 'info' and push.push_idx = 581 and ho.post_id = 1 and push.recv_id != '' and push.is_del = 0";
echo $sql.'<br>';
$res = sql_query($sql);

$push_idx_arr = array();
while($row = sql_fetch_array($res)){

    array_push($push_idx_arr, $row['push_id']);

}

for($i=0; $i<count($push_idx_arr); $i++){

    $push_update = "UPDATE a_push SET
                    is_del = 1,
                    deleted_at = '{$today}'
                    WHERE push_id = '{$push_idx_arr[$i]}'";
    // echo $push_update.'<br>';
    // sql_query($push_update);

}

print_r2($push_idx_arr);