<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

//print_r2($user_building);
$building_sql = "SELECT building.*, post.post_name FROM a_building as building
                 LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
                 WHERE building.building_id = '{$building_id}'";
$building_row = sql_fetch($building_sql);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="mng_policy_wrap">
            <div class="mng_policy_content">
                <?php echo nl2br($building_row['building_policy']); ?>
            </div>
        </div>
    </div>
</div>
<?php
include_once(G5_PATH.'/tail.php');
?>