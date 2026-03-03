<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

// print_r2($user_building);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="mng_policy_wrap">
            <div class="mng_policy_content">
            <?php echo nl2br($user_building['building_policy']); ?>
            </div>
        </div>
    </div>
</div>
<?php
include_once(G5_PATH.'/tail.php');
?>