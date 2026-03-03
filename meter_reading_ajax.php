<?php
require_once "./_common.php";

$mng_building = get_mng_building($member['mb_id']);
$mng_building_t = "'".implode("','", $mng_building)."'";

$sql_where = " and building.is_use = 1 and building.building_id IN ({$mng_building_t}) ";

if($post_id){
    $sql_where .= " and building.post_id = '{$post_id}' ";
}

if($building_name != ''){
    $sql_where .= " and building.building_name like '%{$building_name}%' ";
}

$buidling_sql = "SELECT building.*, post.post_name FROM a_building as building
                LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
                WHERE building.is_del = 0 {$sql_where}
                ORDER BY building.building_name asc, building.building_id DESC";
if($_SERVER['REMOTE_ADDR'] == '59.16.155.80'){
    // echo $buidling_sql;
}
$building_res = sql_query($buidling_sql);
$building_total = sql_num_rows($building_res);
?>
<?php foreach($building_res as $row){ ?>
<li>
    <a href="/meter_reading_info.php?building_id=<?php echo $row['building_id']; ?>">
        <p class="meter_reading_area"><?php echo $row['post_name']; ?></p>
        <p class="meter_reading_building"><?php echo $row['building_name']; ?></p>
    </a>
</li>
<?php }?>
<?php if($building_total == 0){ ?>
<li class="empty_history">등록된 단지가 없습니다.</li>
<?php }?>